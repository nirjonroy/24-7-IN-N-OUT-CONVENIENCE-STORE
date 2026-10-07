<?php

namespace Tests\Feature;

use App\Models\Business;
use App\Models\BusinessHour;
use App\Models\Location;
use App\Models\MediaAsset;
use App\Models\MediaAttachment;
use App\Models\Menu;
use App\Models\MenuItem;
use App\Models\Page;
use App\Models\PageSection;
use App\Models\Review;
use App\Models\SectionItem;
use App\Models\SeoSetting;
use App\Models\SocialLink;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class FrontendHomeIntegrationTest extends TestCase
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

    public function test_home_renders_published_cms_business_menus_reviews_seo_and_schema(): void
    {
        SeoSetting::query()->create($this->seoData([
            'site_name' => '24/7 Store',
            'canonical_base_url' => 'https://example.com',
            'structured_data_enabled' => true,
        ]));

        $home = Page::create($this->pageData([
            'name' => 'Home',
            'slug' => 'home',
            'h1' => 'CMS Home Heading',
            'intro_text' => 'CMS intro text.',
            'is_home' => true,
            'meta_title' => 'Custom Home Title',
            'meta_description' => 'Custom home description.',
        ]));
        Page::create($this->pageData([
            'name' => 'Draft Home',
            'slug' => 'draft-home',
            'h1' => 'Draft Private Heading',
            'status' => Page::STATUS_DRAFT,
            'is_home' => true,
        ]));
        $store = Page::create($this->pageData(['name' => 'Store', 'slug' => 'store', 'h1' => 'Store Page']));
        $private = Page::create($this->pageData(['name' => 'Private', 'slug' => 'private', 'h1' => 'Private Page', 'status' => Page::STATUS_PRIVATE]));

        $hero = PageSection::create([
            'page_id' => $home->id,
            'section_key' => 'hero',
            'section_type' => 'hero',
            'subtitle' => 'Oxon Hill',
            'content' => 'Hero dynamic copy.',
            'primary_button_label' => 'Directions now',
            'primary_button_url' => 'https://maps.example.com',
            'secondary_button_label' => 'Store page',
            'secondary_button_url' => '/store',
            'is_active' => true,
        ]);
        SectionItem::create(['page_section_id' => $hero->id, 'title' => 'Hero Card', 'button_url' => '/store', 'is_active' => true]);

        $services = PageSection::create([
            'page_id' => $home->id,
            'section_key' => 'services',
            'section_type' => 'service_grid',
            'subtitle' => 'Services',
            'title' => 'Dynamic services',
            'content' => 'Dynamic section copy.',
            'sort_order' => 2,
            'is_active' => true,
        ]);
        SectionItem::create(['page_section_id' => $services->id, 'title' => 'Phone Repair', 'description' => 'Repair copy', 'button_label' => 'Repair info', 'button_url' => '/phone-repair', 'is_active' => true]);
        SectionItem::create(['page_section_id' => $services->id, 'title' => 'Hidden Item', 'is_active' => false]);
        PageSection::create(['page_id' => $home->id, 'section_key' => 'hidden', 'section_type' => 'text', 'title' => 'Hidden Section', 'is_active' => false]);
        PageSection::create(['page_id' => $home->id, 'section_key' => 'unknown', 'section_type' => 'unknown_type', 'title' => 'Unknown Section Works', 'is_active' => true, 'sort_order' => 3]);

        $business = Business::create(['name' => '24/7 Store LLC', 'short_name' => '24/7 Store', 'tagline' => 'Oxon Hill, Maryland', 'description' => 'Business dynamic description.', 'schema_types' => ['ConvenienceStore'], 'is_active' => true]);
        $location = Location::create([
            'business_id' => $business->id,
            'name' => 'Main Location',
            'slug' => 'main',
            'phone' => '555-111-2222',
            'email' => 'info@example.com',
            'address_line_1' => '6168 Oxon Hill Rd',
            'city' => 'Oxon Hill',
            'state' => 'MD',
            'postal_code' => '20745',
            'country_code' => 'US',
            'google_maps_url' => 'https://maps.example.com',
            'is_primary' => true,
            'is_active' => true,
        ]);
        BusinessHour::create(['location_id' => $location->id, 'day_of_week' => 1, 'opens_at' => '09:00', 'closes_at' => '17:00']);
        SocialLink::create(['business_id' => $business->id, 'platform' => 'facebook', 'label' => 'Facebook', 'url' => 'https://facebook.com/store', 'is_active' => true]);
        SocialLink::create(['business_id' => $business->id, 'platform' => 'x', 'label' => 'Inactive Social', 'url' => 'https://example.com/x', 'is_active' => false]);

        $header = Menu::create(['name' => 'Header', 'key' => 'header', 'location' => 'header', 'is_active' => true]);
        MenuItem::create(['menu_id' => $header->id, 'page_id' => $home->id, 'label' => 'Home', 'link_type' => MenuItem::LINK_PAGE, 'is_active' => true]);
        MenuItem::create(['menu_id' => $header->id, 'page_id' => $store->id, 'label' => 'Store Menu', 'link_type' => MenuItem::LINK_PAGE, 'is_active' => true]);
        MenuItem::create(['menu_id' => $header->id, 'page_id' => $private->id, 'label' => 'Private Menu', 'link_type' => MenuItem::LINK_PAGE, 'is_active' => true]);
        MenuItem::create(['menu_id' => $header->id, 'url' => '/hidden', 'label' => 'Inactive Menu', 'link_type' => MenuItem::LINK_CUSTOM, 'is_active' => false]);

        $footerPrimary = Menu::create(['name' => 'Footer Primary', 'key' => 'footer-primary', 'location' => 'footer_primary', 'is_active' => true]);
        MenuItem::create(['menu_id' => $footerPrimary->id, 'url' => '/smoothies', 'label' => 'Footer Smoothies', 'link_type' => MenuItem::LINK_CUSTOM, 'is_active' => true]);
        $footerSecondary = Menu::create(['name' => 'Footer Secondary', 'key' => 'footer-secondary', 'location' => 'footer_secondary', 'is_active' => true]);
        MenuItem::create(['menu_id' => $footerSecondary->id, 'url' => '/contact', 'label' => 'Footer Contact', 'link_type' => MenuItem::LINK_CUSTOM, 'is_active' => true]);

        Review::create(['author_name' => 'Alice Buyer', 'rating' => 5, 'review_text' => 'Great local stop.', 'is_featured' => true, 'is_active' => true]);
        Review::create(['author_name' => 'Inactive Reviewer', 'review_text' => 'Do not show', 'is_featured' => true, 'is_active' => false]);

        $response = $this->get('/');
        $html = $response->getContent();

        $response->assertOk()
            ->assertSee('CMS Home Heading')
            ->assertSee('Hero dynamic copy.')
            ->assertSee('Dynamic services')
            ->assertSee('Convenience • Repair • Smoothies • 21+ retail')
            ->assertSee('storefront-google.webp')
            ->assertSee('Phone Repair')
            ->assertSee('Unknown Section Works')
            ->assertSee('24/7 Store')
            ->assertSee('6168 Oxon Hill Rd')
            ->assertSee('555-111-2222')
            ->assertSee('Facebook')
            ->assertSee('Store Menu')
            ->assertSee('Footer Smoothies')
            ->assertSee('Footer Contact')
            ->assertSee('Alice Buyer')
            ->assertSee('Custom Home Title')
            ->assertSee('Custom home description.')
            ->assertSee('https://example.com')
            ->assertSee('application/ld+json', false)
            ->assertSee('ConvenienceStore')
            ->assertSee('data-theme-toggle')
            ->assertSee('id="mobile-menu"', false)
            ->assertDontSee('Draft Private Heading')
            ->assertDontSee('Private Menu')
            ->assertDontSee('Inactive Menu')
            ->assertDontSee('Hidden Section')
            ->assertDontSee('Hidden Item')
            ->assertDontSee('Inactive Social')
            ->assertDontSee('Inactive Reviewer');

        $this->assertSame(1, preg_match_all('/<h1\b/i', $html));
        $this->assertSame(0, preg_match_all('/<h[2-6]\b/i', $html));
        $this->assertSame(1, preg_match_all('/<title>/i', $html));
        $this->assertSame(1, preg_match_all('/<meta name="description"/i', $html));
        $this->assertSame(1, preg_match_all('/<link rel="canonical"/i', $html));
    }

    public function test_home_falls_back_when_no_published_home_and_hides_structured_data_when_disabled(): void
    {
        SeoSetting::query()->create($this->seoData(['structured_data_enabled' => false]));
        Page::create($this->pageData(['slug' => 'private-home', 'h1' => 'Private H1', 'is_home' => true, 'status' => Page::STATUS_PRIVATE]));

        $response = $this->get('/');

        $response->assertOk()
            ->assertSee('Convenience, phone repair and smoothies — all at one Oxon Hill stop.')
            ->assertSee('Store')
            ->assertSee('Phone Repair')
            ->assertSee('21+ Vape')
            ->assertSee('storefront-google.webp')
            ->assertSee('A practical neighborhood stop, built around everyday needs.')
            ->assertSee('Easy to find in Oxon Hill.')
            ->assertSee('Find 24/7 IN N OUT on Oxon Hill Rd.')
            ->assertDontSee('Private H1')
            ->assertDontSee('application/ld+json', false);
    }

    public function test_cms_hero_media_overrides_static_hero_fallback(): void
    {
        SeoSetting::query()->create($this->seoData());
        $home = Page::create($this->pageData(['slug' => 'home', 'is_home' => true]));
        $hero = PageSection::create([
            'page_id' => $home->id,
            'section_key' => 'hero',
            'section_type' => 'hero',
            'is_active' => true,
        ]);
        $media = MediaAsset::create([
            'disk' => 'public',
            'directory' => 'cms',
            'path' => 'cms/custom-hero.webp',
            'file_name' => 'custom-hero.webp',
            'original_name' => 'custom-hero.webp',
            'mime_type' => 'image/webp',
            'extension' => 'webp',
            'file_size' => 1000,
            'is_active' => true,
        ]);
        MediaAttachment::create([
            'media_asset_id' => $media->id,
            'mediable_type' => PageSection::class,
            'mediable_id' => $hero->id,
            'collection' => 'image',
            'is_primary' => true,
        ]);

        $this->get('/')
            ->assertOk()
            ->assertSee('/storage/cms/custom-hero.webp', false);
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

    private function seoData(array $overrides = []): array
    {
        return array_merge([
            'site_name' => '24/7 IN N OUT',
            'title_separator' => '|',
            'canonical_base_url' => 'https://example.com',
            'default_robots_index' => true,
            'default_robots_follow' => true,
            'twitter_card' => 'summary_large_image',
            'sitemap_enabled' => true,
            'robots_enabled' => true,
            'structured_data_enabled' => true,
        ], $overrides);
    }
}
