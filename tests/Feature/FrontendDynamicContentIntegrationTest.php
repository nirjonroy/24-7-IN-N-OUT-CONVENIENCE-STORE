<?php

namespace Tests\Feature;

use App\Models\Faq;
use App\Models\FaqCategory;
use App\Models\GalleryCategory;
use App\Models\GalleryItem;
use App\Models\MediaAsset;
use App\Models\MediaAttachment;
use App\Models\MediaVariant;
use App\Models\Page;
use App\Models\PageSection;
use App\Models\SectionItem;
use App\Models\SeoSetting;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class FrontendDynamicContentIntegrationTest extends TestCase
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

    public function test_about_renders_cms_content_sections_ordering_and_dynamic_media(): void
    {
        $page = Page::create($this->pageData([
            'name' => 'About',
            'slug' => 'about',
            'h1' => 'CMS About Heading',
            'intro_text' => 'CMS about intro.',
        ]));

        $hero = PageSection::create($this->sectionData($page, [
            'section_key' => 'hero',
            'section_type' => 'hero',
            'section_label' => 'CMS eyebrow',
            'content' => 'CMS story paragraph.',
            'sort_order' => 1,
        ]));
        $this->attachMedia($hero, 'image', 'about/original.jpg', [
            ['variant' => 'large', 'path' => 'about/large.jpg', 'width' => 1200, 'height' => 800],
        ], 'CMS about image');

        $identity = PageSection::create($this->sectionData($page, [
            'section_key' => 'identity',
            'section_type' => 'feature_grid',
            'section_label' => 'CMS identity',
            'title' => 'CMS identity heading',
            'content' => 'CMS identity copy.',
            'sort_order' => 2,
        ]));
        SectionItem::create($this->itemData($identity, [
            'subtitle' => 'First label',
            'title' => 'First dynamic card',
            'sort_order' => 1,
        ]));

        PageSection::create($this->sectionData($page, [
            'section_key' => 'inactive',
            'title' => 'Hidden about section',
            'is_active' => false,
            'sort_order' => 3,
        ]));
        PageSection::create($this->sectionData($page, [
            'section_key' => 'second',
            'title' => 'Second visible section',
            'content' => 'Second visible content.',
            'sort_order' => 4,
        ]));

        $this->get('/about')
            ->assertOk()
            ->assertSee('CMS About Heading')
            ->assertSee('CMS about intro.')
            ->assertSee('CMS story paragraph.')
            ->assertSee('/storage/about/large.jpg', false)
            ->assertSee('First dynamic card')
            ->assertSee('Second visible section')
            ->assertDontSee('Hidden about section')
            ->assertSeeInOrder(['CMS identity heading', 'First dynamic card', 'Second visible section']);
    }

    public function test_about_static_fallback_renders_when_no_page_exists(): void
    {
        $this->get('/about')
            ->assertOk()
            ->assertSee('One Oxon Hill location, four clearly defined categories.')
            ->assertSee('storefront-google.webp');
    }

    public function test_faq_prefers_page_assignments_and_filters_inactive_content(): void
    {
        $page = Page::create($this->pageData(['name' => 'FAQ', 'slug' => 'faq']));
        $category = FaqCategory::create($this->faqCategoryData(['name' => 'Store Information', 'slug' => 'store-information']));
        $inactiveCategory = FaqCategory::create($this->faqCategoryData(['name' => 'Hidden Category', 'slug' => 'hidden-category', 'is_active' => false]));

        $second = Faq::create($this->faqData($category, [
            'question' => 'Second assigned question?',
            'answer' => 'Second assigned answer.',
            'sort_order' => 1,
        ]));
        $first = Faq::create($this->faqData($category, [
            'question' => 'First assigned question?',
            'answer' => 'First assigned answer.',
            'sort_order' => 2,
        ]));
        $page->faqs()->attach($second->id, ['sort_order' => 20]);
        $page->faqs()->attach($first->id, ['sort_order' => 10]);

        Faq::create($this->faqData($category, ['question' => 'Global unassigned question?', 'answer' => 'Global answer.']));
        Faq::create($this->faqData($category, ['question' => 'Inactive question?', 'answer' => 'Inactive answer.', 'is_active' => false]));
        Faq::create($this->faqData($inactiveCategory, ['question' => 'Inactive category question?', 'answer' => 'Inactive category answer.']));
        $deleted = Faq::create($this->faqData($category, ['question' => 'Deleted question?', 'answer' => 'Deleted answer.']));
        $deleted->delete();

        $this->get('/faq')
            ->assertOk()
            ->assertSee('Store Information')
            ->assertSeeInOrder(['First assigned question?', 'Second assigned question?'])
            ->assertSee('First assigned answer.')
            ->assertDontSee('Global unassigned question?')
            ->assertDontSee('Inactive question?')
            ->assertDontSee('Inactive category question?')
            ->assertDontSee('Deleted question?')
            ->assertSee('aria-expanded="false"', false)
            ->assertSee('aria-controls=', false)
            ->assertSee('role="region"', false);

        $first->update(['question' => 'Updated assigned question?', 'answer' => 'Updated assigned answer.']);

        $this->get('/faq')
            ->assertOk()
            ->assertSee('Updated assigned question?')
            ->assertSee('Updated assigned answer.')
            ->assertDontSee('First assigned question?');
    }

    public function test_faq_uses_active_global_records_when_page_has_no_assignments(): void
    {
        Page::create($this->pageData(['name' => 'FAQ', 'slug' => 'faq']));
        $category = FaqCategory::create($this->faqCategoryData(['name' => 'General Store', 'slug' => 'general-store', 'sort_order' => 5]));
        Faq::create($this->faqData($category, ['question' => 'Later global?', 'answer' => 'Later answer.', 'sort_order' => 2]));
        Faq::create($this->faqData(null, ['question' => 'Earlier uncategorized?', 'answer' => 'Earlier answer.', 'sort_order' => 1]));

        $this->get('/faq')
            ->assertOk()
            ->assertSee('General')
            ->assertSee('General Store')
            ->assertSeeInOrder(['Earlier uncategorized?', 'Later global?']);
    }

    public function test_gallery_renders_active_items_categories_and_media_only(): void
    {
        $activeCategory = GalleryCategory::create($this->galleryCategoryData(['name' => 'Store Exterior', 'slug' => 'store-exterior']));
        $inactiveCategory = GalleryCategory::create($this->galleryCategoryData(['name' => 'Hidden Gallery', 'slug' => 'hidden-gallery', 'is_active' => false]));

        $featured = GalleryItem::create($this->galleryItemData($activeCategory, [
            'title' => 'Featured storefront',
            'caption' => 'Featured storefront caption',
            'is_featured' => true,
            'sort_order' => 5,
        ]));
        $this->attachMedia($featured, 'image', 'gallery/original.jpg', [
            ['variant' => 'thumbnail', 'path' => 'gallery/thumb.jpg', 'width' => 360, 'height' => 240],
            ['variant' => 'large', 'path' => 'gallery/large.jpg', 'width' => 1200, 'height' => 800],
        ], 'Featured alt override');

        $active = GalleryItem::create($this->galleryItemData($activeCategory, [
            'title' => 'Active shelf',
            'caption' => null,
            'sort_order' => 1,
        ]));
        $this->attachMedia($active, 'image', 'gallery/active-original.jpg', [
            ['variant' => 'thumbnail', 'path' => 'gallery/active-thumb.jpg', 'width' => 360, 'height' => 240],
        ]);

        $inactive = GalleryItem::create($this->galleryItemData($activeCategory, ['title' => 'Inactive gallery item', 'is_active' => false]));
        $this->attachMedia($inactive, 'image', 'gallery/inactive.jpg');
        $hiddenCategoryItem = GalleryItem::create($this->galleryItemData($inactiveCategory, ['title' => 'Inactive category gallery item']));
        $this->attachMedia($hiddenCategoryItem, 'image', 'gallery/hidden-category.jpg');
        GalleryItem::create($this->galleryItemData($activeCategory, ['title' => 'Missing media item']));
        $deleted = GalleryItem::create($this->galleryItemData($activeCategory, ['title' => 'Deleted gallery item']));
        $this->attachMedia($deleted, 'image', 'gallery/deleted.jpg');
        $deleted->delete();

        $this->get('/gallery')
            ->assertOk()
            ->assertSee('Featured storefront caption')
            ->assertSee('Active shelf')
            ->assertSee('Store Exterior')
            ->assertSee('/storage/gallery/thumb.jpg', false)
            ->assertSee('alt="Featured alt override"', false)
            ->assertDontSee('Inactive gallery item')
            ->assertDontSee('Inactive category gallery item')
            ->assertDontSee('Hidden Gallery')
            ->assertDontSee('Missing media item')
            ->assertDontSee('Deleted gallery item')
            ->assertSeeInOrder(['Featured storefront caption', 'Active shelf']);
    }

    public function test_about_faq_and_gallery_keep_shared_seo_and_html_rules(): void
    {
        foreach (['/about', '/faq', '/gallery'] as $path) {
            $html = $this->get($path)->assertOk()->getContent();

            $this->assertSame(1, preg_match_all('/<h1\b/i', $html), "{$path} should have one H1.");
            $this->assertSame(0, preg_match_all('/<h[2-6]\b/i', $html), "{$path} should not render H2-H6.");
            $this->assertSame(1, preg_match_all('/<title>/i', $html), "{$path} should have one title.");
            $this->assertSame(1, preg_match_all('/<meta name="description"/i', $html), "{$path} should have one meta description.");
            $this->assertSame(1, preg_match_all('/<link rel="canonical"/i', $html), "{$path} should have one canonical.");
            $this->assertSame(1, preg_match_all('/<meta name="robots"/i', $html), "{$path} should have one robots meta.");
            $this->assertSame(1, preg_match_all('/<meta property="og:title"/i', $html), "{$path} should have one OG title.");
            $this->assertStringNotContainsString('.html', $html);
            $this->assertLessThanOrEqual(1, preg_match_all('/<header\b/i', $html), "{$path} should not duplicate the header.");
            $this->assertLessThanOrEqual(1, preg_match_all('/<footer\b/i', $html), "{$path} should not duplicate the footer.");
        }

        foreach (['about.blade.php', 'faq.blade.php', 'gallery.blade.php'] as $view) {
            $contents = file_get_contents(resource_path('views/frontend/'.$view));
            $this->assertStringNotContainsString('::query', $contents);
            $this->assertStringNotContainsString('::where', $contents);
            $this->assertStringNotContainsString('::active', $contents);
        }
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

    private function sectionData(Page $page, array $overrides = []): array
    {
        return array_merge([
            'page_id' => $page->id,
            'section_key' => 'section-'.uniqid(),
            'section_type' => 'content',
            'section_label' => null,
            'title' => null,
            'subtitle' => null,
            'content' => null,
            'sort_order' => 0,
            'is_active' => true,
        ], $overrides);
    }

    private function itemData(PageSection $section, array $overrides = []): array
    {
        return array_merge([
            'page_section_id' => $section->id,
            'item_key' => 'item-'.uniqid(),
            'title' => 'Item title',
            'subtitle' => null,
            'description' => null,
            'sort_order' => 0,
            'is_active' => true,
        ], $overrides);
    }

    private function faqCategoryData(array $overrides = []): array
    {
        return array_merge([
            'name' => 'Category',
            'slug' => 'category-'.uniqid(),
            'is_active' => true,
            'sort_order' => 0,
        ], $overrides);
    }

    private function faqData(?FaqCategory $category, array $overrides = []): array
    {
        return array_merge([
            'faq_category_id' => $category?->id,
            'question' => 'Question?',
            'answer' => 'Answer.',
            'is_active' => true,
            'sort_order' => 0,
        ], $overrides);
    }

    private function galleryCategoryData(array $overrides = []): array
    {
        return array_merge([
            'name' => 'Gallery category',
            'slug' => 'gallery-category-'.uniqid(),
            'is_active' => true,
            'sort_order' => 0,
        ], $overrides);
    }

    private function galleryItemData(?GalleryCategory $category, array $overrides = []): array
    {
        return array_merge([
            'gallery_category_id' => $category?->id,
            'title' => 'Gallery item',
            'caption' => null,
            'is_featured' => false,
            'is_active' => true,
            'sort_order' => 0,
        ], $overrides);
    }

    private function attachMedia(object $model, string $collection, string $path, array $variants = [], ?string $altOverride = null): MediaAsset
    {
        $media = MediaAsset::create([
            'disk' => 'public',
            'directory' => dirname($path),
            'path' => $path,
            'file_name' => basename($path),
            'mime_type' => 'image/jpeg',
            'extension' => 'jpg',
            'file_size' => 1000,
            'width' => 1200,
            'height' => 800,
            'alt_text' => 'Media asset alt',
            'is_active' => true,
        ]);

        foreach ($variants as $variant) {
            MediaVariant::create(array_merge([
                'media_asset_id' => $media->id,
                'disk' => 'public',
                'mime_type' => 'image/jpeg',
                'extension' => 'jpg',
                'file_size' => 500,
            ], $variant));
        }

        MediaAttachment::create([
            'media_asset_id' => $media->id,
            'mediable_type' => $model::class,
            'mediable_id' => $model->id,
            'collection' => $collection,
            'sort_order' => 0,
            'is_primary' => true,
            'alt_text_override' => $altOverride,
        ]);

        return $media;
    }
}
