<?php

namespace Tests\Feature;

use App\Models\DeviceBrand;
use App\Models\DeviceModel;
use App\Models\MediaAsset;
use App\Models\MediaAttachment;
use App\Models\MediaVariant;
use App\Models\RepairService;
use App\Models\RepairServicePrice;
use App\Models\SeoSetting;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class FrontendPhoneRepairIntegrationTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        SeoSetting::clearCache();
    }

    protected function tearDown(): void
    {
        SeoSetting::clearCache();

        parent::tearDown();
    }

    public function test_phone_repair_page_renders_fallback_and_html_rules_without_repair_data(): void
    {
        $html = $this->get('/phone-repair')
            ->assertOk()
            ->assertSee('Phone repair at 6168 Oxon Hill Rd.')
            ->assertSee('Repair explorer')
            ->assertSee('Bring your device')
            ->assertSee('Screen or glass damage')
            ->assertSee('phone-repair.js')
            ->getContent();

        $this->assertSame(1, preg_match_all('/<h1\b/i', $html));
        $this->assertSame(0, preg_match_all('/<h[2-6]\b/i', $html));
        $this->assertSame(1, preg_match_all('/<title>/i', $html));
        $this->assertSame(1, preg_match_all('/<meta name="description"/i', $html));
        $this->assertSame(1, preg_match_all('/<link rel="canonical"/i', $html));
        $this->assertStringContainsString('href="http://localhost/phone-repair"', $html);
        $this->assertStringNotContainsString('.html', $html);
        $this->assertSame(1, preg_match_all('/<header\b/i', $html));
        $this->assertSame(1, preg_match_all('/<footer\b/i', $html));
    }

    public function test_active_brands_and_services_render_and_inactive_records_do_not(): void
    {
        $apple = DeviceBrand::create($this->brandData(['name' => 'Apple', 'slug' => 'apple', 'sort_order' => 2]));
        $samsung = DeviceBrand::create($this->brandData(['name' => 'Samsung', 'slug' => 'samsung', 'sort_order' => 1]));
        DeviceBrand::create($this->brandData(['name' => 'Inactive Brand', 'slug' => 'inactive-brand', 'is_active' => false]));
        $deleted = DeviceBrand::create($this->brandData(['name' => 'Deleted Brand', 'slug' => 'deleted-brand']));
        $deleted->delete();
        $media = $this->media(['path' => 'media/repair/screen.jpg']);
        $this->variant($media, 'medium', 'media/repair/screen-medium.jpg');
        $service = RepairService::create($this->serviceData([
            'title' => 'Screen Repair',
            'slug' => 'screen-repair',
            'short_description' => 'Glass and display repair.',
            'starting_price' => 99,
            'estimated_minutes_min' => 30,
            'estimated_minutes_max' => 60,
            'warranty_text' => '90 days',
            'is_featured' => true,
        ]));
        $this->attach($service, $media, 'image');
        RepairService::create($this->serviceData(['title' => 'Inactive Repair', 'slug' => 'inactive-repair', 'is_active' => false]));

        $html = $this->get('/phone-repair')
            ->assertOk()
            ->assertSee('Samsung')
            ->assertSee('Apple')
            ->assertSee('Screen Repair')
            ->assertSee('Glass and display repair.')
            ->assertSee('Starting at $99.00')
            ->assertSee('30-60 minutes')
            ->assertSee('90 days')
            ->assertSee('media/repair/screen-medium.jpg')
            ->assertDontSee('Inactive Brand')
            ->assertDontSee('Deleted Brand')
            ->assertDontSee('Inactive Repair')
            ->assertDontSee('Screen or glass damage')
            ->getContent();

        $this->assertLessThan(strpos($html, 'Apple'), strpos($html, 'Samsung'));
        $this->assertNotNull($apple);
        $this->assertNotNull($samsung);
    }

    public function test_models_endpoint_returns_only_active_models_for_active_brand(): void
    {
        $apple = DeviceBrand::create($this->brandData(['name' => 'Apple', 'slug' => 'apple']));
        $samsung = DeviceBrand::create($this->brandData(['name' => 'Samsung', 'slug' => 'samsung']));
        $inactiveBrand = DeviceBrand::create($this->brandData(['name' => 'Inactive', 'slug' => 'inactive', 'is_active' => false]));
        DeviceModel::create($this->modelData($apple, ['name' => 'iPhone 15', 'slug' => 'iphone-15', 'model_number' => 'A3102']));
        DeviceModel::create($this->modelData($apple, ['name' => 'Hidden iPhone', 'slug' => 'hidden-iphone', 'is_active' => false]));
        DeviceModel::create($this->modelData($samsung, ['name' => 'Galaxy S24', 'slug' => 'galaxy-s24']));
        DeviceModel::create($this->modelData($inactiveBrand, ['name' => 'Inactive Brand Phone', 'slug' => 'inactive-brand-phone']));

        $this->getJson(route('frontend.phone-repair.models', ['brand_id' => $apple->id]))
            ->assertOk()
            ->assertJsonPath('data.0.name', 'iPhone 15')
            ->assertJsonPath('data.0.model_number', 'A3102')
            ->assertJsonMissing(['name' => 'Hidden iPhone'])
            ->assertJsonMissing(['name' => 'Galaxy S24'])
            ->assertJsonMissing(['name' => 'Inactive Brand Phone']);

        $this->getJson(route('frontend.phone-repair.models', ['brand_id' => $inactiveBrand->id]))
            ->assertNotFound();

        $this->getJson(route('frontend.phone-repair.models', ['brand_id' => 'abc']))
            ->assertStatus(422);
    }

    public function test_estimate_endpoint_returns_specific_price_time_warranty_and_note(): void
    {
        [$model, $service] = $this->repairFixture();
        RepairServicePrice::create([
            'device_model_id' => $model->id,
            'repair_service_id' => $service->id,
            'price' => 149,
            'price_label' => 'Installed',
            'estimated_minutes' => 60,
            'warranty_text' => '90-day warranty',
            'notes' => 'OLED screen price.',
            'is_available' => true,
        ]);

        $this->getJson(route('frontend.phone-repair.estimate', [
            'device_model_id' => $model->id,
            'repair_service_id' => $service->id,
        ]))->assertOk()
            ->assertJsonPath('available', true)
            ->assertJsonPath('price', '149.00')
            ->assertJsonPath('formatted_price', '$149.00')
            ->assertJsonPath('is_specific_price', true)
            ->assertJsonPath('estimated_minutes', 60)
            ->assertJsonPath('estimated_range', 'About 60 minutes')
            ->assertJsonPath('warranty', '90-day warranty')
            ->assertJsonPath('note', 'OLED screen price.');
    }

    public function test_estimate_falls_back_to_generic_price_but_not_for_explicit_unavailable_mapping(): void
    {
        [$model, $service] = $this->repairFixture();

        $this->getJson(route('frontend.phone-repair.estimate', [
            'device_model_id' => $model->id,
            'repair_service_id' => $service->id,
        ]))->assertOk()
            ->assertJsonPath('available', true)
            ->assertJsonPath('formatted_price', null)
            ->assertJsonPath('price_label', 'Starting at $79.00')
            ->assertJsonPath('is_specific_price', false)
            ->assertJsonPath('estimated_range', '30-60 minutes')
            ->assertJsonPath('warranty', '30-day warranty');

        RepairServicePrice::create([
            'device_model_id' => $model->id,
            'repair_service_id' => $service->id,
            'price' => 179,
            'is_available' => false,
            'is_price_visible' => true,
        ]);

        $this->getJson(route('frontend.phone-repair.estimate', [
            'device_model_id' => $model->id,
            'repair_service_id' => $service->id,
        ]))->assertOk()
            ->assertJsonPath('available', false)
            ->assertJsonPath('availability_label', 'Currently unavailable')
            ->assertJsonPath('formatted_price', null)
            ->assertJsonPath('is_specific_price', true);
    }

    public function test_hidden_prices_and_missing_prices_do_not_expose_zero_or_numeric_values(): void
    {
        [$model, $service] = $this->repairFixture(['is_price_visible' => false, 'starting_price' => 88, 'price_note' => 'Call for pricing']);

        $this->getJson(route('frontend.phone-repair.estimate', [
            'device_model_id' => $model->id,
            'repair_service_id' => $service->id,
        ]))->assertOk()
            ->assertJsonPath('price', null)
            ->assertJsonPath('formatted_price', null)
            ->assertJsonPath('price_label', 'Call for pricing');

        $visible = RepairService::create($this->serviceData([
            'title' => 'Diagnostic',
            'slug' => 'diagnostic',
            'starting_price' => null,
            'price_note' => null,
        ]));

        $this->getJson(route('frontend.phone-repair.estimate', [
            'device_model_id' => $model->id,
            'repair_service_id' => $visible->id,
        ]))->assertOk()
            ->assertJsonPath('price', null)
            ->assertJsonPath('formatted_price', null)
            ->assertJsonPath('price_label', 'Request a quote');
    }

    public function test_estimate_rejects_inactive_and_soft_deleted_entities(): void
    {
        [$model, $service] = $this->repairFixture();
        $inactiveService = RepairService::create($this->serviceData(['title' => 'Inactive', 'slug' => 'inactive', 'is_active' => false]));
        $inactiveBrand = DeviceBrand::create($this->brandData(['name' => 'Inactive Brand', 'slug' => 'inactive-brand', 'is_active' => false]));
        $inactiveModel = DeviceModel::create($this->modelData($inactiveBrand, ['name' => 'Inactive Model', 'slug' => 'inactive-model']));
        $deletedService = RepairService::create($this->serviceData(['title' => 'Deleted', 'slug' => 'deleted']));
        $deletedService->delete();

        $this->getJson(route('frontend.phone-repair.estimate', ['device_model_id' => $model->id, 'repair_service_id' => $inactiveService->id]))->assertNotFound();
        $this->getJson(route('frontend.phone-repair.estimate', ['device_model_id' => $inactiveModel->id, 'repair_service_id' => $service->id]))->assertNotFound();
        $this->getJson(route('frontend.phone-repair.estimate', ['device_model_id' => $model->id, 'repair_service_id' => $deletedService->id]))->assertNotFound();
    }

    private function repairFixture(array $serviceOverrides = []): array
    {
        $brand = DeviceBrand::create($this->brandData(['name' => 'Apple', 'slug' => 'apple']));
        $model = DeviceModel::create($this->modelData($brand, ['name' => 'iPhone 15', 'slug' => 'iphone-15']));
        $service = RepairService::create($this->serviceData(array_merge([
            'title' => 'Screen Repair',
            'slug' => 'screen-repair',
            'starting_price' => 79,
            'estimated_minutes_min' => 30,
            'estimated_minutes_max' => 60,
            'warranty_text' => '30-day warranty',
        ], $serviceOverrides)));

        return [$model, $service];
    }

    private function brandData(array $overrides = []): array
    {
        return array_merge(['name' => 'Apple', 'slug' => 'apple-'.uniqid(), 'is_featured' => false, 'is_active' => true, 'sort_order' => 0], $overrides);
    }

    private function modelData(DeviceBrand $brand, array $overrides = []): array
    {
        return array_merge(['device_brand_id' => $brand->id, 'name' => 'iPhone 15', 'slug' => 'iphone-'.uniqid(), 'device_type' => 'phone', 'is_featured' => false, 'is_active' => true, 'sort_order' => 0], $overrides);
    }

    private function serviceData(array $overrides = []): array
    {
        return array_merge(['title' => 'Screen Repair', 'slug' => 'screen-'.uniqid(), 'repair_type' => 'screen_repair', 'is_price_visible' => true, 'diagnostic_required' => false, 'is_featured' => false, 'is_active' => true, 'sort_order' => 0], $overrides);
    }

    private function media(array $overrides = []): MediaAsset
    {
        return MediaAsset::create(array_merge(['disk' => 'public', 'directory' => 'media/repair', 'path' => 'media/repair/test.jpg', 'file_name' => uniqid('test_', true).'.jpg', 'original_name' => 'test.jpg', 'mime_type' => 'image/jpeg', 'extension' => 'jpg', 'file_size' => 1200, 'width' => 100, 'height' => 100, 'checksum' => hash('sha256', uniqid('', true)), 'is_active' => true], $overrides));
    }

    private function variant(MediaAsset $media, string $variant, string $path): void
    {
        MediaVariant::create(['media_asset_id' => $media->id, 'variant' => $variant, 'disk' => 'public', 'path' => $path, 'mime_type' => 'image/jpeg', 'extension' => 'jpg', 'file_size' => 500]);
    }

    private function attach(RepairService $service, MediaAsset $media, string $collection): void
    {
        MediaAttachment::create(['media_asset_id' => $media->id, 'mediable_type' => RepairService::class, 'mediable_id' => $service->id, 'collection' => $collection, 'is_primary' => true]);
    }
}
