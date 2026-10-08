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

class AdultRetailReferenceSeeder extends Seeder
{
    private MediaAttachmentService $attachments;

    public function __construct()
    {
        $this->attachments = app(MediaAttachmentService::class);
    }

    public function run(): void
    {
        $media = $this->importMedia('vape.svg', 'Adult retail vape illustration');
        $categories = $this->seedCategories($media);
        $this->seedItems($categories, $media);
    }

    private function seedCategories(MediaAsset $media): array
    {
        $categories = [
            'vape-devices' => ['Vape Devices', 'Generic adult retail vape device reference category.', true, 10],
            'vape-accessories' => ['Vape Accessories', 'Generic vape accessory reference category.', false, 20],
            'tobacco-products' => ['Tobacco Products', 'Generic tobacco product reference category.', false, 30],
            'rolling-accessories' => ['Rolling Accessories', 'Generic rolling accessory reference category.', false, 40],
            'adult-retail-accessories' => ['Adult Retail Accessories', 'Generic adult retail accessory reference category.', false, 50],
        ];

        $seeded = [];

        foreach ($categories as $slug => [$name, $description, $featured, $sortOrder]) {
            $category = $this->firstOrRestore(CatalogCategory::class, ['slug' => $slug], [
                'name' => $name,
                'slug' => $slug,
                'business_area' => 'adult_retail',
                'description' => $description,
                'minimum_age' => 21,
                'is_featured' => $featured,
                'is_active' => true,
                'sort_order' => $sortOrder,
                'page_name' => $name,
                'seo_title' => $name,
                'seo_description' => $description,
                'meta_title' => $name,
                'meta_description' => $description,
            ]);

            $this->attachIfEmpty($category, 'image', $media, $name);
            $seeded[$slug] = $category;
        }

        return $seeded;
    }

    private function seedItems(array $categories, MediaAsset $media): void
    {
        $items = [
            ['Rechargeable Vape Device', 'rechargeable-vape-device', 'vape-devices', 'Generic rechargeable vape device reference item.', true, 10],
            ['Disposable Vape Device', 'disposable-vape-device', 'vape-devices', 'Generic disposable vape device reference item.', true, 20],
            ['Replacement Pod', 'replacement-pod', 'vape-accessories', 'Generic replacement pod reference item.', false, 30],
            ['USB-C Vape Charging Cable', 'usb-c-vape-charging-cable', 'vape-accessories', 'Generic USB-C charging cable for compatible vape devices.', false, 40],
            ['Rolling Papers', 'rolling-papers', 'rolling-accessories', 'Generic rolling papers reference item.', false, 50],
            ['Lighter', 'lighter', 'adult-retail-accessories', 'Generic lighter reference item.', false, 60],
        ];

        foreach ($items as [$name, $slug, $categorySlug, $description, $featured, $sortOrder]) {
            $item = $this->firstOrRestore(CatalogItem::class, ['slug' => $slug], [
                'catalog_category_id' => $categories[$categorySlug]->id,
                'name' => $name,
                'slug' => $slug,
                'item_type' => 'adult_product',
                'short_description' => $description,
                'description' => $description,
                'price' => null,
                'compare_at_price' => null,
                'price_label' => 'Ask in store',
                'is_price_visible' => false,
                'minimum_age' => 21,
                'is_age_restricted' => true,
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

            $this->attachIfEmpty($item, 'primary_image', $media, $name);
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
            'disk' => 'public', 'directory' => $directory, 'path' => $path,
            'file_name' => $fileName, 'original_name' => $fileName,
            'mime_type' => File::mimeType($source) ?: 'image/svg+xml',
            'extension' => 'svg', 'file_size' => File::size($source),
            'width' => 960, 'height' => 640, 'checksum' => $checksum,
            'title' => $title, 'alt_text' => $title, 'source_url' => null, 'is_active' => true,
        ]);
    }

    private function attachIfEmpty(Model $model, string $collection, MediaAsset $media, string $alt): void
    {
        if (! $model->getMediaAttachment($collection)) {
            $this->attachments->syncSingle($model, $collection, $media->id, ['alt_text_override' => $alt, 'title_override' => $alt]);
        }
    }

    private function firstOrRestore(string $class, array $attributes, array $defaults): Model
    {
        $query = in_array(\Illuminate\Database\Eloquent\SoftDeletes::class, class_uses_recursive($class), true) ? $class::withTrashed() : $class::query();
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
