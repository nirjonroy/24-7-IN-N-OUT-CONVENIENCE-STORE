<?php

namespace Database\Seeders;

use App\Models\DeviceBrand;
use App\Models\DeviceModel;
use App\Models\MediaAsset;
use App\Models\RepairService;
use App\Models\RepairServicePrice;
use App\Services\MediaAttachmentService;
use Illuminate\Database\Seeder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Storage;

class RepairReferenceSeeder extends Seeder
{
    private MediaAttachmentService $attachments;

    public function __construct()
    {
        $this->attachments = app(MediaAttachmentService::class);
    }

    public function run(): void
    {
        $media = $this->mediaAssets();
        $brands = $this->seedBrands();
        $models = $this->seedModels($brands);
        $services = $this->seedServices();

        foreach (['iphone-16-pro-max', 'iphone-15-pro-max', 'samsung-galaxy-s24-ultra', 'google-pixel-8-pro'] as $slug) {
            $this->attachIfEmpty($models[$slug], 'image', $media['generic-phone'], $models[$slug]->name);
        }

        foreach ($services as $slug => $service) {
            $assetKey = match ($slug) {
                'screen-repair' => 'screen-repair',
                'battery-replacement' => 'battery',
                'charging-port-repair' => 'charging-port',
                'camera-repair' => 'camera',
                'speaker-repair' => 'speaker',
                'water-damage' => 'water-damage',
                'back-glass-repair' => 'back-glass',
                default => 'diagnostic',
            };

            $this->attachIfEmpty($service, 'image', $media[$assetKey], $service->title);
        }

        $this->seedPrices($models, $services);
    }

    private function seedBrands(): array
    {
        $brands = [
            'apple' => [
                'name' => 'Apple',
                'description' => 'Repair support for selected Apple iPhone and iPad devices.',
                'is_featured' => true,
                'sort_order' => 10,
                'page_name' => 'Apple Device Repair',
                'seo_title' => 'Apple Device Repair',
                'seo_description' => 'Repair support for selected Apple iPhone and iPad devices.',
                'meta_title' => 'Apple Device Repair',
                'meta_description' => 'Professional repair options for supported Apple devices, including screen, battery and charging issues.',
            ],
            'samsung' => [
                'name' => 'Samsung',
                'description' => 'Repair support for selected Samsung Galaxy phones and tablets.',
                'is_featured' => true,
                'sort_order' => 20,
                'page_name' => 'Samsung Device Repair',
                'seo_title' => 'Samsung Device Repair',
                'seo_description' => 'Repair support for selected Samsung Galaxy devices.',
                'meta_title' => 'Samsung Device Repair',
                'meta_description' => 'Repair options for supported Samsung devices, including display, battery, charging and camera issues.',
            ],
            'google' => [
                'name' => 'Google',
                'description' => 'Repair support for selected Google Pixel phones.',
                'is_featured' => true,
                'sort_order' => 30,
                'page_name' => 'Google Pixel Repair',
                'seo_title' => 'Google Pixel Repair',
                'seo_description' => 'Repair support for selected Google Pixel devices.',
                'meta_title' => 'Google Pixel Repair',
                'meta_description' => 'Repair options for supported Google Pixel phones, including screen, battery and charging issues.',
            ],
        ];

        return collect($brands)->mapWithKeys(function (array $defaults, string $slug) {
            return [$slug => $this->firstOrRestore(DeviceBrand::class, ['slug' => $slug], array_merge($defaults, [
                'slug' => $slug,
                'is_active' => true,
            ]))];
        })->all();
    }

    private function seedModels(array $brands): array
    {
        $models = [
            ['brand' => 'apple', 'name' => 'iPhone 16 Pro Max', 'slug' => 'iphone-16-pro-max', 'device_type' => 'phone', 'release_year' => 2024, 'is_featured' => true, 'sort_order' => 10],
            ['brand' => 'apple', 'name' => 'iPhone 15 Pro Max', 'slug' => 'iphone-15-pro-max', 'device_type' => 'phone', 'release_year' => 2023, 'is_featured' => true, 'sort_order' => 20],
            ['brand' => 'apple', 'name' => 'iPhone 15', 'slug' => 'iphone-15', 'device_type' => 'phone', 'release_year' => 2023, 'is_featured' => false, 'sort_order' => 30],
            ['brand' => 'apple', 'name' => 'iPhone 14 Pro Max', 'slug' => 'iphone-14-pro-max', 'device_type' => 'phone', 'release_year' => 2022, 'is_featured' => false, 'sort_order' => 40],
            ['brand' => 'apple', 'name' => 'iPad Air 5', 'slug' => 'ipad-air-5', 'device_type' => 'tablet', 'release_year' => 2022, 'is_featured' => false, 'sort_order' => 50],
            ['brand' => 'samsung', 'name' => 'Samsung Galaxy S24 Ultra', 'slug' => 'samsung-galaxy-s24-ultra', 'device_type' => 'phone', 'release_year' => 2024, 'is_featured' => true, 'sort_order' => 10],
            ['brand' => 'samsung', 'name' => 'Samsung Galaxy S23 Ultra', 'slug' => 'samsung-galaxy-s23-ultra', 'device_type' => 'phone', 'release_year' => 2023, 'is_featured' => false, 'sort_order' => 20],
            ['brand' => 'samsung', 'name' => 'Samsung Galaxy Z Flip 5', 'slug' => 'samsung-galaxy-z-flip-5', 'device_type' => 'phone', 'release_year' => 2023, 'is_featured' => false, 'sort_order' => 30],
            ['brand' => 'samsung', 'name' => 'Samsung Galaxy Z Fold 5', 'slug' => 'samsung-galaxy-z-fold-5', 'device_type' => 'phone', 'release_year' => 2023, 'is_featured' => false, 'sort_order' => 40],
            ['brand' => 'samsung', 'name' => 'Samsung Galaxy Tab S9', 'slug' => 'samsung-galaxy-tab-s9', 'device_type' => 'tablet', 'release_year' => 2023, 'is_featured' => false, 'sort_order' => 50],
            ['brand' => 'google', 'name' => 'Google Pixel 8 Pro', 'slug' => 'google-pixel-8-pro', 'device_type' => 'phone', 'release_year' => 2023, 'is_featured' => true, 'sort_order' => 10],
            ['brand' => 'google', 'name' => 'Google Pixel 8', 'slug' => 'google-pixel-8', 'device_type' => 'phone', 'release_year' => 2023, 'is_featured' => false, 'sort_order' => 20],
        ];

        $seeded = [];

        foreach ($models as $model) {
            $name = $model['name'];
            $seeded[$model['slug']] = $this->firstOrRestore(DeviceModel::class, [
                'device_brand_id' => $brands[$model['brand']]->id,
                'slug' => $model['slug'],
            ], [
                'device_brand_id' => $brands[$model['brand']]->id,
                'name' => $name,
                'slug' => $model['slug'],
                'device_type' => $model['device_type'],
                'release_year' => $model['release_year'],
                'description' => 'Available repair services for the '.$name.'.',
                'is_featured' => $model['is_featured'],
                'is_active' => true,
                'sort_order' => $model['sort_order'],
                'page_name' => $name.' Repair',
                'seo_title' => $name.' Repair',
                'seo_description' => 'Available repair services for the '.$name.'.',
                'meta_title' => $name.' Repair',
                'meta_description' => 'Repair options for the '.$name.', including selected display, battery and charging issues.',
            ]);
        }

        return $seeded;
    }

    private function seedServices(): array
    {
        $services = [
            'screen-repair' => ['Screen Repair', 'screen_repair', 'Repair service for cracked, damaged or non-responsive phone displays. Final availability and pricing depend on the selected device.', true, 10],
            'battery-replacement' => ['Battery Replacement', 'battery_replacement', 'Battery replacement for devices with rapid discharge, unexpected shutdown or reduced battery performance.', true, 20],
            'charging-port-repair' => ['Charging Port Repair', 'charging_port', 'Diagnosis and repair for devices that charge intermittently or no longer recognize a charging cable.', true, 30],
            'camera-repair' => ['Camera Repair', 'camera_repair', 'Repair options for devices with camera focus, lens or image capture issues.', false, 40],
            'speaker-repair' => ['Speaker Repair', 'speaker_repair', 'Repair support for devices with low, distorted or missing speaker audio.', false, 50],
            'water-damage' => ['Water Damage Diagnostic', 'water_damage', 'Diagnostic service for devices exposed to liquid or moisture. Repair availability depends on inspection results.', true, 60],
            'back-glass-repair' => ['Back Glass Repair', 'back_glass', 'Repair option for cracked or damaged back glass on supported devices.', false, 70],
            'power-button-repair' => ['Power Button Repair', 'other', 'Repair support for devices with stuck, loose or non-responsive power buttons.', false, 80],
            'device-diagnostic' => ['Device Diagnostic', 'diagnostic', 'General diagnostic review for devices with uncertain symptoms or multiple issues.', true, 90],
        ];

        return collect($services)->mapWithKeys(function (array $data, string $slug) {
            [$title, $repairType, $description, $featured, $sortOrder] = $data;

            return [$slug => $this->firstOrRestore(RepairService::class, ['slug' => $slug], [
                'title' => $title,
                'slug' => $slug,
                'repair_type' => $repairType,
                'short_description' => $description,
                'description' => $description,
                'starting_price' => null,
                'compare_at_price' => null,
                'price_note' => 'Request quote',
                'is_price_visible' => false,
                'estimated_minutes_min' => null,
                'estimated_minutes_max' => null,
                'warranty_text' => null,
                'diagnostic_required' => in_array($repairType, ['water_damage', 'diagnostic'], true),
                'is_featured' => $featured,
                'is_active' => true,
                'sort_order' => $sortOrder,
                'page_name' => $title,
                'seo_title' => $title === 'Screen Repair' ? 'Phone Screen Repair' : $title,
                'seo_description' => $description,
                'meta_title' => $title === 'Screen Repair' ? 'Phone Screen Repair' : $title,
                'meta_description' => $description,
            ])];
        })->all();
    }

    private function seedPrices(array $models, array $services): void
    {
        $map = [
            'iphone-15-pro-max' => ['screen-repair', 'battery-replacement', 'charging-port-repair', 'camera-repair', 'speaker-repair', 'water-damage', 'back-glass-repair', 'power-button-repair'],
            'iphone-16-pro-max' => ['screen-repair', 'battery-replacement', 'charging-port-repair', 'camera-repair', 'water-damage'],
            'iphone-15' => ['screen-repair', 'battery-replacement', 'charging-port-repair', 'camera-repair', 'water-damage'],
            'iphone-14-pro-max' => ['screen-repair', 'battery-replacement', 'charging-port-repair', 'back-glass-repair', 'water-damage'],
            'ipad-air-5' => ['screen-repair', 'battery-replacement', 'charging-port-repair', 'device-diagnostic'],
            'samsung-galaxy-s24-ultra' => ['screen-repair', 'battery-replacement', 'charging-port-repair', 'camera-repair', 'speaker-repair', 'water-damage'],
            'samsung-galaxy-s23-ultra' => ['screen-repair', 'battery-replacement', 'charging-port-repair', 'camera-repair', 'speaker-repair', 'water-damage'],
            'samsung-galaxy-z-flip-5' => ['screen-repair', 'battery-replacement', 'charging-port-repair', 'water-damage'],
            'samsung-galaxy-z-fold-5' => ['screen-repair', 'battery-replacement', 'charging-port-repair', 'water-damage'],
            'samsung-galaxy-tab-s9' => ['screen-repair', 'battery-replacement', 'charging-port-repair', 'device-diagnostic'],
            'google-pixel-8-pro' => ['screen-repair', 'battery-replacement', 'charging-port-repair', 'camera-repair', 'water-damage'],
            'google-pixel-8' => ['screen-repair', 'battery-replacement', 'charging-port-repair', 'camera-repair', 'water-damage'],
        ];

        foreach ($map as $modelSlug => $serviceSlugs) {
            foreach ($serviceSlugs as $index => $serviceSlug) {
                RepairServicePrice::firstOrCreate([
                    'device_model_id' => $models[$modelSlug]->id,
                    'repair_service_id' => $services[$serviceSlug]->id,
                ], [
                    'price' => null,
                    'compare_at_price' => null,
                    'price_label' => 'Request quote',
                    'is_price_visible' => false,
                    'estimated_minutes' => null,
                    'warranty_text' => null,
                    'notes' => null,
                    'is_available' => true,
                    'sort_order' => ($index + 1) * 10,
                ]);
            }
        }
    }

    private function mediaAssets(): array
    {
        return collect([
            'generic-phone' => ['generic-phone.svg', 'Neutral smartphone repair illustration'],
            'screen-repair' => ['screen-repair.svg', 'Screen repair illustration'],
            'battery' => ['battery.svg', 'Battery replacement illustration'],
            'charging-port' => ['charging-port.svg', 'Charging port repair illustration'],
            'camera' => ['camera.svg', 'Camera repair illustration'],
            'speaker' => ['speaker.svg', 'Speaker repair illustration'],
            'water-damage' => ['water-damage.svg', 'Water damage diagnostic illustration'],
            'back-glass' => ['back-glass.svg', 'Back glass repair illustration'],
            'diagnostic' => ['diagnostic.svg', 'Device diagnostic illustration'],
        ])->mapWithKeys(fn (array $asset, string $key) => [$key => $this->importMedia($asset[0], $asset[1])])->all();
    }

    private function importMedia(string $fileName, string $title): MediaAsset
    {
        $source = database_path('seeders/assets/repair/'.$fileName);
        $checksum = hash_file('sha256', $source);
        $existing = MediaAsset::active()->where('checksum', $checksum)->first();

        if ($existing) {
            return $existing;
        }

        $directory = 'media/reference/repair';
        $path = $directory.'/'.$fileName;
        Storage::disk('public')->put($path, File::get($source));

        return MediaAsset::create([
            'disk' => 'public',
            'directory' => $directory,
            'path' => $path,
            'file_name' => $fileName,
            'original_name' => $fileName,
            'mime_type' => File::mimeType($source) ?: 'image/svg+xml',
            'extension' => pathinfo($fileName, PATHINFO_EXTENSION),
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
