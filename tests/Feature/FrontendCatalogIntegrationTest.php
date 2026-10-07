<?php

namespace Tests\Feature;

use App\Models\CatalogCategory;
use App\Models\CatalogItem;
use App\Models\MediaAsset;
use App\Models\MediaAttachment;
use App\Models\MediaVariant;
use App\Models\Page;
use App\Models\PageSection;
use App\Models\SeoSetting;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class FrontendCatalogIntegrationTest extends TestCase
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

    public function test_common_seo_partial_outputs_no_duplicate_seo_tags_or_keywords(): void
    {
        foreach (['/', '/about', '/convenience-store', '/smoothies', '/vape-tobacco'] as $path) {
            $html = $this->get($path)->assertOk()->getContent();

            $this->assertSame(1, preg_match_all('/<title>/i', $html), "{$path} title count");
            $this->assertLessThanOrEqual(1, preg_match_all('/<meta name="description"/i', $html), "{$path} description count");
            $this->assertLessThanOrEqual(1, preg_match_all('/<link rel="canonical"/i', $html), "{$path} canonical count");
            $this->assertLessThanOrEqual(1, preg_match_all('/<meta property="og:title"/i', $html), "{$path} og:title count");
            $this->assertLessThanOrEqual(1, preg_match_all('/<meta property="og:description"/i', $html), "{$path} og:description count");
            $this->assertLessThanOrEqual(1, preg_match_all('/<meta property="og:url"/i', $html), "{$path} og:url count");
            $this->assertSame(0, preg_match_all('/<meta name="keywords"/i', $html), "{$path} keywords count");
        }
    }

    public function test_convenience_page_uses_only_active_convenience_catalog_records(): void
    {
        $snacks = CatalogCategory::create($this->categoryData(['name' => 'Snacks', 'slug' => 'snacks', 'sort_order' => 2]));
        $drinks = CatalogCategory::create($this->categoryData(['name' => 'Cold Drinks', 'slug' => 'cold-drinks', 'sort_order' => 1]));
        $inactive = CatalogCategory::create($this->categoryData(['name' => 'Inactive Category', 'slug' => 'inactive-category', 'is_active' => false]));
        $smoothie = CatalogCategory::create($this->categoryData(['name' => 'Fruit Smoothies', 'slug' => 'fruit-smoothies', 'business_area' => 'smoothie']));
        $media = $this->media(['path' => 'media/catalog/candy.jpg']);
        $this->variant($media, 'medium', 'media/catalog/candy-medium.jpg');

        $featured = CatalogItem::create($this->itemData($snacks, [
            'name' => 'Featured Candy',
            'slug' => 'featured-candy',
            'short_description' => 'Front counter favorite.',
            'price' => 2.5,
            'is_featured' => true,
            'sort_order' => 2,
        ]));
        $this->attach($featured, $media, 'primary_image');
        CatalogItem::create($this->itemData($drinks, ['name' => 'Bottled Water', 'slug' => 'bottled-water', 'sort_order' => 1, 'is_available' => false]));
        CatalogItem::create($this->itemData($snacks, ['name' => 'Inactive Chips', 'slug' => 'inactive-chips', 'is_active' => false]));
        CatalogItem::create($this->itemData($inactive, ['name' => 'Hidden Category Item', 'slug' => 'hidden-category-item']));
        CatalogItem::create($this->itemData($smoothie, ['name' => 'Mango Smoothie', 'slug' => 'mango-smoothie', 'item_type' => 'smoothie']));

        $html = $this->get('/convenience-store')
            ->assertOk()
            ->assertSee('Cold Drinks')
            ->assertSee('Snacks')
            ->assertSee('Featured Candy')
            ->assertSee('Front counter favorite.')
            ->assertSee('$2.50')
            ->assertSee('media/catalog/candy-medium.jpg')
            ->assertSee('Bottled Water')
            ->assertSee('Currently unavailable')
            ->assertDontSee('Inactive Category')
            ->assertDontSee('Inactive Chips')
            ->assertDontSee('Hidden Category Item')
            ->assertDontSee('Mango Smoothie')
            ->getContent();

        $this->assertLessThan(strpos($html, 'Snacks'), strpos($html, 'Cold Drinks'));
    }

    public function test_smoothies_page_renders_variants_prices_and_ingredients_without_zero_price(): void
    {
        $category = CatalogCategory::create($this->categoryData(['name' => 'Fruit Smoothies', 'slug' => 'fruit-smoothies', 'business_area' => 'smoothie']));
        $other = CatalogCategory::create($this->categoryData(['name' => 'Snacks', 'slug' => 'snacks']));
        $media = $this->media(['path' => 'media/catalog/mango.jpg']);
        $this->variant($media, 'medium', 'media/catalog/mango-medium.jpg');
        $smoothie = CatalogItem::create($this->itemData($category, [
            'name' => 'Mango Banana Smoothie',
            'slug' => 'mango-banana-smoothie',
            'item_type' => 'smoothie',
            'ingredients' => 'Mango, banana, milk',
            'price' => null,
            'price_label' => null,
        ]));
        $this->attach($smoothie, $media, 'primary_image');
        $smoothie->variants()->create(['name' => 'Small', 'price' => 4.99, 'is_default' => true, 'is_available' => true, 'sort_order' => 2]);
        $smoothie->variants()->create(['name' => 'Large', 'price' => 6.99, 'is_available' => true, 'sort_order' => 1]);
        $smoothie->variants()->create(['name' => 'Unavailable Size', 'price' => 9.99, 'is_available' => false, 'sort_order' => 3]);
        CatalogItem::create($this->itemData($category, ['name' => 'No Price Smoothie', 'slug' => 'no-price-smoothie', 'item_type' => 'smoothie', 'price' => null]));
        CatalogItem::create($this->itemData($other, ['name' => 'Candy Bar', 'slug' => 'candy-bar']));

        $html = $this->get('/smoothies')
            ->assertOk()
            ->assertSee('Fruit Smoothies')
            ->assertSee('Mango Banana Smoothie')
            ->assertSee('Mango, banana, milk')
            ->assertSee('Large')
            ->assertSee('$6.99')
            ->assertSee('Small')
            ->assertSee('$4.99')
            ->assertSee('media/catalog/mango-medium.jpg')
            ->assertSee('No Price Smoothie')
            ->assertDontSee('Unavailable Size')
            ->assertDontSee('$0.00')
            ->assertDontSee('Candy Bar')
            ->getContent();

        $this->assertLessThan(strpos($html, 'Small'), strpos($html, 'Large'));
    }

    public function test_adult_retail_page_is_area_scoped_and_age_aware(): void
    {
        $adult = CatalogCategory::create($this->categoryData(['name' => 'Adult Retail', 'slug' => 'adult-retail', 'business_area' => 'adult_retail', 'minimum_age' => 21]));
        $convenience = CatalogCategory::create($this->categoryData(['name' => 'Snacks', 'slug' => 'snacks']));
        CatalogItem::create($this->itemData($adult, [
            'name' => 'Age Restricted Item',
            'slug' => 'age-restricted-item',
            'item_type' => 'adult_product',
            'is_age_restricted' => true,
            'minimum_age' => 21,
        ]));
        CatalogItem::create($this->itemData($adult, ['name' => 'Inactive Adult Item', 'slug' => 'inactive-adult-item', 'item_type' => 'adult_product', 'is_active' => false]));
        CatalogItem::create($this->itemData($convenience, ['name' => 'Candy Bar', 'slug' => 'candy-bar']));

        $this->get('/vape-tobacco')
            ->assertOk()
            ->assertSee('Adult Retail')
            ->assertSee('Age Restricted Item')
            ->assertSee('21+')
            ->assertSee('21+ only')
            ->assertDontSee('Inactive Adult Item')
            ->assertDontSee('Candy Bar');
    }

    public function test_cms_page_and_section_copy_override_catalog_page_fallbacks(): void
    {
        $page = Page::create($this->pageData([
            'name' => 'Convenience CMS',
            'slug' => 'convenience-store',
            'h1' => 'CMS Convenience H1',
            'intro_text' => 'CMS convenience intro.',
        ]));
        PageSection::create([
            'page_id' => $page->id,
            'section_key' => 'catalog',
            'section_type' => 'catalog_categories',
            'subtitle' => 'CMS Catalog Eyebrow',
            'title' => 'CMS Catalog Title',
            'content' => 'CMS catalog section copy.',
            'is_active' => true,
        ]);
        $category = CatalogCategory::create($this->categoryData(['name' => 'CMS Category', 'slug' => 'cms-category']));
        CatalogItem::create($this->itemData($category, ['name' => 'CMS Item', 'slug' => 'cms-item']));

        $this->get('/convenience-store')
            ->assertOk()
            ->assertSee('CMS Convenience H1')
            ->assertSee('CMS convenience intro.')
            ->assertSee('CMS Catalog Eyebrow')
            ->assertSee('CMS Catalog Title')
            ->assertSee('CMS catalog section copy.')
            ->assertSee('CMS Category')
            ->assertSee('CMS Item');
    }

    public function test_catalog_pages_follow_frontend_html_rules(): void
    {
        foreach (['/convenience-store', '/smoothies', '/vape-tobacco'] as $path) {
            $html = $this->get($path)->assertOk()->getContent();

            $this->assertSame(1, preg_match_all('/<h1\b/i', $html), "{$path} H1 count");
            $this->assertSame(0, preg_match_all('/<h[2-6]\b/i', $html), "{$path} H2-H6 count");
            $this->assertStringNotContainsString('.html', $html);
            $this->assertSame(1, preg_match_all('/<header\b/i', $html), "{$path} header count");
            $this->assertSame(1, preg_match_all('/<footer\b/i', $html), "{$path} footer count");
        }
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
            'short_description' => 'Shelf item.',
            'price' => 1.99,
            'is_price_visible' => true,
            'is_age_restricted' => false,
            'is_featured' => false,
            'is_available' => true,
            'is_active' => true,
            'sort_order' => 0,
        ], $overrides);
    }

    private function pageData(array $overrides = []): array
    {
        return array_merge([
            'name' => 'Page',
            'slug' => 'page-'.uniqid(),
            'template' => 'default',
            'h1' => 'Page Heading',
            'robots_index' => true,
            'robots_follow' => true,
            'is_home' => false,
            'status' => Page::STATUS_PUBLISHED,
            'published_at' => now(),
        ], $overrides);
    }

    private function media(array $overrides = []): MediaAsset
    {
        return MediaAsset::create(array_merge([
            'disk' => 'public',
            'directory' => 'media/catalog',
            'path' => 'media/catalog/test.jpg',
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

    private function variant(MediaAsset $media, string $variant, string $path): void
    {
        MediaVariant::create([
            'media_asset_id' => $media->id,
            'variant' => $variant,
            'disk' => 'public',
            'path' => $path,
            'mime_type' => 'image/jpeg',
            'extension' => 'jpg',
            'file_size' => 500,
        ]);
    }

    private function attach(CatalogItem $item, MediaAsset $media, string $collection): void
    {
        MediaAttachment::create([
            'media_asset_id' => $media->id,
            'mediable_type' => CatalogItem::class,
            'mediable_id' => $item->id,
            'collection' => $collection,
            'is_primary' => true,
        ]);
    }
}
