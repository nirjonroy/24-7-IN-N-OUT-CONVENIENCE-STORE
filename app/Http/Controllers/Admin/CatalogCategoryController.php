<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\StoreCatalogCategoryRequest;
use App\Http\Requests\UpdateCatalogCategoryRequest;
use App\Models\CatalogCategory;
use App\Services\MediaAttachmentService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class CatalogCategoryController extends Controller
{
    public function index(): View
    {
        return view('admin.catalog.categories.index', [
            'categories' => CatalogCategory::with('parent')
                ->withCount('items')
                ->with('mediaAttachments.media.variants')
                ->orderBy('sort_order')
                ->orderBy('name')
                ->paginate(15),
        ]);
    }

    public function create(): View
    {
        return view('admin.catalog.categories.create', [
            'category' => new CatalogCategory(['is_active' => true]),
            'categories' => CatalogCategory::orderBy('name')->get(),
            'businessAreas' => CatalogCategory::BUSINESS_AREAS,
        ]);
    }

    public function store(StoreCatalogCategoryRequest $request, MediaAttachmentService $mediaAttachments): RedirectResponse
    {
        [$data, $media] = $this->splitMediaData($request->validated());
        $category = CatalogCategory::create($this->categoryData($request, $data));
        $this->syncMedia($category, $mediaAttachments, $media);

        return redirect()->route('admin.catalog.categories.index')->with('success', 'Category created successfully.');
    }

    public function show(CatalogCategory $category): RedirectResponse
    {
        return redirect()->route('admin.catalog.categories.edit', $category);
    }

    public function edit(CatalogCategory $category): View
    {
        $category->load('mediaAttachments.media.variants');

        return view('admin.catalog.categories.edit', [
            'category' => $category,
            'categories' => CatalogCategory::whereKeyNot($category->id)->orderBy('name')->get(),
            'businessAreas' => CatalogCategory::BUSINESS_AREAS,
        ]);
    }

    public function update(UpdateCatalogCategoryRequest $request, CatalogCategory $category, MediaAttachmentService $mediaAttachments): RedirectResponse
    {
        [$data, $media] = $this->splitMediaData($request->validated());
        $category->update($this->categoryData($request, $data));
        $this->syncMedia($category, $mediaAttachments, $media);

        return redirect()->route('admin.catalog.categories.index')->with('success', 'Category updated successfully.');
    }

    public function destroy(CatalogCategory $category): RedirectResponse
    {
        if ($category->items()->exists()) {
            return redirect()->route('admin.catalog.categories.index')
                ->with('error', 'This category still contains catalog items. Move or delete those items first.');
        }

        $category->delete();

        return redirect()->route('admin.catalog.categories.index')->with('success', 'Category deleted successfully.');
    }

    private function categoryData(Request $request, array $data): array
    {
        $data['parent_id'] = $data['parent_id'] ?? null;
        $data['is_featured'] = $request->boolean('is_featured');
        $data['is_active'] = $request->boolean('is_active');
        $data['sort_order'] = $data['sort_order'] ?? 0;

        return $data;
    }

    private function splitMediaData(array $data): array
    {
        $media = [
            'image' => ['id' => $data['image_media_id'] ?? null, 'alt' => $data['image_alt_override'] ?? null],
            'meta_image' => ['id' => $data['meta_image_media_id'] ?? null, 'alt' => $data['meta_image_alt_override'] ?? null],
        ];

        unset($data['image_media_id'], $data['image_alt_override'], $data['meta_image_media_id'], $data['meta_image_alt_override']);

        return [$data, $media];
    }

    private function syncMedia(CatalogCategory $category, MediaAttachmentService $mediaAttachments, array $media): void
    {
        foreach ($media as $collection => $values) {
            $mediaAttachments->syncSingle($category, $collection, $values['id'] ? (int) $values['id'] : null, [
                'alt_text_override' => $values['alt'],
            ]);
        }
    }
}
