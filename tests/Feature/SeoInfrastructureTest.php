<?php

namespace Tests\Feature;

use App\Models\Business;
use App\Models\BusinessHour;
use App\Models\Location;
use App\Models\MediaAsset;
use App\Models\Page;
use App\Models\Redirect;
use App\Models\SeoSetting;
use App\Models\SocialLink;
use App\Models\User;
use App\Services\SeoService;
use App\Services\StructuredDataService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Cache;
use Tests\TestCase;

class SeoInfrastructureTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        Cache::flush();
    }

    public function test_redirect_can_be_created_and_source_is_normalized(): void
    {
        $user = User::factory()->create(['email' => config('admin.email')]);

        $this->actingAs($user)->post(route('admin.seo.redirects.store'), $this->redirectPayload([
            'source_path' => 'old//phone-repair',
            'target_url' => '/phone-repair',
        ]))->assertRedirect(route('admin.seo.redirects.index'));

        $redirect = Redirect::first();

        $this->assertSame('/old/phone-repair', $redirect->source_path);
        $this->assertSame($user->id, $redirect->created_by);
    }

    public function test_redirect_validation_blocks_self_bad_status_and_dangerous_target(): void
    {
        $user = User::factory()->create(['email' => config('admin.email')]);

        $this->actingAs($user)->post(route('admin.seo.redirects.store'), $this->redirectPayload([
            'source_path' => '/about',
            'target_url' => '/about',
        ]))->assertSessionHasErrors('target_url');

        $this->actingAs($user)->post(route('admin.seo.redirects.store'), $this->redirectPayload([
            'status_code' => 303,
        ]))->assertSessionHasErrors('status_code');

        $this->actingAs($user)->post(route('admin.seo.redirects.store'), $this->redirectPayload([
            'target_url' => 'javascript:alert(1)',
        ]))->assertSessionHasErrors('target_url');

        $this->actingAs($user)->post(route('admin.seo.redirects.store'), $this->redirectPayload([
            'target_url' => '//evil.test/path',
        ]))->assertSessionHasErrors('target_url');
    }

    public function test_exact_redirect_matches_only_exact_path_and_tracks_hits(): void
    {
        Redirect::create($this->redirectData(['source_path' => '/old-page', 'target_url' => '/new-page']));

        $this->get('/old-page')->assertRedirect('/new-page');
        $this->get('/old-page-child')->assertNotFound();

        $redirect = Redirect::first();
        $this->assertSame(1, $redirect->fresh()->hit_count);
        $this->assertNotNull($redirect->fresh()->last_hit_at);
    }

    public function test_prefix_redirect_preserves_suffix_and_query_handling(): void
    {
        Redirect::create($this->redirectData([
            'source_path' => '/old-products/',
            'target_url' => '/products/',
            'match_type' => 'prefix',
            'preserve_query_string' => true,
        ]));

        $this->get('/old-products/item-one?utm_source=google')->assertRedirect('/products/item-one?utm_source=google');

        Redirect::query()->delete();
        Cache::forget(\App\Services\RedirectService::CACHE_KEY_PREFIXES);
        Redirect::create($this->redirectData([
            'source_path' => '/old-products/',
            'target_url' => '/products/?ref=legacy',
            'match_type' => 'prefix',
            'preserve_query_string' => false,
        ]));

        $this->get('/old-products/item-two?utm_source=google')->assertRedirect('/products/item-two?ref=legacy');
    }

    public function test_inactive_redirect_and_admin_paths_are_not_redirected(): void
    {
        Redirect::create($this->redirectData(['source_path' => '/inactive', 'target_url' => '/target', 'is_active' => false]));
        Redirect::create($this->redirectData(['source_path' => '/admin/old', 'target_url' => '/target']));

        $this->get('/inactive')->assertNotFound();
        $this->get('/admin/old')->assertNotFound();
    }

    public function test_redirect_admin_requires_authentication(): void
    {
        $this->get(route('admin.seo.redirects.index'))->assertRedirect('/login');
    }

    public function test_seo_settings_singleton_update_cache_and_media_work(): void
    {
        $user = User::factory()->create(['email' => config('admin.email')]);
        $media = $this->media();

        $this->assertSame(SeoSetting::current()->id, SeoSetting::current()->id);

        $this->actingAs($user)->put(route('admin.seo.settings.update'), $this->seoPayload([
            'site_name' => 'First Name',
            'canonical_base_url' => 'https://example.com/',
            'default_meta_image_media_id' => $media->id,
            'sitemap_enabled' => '0',
        ]))->assertRedirect(route('admin.seo.settings.edit'));

        $setting = SeoSetting::current();
        $this->assertSame('First Name', $setting->site_name);
        $this->assertSame('https://example.com', $setting->canonical_base_url);
        $this->assertFalse($setting->sitemap_enabled);
        $this->assertSame($media->id, $setting->getMediaAsset('default_meta_image')->id);

        $this->actingAs($user)->put(route('admin.seo.settings.update'), $this->seoPayload([
            'site_name' => 'Second Name',
        ]))->assertRedirect(route('admin.seo.settings.edit'));

        $this->assertSame('Second Name', SeoSetting::current()->site_name);
    }

    public function test_seo_settings_requires_authentication(): void
    {
        $this->put(route('admin.seo.settings.update'), $this->seoPayload())->assertRedirect('/login');
    }

    public function test_seo_service_resolves_title_canonical_robots_and_image(): void
    {
        SeoSetting::query()->create($this->seoData(['site_name' => 'Store', 'canonical_base_url' => 'https://example.com']));
        $page = Page::create($this->pageData(['name' => 'Phone Repair', 'meta_title' => 'Phone Repair in Oxon Hill', 'robots_index' => false]));

        $resolved = app(SeoService::class)->resolve($page, request()->create('/phone-repair?utm=1'));

        $this->assertSame('Phone Repair in Oxon Hill | Store', $resolved['title']);
        $this->assertSame('https://example.com/phone-repair', $resolved['canonical']);
        $this->assertSame('noindex, follow', $resolved['robots']);
    }

    public function test_sitemap_includes_only_indexable_published_pages_and_escapes_urls(): void
    {
        SeoSetting::query()->create($this->seoData(['canonical_base_url' => 'https://example.com']));
        Page::create($this->pageData(['name' => 'Published', 'slug' => 'published', 'status' => Page::STATUS_PUBLISHED, 'canonical_url' => 'https://example.com/a?x=1&y=2']));
        Page::create($this->pageData(['name' => 'Draft', 'slug' => 'draft', 'status' => Page::STATUS_DRAFT]));
        Page::create($this->pageData(['name' => 'Private', 'slug' => 'private', 'status' => Page::STATUS_PRIVATE]));
        Page::create($this->pageData(['name' => 'Noindex', 'slug' => 'noindex', 'status' => Page::STATUS_PUBLISHED, 'robots_index' => false]));
        $deleted = Page::create($this->pageData(['name' => 'Deleted', 'slug' => 'deleted', 'status' => Page::STATUS_PUBLISHED]));
        $deleted->delete();

        $response = $this->get('/sitemap.xml')->assertOk();

        $response->assertHeader('Content-Type', 'application/xml; charset=UTF-8');
        $response->assertSee('https://example.com/published', false);
        $response->assertDontSee('https://example.com/a?x=1&amp;y=2', false);
        $response->assertSee('<lastmod>', false);
        $response->assertDontSee('/draft');
        $response->assertDontSee('/private');
        $response->assertDontSee('/noindex');
        $response->assertDontSee('/deleted');
    }

    public function test_disabled_sitemap_returns_empty_urlset(): void
    {
        SeoSetting::query()->create($this->seoData(['sitemap_enabled' => false]));
        Page::create($this->pageData(['slug' => 'published', 'status' => Page::STATUS_PUBLISHED]));

        $response = $this->get('/sitemap.xml')->assertOk();
        $response->assertSee('<urlset', false)->assertDontSee('<loc>', false);
    }

    public function test_robots_txt_output_for_production_and_testing_safety(): void
    {
        $this->app['env'] = 'production';
        SeoSetting::query()->create($this->seoData([
            'canonical_base_url' => 'https://example.com',
            'robots_txt_extra' => "Disallow: /private-test\nAllow: /public-test",
        ]));

        $response = $this->get('/robots.txt')->assertOk();
        $response->assertHeader('Content-Type', 'text/plain; charset=UTF-8');
        $response->assertSee('Disallow: /admin/', false);
        $response->assertSee('Sitemap: https://example.com/sitemap.xml', false);
        $response->assertSee('Disallow: /private-test', false);

        SeoSetting::clearCache();
        $this->app['env'] = 'testing';
        $this->get('/robots.txt')->assertSee("User-agent: *\nDisallow: /", false);
    }

    public function test_structured_data_service_maps_business_location_hours_socials_and_breadcrumbs(): void
    {
        SeoSetting::query()->create($this->seoData(['canonical_base_url' => 'https://example.com']));
        $business = Business::create(['name' => '24/7 Store', 'description' => 'Convenience store', 'schema_types' => ['ConvenienceStore'], 'currency' => 'USD', 'is_active' => true]);
        $location = Location::create([
            'business_id' => $business->id,
            'name' => 'Main Store',
            'slug' => 'main',
            'phone' => '555-111-2222',
            'address_line_1' => '6168 Oxon Hill Rd',
            'city' => 'Oxon Hill',
            'state' => 'MD',
            'postal_code' => '20745',
            'country_code' => 'US',
            'latitude' => '38.8050000',
            'longitude' => '-76.9980000',
            'price_range' => '$',
            'is_primary' => true,
            'is_active' => true,
        ]);
        BusinessHour::create(['location_id' => $location->id, 'day_of_week' => 1, 'opens_at' => '09:00', 'closes_at' => '17:00']);
        SocialLink::create(['business_id' => $business->id, 'platform' => 'facebook', 'url' => 'https://facebook.com/store', 'is_active' => true]);

        $service = app(StructuredDataService::class);
        $local = $service->localBusiness();
        $website = $service->website();
        $breadcrumb = $service->breadcrumb([
            ['name' => 'Home', 'url' => 'https://example.com'],
            ['name' => 'Phone Repair', 'url' => 'https://example.com/phone-repair'],
        ]);

        $this->assertSame('24/7 Store', $local['name']);
        $this->assertSame('6168 Oxon Hill Rd', $local['address']['streetAddress']);
        $this->assertSame('38.8050000', $local['geo']['latitude']);
        $this->assertSame('Monday', $local['openingHoursSpecification'][0]['dayOfWeek']);
        $this->assertSame(['https://facebook.com/store'], $local['sameAs']);
        $this->assertSame('WebSite', $website['@type']);
        $this->assertSame(2, $breadcrumb['itemListElement'][1]['position']);
        $this->assertStringNotContainsString('<script', json_encode($local));
    }

    private function redirectPayload(array $overrides = []): array
    {
        return array_merge([
            'source_path' => '/old-page',
            'target_url' => '/new-page',
            'match_type' => 'exact',
            'status_code' => 301,
            'preserve_query_string' => '1',
            'is_active' => '1',
        ], $overrides);
    }

    private function seoPayload(array $overrides = []): array
    {
        return array_merge([
            'site_name' => '24/7 IN N OUT CONVENIENCE STORE',
            'title_separator' => '|',
            'canonical_base_url' => 'https://example.com',
            'default_robots_index' => '1',
            'default_robots_follow' => '1',
            'twitter_card' => 'summary_large_image',
            'sitemap_enabled' => '1',
            'robots_enabled' => '1',
            'structured_data_enabled' => '1',
        ], $overrides);
    }

    private function redirectData(array $overrides = []): array
    {
        return array_merge([
            'source_path' => '/old-page',
            'target_url' => '/new-page',
            'match_type' => 'exact',
            'status_code' => 301,
            'preserve_query_string' => true,
            'is_active' => true,
        ], $overrides);
    }

    private function seoData(array $overrides = []): array
    {
        return array_merge([
            'site_name' => '24/7 IN N OUT CONVENIENCE STORE',
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

    private function pageData(array $overrides = []): array
    {
        return array_merge([
            'name' => 'Page',
            'slug' => 'page-'.uniqid(),
            'template' => 'default',
            'h1' => 'Page',
            'status' => Page::STATUS_DRAFT,
            'robots_index' => true,
            'robots_follow' => true,
            'is_home' => false,
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
