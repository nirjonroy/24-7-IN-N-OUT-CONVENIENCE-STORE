<?php

namespace App\Services;

use App\Models\GalleryCategory;
use App\Models\GalleryItem;
use App\Models\MediaAttachment;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Collection;

class FrontendGalleryService
{
    public function forPage(array $fallback): array
    {
        $items = $this->items();

        if ($items->isEmpty()) {
            return [
                'source' => 'fallback',
                'categories' => collect(),
                'items' => $this->fallbackItems($fallback),
                'featuredItems' => collect(),
            ];
        }

        $categoryIds = $items
            ->pluck('category_id')
            ->filter()
            ->unique()
            ->values();

        $categories = GalleryCategory::active()
            ->whereIn('id', $categoryIds)
            ->orderBy('sort_order')
            ->orderBy('id')
            ->get(['id', 'name', 'slug', 'sort_order'])
            ->map(fn (GalleryCategory $category) => [
                'id' => $category->id,
                'name' => $category->name,
                'slug' => $category->slug,
            ]);

        return [
            'source' => 'dynamic',
            'categories' => $categories,
            'items' => $items,
            'featuredItems' => $items->where('is_featured', true)->values(),
        ];
    }

    private function items(): Collection
    {
        return GalleryItem::active()
            ->where(fn (Builder $query) => $query
                ->whereNull('gallery_category_id')
                ->orWhereHas('category', fn (Builder $category) => $category->active()))
            ->with(['category', 'mediaAttachments.media.variants'])
            ->orderByDesc('is_featured')
            ->orderBy('sort_order')
            ->orderBy('id')
            ->limit(24)
            ->get()
            ->map(fn (GalleryItem $item) => $this->mapItem($item))
            ->filter()
            ->values();
    }

    private function mapItem(GalleryItem $item): ?array
    {
        $attachment = $this->imageAttachment($item);

        if (! $attachment?->media?->is_active) {
            return null;
        }

        $media = $attachment->media;

        return [
            'id' => $item->id,
            'title' => $item->title,
            'caption' => $item->caption,
            'description' => $item->description,
            'category_id' => $item->category?->id,
            'category_name' => $item->category?->name,
            'is_featured' => $item->is_featured,
            'image' => $media->getVariantUrl('thumbnail'),
            'large_image' => $media->getVariantUrl('large'),
            'alt' => $attachment->alt_text_override ?: ($media->alt_text ?: ($item->title ?: '')),
            'ratio' => 'aspect-[3/2]',
            'width' => $media->getVariant('thumbnail')?->width ?: ($media->width ?: 720),
            'height' => $media->getVariant('thumbnail')?->height ?: ($media->height ?: 480),
        ];
    }

    private function imageAttachment(GalleryItem $item): ?MediaAttachment
    {
        return $item->mediaAttachments
            ->where('collection', 'image')
            ->sortBy([
                ['is_primary', 'desc'],
                ['sort_order', 'asc'],
                ['id', 'asc'],
            ])
            ->first();
    }

    private function fallbackItems(array $fallback): Collection
    {
        return collect($fallback['items'] ?? [])->map(fn (array $item) => [
            'id' => null,
            'title' => $item['title'] ?? null,
            'caption' => $item['caption'] ?? ($item['title'] ?? null),
            'description' => null,
            'category_id' => null,
            'category_name' => null,
            'is_featured' => false,
            'image' => $item['image'] ?? null,
            'large_image' => $item['image'] ?? null,
            'alt' => $item['alt'] ?? ($item['title'] ?? ''),
            'ratio' => $item['ratio'] ?? 'aspect-[3/2]',
            'width' => $item['width'] ?? 720,
            'height' => $item['height'] ?? 480,
        ])->filter(fn (array $item) => $item['image'])->values();
    }
}
