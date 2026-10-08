<?php

namespace Tests\Feature;

use App\Models\CatalogCategory;
use App\Models\CatalogItem;
use App\Models\DeviceBrand;
use App\Models\DeviceModel;
use App\Models\MediaAsset;
use App\Models\MediaAttachment;
use App\Models\RepairService;
use App\Models\RepairServicePrice;
use App\Services\FrontendRepairService;
use Database\Seeders\PhoneAccessoryReferenceSeeder;
use Database\Seeders\RepairReferenceSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class RepairReferenceSeederTest extends TestCase
{
    use RefreshDatabase;

    public function test_repair_reference_seeder_creates_expected_taxonomy_relationships_and_media(): void
    {
        $this->seed(RepairReferenceSeeder::class);

        $apple = DeviceBrand::where('slug', 'apple')->first();
        $samsung = DeviceBrand::where('slug', 'samsung')->first();
        $google = DeviceBrand::where('slug', 'google')->first();

        $this->assertNotNull($apple);
        $this->assertNotNull($samsung);
        $this->assertNotNull($google);

        $iphone = DeviceModel::where('slug', 'iphone-15-pro-max')->first();
        $galaxy = DeviceModel::where('slug', 'samsung-galaxy-s24-ultra')->first();
        $pixel = DeviceModel::where('slug', 'google-pixel-8-pro')->first();

        $this->assertTrue($iphone->brand->is($apple));
        $this->assertTrue($galaxy->brand->is($samsung));
        $this->assertTrue($pixel->brand->is($google));
        $this->assertSame('tablet', DeviceModel::where('slug', 'ipad-air-5')->value('device_type'));

        $screen = RepairService::where('slug', 'screen-repair')->first();
        $battery = RepairService::where('slug', 'battery-replacement')->first();

        $this->assertNotNull($screen);
        $this->assertNotNull($battery);
        $this->assertSame('Request quote', $screen->price_note);
        $this->assertFalse((bool) $screen->is_price_visible);

        $this->assertDatabaseHas('repair_service_prices', [
            'device_model_id' => $iphone->id,
            'repair_service_id' => $screen->id,
            'price' => null,
            'price_label' => 'Request quote',
            'is_price_visible' => false,
            'is_available' => true,
        ]);

        $this->assertGreaterThanOrEqual(9, MediaAsset::count());
        $this->assertNotNull($iphone->getMediaAttachment('image'));
        $this->assertNotNull($screen->getMediaAttachment('image'));
        $this->assertSame(0, MediaAsset::where('source_url', 'like', '%dcphonerepair.com%')->count());
    }

    public function test_repair_reference_seeder_is_idempotent_and_request_quote_does_not_render_zero_price(): void
    {
        $this->seed(RepairReferenceSeeder::class);
        $counts = $this->repairCounts();

        $this->seed(RepairReferenceSeeder::class);

        $this->assertSame($counts, $this->repairCounts());

        $device = DeviceModel::where('slug', 'iphone-15-pro-max')->firstOrFail();
        $service = RepairService::where('slug', 'screen-repair')->firstOrFail();
        $estimate = app(FrontendRepairService::class)->estimate($device->id, $service->id);

        $this->assertSame('Request quote', $estimate['price_label']);
        $this->assertNull($estimate['price']);
        $this->assertNull($estimate['formatted_price']);
        $this->assertStringNotContainsString('$0.00', json_encode($estimate));
    }

    public function test_phone_accessory_reference_seeder_creates_small_generic_catalog_and_is_idempotent(): void
    {
        $this->seed(PhoneAccessoryReferenceSeeder::class);

        $category = CatalogCategory::where('slug', 'phone-accessories')->first();

        $this->assertNotNull($category);
        $this->assertSame('phone_accessory', $category->business_area);
        $this->assertSame(5, CatalogItem::where('catalog_category_id', $category->id)->count());
        $this->assertSame('Ask in store', CatalogItem::where('slug', 'usb-c-charging-cable')->value('price_label'));
        $this->assertNotNull(CatalogItem::where('slug', 'protective-phone-case')->first()->getMediaAttachment('image'));

        $counts = [
            'categories' => CatalogCategory::count(),
            'items' => CatalogItem::count(),
            'attachments' => MediaAttachment::count(),
        ];

        $this->seed(PhoneAccessoryReferenceSeeder::class);

        $this->assertSame($counts, [
            'categories' => CatalogCategory::count(),
            'items' => CatalogItem::count(),
            'attachments' => MediaAttachment::count(),
        ]);
    }

    private function repairCounts(): array
    {
        return [
            'brands' => DeviceBrand::count(),
            'models' => DeviceModel::count(),
            'services' => RepairService::count(),
            'prices' => RepairServicePrice::count(),
            'media' => MediaAsset::count(),
            'attachments' => MediaAttachment::count(),
        ];
    }
}
