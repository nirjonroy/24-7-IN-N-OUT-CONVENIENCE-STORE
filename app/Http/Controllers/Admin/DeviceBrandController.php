<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\StoreDeviceBrandRequest;
use App\Http\Requests\UpdateDeviceBrandRequest;
use App\Models\DeviceBrand;
use App\Services\MediaAttachmentService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class DeviceBrandController extends Controller
{
    public function index(Request $request): View
    {
        return view('admin.phone-repair.brands.index', [
            'brands' => DeviceBrand::withCount('models')->with('mediaAttachments.media.variants')
                ->when($request->filled('search'), fn ($q) => $q->where('name', 'like', '%'.$request->search.'%')->orWhere('slug', 'like', '%'.$request->search.'%'))
                ->orderBy('sort_order')->orderBy('name')->paginate(15)->withQueryString(),
        ]);
    }

    public function create(): View
    {
        return view('admin.phone-repair.brands.create', ['brand' => new DeviceBrand(['is_active' => true])]);
    }

    public function store(StoreDeviceBrandRequest $request, MediaAttachmentService $media): RedirectResponse
    {
        [$data, $mediaData] = $this->splitMedia($request->validated());
        $brand = DeviceBrand::create($this->data($request, $data));
        $this->syncMedia($brand, $media, $mediaData);
        return redirect()->route('admin.phone-repair.brands.index')->with('success', 'Device brand created successfully.');
    }

    public function show(DeviceBrand $brand): RedirectResponse { return redirect()->route('admin.phone-repair.brands.edit', $brand); }

    public function edit(DeviceBrand $brand): View
    {
        $brand->load('mediaAttachments.media.variants');
        return view('admin.phone-repair.brands.edit', compact('brand'));
    }

    public function update(UpdateDeviceBrandRequest $request, DeviceBrand $brand, MediaAttachmentService $media): RedirectResponse
    {
        [$data, $mediaData] = $this->splitMedia($request->validated());
        $brand->update($this->data($request, $data));
        $this->syncMedia($brand, $media, $mediaData);
        return redirect()->route('admin.phone-repair.brands.index')->with('success', 'Device brand updated successfully.');
    }

    public function destroy(DeviceBrand $brand): RedirectResponse
    {
        if ($brand->models()->exists()) {
            return redirect()->route('admin.phone-repair.brands.index')->with('error', 'This brand still contains device models. Move or delete those models first.');
        }
        $brand->delete();
        return redirect()->route('admin.phone-repair.brands.index')->with('success', 'Device brand deleted successfully.');
    }

    private function data(Request $request, array $data): array
    {
        $data['is_featured'] = $request->boolean('is_featured');
        $data['is_active'] = $request->boolean('is_active');
        $data['sort_order'] = $data['sort_order'] ?? 0;
        return $data;
    }

    private function splitMedia(array $data): array
    {
        $media = ['logo' => [$data['logo_media_id'] ?? null, $data['logo_alt_override'] ?? null], 'image' => [$data['image_media_id'] ?? null, $data['image_alt_override'] ?? null], 'meta_image' => [$data['meta_image_media_id'] ?? null, $data['meta_image_alt_override'] ?? null]];
        unset($data['logo_media_id'], $data['logo_alt_override'], $data['image_media_id'], $data['image_alt_override'], $data['meta_image_media_id'], $data['meta_image_alt_override']);
        return [$data, $media];
    }

    private function syncMedia(DeviceBrand $brand, MediaAttachmentService $service, array $media): void
    {
        foreach ($media as $collection => [$id, $alt]) {
            $service->syncSingle($brand, $collection, $id ? (int) $id : null, ['alt_text_override' => $alt]);
        }
    }
}
