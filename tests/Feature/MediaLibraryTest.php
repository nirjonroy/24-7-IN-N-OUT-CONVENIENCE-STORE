<?php

namespace Tests\Feature;

use App\Models\Business;
use App\Models\MediaAsset;
use App\Models\Page;
use App\Models\PageSection;
use App\Models\SectionItem;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class MediaLibraryTest extends TestCase
{
    use RefreshDatabase;

    public function test_admin_can_upload_valid_jpg(): void
    {
        Storage::fake('public');
        $user = User::factory()->create(['email' => config('admin.email')]);

        $this->actingAs($user)->post(route('admin.media.store'), [
            'file' => UploadedFile::fake()->image('store photo.jpg', 640, 420)->size(200),
            'title' => 'Store Photo',
            'alt_text' => 'Front of the store',
        ])->assertRedirect();

        $media = MediaAsset::first();

        $this->assertNotNull($media);
        $this->assertSame('public', $media->disk);
        $this->assertSame('Store Photo', $media->title);
        $this->assertSame(640, $media->width);
        $this->assertSame(420, $media->height);
        $this->assertNotNull($media->checksum);
        $this->assertMatchesRegularExpression('/^[a-f0-9-]+\.jpg$/', $media->file_name);
        $this->assertStringStartsWith('media/', $media->path);
        $this->assertStringNotContainsString(' ', $media->file_name);
        Storage::disk('public')->assertExists($media->path);
    }

    public function test_admin_can_upload_png(): void
    {
        Storage::fake('public');
        $user = User::factory()->create(['email' => config('admin.email')]);

        $this->actingAs($user)->post(route('admin.media.store'), [
            'file' => UploadedFile::fake()->image('logo.png', 300, 120)->size(100),
        ])->assertRedirect();

        $this->assertDatabaseHas('media_assets', [
            'extension' => 'png',
            'width' => 300,
            'height' => 120,
        ]);
    }

    public function test_admin_can_upload_webp_when_runtime_supports_it(): void
    {
        if (! function_exists('imagewebp')) {
            $this->markTestSkipped('WebP is not supported by this PHP runtime.');
        }

        Storage::fake('public');
        $user = User::factory()->create(['email' => config('admin.email')]);

        $this->actingAs($user)->post(route('admin.media.store'), [
            'file' => $this->webpUpload(),
        ])->assertRedirect();

        $this->assertDatabaseHas('media_assets', ['extension' => 'webp']);
    }

    public function test_invalid_extension_and_oversized_file_are_rejected(): void
    {
        $user = User::factory()->create(['email' => config('admin.email')]);

        $this->actingAs($user)->post(route('admin.media.store'), [
            'file' => UploadedFile::fake()->create('bad.svg', 10, 'image/svg+xml'),
        ])->assertSessionHasErrors('file');

        $this->actingAs($user)->post(route('admin.media.store'), [
            'file' => UploadedFile::fake()->image('large.jpg')->size(9000),
        ])->assertSessionHasErrors('file');
    }

    public function test_media_can_attach_to_page_section_and_item_with_single_replacement(): void
    {
        $user = User::factory()->create(['email' => config('admin.email')]);
        $firstMedia = $this->media(['title' => 'First']);
        $secondMedia = $this->media(['title' => 'Second']);

        $this->actingAs($user)->post(route('admin.pages.store'), $this->pagePayload([
            'meta_image_media_id' => $firstMedia->id,
            'meta_image_alt_override' => 'Custom page alt',
        ]))->assertRedirect(route('admin.pages.index'));

        $page = Page::first();
        $this->assertSame($firstMedia->id, $page->getMediaAsset('meta_image')->id);
        $this->assertSame('Custom page alt', $page->getMediaAlt('meta_image'));

        $this->actingAs($user)->put(route('admin.pages.update', $page), $this->pagePayload([
            'slug' => 'home',
            'meta_image_media_id' => $secondMedia->id,
        ]))->assertRedirect(route('admin.pages.index'));

        $this->assertSame($secondMedia->id, $page->fresh()->getMediaAsset('meta_image')->id);
        $this->assertDatabaseHas('media_assets', ['id' => $firstMedia->id, 'deleted_at' => null]);

        $this->actingAs($user)->post(route('admin.pages.sections.store', $page), $this->sectionPayload([
            'image_media_id' => $firstMedia->id,
        ]))->assertRedirect(route('admin.pages.sections.index', $page));

        $section = PageSection::first();
        $this->assertSame($firstMedia->id, $section->getMediaAsset('image')->id);

        $this->actingAs($user)->post(route('admin.pages.sections.items.store', [$page, $section]), $this->itemPayload([
            'image_media_id' => $secondMedia->id,
        ]))->assertRedirect(route('admin.pages.sections.items.index', [$page, $section]));

        $item = SectionItem::first();
        $this->assertSame($secondMedia->id, $item->getMediaAsset('image')->id);
    }

    public function test_business_supports_meta_logo_and_favicon_attachments(): void
    {
        $user = User::factory()->create(['email' => config('admin.email')]);
        $media = $this->media();

        $this->actingAs($user)->post(route('admin.businesses.store'), [
            'name' => '24/7 IN N OUT',
            'currency' => 'USD',
            'is_active' => '1',
            'meta_image_media_id' => $media->id,
            'logo_media_id' => $media->id,
            'favicon_media_id' => $media->id,
        ])->assertRedirect(route('admin.businesses.index'));

        $business = Business::first();
        $this->assertSame($media->id, $business->getMediaAsset('meta_image')->id);
        $this->assertSame($media->id, $business->getMediaAsset('logo')->id);
        $this->assertSame($media->id, $business->getMediaAsset('favicon')->id);
    }

    public function test_inactive_media_cannot_be_newly_attached(): void
    {
        $user = User::factory()->create(['email' => config('admin.email')]);
        $inactive = $this->media(['is_active' => false]);

        $this->actingAs($user)->post(route('admin.pages.store'), $this->pagePayload([
            'meta_image_media_id' => $inactive->id,
        ]))->assertSessionHasErrors('meta_image_media_id');
    }

    public function test_used_media_cannot_be_deleted_but_unused_media_can_be_soft_deleted(): void
    {
        $user = User::factory()->create(['email' => config('admin.email')]);
        $used = $this->media();
        $unused = $this->media(['title' => 'Unused']);
        $page = Page::create($this->modelPageData());
        $page->mediaAttachments()->create([
            'media_asset_id' => $used->id,
            'collection' => 'meta_image',
            'is_primary' => true,
        ]);

        $this->actingAs($user)->delete(route('admin.media.destroy', $used))
            ->assertRedirect(route('admin.media.index'))
            ->assertSessionHas('error');
        $this->assertDatabaseHas('media_assets', ['id' => $used->id, 'deleted_at' => null]);

        $this->actingAs($user)->delete(route('admin.media.destroy', $unused))
            ->assertRedirect(route('admin.media.index'))
            ->assertSessionHas('success');
        $this->assertSoftDeleted('media_assets', ['id' => $unused->id]);
    }

    public function test_media_search_works(): void
    {
        $user = User::factory()->create(['email' => config('admin.email')]);
        $this->media(['title' => 'Front Counter']);
        $this->media(['title' => 'Back Office']);

        $this->actingAs($user)->get(route('admin.media.index', ['search' => 'Front']))
            ->assertOk()
            ->assertSee('Front Counter')
            ->assertDontSee('Back Office');
    }

    public function test_unauthorized_user_cannot_access_admin_media_routes(): void
    {
        $this->get(route('admin.media.index'))->assertRedirect(route('login'));
        $this->get(route('admin.media.picker'))->assertRedirect(route('login'));
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

    private function webpUpload(): UploadedFile
    {
        $path = tempnam(sys_get_temp_dir(), 'webp_test_').'.webp';
        $image = imagecreatetruecolor(80, 60);
        imagewebp($image, $path);
        imagedestroy($image);

        return new UploadedFile($path, 'sample.webp', 'image/webp', null, true);
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

    private function modelPageData(): array
    {
        return [
            'name' => 'Home',
            'slug' => 'home',
            'template' => 'default',
            'h1' => 'Welcome',
            'status' => 'draft',
            'robots_index' => true,
            'robots_follow' => true,
            'is_home' => false,
        ];
    }
}
