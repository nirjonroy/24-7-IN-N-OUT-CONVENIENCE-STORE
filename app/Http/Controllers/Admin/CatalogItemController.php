<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\StoreCatalogItemRequest;
use App\Http\Requests\UpdateCatalogItemRequest;
use App\Models\CatalogCategory;
use App\Models\CatalogItem;
use App\Services\MediaAttachmentService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\View\View;

class CatalogItemController extends Controller
{
    public function index(Request $request): View
    {
        return view('admin.catalog.items.index', [
            'items' => CatalogItem::with(['category', 'mediaAttachments.media.variants'])
                ->when($request->filled('search'), function ($query) use ($request) {
                    $search = '%'.$request->search.'%';
                    $query->where(function ($query) use ($search) {
                        $query->where('name', 'like', $search)
                            ->orWhere('sku', 'like', $search)
                            ->orWhere('brand', 'like', $search)
                            ->orWhere('slug', 'like', $search);
                    });
                })
                ->when($request->filled('category_id'), fn ($query) => $query->where('catalog_category_id', $request->category_id))
                ->when($request->filled('business_area'), fn ($query) => $query->businessArea($request->business_area))
                ->when($request->filled('item_type'), fn ($query) => $query->where('item_type', $request->item_type))
                ->when($request->filled('featured'), fn ($query) => $query->where('is_featured', $request->featured === 'yes'))
                ->when($request->filled('availability'), fn ($query) => $query->where('is_available', $request->availability === 'available'))
                ->when($request->filled('status'), fn ($query) => $query->where('is_active', $request->status === 'active'))
                ->latest()
                ->paginate(15)
                ->withQueryString(),
            'categories' => CatalogCategory::orderBy('name')->get(),
            'businessAreas' => CatalogCategory::BUSINESS_AREAS,
            'itemTypes' => CatalogItem::ITEM_TYPES,
            'filters' => $request->only(['search', 'category_id', 'business_area', 'item_type', 'featured', 'availability', 'status']),
        ]);
    }

    public function create(Request $request): View
    {
        $category = $request->filled('category_id') ? CatalogCategory::find($request->category_id) : null;

        return view('admin.catalog.items.create', [
            'item' => new CatalogItem($this->defaultItemValues($category)),
            'categories' => CatalogCategory::orderBy('name')->get(),
            'itemTypes' => CatalogItem::ITEM_TYPES,
        ]);
    }

    public function store(StoreCatalogItemRequest $request, MediaAttachmentService $mediaAttachments): RedirectResponse
    {
        DB::transaction(function () use ($request, $mediaAttachments) {
            [$data, $media] = $this->splitMediaData($request->validated());
            $item = CatalogItem::create($this->itemData($request, $data));
            $this->syncMedia($item, $mediaAttachments, $media);
        });

        return redirect()->route('admin.catalog.items.index')->with('success', 'Catalog item created successfully.');
    }

    public function show(CatalogItem $item): RedirectResponse
    {
        return redirect()->route('admin.catalog.items.edit', $item);
    }

    public function edit(CatalogItem $item): View
    {
        $item->load(['mediaAttachments.media.variants', 'category']);

        return view('admin.catalog.items.edit', [
            'item' => $item,
            'categories' => CatalogCategory::orderBy('name')->get(),
            'itemTypes' => CatalogItem::ITEM_TYPES,
        ]);
    }

    public function update(UpdateCatalogItemRequest $request, CatalogItem $item, MediaAttachmentService $mediaAttachments): RedirectResponse
    {
        DB::transaction(function () use ($request, $item, $mediaAttachments) {
            [$data, $media] = $this->splitMediaData($request->validated());
            $item->update($this->itemData($request, $data));
            $this->syncMedia($item, $mediaAttachments, $media);
        });

        return redirect()->route('admin.catalog.items.index')->with('success', 'Catalog item updated successfully.');
    }

    public function destroy(CatalogItem $item): RedirectResponse
    {
        $item->delete();

        return redirect()->route('admin.catalog.items.index')->with('success', 'Catalog item deleted successfully.');
    }

    private function defaultItemValues(?CatalogCategory $category): array
    {
        if ($category?->business_area === 'adult_retail') {
            return ['item_type' => 'adult_product', 'is_age_restricted' => true, 'minimum_age' => 21, 'is_price_visible' => true, 'is_available' => true, 'is_active' => true];
        }

        return ['is_price_visible' => true, 'is_available' => true, 'is_active' => true];
    }

    private function itemData(Request $request, array $data): array
    {
        $category = CatalogCategory::find($data['catalog_category_id']);
        $data['details'] = ! empty($data['details']) ? json_decode($data['details'], true) : null;
        $data['is_price_visible'] = $request->boolean('is_price_visible');
        $data['is_age_restricted'] = $request->boolean('is_age_restricted');
        $data['is_featured'] = $request->boolean('is_featured');
        $data['is_available'] = $request->boolean('is_available');
        $data['is_active'] = $request->boolean('is_active');
        $data['sort_order'] = $data['sort_order'] ?? 0;

        if ($category?->business_area === 'adult_retail') {
            $data['is_age_restricted'] = $data['is_age_restricted'] || $request->boolean('is_age_restricted');
            $data['minimum_age'] = $data['minimum_age'] ?? 21;
        }

        return $data;
    }

    private function splitMediaData(array $data): array
    {
        $media = [
            'primary_image' => ['id' => $data['primary_image_media_id'] ?? null, 'alt' => $data['primary_image_alt_override'] ?? null],
            'meta_image' => ['id' => $data['meta_image_media_id'] ?? null, 'alt' => $data['meta_image_alt_override'] ?? null],
            'gallery' => $this->galleryIds($data['gallery_media_ids'] ?? ''),
        ];

        unset($data['primary_image_media_id'], $data['primary_image_alt_override'], $data['meta_image_media_id'], $data['meta_image_alt_override'], $data['gallery_media_ids']);

        return [$data, $media];
    }

    private function galleryIds(string $ids): array
    {
        return array_values(array_unique(array_filter(array_map(fn ($value) => (int) trim($value), explode(',', $ids)))));
    }

    private function syncMedia(CatalogItem $item, MediaAttachmentService $mediaAttachments, array $media): void
    {
        foreach (['primary_image', 'meta_image'] as $collection) {
            $values = $media[$collection];
            $mediaAttachments->syncSingle($item, $collection, $values['id'] ? (int) $values['id'] : null, [
                'alt_text_override' => $values['alt'],
            ]);
        }

        $mediaAttachments->syncCollection($item, 'gallery', $media['gallery']);
    }
}
