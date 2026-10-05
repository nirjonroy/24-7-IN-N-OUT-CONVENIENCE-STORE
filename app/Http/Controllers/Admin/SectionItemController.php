<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\StoreSectionItemRequest;
use App\Http\Requests\UpdateSectionItemRequest;
use App\Models\Page;
use App\Models\PageSection;
use App\Models\SectionItem;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Str;
use Illuminate\View\View;

class SectionItemController extends Controller
{
    public function index(Page $page, PageSection $section): View
    {
        $this->ensureSectionBelongsToPage($page, $section);

        return view('admin.section-items.index', [
            'page' => $page,
            'section' => $section,
            'items' => $section->items()->paginate(15),
        ]);
    }

    public function create(Page $page, PageSection $section): View
    {
        $this->ensureSectionBelongsToPage($page, $section);

        return view('admin.section-items.create', [
            'page' => $page,
            'section' => $section,
            'item' => new SectionItem(['is_active' => true]),
        ]);
    }

    public function store(StoreSectionItemRequest $request, Page $page, PageSection $section): RedirectResponse
    {
        $this->ensureSectionBelongsToPage($page, $section);
        $section->items()->create($this->itemData($request->validated()));

        return redirect()->route('admin.pages.sections.items.index', [$page, $section])->with('success', 'Section item created successfully.');
    }

    public function show(Page $page, PageSection $section, SectionItem $item): RedirectResponse
    {
        $this->ensureItemContext($page, $section, $item);

        return redirect()->route('admin.pages.sections.items.edit', [$page, $section, $item]);
    }

    public function edit(Page $page, PageSection $section, SectionItem $item): View
    {
        $this->ensureItemContext($page, $section, $item);

        return view('admin.section-items.edit', compact('page', 'section', 'item'));
    }

    public function update(UpdateSectionItemRequest $request, Page $page, PageSection $section, SectionItem $item): RedirectResponse
    {
        $this->ensureItemContext($page, $section, $item);
        $item->update($this->itemData($request->validated()));

        return redirect()->route('admin.pages.sections.items.index', [$page, $section])->with('success', 'Section item updated successfully.');
    }

    public function destroy(Page $page, PageSection $section, SectionItem $item): RedirectResponse
    {
        $this->ensureItemContext($page, $section, $item);
        $item->delete();

        return redirect()->route('admin.pages.sections.items.index', [$page, $section])->with('success', 'Section item deleted successfully.');
    }

    private function itemData(array $data): array
    {
        if (! empty($data['item_key'])) {
            $data['item_key'] = Str::slug($data['item_key'], '_');
        }

        $data['settings'] = ! empty($data['settings']) ? json_decode($data['settings'], true) : null;
        $data['sort_order'] = $data['sort_order'] ?? 0;
        $data['is_active'] = request()->boolean('is_active');

        return $data;
    }

    private function ensureSectionBelongsToPage(Page $page, PageSection $section): void
    {
        abort_unless((int) $section->page_id === (int) $page->id, 404);
    }

    private function ensureItemContext(Page $page, PageSection $section, SectionItem $item): void
    {
        $this->ensureSectionBelongsToPage($page, $section);
        abort_unless((int) $item->page_section_id === (int) $section->id, 404);
    }
}
