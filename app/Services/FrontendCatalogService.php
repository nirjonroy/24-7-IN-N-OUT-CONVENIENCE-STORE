<?php

namespace App\Services;

use App\Models\CatalogCategory;
use App\Models\CatalogItem;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Collection;

class FrontendCatalogService
{
    public const AREAS = [
        'convenience-store' => 'convenience',
        'smoothies' => 'smoothie',
        'vape-tobacco' => 'adult_retail',
    ];

    public function forArea(string $area, array $fallback): array
    {
        $categories = $this->categories($area, $fallback);
        $items = $this->items($area, $fallback);
        $featuredItems = $items->where('is_featured', true)->values();
        $regularItems = $items->reject(fn ($item) => $item['is_featured'])->values();

        return [
            'area' => $area,
            'categories' => $categories,
            'items' => $items,
            'featuredItems' => $featuredItems,
            'regularItems' => $regularItems,
            'hasCategories' => $categories->isNotEmpty(),
            'hasItems' => $items->isNotEmpty(),
            'hasFeaturedItems' => $featuredItems->isNotEmpty(),
            'ageRequirement' => $this->ageRequirement($area, $categories, $items),
            'itemCount' => $items->count(),
        ];
    }

    private function categories(string $area, array $fallback): Collection
    {
        return CatalogCategory::active()
            ->businessArea($area)
            ->with([
                'children' => fn ($query) => $query->active()->businessArea($area)->with('mediaAttachments.media.variants'),
                'mediaAttachments.media.variants',
            ])
            ->orderBy('sort_order')
            ->orderBy('id')
            ->get()
            ->map(fn (CatalogCategory $category) => $this->categoryData($category, $fallback))
            ->values();
    }

    private function items(string $area, array $fallback): Collection
    {
        return CatalogItem::active()
            ->whereHas('category', fn ($query) => $query->active()->businessArea($area))
            ->with([
                'category',
                'mediaAttachments.media.variants',
                'variants' => fn ($query) => $query->where('is_available', true)->orderBy('sort_order')->orderBy('id'),
            ])
            ->orderByDesc('is_featured')
            ->orderBy('sort_order')
            ->orderBy('id')
            ->limit(24)
            ->get()
            ->map(fn (CatalogItem $item) => $this->itemData($item, $fallback, $area))
            ->values();
    }

    private function categoryData(CatalogCategory $category, array $fallback): array
    {
        return [
            'id' => $category->id,
            'name' => $category->name,
            'slug' => $category->slug,
            'description' => $category->description,
            'minimum_age' => $category->minimum_age,
            'is_featured' => $category->is_featured,
            'image' => $this->mediaUrl($category, 'image', $fallback['image'] ?? null, 'medium'),
            'image_alt' => $this->mediaAlt($category, 'image', $category->name),
            'children' => $category->children->map(fn (CatalogCategory $child) => $this->categoryData($child, $fallback))->values(),
        ];
    }

    private function itemData(CatalogItem $item, array $fallback, string $area): array
    {
        $variants = $area === 'smoothie'
            ? $item->variants->map(fn ($variant) => [
                'name' => $variant->name,
                'price' => $this->money($variant->price),
                'compare_at_price' => $this->money($variant->compare_at_price),
                'price_label' => $variant->price_label,
                'is_default' => $variant->is_default,
                'is_available' => $variant->is_available,
            ])->values()
            : collect();

        return [
            'id' => $item->id,
            'name' => $item->name,
            'slug' => $item->slug,
            'category' => $item->category?->name,
            'category_slug' => $item->category?->slug,
            'item_type' => $item->item_type,
            'brand' => $item->brand,
            'short_description' => $item->short_description,
            'description' => $item->description,
            'ingredients' => $item->ingredients,
            'size_label' => $item->size_label,
            'price' => $this->money($item->price),
            'compare_at_price' => $this->money($item->compare_at_price),
            'price_label' => $item->price_label,
            'is_price_visible' => $item->is_price_visible,
            'display_price' => $this->displayPrice($item, $variants),
            'is_featured' => $item->is_featured,
            'is_available' => $item->is_available,
            'is_age_restricted' => $item->is_age_restricted,
            'minimum_age' => $item->minimum_age ?: $item->category?->minimum_age,
            'image' => $this->mediaUrl($item, 'primary_image', $fallback['image'] ?? null, 'medium'),
            'image_alt' => $this->mediaAlt($item, 'primary_image', $item->name),
            'variants' => $variants,
        ];
    }

    private function displayPrice(CatalogItem $item, Collection $variants): ?string
    {
        if (! $item->is_price_visible) {
            return $item->price_label ?: null;
        }

        $defaultVariant = $variants->firstWhere('is_default', true) ?: $variants->first();

        if ($defaultVariant && ($defaultVariant['price'] || $defaultVariant['price_label'])) {
            return $defaultVariant['price'] ?: $defaultVariant['price_label'];
        }

        return $this->money($item->price) ?: $item->price_label;
    }

    private function ageRequirement(string $area, Collection $categories, Collection $items): ?int
    {
        $ages = $categories->pluck('minimum_age')
            ->merge($items->pluck('minimum_age'))
            ->filter()
            ->map(fn ($age) => (int) $age);

        if ($area === 'adult_retail') {
            $ages->push(21);
        }

        return $ages->max();
    }

    private function money($value): ?string
    {
        if ($value === null || $value === '') {
            return null;
        }

        return '$'.number_format((float) $value, 2);
    }

    private function mediaUrl(Model $model, string $collection, ?string $fallback = null, ?string $variant = null): ?string
    {
        if (! $model->relationLoaded('mediaAttachments')) {
            return $fallback;
        }

        $attachment = $model->mediaAttachments
            ->where('collection', $collection)
            ->sortBy([
                ['is_primary', 'desc'],
                ['sort_order', 'asc'],
                ['id', 'asc'],
            ])
            ->first();

        if (! $attachment?->media) {
            return $fallback;
        }

        return $variant ? $attachment->media->getVariantUrl($variant) : $attachment->media->url;
    }

    private function mediaAlt(Model $model, string $collection, string $fallback): string
    {
        if (! $model->relationLoaded('mediaAttachments')) {
            return $fallback;
        }

        $attachment = $model->mediaAttachments
            ->where('collection', $collection)
            ->sortBy([
                ['is_primary', 'desc'],
                ['sort_order', 'asc'],
                ['id', 'asc'],
            ])
            ->first();

        return $attachment?->alt_text_override ?: ($attachment?->media?->alt_text ?: $fallback);
    }
}
