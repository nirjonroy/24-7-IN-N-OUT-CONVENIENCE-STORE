<?php

namespace Tests\Feature;

use App\Models\Faq;
use App\Models\FaqCategory;
use App\Models\GalleryCategory;
use App\Models\GalleryItem;
use App\Models\MediaAsset;
use App\Models\Page;
use App\Models\User;
use Illuminate\Database\QueryException;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class FaqGalleryAdminTest extends TestCase
{
    use RefreshDatabase;

    public function test_faq_category_can_be_created_with_unique_slug_and_seo(): void
    {
        $user = User::factory()->create();

        $this->actingAs($user)->post(route('admin.faq.categories.store'), $this->faqCategoryPayload([
            'seo_title' => 'FAQ SEO',
        ]))->assertRedirect(route('admin.faq.categories.index'));

        $category = FaqCategory::first();

        $this->assertSame('general-faqs', $category->slug);
        $this->assertSame('FAQ SEO', $category->seo_title);

        $this->actingAs($user)->post(route('admin.faq.categories.store'), $this->faqCategoryPayload([
            'name' => 'Duplicate',
            'slug' => 'general-faqs',
        ]))->assertSessionHasErrors('slug');
    }

    public function test_faq_can_be_created_without_category_and_assigned_to_pages(): void
    {
        $user = User::factory()->create();
        $home = Page::create($this->pageData(['slug' => 'home']));
        $contact = Page::create($this->pageData(['name' => 'Contact', 'slug' => 'contact', 'h1' => 'Contact']));

        $this->actingAs($user)->post(route('admin.faq.questions.store'), $this->faqPayload([
            'faq_category_id' => null,
            'pages' => [$home->id, $contact->id],
            'seo_title' => 'Question SEO',
        ]))->assertRedirect(route('admin.faq.questions.index'));

        $faq = Faq::first();

        $this->assertNull($faq->faq_category_id);
        $this->assertSame('Question SEO', $faq->seo_title);
        $this->assertEqualsCanonicalizing([$home->id, $contact->id], $faq->pages()->pluck('pages.id')->all());
    }

    public function test_faq_page_sync_and_unique_pivot_work(): void
    {
        $user = User::factory()->create();
        $first = Page::create($this->pageData(['slug' => 'first']));
        $second = Page::create($this->pageData(['name' => 'Second', 'slug' => 'second', 'h1' => 'Second']));

        $this->actingAs($user)->post(route('admin.faq.questions.store'), $this->faqPayload([
            'pages' => [$first->id],
        ]))->assertRedirect(route('admin.faq.questions.index'));

        $faq = Faq::first();

        $this->actingAs($user)->put(route('admin.faq.questions.update', $faq), $this->faqPayload([
            'question' => $faq->question,
            'pages' => [$second->id],
        ]))->assertRedirect(route('admin.faq.questions.index'));

        $this->assertEquals([$second->id], $faq->fresh()->pages()->pluck('pages.id')->all());

        $this->expectException(QueryException::class);
        $faq->pages()->attach($second->id);
    }

    public function test_faq_filters_and_delete_do_not_delete_pages(): void
    {
        $user = User::factory()->create();
        $category = FaqCategory::create($this->faqCategoryData());
        $page = Page::create($this->pageData(['slug' => 'support']));
        $match = Faq::create($this->faqData(['faq_category_id' => $category->id, 'question' => 'Do you sell snacks?', 'is_featured' => true]));
        $miss = Faq::create($this->faqData(['question' => 'Other question', 'is_featured' => false]));
        $match->pages()->attach($page->id);

        $this->actingAs($user)->get(route('admin.faq.questions.index', [
            'search' => 'snacks',
            'category_id' => $category->id,
            'page_id' => $page->id,
            'featured' => '1',
            'status' => 'active',
        ]))->assertOk()->assertSee('Do you sell snacks?')->assertDontSee('Other question');

        $this->actingAs($user)->delete(route('admin.faq.questions.destroy', $match))
            ->assertRedirect(route('admin.faq.questions.index'));

        $this->assertSoftDeleted('faqs', ['id' => $match->id]);
        $this->assertDatabaseHas('pages', ['id' => $page->id, 'deleted_at' => null]);
        $this->assertDatabaseMissing('faq_page', ['faq_id' => $match->id]);
        $this->assertDatabaseHas('faqs', ['id' => $miss->id]);
    }

    public function test_gallery_category_can_be_created_with_unique_slug_and_seo(): void
    {
        $user = User::factory()->create();

        $this->actingAs($user)->post(route('admin.gallery.categories.store'), $this->galleryCategoryPayload([
            'seo_title' => 'Gallery SEO',
        ]))->assertRedirect(route('admin.gallery.categories.index'));

        $category = GalleryCategory::first();

        $this->assertSame('store-photos', $category->slug);
        $this->assertSame('Gallery SEO', $category->seo_title);

        $this->actingAs($user)->post(route('admin.gallery.categories.store'), $this->galleryCategoryPayload([
            'name' => 'Duplicate',
            'slug' => 'store-photos',
        ]))->assertSessionHasErrors('slug');
    }

    public function test_gallery_item_requires_image_and_can_store_media_and_seo(): void
    {
        $user = User::factory()->create();
        $category = GalleryCategory::create($this->galleryCategoryData());
        $image = $this->media(['title' => 'Image']);
        $meta = $this->media(['title' => 'Meta']);

        $this->actingAs($user)->post(route('admin.gallery.items.store'), $this->galleryItemPayload([
            'gallery_category_id' => $category->id,
            'image_media_id' => null,
        ]))->assertSessionHasErrors('image_media_id');

        $this->actingAs($user)->post(route('admin.gallery.items.store'), $this->galleryItemPayload([
            'gallery_category_id' => $category->id,
            'image_media_id' => $image->id,
            'meta_image_media_id' => $meta->id,
            'seo_title' => 'Gallery Item SEO',
        ]))->assertRedirect(route('admin.gallery.items.index'));

        $item = GalleryItem::first();

        $this->assertSame($category->id, $item->gallery_category_id);
        $this->assertSame('Gallery Item SEO', $item->seo_title);
        $this->assertSame($image->id, $item->getMediaAsset('image')->id);
        $this->assertSame($meta->id, $item->getMediaAsset('meta_image')->id);
    }

    public function test_gallery_item_media_replacement_and_delete_preserve_media_assets(): void
    {
        $user = User::factory()->create();
        $first = $this->media(['title' => 'First']);
        $second = $this->media(['title' => 'Second']);

        $this->actingAs($user)->post(route('admin.gallery.items.store'), $this->galleryItemPayload([
            'image_media_id' => $first->id,
        ]))->assertRedirect(route('admin.gallery.items.index'));

        $item = GalleryItem::first();

        $this->actingAs($user)->put(route('admin.gallery.items.update', $item), $this->galleryItemPayload([
            'title' => $item->title,
            'image_media_id' => $second->id,
        ]))->assertRedirect(route('admin.gallery.items.index'));

        $this->assertSame($second->id, $item->fresh()->getMediaAsset('image')->id);
        $this->assertDatabaseHas('media_assets', ['id' => $first->id, 'deleted_at' => null]);

        $this->actingAs($user)->delete(route('admin.gallery.items.destroy', $item))
            ->assertRedirect(route('admin.gallery.items.index'));

        $this->assertSoftDeleted('gallery_items', ['id' => $item->id]);
        $this->assertDatabaseHas('media_assets', ['id' => $second->id, 'deleted_at' => null]);
        $this->assertDatabaseMissing('media_attachments', ['mediable_type' => GalleryItem::class, 'mediable_id' => $item->id]);
    }

    public function test_gallery_filters_and_soft_deleted_category_relation_behave(): void
    {
        $user = User::factory()->create();
        $category = GalleryCategory::create($this->galleryCategoryData());
        $match = GalleryItem::create($this->galleryItemData(['gallery_category_id' => $category->id, 'title' => 'Outside Storefront', 'is_featured' => true]));
        GalleryItem::create($this->galleryItemData(['title' => 'Inside Shelves', 'is_featured' => false]));

        $this->actingAs($user)->get(route('admin.gallery.items.index', [
            'search' => 'Outside',
            'category_id' => $category->id,
            'featured' => '1',
            'status' => 'active',
        ]))->assertOk()->assertSee('Outside Storefront')->assertDontSee('Inside Shelves');

        $this->actingAs($user)->delete(route('admin.gallery.categories.destroy', $category))
            ->assertRedirect(route('admin.gallery.categories.index'));

        $this->assertNull($match->fresh()->category);
    }

    public function test_admin_faq_and_gallery_routes_require_authentication(): void
    {
        $this->get(route('admin.faq.questions.index'))->assertRedirect('/login');
        $this->get(route('admin.gallery.items.index'))->assertRedirect('/login');
    }

    private function faqCategoryPayload(array $overrides = []): array
    {
        return array_merge(['name' => 'General FAQs', 'slug' => '', 'is_featured' => '0', 'is_active' => '1', 'sort_order' => 0], $overrides);
    }

    private function galleryCategoryPayload(array $overrides = []): array
    {
        return array_merge(['name' => 'Store Photos', 'slug' => '', 'is_featured' => '0', 'is_active' => '1', 'sort_order' => 0], $overrides);
    }

    private function faqPayload(array $overrides = []): array
    {
        return array_merge(['question' => 'What are your hours?', 'answer' => 'We are open daily.', 'is_featured' => '0', 'is_active' => '1', 'sort_order' => 0, 'pages' => []], $overrides);
    }

    private function galleryItemPayload(array $overrides = []): array
    {
        return array_merge(['title' => 'Storefront', 'caption' => 'Front entrance', 'is_featured' => '0', 'is_active' => '1', 'sort_order' => 0], $overrides);
    }

    private function faqCategoryData(array $overrides = []): array
    {
        return array_merge(['name' => 'General', 'slug' => 'general-'.uniqid(), 'is_featured' => false, 'is_active' => true, 'sort_order' => 0], $overrides);
    }

    private function galleryCategoryData(array $overrides = []): array
    {
        return array_merge(['name' => 'Photos', 'slug' => 'photos-'.uniqid(), 'is_featured' => false, 'is_active' => true, 'sort_order' => 0], $overrides);
    }

    private function faqData(array $overrides = []): array
    {
        return array_merge(['question' => 'Question '.uniqid(), 'answer' => 'Answer', 'is_featured' => false, 'is_active' => true, 'sort_order' => 0], $overrides);
    }

    private function galleryItemData(array $overrides = []): array
    {
        return array_merge(['title' => 'Gallery '.uniqid(), 'is_featured' => false, 'is_active' => true, 'sort_order' => 0], $overrides);
    }

    private function pageData(array $overrides = []): array
    {
        return array_merge(['name' => 'Home', 'slug' => 'home', 'template' => 'default', 'h1' => 'Welcome', 'status' => 'draft', 'robots_index' => true, 'robots_follow' => true, 'is_home' => false], $overrides);
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
