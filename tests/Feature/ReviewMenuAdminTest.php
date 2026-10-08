<?php

namespace Tests\Feature;

use App\Models\MediaAsset;
use App\Models\Menu;
use App\Models\MenuItem;
use App\Models\Page;
use App\Models\Review;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ReviewMenuAdminTest extends TestCase
{
    use RefreshDatabase;

    public function test_review_can_be_created_featured_with_avatar_and_seo(): void
    {
        $user = User::factory()->create(['email' => config('admin.email')]);
        $avatar = $this->media(['title' => 'Avatar']);
        $meta = $this->media(['title' => 'Meta']);

        $this->actingAs($user)->post(route('admin.reviews.store'), $this->reviewPayload([
            'source' => 'google',
            'rating' => '4.5',
            'is_featured' => '1',
            'avatar_media_id' => $avatar->id,
            'meta_image_media_id' => $meta->id,
            'seo_title' => 'Review SEO',
        ]))->assertRedirect(route('admin.reviews.index'));

        $review = Review::first();

        $this->assertSame('google', $review->source);
        $this->assertSame('4.5', $review->rating);
        $this->assertTrue($review->is_featured);
        $this->assertSame('Review SEO', $review->seo_title);
        $this->assertSame($avatar->id, $review->getMediaAsset('avatar')->id);
        $this->assertSame($meta->id, $review->getMediaAsset('meta_image')->id);
    }

    public function test_review_rating_validation_and_filters_work(): void
    {
        $user = User::factory()->create(['email' => config('admin.email')]);

        $this->actingAs($user)->post(route('admin.reviews.store'), $this->reviewPayload(['rating' => '5.1']))
            ->assertSessionHasErrors('rating');

        $this->actingAs($user)->post(route('admin.reviews.store'), $this->reviewPayload(['rating' => '0.9']))
            ->assertSessionHasErrors('rating');

        Review::create($this->reviewData(['source' => 'facebook', 'author_name' => 'Alice Buyer', 'review_text' => 'Great smoothie', 'rating' => '5.0']));
        Review::create($this->reviewData(['source' => 'google', 'author_name' => 'Bob', 'review_text' => 'Phone repair', 'rating' => '4.0']));

        $this->actingAs($user)->get(route('admin.reviews.index', [
            'source' => 'facebook',
            'search' => 'smoothie',
            'rating' => '5.0',
        ]))->assertOk()->assertSee('Alice Buyer')->assertDontSee('Bob');
    }

    public function test_replacing_review_avatar_preserves_media_and_review_soft_deletes(): void
    {
        $user = User::factory()->create(['email' => config('admin.email')]);
        $first = $this->media(['title' => 'First']);
        $second = $this->media(['title' => 'Second']);

        $this->actingAs($user)->post(route('admin.reviews.store'), $this->reviewPayload([
            'avatar_media_id' => $first->id,
        ]))->assertRedirect(route('admin.reviews.index'));

        $review = Review::first();

        $this->actingAs($user)->put(route('admin.reviews.update', $review), $this->reviewPayload([
            'author_name' => $review->author_name,
            'avatar_media_id' => $second->id,
        ]))->assertRedirect(route('admin.reviews.index'));

        $this->assertSame($second->id, $review->fresh()->getMediaAsset('avatar')->id);
        $this->assertDatabaseHas('media_assets', ['id' => $first->id, 'deleted_at' => null]);

        $this->actingAs($user)->delete(route('admin.reviews.destroy', $review))
            ->assertRedirect(route('admin.reviews.index'));

        $this->assertSoftDeleted('reviews', ['id' => $review->id]);
        $this->assertDatabaseHas('media_assets', ['id' => $second->id, 'deleted_at' => null]);
        $this->assertDatabaseMissing('media_attachments', ['mediable_type' => Review::class, 'mediable_id' => $review->id]);
    }

    public function test_menu_can_be_created_with_unique_key_location_and_seo(): void
    {
        $user = User::factory()->create(['email' => config('admin.email')]);

        $this->actingAs($user)->post(route('admin.menus.store'), $this->menuPayload([
            'location' => 'header',
            'seo_title' => 'Menu SEO',
        ]))->assertRedirect(route('admin.menus.index'));

        $menu = Menu::first();

        $this->assertSame('main_navigation', $menu->key);
        $this->assertSame('header', $menu->location);
        $this->assertSame('Menu SEO', $menu->seo_title);

        $this->actingAs($user)->post(route('admin.menus.store'), $this->menuPayload([
            'name' => 'Duplicate',
            'key' => 'main_navigation',
        ]))->assertSessionHasErrors('key');
    }

    public function test_menu_items_support_page_and_custom_links_with_security(): void
    {
        $user = User::factory()->create(['email' => config('admin.email')]);
        $menu = Menu::create($this->menuData());
        $page = Page::create($this->pageData(['slug' => 'contact']));

        $this->actingAs($user)->post(route('admin.menus.items.store', $menu), $this->menuItemPayload([
            'link_type' => 'page',
            'page_id' => null,
        ]))->assertSessionHasErrors('page_id');

        $this->actingAs($user)->post(route('admin.menus.items.store', $menu), $this->menuItemPayload([
            'label' => 'Contact',
            'link_type' => 'page',
            'page_id' => $page->id,
            'seo_title' => 'Menu Item SEO',
        ]))->assertRedirect(route('admin.menus.items.index', $menu));

        $this->actingAs($user)->post(route('admin.menus.items.store', $menu), $this->menuItemPayload([
            'label' => 'Call',
            'link_type' => 'custom',
            'url' => 'tel:+15555555555',
        ]))->assertRedirect(route('admin.menus.items.index', $menu));

        $this->actingAs($user)->post(route('admin.menus.items.store', $menu), $this->menuItemPayload([
            'label' => 'Bad',
            'link_type' => 'custom',
            'url' => 'javascript:alert(1)',
        ]))->assertSessionHasErrors('url');

        $this->assertDatabaseHas('menu_items', ['label' => 'Contact', 'page_id' => $page->id, 'url' => null, 'seo_title' => 'Menu Item SEO']);
        $this->assertDatabaseHas('menu_items', ['label' => 'Call', 'page_id' => null, 'url' => 'tel:+15555555555']);
    }

    public function test_menu_parent_rules_depth_cross_menu_and_circular_are_enforced(): void
    {
        $user = User::factory()->create(['email' => config('admin.email')]);
        $menu = Menu::create($this->menuData());
        $otherMenu = Menu::create($this->menuData(['name' => 'Footer', 'key' => 'footer']));
        $parent = MenuItem::create($this->menuItemData($menu, ['label' => 'Services']));
        $child = MenuItem::create($this->menuItemData($menu, ['label' => 'Phone Repair', 'parent_id' => $parent->id]));
        $otherParent = MenuItem::create($this->menuItemData($otherMenu, ['label' => 'Other']));

        $this->actingAs($user)->post(route('admin.menus.items.store', $menu), $this->menuItemPayload([
            'label' => 'Smoothies',
            'parent_id' => $parent->id,
        ]))->assertRedirect(route('admin.menus.items.index', $menu));

        $this->actingAs($user)->post(route('admin.menus.items.store', $menu), $this->menuItemPayload([
            'label' => 'Cross',
            'parent_id' => $otherParent->id,
        ]))->assertSessionHasErrors('parent_id');

        $this->actingAs($user)->post(route('admin.menus.items.store', $menu), $this->menuItemPayload([
            'label' => 'Too Deep',
            'parent_id' => $child->id,
        ]))->assertSessionHasErrors('parent_id');

        $this->actingAs($user)->put(route('admin.menus.items.update', [$menu, $parent]), $this->menuItemPayload([
            'label' => 'Services',
            'parent_id' => $parent->id,
        ]))->assertSessionHasErrors('parent_id');

        $this->actingAs($user)->put(route('admin.menus.items.update', [$menu, $parent]), $this->menuItemPayload([
            'label' => 'Services',
            'parent_id' => $child->id,
        ]))->assertSessionHasErrors('parent_id');
    }

    public function test_menu_ordering_delete_and_page_delete_behavior(): void
    {
        $user = User::factory()->create(['email' => config('admin.email')]);
        $menu = Menu::create($this->menuData());
        $page = Page::create($this->pageData(['slug' => 'about']));
        $first = MenuItem::create($this->menuItemData($menu, ['label' => 'B', 'sort_order' => 20, 'page_id' => $page->id]));
        $second = MenuItem::create($this->menuItemData($menu, ['label' => 'A', 'sort_order' => 10]));

        $this->actingAs($user)->get(route('admin.menus.items.index', $menu))
            ->assertSeeInOrder(['A', 'B']);

        $page->delete();
        $this->assertNull($first->fresh()->page_id);

        $this->actingAs($user)->delete(route('admin.menus.destroy', $menu))
            ->assertRedirect(route('admin.menus.index'));

        $this->assertSoftDeleted('menus', ['id' => $menu->id]);
        $this->assertSoftDeleted('menu_items', ['id' => $first->id]);
        $this->assertSoftDeleted('menu_items', ['id' => $second->id]);
    }

    public function test_admin_review_and_menu_routes_require_authentication(): void
    {
        $this->get(route('admin.reviews.index'))->assertRedirect('/login');
        $this->get(route('admin.menus.index'))->assertRedirect('/login');
    }

    private function reviewPayload(array $overrides = []): array
    {
        return array_merge([
            'source' => 'manual',
            'author_name' => 'Nirjon Roy',
            'rating' => '5.0',
            'review_text' => 'Great store.',
            'is_featured' => '0',
            'is_active' => '1',
            'sort_order' => 0,
        ], $overrides);
    }

    private function menuPayload(array $overrides = []): array
    {
        return array_merge([
            'name' => 'Main Navigation',
            'key' => '',
            'location' => 'header',
            'is_active' => '1',
            'sort_order' => 0,
        ], $overrides);
    }

    private function menuItemPayload(array $overrides = []): array
    {
        return array_merge([
            'label' => 'Home',
            'link_type' => 'custom',
            'url' => '/',
            'target' => '_self',
            'is_active' => '1',
            'sort_order' => 0,
        ], $overrides);
    }

    private function reviewData(array $overrides = []): array
    {
        return array_merge([
            'source' => 'manual',
            'author_name' => 'Reviewer',
            'rating' => '5.0',
            'review_text' => 'Review text',
            'is_featured' => false,
            'is_active' => true,
            'sort_order' => 0,
        ], $overrides);
    }

    private function menuData(array $overrides = []): array
    {
        return array_merge([
            'name' => 'Main',
            'key' => 'main-'.uniqid(),
            'location' => 'header',
            'is_active' => true,
            'sort_order' => 0,
        ], $overrides);
    }

    private function menuItemData(Menu $menu, array $overrides = []): array
    {
        return array_merge([
            'menu_id' => $menu->id,
            'label' => 'Item '.uniqid(),
            'link_type' => 'custom',
            'url' => '/',
            'target' => '_self',
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
            'h1' => 'Page',
            'status' => 'draft',
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
