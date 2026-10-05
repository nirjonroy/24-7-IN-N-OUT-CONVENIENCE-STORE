<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\StorePageSectionRequest;
use App\Http\Requests\UpdatePageSectionRequest;
use App\Models\Page;
use App\Models\PageSection;
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

    public function store(StorePageSectionRequest $request, Page $page): RedirectResponse
    {
        $page->sections()->create($this->sectionData($request->validated()));

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

        return view('admin.page-sections.edit', [
            'page' => $page,
            'section' => $section,
            'sectionTypes' => self::SECTION_TYPES,
        ]);
    }

    public function update(UpdatePageSectionRequest $request, Page $page, PageSection $section): RedirectResponse
    {
        $this->ensureSectionBelongsToPage($page, $section);
        $section->update($this->sectionData($request->validated()));

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
}
