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

class PhoneAccessoryReferenceSeeder extends Seeder
{
    public function run(): void
    {
        $category = $this->firstOrRestore(CatalogCategory::class, ['slug' => 'phone-accessories'], [
            'name' => 'Phone Accessories',
            'slug' => 'phone-accessories',
            'business_area' => 'phone_accessory',
            'description' => 'Generic phone accessories available for in-store questions.',
            'is_featured' => true,
            'is_active' => true,
            'sort_order' => 10,
            'page_name' => 'Phone Accessories',
            'seo_title' => 'Phone Accessories',
            'seo_description' => 'Reference phone accessory products available for in-store questions.',
            'meta_title' => 'Phone Accessories',
            'meta_description' => 'Generic phone accessories including screen protectors, cables, chargers and cases.',
        ]);

        $media = $this->importMedia();
        $attachments = app(MediaAttachmentService::class);

        foreach ($this->items() as $item) {
            $model = $this->firstOrRestore(CatalogItem::class, ['slug' => $item['slug']], array_merge($item, [
                'catalog_category_id' => $category->id,
                'item_type' => 'phone_accessory',
                'price' => null,
                'compare_at_price' => null,
                'price_label' => 'Ask in store',
                'is_price_visible' => false,
                'is_age_restricted' => false,
                'is_available' => true,
                'is_active' => true,
                'page_name' => $item['name'],
                'seo_title' => $item['name'],
                'seo_description' => $item['short_description'],
                'meta_title' => $item['name'],
                'meta_description' => $item['short_description'],
            ]));

            if (! $model->getMediaAttachment('image')) {
                $attachments->syncSingle($model, 'image', $media->id, [
                    'alt_text_override' => $model->name,
                    'title_override' => $model->name,
                ]);
            }
        }
    }

    private function items(): array
    {
        return [
            ['name' => 'Tempered Glass Screen Protector', 'slug' => 'tempered-glass-screen-protector', 'short_description' => 'Generic tempered glass screen protector for supported phones.', 'is_featured' => true, 'sort_order' => 10],
            ['name' => 'USB-C Charging Cable', 'slug' => 'usb-c-charging-cable', 'short_description' => 'Generic USB-C charging cable for compatible devices.', 'is_featured' => true, 'sort_order' => 20],
            ['name' => '20W USB-C Wall Charger', 'slug' => '20w-usb-c-wall-charger', 'short_description' => 'Generic USB-C wall charger for compatible phones and accessories.', 'is_featured' => false, 'sort_order' => 30],
            ['name' => 'Protective Phone Case', 'slug' => 'protective-phone-case', 'short_description' => 'Generic protective case options for selected phone models.', 'is_featured' => true, 'sort_order' => 40],
            ['name' => 'Wireless Charging Pad', 'slug' => 'wireless-charging-pad', 'short_description' => 'Generic wireless charging pad for compatible devices.', 'is_featured' => false, 'sort_order' => 50],
        ];
    }

    private function importMedia(): MediaAsset
    {
        $source = database_path('seeders/assets/repair/accessory.svg');
        $checksum = hash_file('sha256', $source);
        $existing = MediaAsset::active()->where('checksum', $checksum)->first();

        if ($existing) {
            return $existing;
        }

        $directory = 'media/reference/repair';
        $fileName = 'accessory.svg';
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
            'title' => 'Phone accessory illustration',
            'alt_text' => 'Phone accessory illustration',
            'source_url' => null,
            'is_active' => true,
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
