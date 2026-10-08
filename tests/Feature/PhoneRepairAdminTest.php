<?php

namespace Tests\Feature;

use App\Models\DeviceBrand;
use App\Models\DeviceModel;
use App\Models\MediaAsset;
use App\Models\RepairService;
use App\Models\RepairServicePrice;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class PhoneRepairAdminTest extends TestCase
{
    use RefreshDatabase;

    public function test_device_brand_can_be_created_with_seo_and_media(): void
    {
        $user = User::factory()->create(['email' => config('admin.email')]);
        $media = $this->media();

        $this->actingAs($user)->post(route('admin.phone-repair.brands.store'), $this->brandPayload([
            'seo_title' => 'Apple SEO',
            'logo_media_id' => $media->id,
        ]))->assertRedirect(route('admin.phone-repair.brands.index'));

        $brand = DeviceBrand::first();
        $this->assertSame('apple', $brand->slug);
        $this->assertSame('Apple SEO', $brand->seo_title);
        $this->assertSame($media->id, $brand->getMediaAsset('logo')->id);
    }

    public function test_brand_slug_is_unique_and_brand_with_models_cannot_be_deleted(): void
    {
        $user = User::factory()->create(['email' => config('admin.email')]);
        $brand = DeviceBrand::create($this->brandData(['slug' => 'apple']));
        DeviceModel::create($this->modelData($brand));

        $this->actingAs($user)->post(route('admin.phone-repair.brands.store'), $this->brandPayload(['slug' => 'apple']))
            ->assertSessionHasErrors('slug');

        $this->actingAs($user)->delete(route('admin.phone-repair.brands.destroy', $brand))
            ->assertRedirect(route('admin.phone-repair.brands.index'))
            ->assertSessionHas('error');
    }

    public function test_device_model_can_be_created_and_slug_is_scoped_to_brand(): void
    {
        $user = User::factory()->create(['email' => config('admin.email')]);
        $apple = DeviceBrand::create($this->brandData(['slug' => 'apple']));
        $samsung = DeviceBrand::create($this->brandData(['name' => 'Samsung', 'slug' => 'samsung']));
        DeviceModel::create($this->modelData($apple, ['slug' => 'iphone-15']));

        $this->actingAs($user)->post(route('admin.phone-repair.models.store'), $this->modelPayload([
            'device_brand_id' => $apple->id,
            'slug' => 'iphone-15',
        ]))->assertSessionHasErrors('slug');

        $this->actingAs($user)->post(route('admin.phone-repair.models.store'), $this->modelPayload([
            'device_brand_id' => $samsung->id,
            'slug' => 'iphone-15',
            'device_type' => 'tablet',
        ]))->assertRedirect(route('admin.phone-repair.models.index'));

        $this->assertDatabaseHas('device_models', ['device_brand_id' => $samsung->id, 'slug' => 'iphone-15']);
    }

    public function test_device_type_validation_and_model_media_gallery(): void
    {
        $user = User::factory()->create(['email' => config('admin.email')]);
        $brand = DeviceBrand::create($this->brandData());
        $image = $this->media(['title' => 'Device image']);
        $gallery = $this->media(['title' => 'Gallery image']);

        $this->actingAs($user)->post(route('admin.phone-repair.models.store'), $this->modelPayload([
            'device_brand_id' => $brand->id,
            'device_type' => 'laptop',
        ]))->assertSessionHasErrors('device_type');

        $this->actingAs($user)->post(route('admin.phone-repair.models.store'), $this->modelPayload([
            'device_brand_id' => $brand->id,
            'image_media_id' => $image->id,
            'gallery_media_ids' => (string) $gallery->id,
        ]))->assertRedirect(route('admin.phone-repair.models.index'));

        $model = DeviceModel::first();
        $this->assertSame($image->id, $model->getMediaAsset('image')->id);
        $this->assertSame(1, $model->mediaAttachments()->where('collection', 'gallery')->count());
    }

    public function test_repair_service_can_be_created_and_validates_slug_and_estimated_time(): void
    {
        $user = User::factory()->create(['email' => config('admin.email')]);
        RepairService::create($this->serviceData(['slug' => 'screen-repair']));

        $this->actingAs($user)->post(route('admin.phone-repair.services.store'), $this->servicePayload([
            'slug' => 'screen-repair',
        ]))->assertSessionHasErrors('slug');

        $this->actingAs($user)->post(route('admin.phone-repair.services.store'), $this->servicePayload([
            'slug' => 'battery-replacement',
            'estimated_minutes_min' => 60,
            'estimated_minutes_max' => 30,
        ]))->assertSessionHasErrors('estimated_minutes_max');

        $media = $this->media();
        $this->actingAs($user)->post(route('admin.phone-repair.services.store'), $this->servicePayload([
            'title' => 'Battery Replacement',
            'slug' => 'battery-replacement',
            'estimated_minutes_min' => 30,
            'estimated_minutes_max' => 60,
            'image_media_id' => $media->id,
            'seo_title' => 'Battery SEO',
        ]))->assertRedirect(route('admin.phone-repair.services.index'));

        $service = RepairService::where('slug', 'battery-replacement')->first();
        $this->assertSame($media->id, $service->getMediaAsset('image')->id);
        $this->assertSame('Battery SEO', $service->seo_title);
    }

    public function test_bulk_pricing_update_and_unique_service_device_combo(): void
    {
        $user = User::factory()->create(['email' => config('admin.email')]);
        $brand = DeviceBrand::create($this->brandData());
        $model = DeviceModel::create($this->modelData($brand));
        $service = RepairService::create($this->serviceData(['starting_price' => 79, 'warranty_text' => '30 days']));

        $this->actingAs($user)->post(route('admin.phone-repair.services.prices.update', $service), [
            'prices' => [
                $model->id => [
                    'price' => '149.00',
                    'compare_at_price' => '179.00',
                    'price_label' => 'Installed',
                    'is_price_visible' => '1',
                    'estimated_minutes' => '60',
                    'warranty_text' => '90 days',
                    'notes' => 'OLED screen',
                    'is_available' => '1',
                    'sort_order' => '2',
                ],
            ],
        ])->assertRedirect(route('admin.phone-repair.services.prices.edit', $service));

        $this->assertDatabaseHas('repair_service_prices', [
            'repair_service_id' => $service->id,
            'device_model_id' => $model->id,
            'price' => '149.00',
        ]);

        $this->actingAs($user)->post(route('admin.phone-repair.services.prices.update', $service), [
            'prices' => [$model->id => ['price' => '129.00', 'is_price_visible' => '1', 'is_available' => '1']],
        ])->assertRedirect(route('admin.phone-repair.services.prices.edit', $service));

        $this->assertSame(1, RepairServicePrice::where('repair_service_id', $service->id)->where('device_model_id', $model->id)->count());
        $this->assertSame('129.00', RepairServicePrice::first()->price);
    }

    public function test_only_requested_service_pricing_is_modified_and_filters_work(): void
    {
        $user = User::factory()->create(['email' => config('admin.email')]);
        $apple = DeviceBrand::create($this->brandData(['slug' => 'apple']));
        $samsung = DeviceBrand::create($this->brandData(['name' => 'Samsung', 'slug' => 'samsung']));
        $iphone = DeviceModel::create($this->modelData($apple, ['name' => 'iPhone 15', 'slug' => 'iphone-15', 'device_type' => 'phone']));
        $tablet = DeviceModel::create($this->modelData($samsung, ['name' => 'Galaxy Tab', 'slug' => 'galaxy-tab', 'device_type' => 'tablet']));
        $screen = RepairService::create($this->serviceData(['slug' => 'screen']));
        $battery = RepairService::create($this->serviceData(['title' => 'Battery', 'slug' => 'battery']));
        $battery->prices()->create(['device_model_id' => $iphone->id, 'price' => 50]);

        $this->actingAs($user)->post(route('admin.phone-repair.services.prices.update', $screen), [
            'prices' => [$iphone->id => ['price' => '100', 'is_price_visible' => '1', 'is_available' => '1']],
        ])->assertRedirect();

        $this->assertSame('50.00', $battery->prices()->first()->price);

        $this->actingAs($user)->get(route('admin.phone-repair.services.prices.edit', [
            'service' => $screen,
            'brand_id' => $apple->id,
            'device_type' => 'phone',
        ]))->assertOk()->assertSee('iPhone 15')->assertDontSee('Galaxy Tab');
    }

    public function test_price_fallback_logic_and_soft_delete(): void
    {
        $user = User::factory()->create(['email' => config('admin.email')]);
        $brand = DeviceBrand::create($this->brandData());
        $model = DeviceModel::create($this->modelData($brand));
        $service = RepairService::create($this->serviceData(['starting_price' => 79, 'warranty_text' => '30 days']));

        $fallback = $service->priceForDevice($model);
        $this->assertEquals('79.00', $fallback['price']);
        $this->assertSame('30 days', $fallback['warranty_text']);

        $service->prices()->create(['device_model_id' => $model->id, 'price' => 149, 'warranty_text' => '90 days']);
        $resolved = $service->priceForDevice($model);
        $this->assertEquals('149.00', $resolved['price']);
        $this->assertSame('90 days', $resolved['warranty_text']);

        $this->actingAs($user)->delete(route('admin.phone-repair.services.destroy', $service))->assertRedirect();
        $this->assertSoftDeleted('repair_services', ['id' => $service->id]);
    }

    public function test_removing_media_attachment_does_not_delete_media_and_unauthorized_users_are_blocked(): void
    {
        $user = User::factory()->create(['email' => config('admin.email')]);
        $brand = DeviceBrand::create($this->brandData());
        $media = $this->media();
        $brand->mediaAttachments()->create(['media_asset_id' => $media->id, 'collection' => 'logo', 'is_primary' => true]);

        $this->actingAs($user)->put(route('admin.phone-repair.brands.update', $brand), $this->brandPayload([
            'slug' => $brand->slug,
            'logo_media_id' => null,
        ]))->assertRedirect(route('admin.phone-repair.brands.index'));

        $this->assertDatabaseMissing('media_attachments', ['mediable_type' => DeviceBrand::class, 'mediable_id' => $brand->id, 'collection' => 'logo']);
        $this->assertDatabaseHas('media_assets', ['id' => $media->id, 'deleted_at' => null]);

        auth()->logout();
        $this->get(route('admin.phone-repair.services.index'))->assertRedirect(route('login'));
    }

    private function brandPayload(array $overrides = []): array
    {
        return array_merge(['name' => 'Apple', 'slug' => '', 'is_featured' => '0', 'is_active' => '1', 'sort_order' => 0], $overrides);
    }

    private function modelPayload(array $overrides = []): array
    {
        return array_merge(['name' => 'iPhone 15', 'slug' => '', 'device_type' => 'phone', 'is_featured' => '0', 'is_active' => '1', 'sort_order' => 0], $overrides);
    }

    private function servicePayload(array $overrides = []): array
    {
        return array_merge(['title' => 'Screen Repair', 'slug' => '', 'repair_type' => 'screen_repair', 'is_price_visible' => '1', 'diagnostic_required' => '0', 'is_featured' => '0', 'is_active' => '1', 'sort_order' => 0], $overrides);
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
        return MediaAsset::create(array_merge(['disk' => 'public', 'directory' => 'media/test', 'path' => 'media/test/test.jpg', 'file_name' => uniqid('test_', true).'.jpg', 'original_name' => 'test.jpg', 'mime_type' => 'image/jpeg', 'extension' => 'jpg', 'file_size' => 1200, 'width' => 100, 'height' => 100, 'checksum' => hash('sha256', uniqid('', true)), 'is_active' => true], $overrides));
    }
}
