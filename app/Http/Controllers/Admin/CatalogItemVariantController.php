<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\StoreCatalogItemVariantRequest;
use App\Http\Requests\UpdateCatalogItemVariantRequest;
use App\Models\CatalogItem;
use App\Models\CatalogItemVariant;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\View\View;

class CatalogItemVariantController extends Controller
{
    public function index(CatalogItem $item): View
    {
        return view('admin.catalog.variants.index', [
            'item' => $item,
            'variants' => $item->variants()->paginate(15),
        ]);
    }

    public function create(CatalogItem $item): View
    {
        return view('admin.catalog.variants.create', [
            'item' => $item,
            'variant' => new CatalogItemVariant(['is_available' => true]),
        ]);
    }

    public function store(StoreCatalogItemVariantRequest $request, CatalogItem $item): RedirectResponse
    {
        DB::transaction(function () use ($request, $item) {
            $data = $this->variantData($request, $request->validated());

            if ($data['is_default']) {
                $item->variants()->update(['is_default' => false]);
            }

            $item->variants()->create($data);
        });

        return redirect()->route('admin.catalog.items.variants.index', $item)->with('success', 'Variant created successfully.');
    }

    public function show(CatalogItem $item, CatalogItemVariant $variant): RedirectResponse
    {
        $this->ensureVariantBelongsToItem($item, $variant);

        return redirect()->route('admin.catalog.items.variants.edit', [$item, $variant]);
    }

    public function edit(CatalogItem $item, CatalogItemVariant $variant): View
    {
        $this->ensureVariantBelongsToItem($item, $variant);

        return view('admin.catalog.variants.edit', compact('item', 'variant'));
    }

    public function update(UpdateCatalogItemVariantRequest $request, CatalogItem $item, CatalogItemVariant $variant): RedirectResponse
    {
        $this->ensureVariantBelongsToItem($item, $variant);

        DB::transaction(function () use ($request, $item, $variant) {
            $data = $this->variantData($request, $request->validated());

            if ($data['is_default']) {
                $item->variants()->whereKeyNot($variant->id)->update(['is_default' => false]);
            }

            $variant->update($data);
        });

        return redirect()->route('admin.catalog.items.variants.index', $item)->with('success', 'Variant updated successfully.');
    }

    public function destroy(CatalogItem $item, CatalogItemVariant $variant): RedirectResponse
    {
        $this->ensureVariantBelongsToItem($item, $variant);
        $variant->delete();

        return redirect()->route('admin.catalog.items.variants.index', $item)->with('success', 'Variant deleted successfully.');
    }

    private function variantData(Request $request, array $data): array
    {
        $data['details'] = ! empty($data['details']) ? json_decode($data['details'], true) : null;
        $data['is_default'] = $request->boolean('is_default');
        $data['is_available'] = $request->boolean('is_available');
        $data['sort_order'] = $data['sort_order'] ?? 0;

        return $data;
    }

    private function ensureVariantBelongsToItem(CatalogItem $item, CatalogItemVariant $variant): void
    {
        abort_unless((int) $variant->catalog_item_id === (int) $item->id, 404);
    }
}
