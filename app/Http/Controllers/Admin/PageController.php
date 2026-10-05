<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\StorePageRequest;
use App\Http\Requests\UpdatePageRequest;
use App\Models\Page;
use App\Services\MediaAttachmentService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Illuminate\View\View;

class PageController extends Controller
{
    public function index(): View
    {
        return view('admin.pages.index', [
            'pages' => Page::withCount('sections')
                ->orderBy('sort_order')
                ->orderBy('id')
                ->paginate(10),
        ]);
    }

    public function create(): View
    {
        return view('admin.pages.create', [
            'page' => new Page([
                'template' => 'default',
                'robots_index' => true,
                'robots_follow' => true,
                'status' => Page::STATUS_DRAFT,
            ]),
            'statuses' => Page::STATUSES,
        ]);
    }

    public function store(StorePageRequest $request, MediaAttachmentService $mediaAttachments): RedirectResponse
    {
        DB::transaction(function () use ($request, $mediaAttachments) {
            [$data, $media] = $this->splitMediaData($request->validated());
            $data = $this->pageData($data);

            if ($data['is_home']) {
                Page::query()->update(['is_home' => false]);
            }

            $page = Page::create($data);
            $this->syncMedia($page, $mediaAttachments, $media);
        });

        return redirect()->route('admin.pages.index')->with('success', 'Page created successfully.');
    }

    public function show(Page $page): RedirectResponse
    {
        return redirect()->route('admin.pages.edit', $page);
    }

    public function edit(Page $page): View
    {
        $page->load('mediaAttachments.media.variants');

        return view('admin.pages.edit', [
            'page' => $page,
            'statuses' => Page::STATUSES,
        ]);
    }

    public function update(UpdatePageRequest $request, Page $page, MediaAttachmentService $mediaAttachments): RedirectResponse
    {
        DB::transaction(function () use ($request, $page, $mediaAttachments) {
            [$data, $media] = $this->splitMediaData($request->validated());
            $data = $this->pageData($data, $page);

            if ($data['is_home']) {
                Page::whereKeyNot($page->id)->update(['is_home' => false]);
            }

            $page->update($data);
            $this->syncMedia($page, $mediaAttachments, $media);
        });

        return redirect()->route('admin.pages.index')->with('success', 'Page updated successfully.');
    }

    public function destroy(Page $page): RedirectResponse
    {
        $page->delete();

        return redirect()->route('admin.pages.index')->with('success', 'Page deleted successfully.');
    }

    private function pageData(array $data, ?Page $page = null): array
    {
        $data['slug'] = Str::slug($data['slug'] ?: $data['name']);
        $data['template'] = $data['template'] ?: 'default';
        $data['robots_index'] = request()->boolean('robots_index');
        $data['robots_follow'] = request()->boolean('robots_follow');
        $data['is_home'] = request()->boolean('is_home');
        $data['sort_order'] = $data['sort_order'] ?? 0;

        if ($data['status'] === Page::STATUS_PUBLISHED && empty($data['published_at']) && ! $page?->published_at) {
            $data['published_at'] = now();
        }

        return $data;
    }

    private function splitMediaData(array $data): array
    {
        $media = [
            'meta_image' => [
                'id' => $data['meta_image_media_id'] ?? null,
                'alt' => $data['meta_image_alt_override'] ?? null,
            ],
            'og_image' => [
                'id' => $data['og_image_media_id'] ?? null,
                'alt' => $data['og_image_alt_override'] ?? null,
            ],
        ];

        unset(
            $data['meta_image_media_id'],
            $data['meta_image_alt_override'],
            $data['og_image_media_id'],
            $data['og_image_alt_override']
        );

        return [$data, $media];
    }

    private function syncMedia(Page $page, MediaAttachmentService $mediaAttachments, array $media): void
    {
        foreach ($media as $collection => $values) {
            $mediaAttachments->syncSingle($page, $collection, $values['id'] ? (int) $values['id'] : null, [
                'alt_text_override' => $values['alt'],
            ]);
        }
    }
}
