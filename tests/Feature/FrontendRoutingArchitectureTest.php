<?php

namespace Tests\Feature;

use App\Models\Menu;
use App\Models\MenuItem;
use App\Models\Page;
use App\Models\Redirect;
use App\Models\SeoSetting;
use App\Models\User;
use App\Services\SitemapService;
use Database\Seeders\LegacyFrontendRedirectSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Cache;
use Tests\TestCase;

class FrontendRoutingArchitectureTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        Cache::flush();
        SeoSetting::clearCache();
    }

    public function test_published_generic_page_renders_with_clean_seo_and_heading_rules(): void
    {
        SeoSetting::query()->create($this->seoData(['canonical_base_url' => 'https://example.com']));
        Page::create($this->pageData([
            'name' => 'Privacy Policy',
            'slug' => 'privacy-policy',
            'h1' => 'Privacy Policy',
            'intro_text' => 'How the store handles website inquiries.',
            'meta_description' => 'Privacy policy for website inquiries.',
        ]));

        $html = $this->get('/privacy-policy')
            ->assertOk()
            ->assertSee('Privacy Policy')
            ->assertSee('How the store handles website inquiries.')
            ->getContent();

        $this->assertSame(1, preg_match_all('/<h1\b/i', $html));
        $this->assertSame(0, preg_match_all('/<h[2-6]\b/i', $html));
        $this->assertSame(1, preg_match_all('/<title>/i', $html));
        $this->assertSame(1, preg_match_all('/<meta name="description"/i', $html));
        $this->assertSame(1, preg_match_all('/<link rel="canonical"/i', $html));
        $this->assertStringContainsString('href="https://example.com/privacy-policy"', $html);
        $this->assertStringNotContainsString('.html', $html);
    }

    public function test_draft_private_future_and_missing_generic_pages_return_custom_404(): void
    {
        Page::create($this->pageData(['name' => 'Draft', 'slug' => 'draft-page', 'status' => Page::STATUS_DRAFT, 'published_at' => null]));
        Page::create($this->pageData(['name' => 'Private', 'slug' => 'private-page', 'status' => Page::STATUS_PRIVATE]));
        Page::create($this->pageData(['name' => 'Future', 'slug' => 'future-page', 'published_at' => now()->addDay()]));

        foreach (['/draft-page', '/private-page', '/future-page', '/this-page-does-not-exist'] as $path) {
            $html = $this->get($path)
                ->assertNotFound()
                ->assertSee('Page not found')
                ->assertSee('Back to Home')
                ->getContent();

            $this->assertStringContainsString('noindex, nofollow', $html);
            $this->assertSame(1, preg_match_all('/<h1\b/i', $html));
            $this->assertSame(0, preg_match_all('/<h[2-6]\b/i', $html));
            $this->assertStringNotContainsString('.html', $html);
        }
    }

    public function test_unmatched_redirects_and_legacy_html_redirects_work_with_query_strings(): void
    {
        Redirect::create($this->redirectData(['source_path' => '/old-random-url', 'target_url' => '/about']));
        $this->get('/old-random-url')->assertRedirect('/about');

        $this->seed(LegacyFrontendRedirectSeeder::class);

        $this->get('/index.html')->assertStatus(301)->assertRedirect('/');
        $this->get('/about.html?utm_source=test')->assertStatus(301)->assertRedirect('/about?utm_source=test');
        $this->get('/contact.html')->assertStatus(301)->assertRedirect('/contact');
        $this->get('/phone-repair.html')->assertStatus(301)->assertRedirect('/phone-repair');
        $this->get('/smoothies.html')->assertStatus(301)->assertRedirect('/smoothies');

        $this->assertSame(1, Redirect::where('source_path', '/about.html')->count());
        $this->seed(LegacyFrontendRedirectSeeder::class);
        $this->assertSame(1, Redirect::where('source_path', '/about.html')->count());
    }

    public function test_reserved_slugs_are_blocked_and_generic_route_does_not_intercept_system_routes(): void
    {
        $user = User::factory()->create();

        $this->actingAs($user)->post(route('admin.pages.store'), $this->pagePayload([
            'name' => 'Admin',
            'slug' => 'admin',
        ]))->assertSessionHasErrors(['slug' => 'This URL slug is reserved by the application.']);

        $this->get('/login')->assertRedirect('/dashboard');
        $this->get('/sitemap.xml')->assertOk();
        $this->get('/robots.txt')->assertOk();
        $this->get('/phone-repair')->assertOk();
        $this->getJson('/phone-repair/models')->assertStatus(422)->assertJsonValidationErrors('brand_id');
        $this->getJson('/phone-repair/estimate')->assertStatus(422)->assertJsonValidationErrors(['device_model_id', 'repair_service_id']);
        $this->get('/contact')->assertOk();
    }

    public function test_menu_uses_page_url_service_and_hides_unresolvable_generic_pages(): void
    {
        $header = Menu::create(['name' => 'Header', 'key' => 'header', 'location' => 'header', 'is_active' => true]);
        $published = Page::create($this->pageData(['name' => 'Privacy Policy', 'slug' => 'privacy-policy']));
        $draft = Page::create($this->pageData(['name' => 'Draft Policy', 'slug' => 'draft-policy', 'status' => Page::STATUS_DRAFT, 'published_at' => null]));
        $about = Page::create($this->pageData(['name' => 'About', 'slug' => 'about']));

        MenuItem::create(['menu_id' => $header->id, 'page_id' => $published->id, 'label' => 'Privacy', 'link_type' => MenuItem::LINK_PAGE, 'is_active' => true]);
        MenuItem::create(['menu_id' => $header->id, 'page_id' => $draft->id, 'label' => 'Draft', 'link_type' => MenuItem::LINK_PAGE, 'is_active' => true]);
        MenuItem::create(['menu_id' => $header->id, 'page_id' => $about->id, 'label' => 'About', 'link_type' => MenuItem::LINK_PAGE, 'is_active' => true]);

        $html = $this->get('/')->assertOk()->getContent();

        $this->assertStringContainsString('href="http://localhost/privacy-policy"', $html);
        $this->assertStringContainsString('href="http://localhost/about"', $html);
        $this->assertStringNotContainsString('draft-policy', $html);
    }

    public function test_slug_change_creates_generic_redirect_and_sitemap_uses_public_urls_once(): void
    {
        SeoSetting::query()->create($this->seoData(['canonical_base_url' => 'https://example.com']));
        $page = Page::create($this->pageData(['name' => 'Our Story', 'slug' => 'our-story']));
        Page::create($this->pageData(['name' => 'Draft', 'slug' => 'draft-page', 'status' => Page::STATUS_DRAFT, 'published_at' => null]));
        Page::create($this->pageData(['name' => 'Private', 'slug' => 'private-page', 'status' => Page::STATUS_PRIVATE]));
        Page::create($this->pageData(['name' => 'Future', 'slug' => 'future-page', 'published_at' => now()->addDay()]));
        Page::create($this->pageData(['name' => 'Noindex', 'slug' => 'noindex-page', 'robots_index' => false]));
        Page::create($this->pageData(['name' => 'About', 'slug' => 'about']));

        $page->update(['slug' => 'our-history']);

        $this->assertDatabaseHas('redirects', [
            'source_path' => '/our-story',
            'target_url' => '/our-history',
            'status_code' => 301,
        ]);
        $this->get('/our-story')->assertRedirect('/our-history');

        app(SitemapService::class)->clearCache();
        $xml = $this->get('/sitemap.xml')->assertOk()->getContent();

        $this->assertStringContainsString('https://example.com/our-history', $xml);
        $this->assertStringContainsString('https://example.com/about', $xml);
        $this->assertSame(1, substr_count($xml, 'https://example.com/about'));
        $this->assertStringNotContainsString('/draft-page', $xml);
        $this->assertStringNotContainsString('/private-page', $xml);
        $this->assertStringNotContainsString('/future-page', $xml);
        $this->assertStringNotContainsString('/noindex-page', $xml);
        $this->assertStringNotContainsString('.html', $xml);
        $this->assertStringNotContainsString('phone-repair/models', $xml);
        $this->assertStringNotContainsString('404', $xml);
    }

    public function test_production_robots_disallows_static_references_but_not_public_assets(): void
    {
        $this->app['env'] = 'production';
        SeoSetting::query()->create($this->seoData(['canonical_base_url' => 'https://example.com']));

        $text = $this->get('/robots.txt')->assertOk()->getContent();

        $this->assertStringContainsString('Disallow: /frontend-asset/', $text);
        $this->assertStringContainsString('Sitemap: https://example.com/sitemap.xml', $text);
        $this->assertStringNotContainsString('Disallow: /build/', $text);
        $this->assertStringNotContainsString('Disallow: /storage/', $text);
    }

    public function test_public_responses_include_baseline_security_headers(): void
    {
        $response = $this->get('/')->assertOk();

        $response->assertHeader('X-Content-Type-Options', 'nosniff');
        $response->assertHeader('X-Frame-Options', 'SAMEORIGIN');
        $response->assertHeader('Referrer-Policy', 'strict-origin-when-cross-origin');
        $response->assertHeader('Permissions-Policy');
    }

    private function pagePayload(array $overrides = []): array
    {
        return array_merge([
            'name' => 'Page',
            'slug' => '',
            'template' => 'default',
            'h1' => 'Page',
            'status' => Page::STATUS_DRAFT,
            'sort_order' => 0,
            'robots_index' => '1',
            'robots_follow' => '1',
            'is_home' => '0',
        ], $overrides);
    }

    private function pageData(array $overrides = []): array
    {
        return array_merge([
            'name' => 'Page',
            'slug' => 'page-'.uniqid(),
            'template' => 'default',
            'h1' => 'Page Heading',
            'intro_text' => null,
            'robots_index' => true,
            'robots_follow' => true,
            'is_home' => false,
            'status' => Page::STATUS_PUBLISHED,
            'published_at' => now(),
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
}
