<?php

namespace Database\Seeders;

use App\Models\CatalogCategory;
use App\Models\CatalogItem;
use App\Models\CatalogItemVariant;
use App\Models\MediaAsset;
use App\Services\MediaAttachmentService;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Storage;

class SmoothieReferenceSeeder extends Seeder
{
    private MediaAttachmentService $attachments;

    public function __construct()
    {
        $this->attachments = app(MediaAttachmentService::class);
    }

    public function run(): void
    {
        $media = $this->importMedia('smoothie.svg', 'Smoothie illustration');
        $categories = $this->seedCategories($media);
        $this->seedItems($categories, $media);
    }

    private function seedCategories(MediaAsset $media): array
    {
        $categories = [
            'fruit-smoothies' => ['Fruit Smoothies', 'Fruit-forward smoothie options made for refreshing store visits.', true, 10],
            'protein-smoothies' => ['Protein Smoothies', 'Smoothie options with protein-style ingredients.', true, 20],
            'tropical-smoothies' => ['Tropical Smoothies', 'Mango, pineapple and tropical-style smoothie options.', false, 30],
            'berry-smoothies' => ['Berry Smoothies', 'Berry-style smoothie options with bright fruit flavor.', false, 40],
        ];

        $seeded = [];

        foreach ($categories as $slug => [$name, $description, $featured, $sortOrder]) {
            $category = $this->firstOrRestore(CatalogCategory::class, ['slug' => $slug], [
                'name' => $name,
                'slug' => $slug,
                'business_area' => 'smoothie',
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

            $this->attachIfEmpty($category, 'image', $media, $name);
            $seeded[$slug] = $category;
        }

        return $seeded;
    }

    private function seedItems(array $categories, MediaAsset $media): void
    {
        $items = [
            ['Strawberry Banana Smoothie', 'strawberry-banana-smoothie', 'fruit-smoothies', 'A strawberry and banana smoothie option available at the store.', 'Strawberry, banana, milk or smoothie base', true, 10],
            ['Mango Pineapple Smoothie', 'mango-pineapple-smoothie', 'tropical-smoothies', 'A mango and pineapple smoothie option with tropical fruit flavor.', 'Mango, pineapple, smoothie base', true, 20],
            ['Mixed Berry Smoothie', 'mixed-berry-smoothie', 'berry-smoothies', 'A mixed berry smoothie option with strawberry, blueberry and raspberry.', 'Strawberry, blueberry, raspberry, smoothie base', true, 30],
            ['Tropical Mango Smoothie', 'tropical-mango-smoothie', 'tropical-smoothies', 'A mango-forward tropical smoothie option.', 'Mango, tropical fruit blend, smoothie base', false, 40],
            ['Banana Protein Smoothie', 'banana-protein-smoothie', 'protein-smoothies', 'A banana protein-style smoothie option.', 'Banana, protein blend, milk or smoothie base', false, 50],
            ['Berry Protein Smoothie', 'berry-protein-smoothie', 'protein-smoothies', 'A berry protein-style smoothie option.', 'Mixed berries, protein blend, smoothie base', false, 60],
        ];

        foreach ($items as [$name, $slug, $categorySlug, $description, $ingredients, $featured, $sortOrder]) {
            $item = $this->firstOrRestore(CatalogItem::class, ['slug' => $slug], [
                'catalog_category_id' => $categories[$categorySlug]->id,
                'name' => $name,
                'slug' => $slug,
                'item_type' => 'smoothie',
                'short_description' => $description,
                'description' => $description,
                'ingredients' => $ingredients,
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
                'meta_description' => 'Explore our '.$name.' option.',
            ]);

            $this->attachIfEmpty($item, 'primary_image', $media, $name);
            $this->seedVariants($item);
        }
    }

    private function seedVariants(CatalogItem $item): void
    {
        foreach ([['Small', false, 10], ['Medium', true, 20], ['Large', false, 30]] as [$name, $default, $sortOrder]) {
            CatalogItemVariant::firstOrCreate([
                'catalog_item_id' => $item->id,
                'name' => $name,
            ], [
                'price' => null,
                'compare_at_price' => null,
                'price_label' => 'Ask in store',
                'is_default' => $default,
                'is_available' => true,
                'sort_order' => $sortOrder,
            ]);
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
