<?php

namespace Tests\Feature;

use App\Models\Page;
use App\Models\PageSection;
use App\Models\SectionItem;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class CmsAdminTest extends TestCase
{
    use RefreshDatabase;

    public function test_page_can_be_created_with_seo_fields_and_published_timestamp(): void
    {
        $user = User::factory()->create();

        $this->actingAs($user)->post(route('admin.pages.store'), $this->pagePayload([
            'status' => 'published',
            'seo_title' => 'SEO Home',
            'meta_description' => 'Search description',
        ]))->assertRedirect(route('admin.pages.index'));

        $page = Page::first();

        $this->assertNotNull($page);
        $this->assertSame('home', $page->slug);
        $this->assertSame('SEO Home', $page->seo_title);
        $this->assertSame('Search description', $page->meta_description);
        $this->assertNotNull($page->published_at);
    }

    public function test_slug_must_be_unique(): void
    {
        $user = User::factory()->create();
        Page::create($this->modelPageData(['slug' => 'home']));

        $this->actingAs($user)->post(route('admin.pages.store'), $this->pagePayload([
            'slug' => 'home',
        ]))->assertSessionHasErrors('slug');
    }

    public function test_only_one_home_page_exists(): void
    {
        $user = User::factory()->create();
        $first = Page::create($this->modelPageData(['slug' => 'home', 'is_home' => true]));
        $second = Page::create($this->modelPageData(['name' => 'About', 'slug' => 'about', 'h1' => 'About Us']));

        $this->actingAs($user)->put(route('admin.pages.update', $second), $this->pagePayload([
            'name' => 'About',
            'slug' => 'about',
            'h1' => 'About Us',
            'is_home' => '1',
        ]))->assertRedirect(route('admin.pages.index'));

        $this->assertFalse($first->fresh()->is_home);
        $this->assertTrue($second->fresh()->is_home);
    }

    public function test_page_can_contain_sections_and_section_key_is_scoped_to_page(): void
    {
        $user = User::factory()->create();
        $page = Page::create($this->modelPageData(['slug' => 'home']));
        $otherPage = Page::create($this->modelPageData(['name' => 'About', 'slug' => 'about', 'h1' => 'About Us']));

        $this->actingAs($user)->post(route('admin.pages.sections.store', $page), $this->sectionPayload([
            'section_key' => 'hero',
            'seo_title' => 'Hero SEO',
        ]))->assertRedirect(route('admin.pages.sections.index', $page));

        $this->assertDatabaseHas('page_sections', [
            'page_id' => $page->id,
            'section_key' => 'hero',
            'seo_title' => 'Hero SEO',
        ]);

        $this->actingAs($user)->post(route('admin.pages.sections.store', $page), $this->sectionPayload([
            'section_key' => 'hero',
        ]))->assertSessionHasErrors('section_key');

        $this->actingAs($user)->post(route('admin.pages.sections.store', $otherPage), $this->sectionPayload([
            'section_key' => 'hero',
        ]))->assertSessionDoesntHaveErrors();
    }

    public function test_section_cannot_be_accessed_through_wrong_page_route(): void
    {
        $user = User::factory()->create();
        $page = Page::create($this->modelPageData(['slug' => 'home']));
        $otherPage = Page::create($this->modelPageData(['name' => 'About', 'slug' => 'about', 'h1' => 'About Us']));
        $section = $page->sections()->create($this->modelSectionData());

        $this->actingAs($user)
            ->get(route('admin.pages.sections.edit', [$otherPage, $section]))
            ->assertNotFound();
    }

    public function test_section_can_contain_items(): void
    {
        $user = User::factory()->create();
        $page = Page::create($this->modelPageData(['slug' => 'home']));
        $section = $page->sections()->create($this->modelSectionData());

        $this->actingAs($user)->post(route('admin.pages.sections.items.store', [$page, $section]), $this->itemPayload([
            'title' => 'Convenience Store',
            'settings' => '{"columns":4}',
            'meta_title' => 'Item Meta',
        ]))->assertRedirect(route('admin.pages.sections.items.index', [$page, $section]));

        $item = SectionItem::first();

        $this->assertSame('Convenience Store', $item->title);
        $this->assertSame(['columns' => 4], $item->settings);
        $this->assertSame('Item Meta', $item->meta_title);
    }

    public function test_deleting_page_deletes_sections_and_items(): void
    {
        $user = User::factory()->create();
        $page = Page::create($this->modelPageData(['slug' => 'home']));
        $section = $page->sections()->create($this->modelSectionData());
        $item = $section->items()->create($this->modelItemData());

        $this->actingAs($user)->delete(route('admin.pages.destroy', $page))
            ->assertRedirect(route('admin.pages.index'));

        $this->assertSoftDeleted('pages', ['id' => $page->id]);
        $this->assertDatabaseMissing('page_sections', ['id' => $section->id]);
        $this->assertDatabaseMissing('section_items', ['id' => $item->id]);
    }

    public function test_deleting_section_deletes_items(): void
    {
        $user = User::factory()->create();
        $page = Page::create($this->modelPageData(['slug' => 'home']));
        $section = $page->sections()->create($this->modelSectionData());
        $item = $section->items()->create($this->modelItemData());

        $this->actingAs($user)->delete(route('admin.pages.sections.destroy', [$page, $section]))
            ->assertRedirect(route('admin.pages.sections.index', $page));

        $this->assertDatabaseMissing('page_sections', ['id' => $section->id]);
        $this->assertDatabaseMissing('section_items', ['id' => $item->id]);
    }

    public function test_status_validation_rejects_unknown_status(): void
    {
        $user = User::factory()->create();

        $this->actingAs($user)->post(route('admin.pages.store'), $this->pagePayload([
            'status' => 'archived',
        ]))->assertSessionHasErrors('status');
    }

    private function pagePayload(array $overrides = []): array
    {
        return array_merge([
            'name' => 'Home',
            'slug' => '',
            'template' => 'default',
            'h1' => 'Welcome',
            'status' => 'draft',
            'sort_order' => 0,
            'robots_index' => '1',
            'robots_follow' => '1',
            'is_home' => '0',
        ], $overrides);
    }

    private function sectionPayload(array $overrides = []): array
    {
        return array_merge([
            'section_key' => 'hero',
            'section_type' => 'hero',
            'title' => 'Hero',
            'sort_order' => 0,
            'is_active' => '1',
        ], $overrides);
    }

    private function itemPayload(array $overrides = []): array
    {
        return array_merge([
            'title' => 'Item',
            'sort_order' => 0,
            'is_active' => '1',
        ], $overrides);
    }

    private function modelPageData(array $overrides = []): array
    {
        return array_merge([
            'name' => 'Home',
            'slug' => 'home',
            'template' => 'default',
            'h1' => 'Welcome',
            'status' => 'draft',
            'robots_index' => true,
            'robots_follow' => true,
            'is_home' => false,
        ], $overrides);
    }

    private function modelSectionData(array $overrides = []): array
    {
        return array_merge([
            'section_key' => 'hero',
            'section_type' => 'hero',
            'title' => 'Hero',
            'is_active' => true,
        ], $overrides);
    }

    private function modelItemData(array $overrides = []): array
    {
        return array_merge([
            'title' => 'Item',
            'is_active' => true,
        ], $overrides);
    }
}
