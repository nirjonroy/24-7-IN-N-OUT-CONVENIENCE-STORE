<?php

namespace Tests\Feature;

use App\Models\Menu;
use App\Models\MenuItem;
use App\Models\Page;
use App\Models\SeoSetting;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class FrontendPublicRoutesTest extends TestCase
{
    use RefreshDatabase;

    private array $publicRoutes = [
        '/' => 'home',
        '/convenience-store' => 'frontend.convenience-store',
        '/phone-repair' => 'frontend.phone-repair',
        '/smoothies' => 'frontend.smoothies',
        '/vape-tobacco' => 'frontend.vape-tobacco',
        '/about' => 'frontend.about',
        '/faq' => 'frontend.faq',
        '/gallery' => 'frontend.gallery',
        '/contact' => 'frontend.contact',
    ];

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

    public function test_all_approved_public_routes_return_200_with_static_fallbacks(): void
    {
        foreach ($this->publicRoutes as $path => $routeName) {
            $this->assertTrue(route($routeName) !== '', "Route {$routeName} should resolve.");
            $this->get($path)->assertOk();
        }
    }

    public function test_special_and_admin_routes_are_not_intercepted(): void
    {
        $this->get('/sitemap.xml')->assertOk();
        $this->get('/robots.txt')->assertOk();

        $this->actingAs(User::factory()->create(['email' => config('admin.email')]))
            ->get('/dashboard')
            ->assertRedirect('/admin');
    }

    public function test_header_links_use_clean_laravel_urls_and_each_target_resolves(): void
    {
        $header = Menu::create([
            'name' => 'Header',
            'key' => 'header',
            'location' => 'header',
            'is_active' => true,
        ]);

        foreach ([
            ['Home', 'home'],
            ['Store', 'convenience-store'],
            ['Phone Repair', 'phone-repair'],
            ['Smoothies', 'smoothies'],
            ['21+ Vape', 'vape-tobacco'],
            ['About', 'about'],
            ['Contact', 'contact'],
        ] as [$label, $slug]) {
            $page = Page::create($this->pageData([
                'name' => $label,
                'slug' => $slug,
                'h1' => "{$label} CMS H1",
                'is_home' => $slug === 'home',
            ]));

            MenuItem::create([
                'menu_id' => $header->id,
                'page_id' => $page->id,
                'label' => $label,
                'link_type' => MenuItem::LINK_PAGE,
                'is_active' => true,
            ]);
        }

        $html = $this->get('/')->assertOk()->getContent();

        foreach (array_keys($this->publicRoutes) as $path) {
            if ($path === '/') {
                continue;
            }

            $this->assertStringContainsString('href="http://localhost'.$path.'"', $html);
            $this->get($path)->assertOk();
        }

        $this->assertStringNotContainsString('.html', $html);
    }

    public function test_public_pages_have_one_h1_no_h2_to_h6_and_clean_seo_tags(): void
    {
        foreach ($this->publicRoutes as $path => $routeName) {
            $html = $this->get($path)->assertOk()->getContent();

            $this->assertSame(1, preg_match_all('/<h1\b/i', $html), "{$path} should render exactly one H1.");
            $this->assertSame(0, preg_match_all('/<h[2-6]\b/i', $html), "{$path} should not render H2-H6 tags.");
            $this->assertSame(1, preg_match_all('/<title>/i', $html), "{$path} should render one title tag.");
            $this->assertSame(1, preg_match_all('/<meta name="description"/i', $html), "{$path} should render one meta description.");
            $this->assertSame(1, preg_match_all('/<meta name="robots"/i', $html), "{$path} should render one robots meta tag.");
            $this->assertSame(1, preg_match_all('/<link rel="canonical"/i', $html), "{$path} should render one canonical tag.");
            $this->assertStringContainsString('href="'.route($routeName).'"', $html);
            $this->assertStringNotContainsString('.html', $html);
        }
    }

    public function test_inner_pages_keep_required_static_fallback_imagery(): void
    {
        $expectations = [
            '/about' => 'storefront-google.webp',
            '/convenience-store' => 'convenience-illustration.svg',
            '/phone-repair' => 'phone-repair-illustration.svg',
            '/smoothies' => 'smoothie-illustration.svg',
            '/vape-tobacco' => 'adult-21-illustration.svg',
            '/gallery' => 'storefront-google.webp',
            '/contact' => 'output=embed',
        ];

        foreach ($expectations as $path => $needle) {
            $this->get($path)
                ->assertOk()
                ->assertSee($needle, false);
        }
    }

    public function test_published_cms_page_overrides_inner_page_h1_and_intro(): void
    {
        Page::create($this->pageData([
            'name' => 'About CMS',
            'slug' => 'about',
            'h1' => 'CMS About Heading',
            'intro_text' => 'CMS about intro text.',
        ]));

        $this->get('/about')
            ->assertOk()
            ->assertSee('CMS About Heading')
            ->assertSee('CMS about intro text.')
            ->assertDontSee('One Oxon Hill location, four clearly defined categories.');
    }

    public function test_draft_page_content_does_not_leak_publicly(): void
    {
        Page::create($this->pageData([
            'name' => 'Draft About',
            'slug' => 'about',
            'h1' => 'Private Draft Heading',
            'intro_text' => 'Private draft intro.',
            'status' => Page::STATUS_DRAFT,
            'published_at' => null,
        ]));

        $this->get('/about')
            ->assertOk()
            ->assertSee('One Oxon Hill location, four clearly defined categories.')
            ->assertDontSee('Private Draft Heading')
            ->assertDontSee('Private draft intro.');
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
}
