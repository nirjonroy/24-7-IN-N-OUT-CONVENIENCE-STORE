<?php

namespace Tests\Feature;

use App\Models\CatalogCategory;
use App\Models\CatalogItem;
use App\Models\CatalogItemVariant;
use App\Models\MediaAsset;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class CatalogAdminTest extends TestCase
{
    use RefreshDatabase;

    public function test_category_can_be_created_with_seo_and_media(): void
    {
        $user = User::factory()->create(['email' => config('admin.email')]);
        $media = $this->media();

        $this->actingAs($user)->post(route('admin.catalog.categories.store'), $this->categoryPayload([
            'seo_title' => 'Snacks SEO',
            'image_media_id' => $media->id,
        ]))->assertRedirect(route('admin.catalog.categories.index'));

        $category = CatalogCategory::first();

        $this->assertSame('snacks', $category->slug);
        $this->assertSame('Snacks SEO', $category->seo_title);
        $this->assertSame($media->id, $category->getMediaAsset('image')->id);
    }

    public function test_category_slug_is_unique_and_hierarchy_works(): void
    {
        $user = User::factory()->create(['email' => config('admin.email')]);
        $parent = CatalogCategory::create($this->categoryData(['name' => 'Drinks', 'slug' => 'drinks']));

        $this->actingAs($user)->post(route('admin.catalog.categories.store'), $this->categoryPayload([
            'name' => 'Energy Drinks',
            'slug' => 'energy-drinks',
            'parent_id' => $parent->id,
        ]))->assertRedirect(route('admin.catalog.categories.index'));

        $child = CatalogCategory::where('slug', 'energy-drinks')->first();
        $this->assertSame($parent->id, $child->parent_id);
        $this->assertTrue($parent->children()->whereKey($child->id)->exists());

        $this->actingAs($user)->post(route('admin.catalog.categories.store'), $this->categoryPayload([
            'slug' => 'drinks',
        ]))->assertSessionHasErrors('slug');
    }

    public function test_category_cannot_parent_itself_or_create_simple_circle(): void
    {
        $user = User::factory()->create(['email' => config('admin.email')]);
        $a = CatalogCategory::create($this->categoryData(['name' => 'A', 'slug' => 'a']));
        $b = CatalogCategory::create($this->categoryData(['name' => 'B', 'slug' => 'b', 'parent_id' => $a->id]));

        $this->actingAs($user)->put(route('admin.catalog.categories.update', $a), $this->categoryPayload([
            'name' => 'A',
            'slug' => 'a',
            'parent_id' => $a->id,
        ]))->assertSessionHasErrors('parent_id');

        $this->actingAs($user)->put(route('admin.catalog.categories.update', $a), $this->categoryPayload([
            'name' => 'A',
            'slug' => 'a',
            'parent_id' => $b->id,
        ]))->assertSessionHasErrors('parent_id');
    }

    public function test_catalog_item_can_be_created_and_type_must_match_category_area(): void
    {
        $user = User::factory()->create(['email' => config('admin.email')]);
        $category = CatalogCategory::create($this->categoryData(['business_area' => 'smoothie']));

        $this->actingAs($user)->post(route('admin.catalog.items.store'), $this->itemPayload([
            'catalog_category_id' => $category->id,
            'item_type' => 'product',
        ]))->assertSessionHasErrors('item_type');

        $this->actingAs($user)->post(route('admin.catalog.items.store'), $this->itemPayload([
            'catalog_category_id' => $category->id,
            'item_type' => 'smoothie',
            'ingredients' => 'Mango, banana, milk',
            'seo_title' => 'Mango SEO',
        ]))->assertRedirect(route('admin.catalog.items.index'));

        $item = CatalogItem::first();
        $this->assertSame($category->id, $item->catalog_category_id);
        $this->assertSame('smoothie', $item->item_type);
        $this->assertSame('Mango SEO', $item->seo_title);
    }

    public function test_item_slug_is_unique_and_adult_item_supports_minimum_age(): void
    {
        $user = User::factory()->create(['email' => config('admin.email')]);
        $adult = CatalogCategory::create($this->categoryData(['name' => 'Adult', 'slug' => 'adult', 'business_area' => 'adult_retail']));
        CatalogItem::create($this->itemData($adult, ['slug' => 'vape-product', 'item_type' => 'adult_product']));

        $this->actingAs($user)->post(route('admin.catalog.items.store'), $this->itemPayload([
            'catalog_category_id' => $adult->id,
            'name' => 'Another',
            'slug' => 'vape-product',
            'item_type' => 'adult_product',
        ]))->assertSessionHasErrors('slug');

        $this->actingAs($user)->post(route('admin.catalog.items.store'), $this->itemPayload([
            'catalog_category_id' => $adult->id,
            'name' => 'Adult Item',
            'slug' => 'adult-item',
            'item_type' => 'adult_product',
            'is_age_restricted' => '1',
        ]))->assertRedirect(route('admin.catalog.items.index'));

        $item = CatalogItem::where('slug', 'adult-item')->first();
        $this->assertTrue($item->is_age_restricted);
        $this->assertSame(21, $item->minimum_age);
    }

    public function test_catalog_primary_image_and_gallery_media_work(): void
    {
        $user = User::factory()->create(['email' => config('admin.email')]);
        $category = CatalogCategory::create($this->categoryData());
        $primary = $this->media(['title' => 'Primary']);
        $galleryA = $this->media(['title' => 'Gallery A']);
        $galleryB = $this->media(['title' => 'Gallery B']);

        $this->actingAs($user)->post(route('admin.catalog.items.store'), $this->itemPayload([
            'catalog_category_id' => $category->id,
            'primary_image_media_id' => $primary->id,
            'gallery_media_ids' => $galleryA->id.','.$galleryB->id,
        ]))->assertRedirect(route('admin.catalog.items.index'));

        $item = CatalogItem::first();
        $this->assertSame($primary->id, $item->getMediaAsset('primary_image')->id);
        $this->assertSame(2, $item->mediaAttachments()->where('collection', 'gallery')->count());

        $this->actingAs($user)->put(route('admin.catalog.items.update', $item), $this->itemPayload([
            'catalog_category_id' => $category->id,
            'slug' => $item->slug,
            'gallery_media_ids' => (string) $galleryA->id,
        ]))->assertRedirect(route('admin.catalog.items.index'));

        $this->assertSame(1, $item->fresh()->mediaAttachments()->where('collection', 'gallery')->count());
        $this->assertDatabaseHas('media_assets', ['id' => $galleryB->id, 'deleted_at' => null]);
    }

    public function test_only_one_default_variant_exists_and_wrong_item_route_is_blocked(): void
    {
        $user = User::factory()->create(['email' => config('admin.email')]);
        $category = CatalogCategory::create($this->categoryData(['business_area' => 'smoothie']));
        $item = CatalogItem::create($this->itemData($category, ['item_type' => 'smoothie']));
        $other = CatalogItem::create($this->itemData($category, ['name' => 'Other', 'slug' => 'other', 'item_type' => 'smoothie']));
        $first = $item->variants()->create($this->variantData(['name' => 'Small', 'is_default' => true]));

        $this->actingAs($user)->post(route('admin.catalog.items.variants.store', $item), $this->variantPayload([
            'name' => 'Large',
            'is_default' => '1',
        ]))->assertRedirect(route('admin.catalog.items.variants.index', $item));

        $this->assertFalse($first->fresh()->is_default);
        $this->assertTrue($item->variants()->where('name', 'Large')->first()->is_default);

        $this->actingAs($user)->get(route('admin.catalog.items.variants.edit', [$other, $first]))
            ->assertNotFound();
    }

    public function test_category_cannot_be_deleted_while_items_exist_and_item_soft_deletes(): void
    {
        $user = User::factory()->create(['email' => config('admin.email')]);
        $category = CatalogCategory::create($this->categoryData());
        $item = CatalogItem::create($this->itemData($category));

        $this->actingAs($user)->delete(route('admin.catalog.categories.destroy', $category))
            ->assertRedirect(route('admin.catalog.categories.index'))
            ->assertSessionHas('error');

        $this->actingAs($user)->delete(route('admin.catalog.items.destroy', $item))
            ->assertRedirect(route('admin.catalog.items.index'));

        $this->assertSoftDeleted('catalog_items', ['id' => $item->id]);
    }

    public function test_item_filters_work(): void
    {
        $user = User::factory()->create(['email' => config('admin.email')]);
        $smoothie = CatalogCategory::create($this->categoryData(['name' => 'Smoothies', 'slug' => 'smoothies', 'business_area' => 'smoothie']));
        $convenience = CatalogCategory::create($this->categoryData(['name' => 'Snacks', 'slug' => 'snacks']));
        CatalogItem::create($this->itemData($smoothie, ['name' => 'Mango Smoothie', 'slug' => 'mango-smoothie', 'item_type' => 'smoothie', 'is_featured' => true]));
        CatalogItem::create($this->itemData($convenience, ['name' => 'Candy Bar', 'slug' => 'candy-bar']));

        $this->actingAs($user)->get(route('admin.catalog.items.index', [
            'search' => 'Mango',
            'business_area' => 'smoothie',
            'featured' => 'yes',
        ]))->assertOk()
            ->assertSee('Mango Smoothie')
            ->assertDontSee('Candy Bar');
    }

    private function categoryPayload(array $overrides = []): array
    {
        return array_merge([
            'name' => 'Snacks',
            'slug' => '',
            'business_area' => 'convenience',
            'is_featured' => '0',
            'is_active' => '1',
            'sort_order' => 0,
        ], $overrides);
    }

    private function itemPayload(array $overrides = []): array
    {
        return array_merge([
            'name' => 'Mango Smoothie',
            'slug' => '',
            'item_type' => 'product',
            'is_price_visible' => '1',
            'is_age_restricted' => '0',
            'is_featured' => '0',
            'is_available' => '1',
            'is_active' => '1',
            'sort_order' => 0,
        ], $overrides);
    }

    private function variantPayload(array $overrides = []): array
    {
        return array_merge([
            'name' => 'Small',
            'is_default' => '0',
            'is_available' => '1',
            'sort_order' => 0,
        ], $overrides);
    }

    private function categoryData(array $overrides = []): array
    {
        return array_merge([
            'name' => 'Snacks',
            'slug' => 'snacks-'.uniqid(),
            'business_area' => 'convenience',
            'is_featured' => false,
            'is_active' => true,
            'sort_order' => 0,
        ], $overrides);
    }

    private function itemData(CatalogCategory $category, array $overrides = []): array
    {
        return array_merge([
            'catalog_category_id' => $category->id,
            'name' => 'Candy Bar',
            'slug' => 'candy-'.uniqid(),
            'item_type' => 'product',
            'is_price_visible' => true,
            'is_age_restricted' => false,
            'is_featured' => false,
            'is_available' => true,
            'is_active' => true,
            'sort_order' => 0,
        ], $overrides);
    }

    private function variantData(array $overrides = []): array
    {
        return array_merge([
            'name' => 'Small',
            'is_default' => false,
            'is_available' => true,
            'sort_order' => 0,
        ], $overrides);
    }

    private function media(array $overrides = []): MediaAsset
    {
        return MediaAsset::create(array_merge([
            'disk' => 'public',
            'directory' => 'media/test',
            'path' => 'media/test/test.jpg',
            'file_name' => uniqid('test_', true).'.jpg',
            'original_name' => 'test.jpg',
            'mime_type' => 'image/jpeg',
            'extension' => 'jpg',
            'file_size' => 1200,
            'width' => 100,
            'height' => 100,
            'checksum' => hash('sha256', uniqid('', true)),
            'is_active' => true,
        ], $overrides));
    }
}
