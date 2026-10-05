<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\StoreRepairServiceRequest;
use App\Http\Requests\UpdateRepairServiceRequest;
use App\Models\RepairService;
use App\Services\MediaAttachmentService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\View\View;

class RepairServiceController extends Controller
{
    public function index(Request $request): View
    {
        return view('admin.phone-repair.services.index', [
            'services' => RepairService::with('mediaAttachments.media.variants')->withCount('prices')
                ->when($request->filled('search'), fn ($q) => $q->where(fn ($q) => $q->where('title', 'like', '%'.$request->search.'%')->orWhere('slug', 'like', '%'.$request->search.'%')))
                ->when($request->filled('repair_type'), fn ($q) => $q->where('repair_type', $request->repair_type))
                ->when($request->filled('featured'), fn ($q) => $q->where('is_featured', $request->featured === 'yes'))
                ->when($request->filled('status'), fn ($q) => $q->where('is_active', $request->status === 'active'))
                ->orderBy('sort_order')->orderBy('title')->paginate(15)->withQueryString(),
            'repairTypes' => RepairService::REPAIR_TYPES,
            'filters' => $request->only(['search', 'repair_type', 'featured', 'status']),
        ]);
    }

    public function create(): View
    {
        return view('admin.phone-repair.services.create', ['service' => new RepairService(['is_price_visible' => true, 'is_active' => true]), 'repairTypes' => RepairService::REPAIR_TYPES]);
    }

    public function store(StoreRepairServiceRequest $request, MediaAttachmentService $media): RedirectResponse
    {
        DB::transaction(function () use ($request, $media) {
            [$data, $mediaData] = $this->splitMedia($request->validated());
            $service = RepairService::create($this->data($request, $data));
            $this->syncMedia($service, $media, $mediaData);
        });
        return redirect()->route('admin.phone-repair.services.index')->with('success', 'Repair service created successfully.');
    }

    public function show(RepairService $service): RedirectResponse { return redirect()->route('admin.phone-repair.services.edit', $service); }

    public function edit(RepairService $service): View
    {
        $service->load('mediaAttachments.media.variants');
        return view('admin.phone-repair.services.edit', ['service' => $service, 'repairTypes' => RepairService::REPAIR_TYPES]);
    }

    public function update(UpdateRepairServiceRequest $request, RepairService $service, MediaAttachmentService $media): RedirectResponse
    {
        DB::transaction(function () use ($request, $service, $media) {
            [$data, $mediaData] = $this->splitMedia($request->validated());
            $service->update($this->data($request, $data));
            $this->syncMedia($service, $media, $mediaData);
        });
        return redirect()->route('admin.phone-repair.services.index')->with('success', 'Repair service updated successfully.');
    }

    public function destroy(RepairService $service): RedirectResponse
    {
        $service->delete();
        return redirect()->route('admin.phone-repair.services.index')->with('success', 'Repair service deleted successfully.');
    }

    private function data(Request $request, array $data): array
    {
        foreach (['is_price_visible', 'diagnostic_required', 'is_featured', 'is_active'] as $field) {
            $data[$field] = $request->boolean($field);
        }
        $data['sort_order'] = $data['sort_order'] ?? 0;
        return $data;
    }

    private function splitMedia(array $data): array
    {
        $media = ['image' => [$data['image_media_id'] ?? null, $data['image_alt_override'] ?? null], 'icon_image' => [$data['icon_image_media_id'] ?? null, $data['icon_image_alt_override'] ?? null], 'meta_image' => [$data['meta_image_media_id'] ?? null, $data['meta_image_alt_override'] ?? null], 'gallery' => $this->ids($data['gallery_media_ids'] ?? '')];
        unset($data['image_media_id'], $data['image_alt_override'], $data['icon_image_media_id'], $data['icon_image_alt_override'], $data['meta_image_media_id'], $data['meta_image_alt_override'], $data['gallery_media_ids']);
        return [$data, $media];
    }

    private function ids(string $ids): array { return array_values(array_unique(array_filter(array_map(fn ($v) => (int) trim($v), explode(',', $ids))))); }

    private function syncMedia(RepairService $serviceModel, MediaAttachmentService $service, array $media): void
    {
        foreach (['image', 'icon_image', 'meta_image'] as $collection) {
            [$id, $alt] = $media[$collection];
            $service->syncSingle($serviceModel, $collection, $id ? (int) $id : null, ['alt_text_override' => $alt]);
        }
        $service->syncCollection($serviceModel, 'gallery', $media['gallery']);
    }
}
