<?php

namespace Tests\Feature;

use App\Models\CatalogCategory;
use App\Models\CatalogItem;
use App\Models\CatalogItemVariant;
use App\Models\DeviceBrand;
use App\Models\DeviceModel;
use App\Models\MediaAsset;
use App\Models\MediaAttachment;
use App\Models\RepairService;
use App\Models\RepairServicePrice;
use App\Services\FrontendCatalogService;
use App\Services\FrontendRepairService;
use Database\Seeders\PhoneAccessoryReferenceSeeder;
use Database\Seeders\RepairReferenceSeeder;
use Database\Seeders\ReferenceContentSeeder;
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

    public function test_reference_content_seeder_covers_all_frontend_business_areas_and_is_idempotent(): void
    {
        $this->seed(ReferenceContentSeeder::class);

        $counts = $this->referenceCounts();

        $this->assertSame(6, CatalogCategory::where('business_area', 'convenience')->count());
        $this->assertSame(9, CatalogItem::whereHas('category', fn ($query) => $query->where('business_area', 'convenience'))->count());
        $this->assertSame(4, CatalogCategory::where('business_area', 'smoothie')->count());
        $this->assertSame(6, CatalogItem::whereHas('category', fn ($query) => $query->where('business_area', 'smoothie'))->count());
        $this->assertSame(5, CatalogCategory::where('business_area', 'adult_retail')->count());
        $this->assertSame(6, CatalogItem::whereHas('category', fn ($query) => $query->where('business_area', 'adult_retail'))->count());
        $this->assertSame(1, CatalogCategory::where('business_area', 'phone_accessory')->count());
        $this->assertSame(5, CatalogItem::whereHas('category', fn ($query) => $query->where('business_area', 'phone_accessory'))->count());

        $this->seed(ReferenceContentSeeder::class);

        $this->assertSame($counts, $this->referenceCounts());
    }

    public function test_frontend_catalog_pages_receive_dynamic_reference_data_without_zero_prices_or_hotlinks(): void
    {
        $this->seed(ReferenceContentSeeder::class);

        $service = app(FrontendCatalogService::class);

        $convenience = $service->forArea('convenience', ['image' => null]);
        $smoothies = $service->forArea('smoothie', ['image' => null]);
        $adult = $service->forArea('adult_retail', ['image' => null]);

        $this->assertTrue($convenience['hasCategories']);
        $this->assertTrue($convenience['hasItems']);
        $this->assertSame('Ask in store', $convenience['items']->firstWhere('slug', 'classic-potato-chips')['display_price']);

        $this->assertTrue($smoothies['hasItems']);
        $smoothie = $smoothies['items']->firstWhere('slug', 'strawberry-banana-smoothie');
        $this->assertCount(3, $smoothie['variants']);
        $this->assertSame('Medium', $smoothie['variants']->firstWhere('is_default', true)['name']);
        $this->assertSame('Ask in store', $smoothie['display_price']);
        $this->assertSame(6, CatalogItemVariant::whereHas('item.category', fn ($query) => $query->where('business_area', 'smoothie'))
            ->where('name', 'Medium')
            ->where('is_default', true)
            ->count());

        $this->assertSame(21, $adult['ageRequirement']);
        $this->assertTrue($adult['items']->every(fn ($item) => $item['is_age_restricted'] && $item['minimum_age'] === 21));

        $payload = json_encode([$convenience, $smoothies, $adult]);
        $this->assertStringNotContainsString('$0.00', $payload);
        $this->assertSame(0, MediaAsset::whereNotNull('source_url')->where('source_url', 'like', 'http%')->count());
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

    private function referenceCounts(): array
    {
        return [
            'brands' => DeviceBrand::count(),
            'models' => DeviceModel::count(),
            'services' => RepairService::count(),
            'prices' => RepairServicePrice::count(),
            'categories' => CatalogCategory::count(),
            'items' => CatalogItem::count(),
            'variants' => CatalogItemVariant::count(),
            'media' => MediaAsset::count(),
            'attachments' => MediaAttachment::count(),
        ];
    }
}
