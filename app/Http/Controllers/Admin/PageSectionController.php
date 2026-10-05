<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\StorePageSectionRequest;
use App\Http\Requests\UpdatePageSectionRequest;
use App\Models\Page;
use App\Models\PageSection;
use App\Services\MediaAttachmentService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Str;
use Illuminate\View\View;

class PageSectionController extends Controller
{
    private const SECTION_TYPES = [
        'hero' => 'Hero',
        'text' => 'Text',
        'image_content' => 'Image Content',
        'feature_grid' => 'Feature Grid',
        'service_grid' => 'Service Grid',
        'stats' => 'Stats',
        'business_info' => 'Business Info',
        'location' => 'Location',
        'catalog_preview' => 'Catalog Preview',
        'repair_preview' => 'Repair Preview',
        'gallery_preview' => 'Gallery Preview',
        'faq_preview' => 'FAQ Preview',
        'cta' => 'CTA',
        'custom' => 'Custom',
    ];

    public function index(Page $page): View
    {
        return view('admin.page-sections.index', [
            'page' => $page,
            'sections' => $page->sections()
                ->withCount('items')
                ->paginate(15),
        ]);
    }

    public function create(Page $page): View
    {
        return view('admin.page-sections.create', [
            'page' => $page,
            'section' => new PageSection(['is_active' => true, 'section_type' => 'custom']),
            'sectionTypes' => self::SECTION_TYPES,
        ]);
    }

    public function store(StorePageSectionRequest $request, Page $page, MediaAttachmentService $mediaAttachments): RedirectResponse
    {
        [$data, $media] = $this->splitMediaData($request->validated());
        $section = $page->sections()->create($this->sectionData($data));
        $this->syncMedia($section, $mediaAttachments, $media);

        return redirect()->route('admin.pages.sections.index', $page)->with('success', 'Page section created successfully.');
    }

    public function show(Page $page, PageSection $section): RedirectResponse
    {
        $this->ensureSectionBelongsToPage($page, $section);

        return redirect()->route('admin.pages.sections.edit', [$page, $section]);
    }

    public function edit(Page $page, PageSection $section): View
    {
        $this->ensureSectionBelongsToPage($page, $section);
        $section->load('mediaAttachments.media.variants');

        return view('admin.page-sections.edit', [
            'page' => $page,
            'section' => $section,
            'sectionTypes' => self::SECTION_TYPES,
        ]);
    }

    public function update(UpdatePageSectionRequest $request, Page $page, PageSection $section, MediaAttachmentService $mediaAttachments): RedirectResponse
    {
        $this->ensureSectionBelongsToPage($page, $section);
        [$data, $media] = $this->splitMediaData($request->validated());
        $section->update($this->sectionData($data));
        $this->syncMedia($section, $mediaAttachments, $media);

        return redirect()->route('admin.pages.sections.index', $page)->with('success', 'Page section updated successfully.');
    }

    public function destroy(Page $page, PageSection $section): RedirectResponse
    {
        $this->ensureSectionBelongsToPage($page, $section);
        $section->delete();

        return redirect()->route('admin.pages.sections.index', $page)->with('success', 'Page section deleted successfully.');
    }

    private function sectionData(array $data): array
    {
        $data['section_key'] = Str::slug($data['section_key'] ?: ($data['title'] ?: $data['section_type']), '_');
        $data['settings'] = ! empty($data['settings']) ? json_decode($data['settings'], true) : null;
        $data['sort_order'] = $data['sort_order'] ?? 0;
        $data['is_active'] = request()->boolean('is_active');

        return $data;
    }

    private function ensureSectionBelongsToPage(Page $page, PageSection $section): void
    {
        abort_unless((int) $section->page_id === (int) $page->id, 404);
    }

    private function splitMediaData(array $data): array
    {
        $media = [
            'image' => ['id' => $data['image_media_id'] ?? null, 'alt' => $data['image_alt_override'] ?? null],
            'background_image' => ['id' => $data['background_image_media_id'] ?? null, 'alt' => $data['background_image_alt_override'] ?? null],
            'meta_image' => ['id' => $data['meta_image_media_id'] ?? null, 'alt' => $data['meta_image_alt_override'] ?? null],
        ];

        unset(
            $data['image_media_id'],
            $data['image_alt_override'],
            $data['background_image_media_id'],
            $data['background_image_alt_override'],
            $data['meta_image_media_id'],
            $data['meta_image_alt_override']
        );

        return [$data, $media];
    }

    private function syncMedia(PageSection $section, MediaAttachmentService $mediaAttachments, array $media): void
    {
        foreach ($media as $collection => $values) {
            $mediaAttachments->syncSingle($section, $collection, $values['id'] ? (int) $values['id'] : null, [
                'alt_text_override' => $values['alt'],
            ]);
        }
    }
}
