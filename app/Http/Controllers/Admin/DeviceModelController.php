<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\StoreDeviceModelRequest;
use App\Http\Requests\UpdateDeviceModelRequest;
use App\Models\DeviceBrand;
use App\Models\DeviceModel;
use App\Services\MediaAttachmentService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\View\View;

class DeviceModelController extends Controller
{
    public function index(Request $request): View
    {
        return view('admin.phone-repair.models.index', [
            'models' => DeviceModel::with(['brand', 'mediaAttachments.media.variants'])->withCount('repairPrices')
                ->when($request->filled('search'), fn ($q) => $q->where(fn ($q) => $q->where('name', 'like', '%'.$request->search.'%')->orWhere('model_number', 'like', '%'.$request->search.'%')->orWhere('slug', 'like', '%'.$request->search.'%')))
                ->when($request->filled('brand_id'), fn ($q) => $q->where('device_brand_id', $request->brand_id))
                ->when($request->filled('device_type'), fn ($q) => $q->where('device_type', $request->device_type))
                ->when($request->filled('featured'), fn ($q) => $q->where('is_featured', $request->featured === 'yes'))
                ->when($request->filled('status'), fn ($q) => $q->where('is_active', $request->status === 'active'))
                ->orderByDesc('updated_at')->paginate(15)->withQueryString(),
            'brands' => DeviceBrand::orderBy('name')->get(),
            'deviceTypes' => DeviceModel::DEVICE_TYPES,
            'filters' => $request->only(['search', 'brand_id', 'device_type', 'featured', 'status']),
        ]);
    }

    public function create(): View
    {
        return view('admin.phone-repair.models.create', ['model' => new DeviceModel(['device_type' => 'phone', 'is_active' => true]), 'brands' => DeviceBrand::orderBy('name')->get(), 'deviceTypes' => DeviceModel::DEVICE_TYPES]);
    }

    public function store(StoreDeviceModelRequest $request, MediaAttachmentService $media): RedirectResponse
    {
        DB::transaction(function () use ($request, $media) {
            [$data, $mediaData] = $this->splitMedia($request->validated());
            $deviceModel = DeviceModel::create($this->data($request, $data));
            $this->syncMedia($deviceModel, $media, $mediaData);
        });
        return redirect()->route('admin.phone-repair.models.index')->with('success', 'Device model created successfully.');
    }

    public function show(DeviceModel $model): RedirectResponse { return redirect()->route('admin.phone-repair.models.edit', $model); }

    public function edit(DeviceModel $model): View
    {
        $model->load('mediaAttachments.media.variants');
        return view('admin.phone-repair.models.edit', ['model' => $model, 'brands' => DeviceBrand::orderBy('name')->get(), 'deviceTypes' => DeviceModel::DEVICE_TYPES]);
    }

    public function update(UpdateDeviceModelRequest $request, DeviceModel $model, MediaAttachmentService $media): RedirectResponse
    {
        DB::transaction(function () use ($request, $model, $media) {
            [$data, $mediaData] = $this->splitMedia($request->validated());
            $model->update($this->data($request, $data));
            $this->syncMedia($model, $media, $mediaData);
        });
        return redirect()->route('admin.phone-repair.models.index')->with('success', 'Device model updated successfully.');
    }

    public function destroy(DeviceModel $model): RedirectResponse
    {
        $model->delete();
        return redirect()->route('admin.phone-repair.models.index')->with('success', 'Device model deleted successfully.');
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
        $media = ['image' => [$data['image_media_id'] ?? null, $data['image_alt_override'] ?? null], 'meta_image' => [$data['meta_image_media_id'] ?? null, $data['meta_image_alt_override'] ?? null], 'gallery' => $this->ids($data['gallery_media_ids'] ?? '')];
        unset($data['image_media_id'], $data['image_alt_override'], $data['meta_image_media_id'], $data['meta_image_alt_override'], $data['gallery_media_ids']);
        return [$data, $media];
    }

    private function ids(string $ids): array { return array_values(array_unique(array_filter(array_map(fn ($v) => (int) trim($v), explode(',', $ids))))); }

    private function syncMedia(DeviceModel $model, MediaAttachmentService $service, array $media): void
    {
        foreach (['image', 'meta_image'] as $collection) {
            [$id, $alt] = $media[$collection];
            $service->syncSingle($model, $collection, $id ? (int) $id : null, ['alt_text_override' => $alt]);
        }
        $service->syncCollection($model, 'gallery', $media['gallery']);
    }
}
