<?php

namespace Database\Seeders;

use App\Models\CatalogCategory;
use App\Models\CatalogItem;
use App\Models\MediaAsset;
use App\Services\MediaAttachmentService;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Storage;

class ConvenienceStoreReferenceSeeder extends Seeder
{
    private MediaAttachmentService $attachments;

    public function __construct()
    {
        $this->attachments = app(MediaAttachmentService::class);
    }

    public function run(): void
    {
        $media = [
            'snack' => $this->importMedia('snack.svg', 'Convenience store snacks illustration'),
            'drink' => $this->importMedia('drink.svg', 'Cold drink illustration'),
            'candy' => $this->importMedia('candy.svg', 'Candy illustration'),
        ];

        $categories = $this->seedCategories($media);
        $this->seedItems($categories, $media);
    }

    private function seedCategories(array $media): array
    {
        $categories = [
            'snacks' => ['Snacks', 'Bagged snacks and quick bites for everyday visits.', true, 10, 'snack'],
            'candy-chocolate' => ['Candy & Chocolate', 'Sweet candy and chocolate-style reference products.', true, 20, 'candy'],
            'cold-drinks' => ['Cold Drinks', 'Chilled soft drinks and refreshing bottled beverages.', true, 30, 'drink'],
            'energy-drinks' => ['Energy Drinks', 'Generic energy drink options available in store.', false, 40, 'drink'],
            'water-juice' => ['Water & Juice', 'Bottled water and juice-style drink options.', false, 50, 'drink'],
            'everyday-essentials' => ['Everyday Essentials', 'Common convenience items for quick store trips.', false, 60, 'snack'],
        ];

        $seeded = [];

        foreach ($categories as $slug => [$name, $description, $featured, $sortOrder, $asset]) {
            $category = $this->firstOrRestore(CatalogCategory::class, ['slug' => $slug], [
                'name' => $name,
                'slug' => $slug,
                'business_area' => 'convenience',
                'description' => $description,
                'is_featured' => $featured,
                'is_active' => true,
                'sort_order' => $sortOrder,
                'page_name' => $name,
                'seo_title' => $name,
                'seo_description' => $description,
                'meta_title' => $name,
                'meta_description' => $description,
            ]);

            $this->attachIfEmpty($category, 'image', $media[$asset], $name);
            $seeded[$slug] = $category;
        }

        return $seeded;
    }

    private function seedItems(array $categories, array $media): void
    {
        $items = [
            ['Classic Potato Chips', 'classic-potato-chips', 'snacks', 'Generic classic-style potato chips for quick snack trips.', true, 10, 'snack'],
            ['Nacho Cheese Tortilla Chips', 'nacho-cheese-tortilla-chips', 'snacks', 'Generic nacho cheese tortilla chips for snacking.', false, 20, 'snack'],
            ['Chocolate Bar', 'chocolate-bar', 'candy-chocolate', 'Generic chocolate bar reference item.', true, 30, 'candy'],
            ['Gummy Candy', 'gummy-candy', 'candy-chocolate', 'Generic gummy candy reference item.', false, 40, 'candy'],
            ['Cola Soft Drink', 'cola-soft-drink', 'cold-drinks', 'Generic cola-style soft drink option.', true, 50, 'drink'],
            ['Lemon-Lime Soft Drink', 'lemon-lime-soft-drink', 'cold-drinks', 'Generic lemon-lime soft drink option.', false, 60, 'drink'],
            ['Energy Drink', 'energy-drink', 'energy-drinks', 'Generic energy drink reference item.', true, 70, 'drink'],
            ['Bottled Water', 'bottled-water', 'water-juice', 'Bottled water option available for in-store questions.', true, 80, 'drink'],
            ['Orange Juice', 'orange-juice', 'water-juice', 'Generic orange juice reference item.', false, 90, 'drink'],
        ];

        foreach ($items as [$name, $slug, $categorySlug, $description, $featured, $sortOrder, $asset]) {
            $item = $this->firstOrRestore(CatalogItem::class, ['slug' => $slug], [
                'catalog_category_id' => $categories[$categorySlug]->id,
                'name' => $name,
                'slug' => $slug,
                'item_type' => 'product',
                'short_description' => $description,
                'description' => $description,
                'price' => null,
                'compare_at_price' => null,
                'price_label' => 'Ask in store',
                'is_price_visible' => false,
                'is_featured' => $featured,
                'is_available' => true,
                'is_active' => true,
                'sort_order' => $sortOrder,
                'page_name' => $name,
                'seo_title' => $name,
                'seo_description' => $description,
                'meta_title' => $name,
                'meta_description' => $description,
            ]);

            $this->attachIfEmpty($item, 'primary_image', $media[$asset], $name);
        }
    }

    private function importMedia(string $fileName, string $title): MediaAsset
    {
        $source = database_path('seeders/assets/repair/'.$fileName);
        $checksum = hash_file('sha256', $source);
        $existing = MediaAsset::active()->where('checksum', $checksum)->first();

        if ($existing) {
            return $existing;
        }

        $directory = 'media/reference/catalog';
        $path = $directory.'/'.$fileName;
        Storage::disk('public')->put($path, File::get($source));

        return MediaAsset::create([
            'disk' => 'public',
            'directory' => $directory,
            'path' => $path,
            'file_name' => $fileName,
            'original_name' => $fileName,
            'mime_type' => File::mimeType($source) ?: 'image/svg+xml',
            'extension' => 'svg',
            'file_size' => File::size($source),
            'width' => 960,
            'height' => 640,
            'checksum' => $checksum,
            'title' => $title,
            'alt_text' => $title,
            'source_url' => null,
            'is_active' => true,
        ]);
    }

    private function attachIfEmpty(Model $model, string $collection, MediaAsset $media, string $alt): void
    {
        if ($model->getMediaAttachment($collection)) {
            return;
        }

        $this->attachments->syncSingle($model, $collection, $media->id, [
            'alt_text_override' => $alt,
            'title_override' => $alt,
        ]);
    }

    private function firstOrRestore(string $class, array $attributes, array $defaults): Model
    {
        $query = in_array(\Illuminate\Database\Eloquent\SoftDeletes::class, class_uses_recursive($class), true)
            ? $class::withTrashed()
            : $class::query();
        $model = $query->where($attributes)->first();

        if (! $model) {
            return $class::create($defaults);
        }

        if (method_exists($model, 'trashed') && $model->trashed()) {
            $model->restore();
        }

        return $model;
    }
}
