<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\BulkRepairServicePriceRequest;
use App\Models\DeviceBrand;
use App\Models\DeviceModel;
use App\Models\RepairService;
use App\Models\RepairServicePrice;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\View\View;

class RepairServicePriceController extends Controller
{
    public function edit(Request $request, RepairService $service): View
    {
        $models = DeviceModel::with('brand')
            ->when($request->filled('brand_id'), fn ($q) => $q->where('device_brand_id', $request->brand_id))
            ->when($request->filled('device_type'), fn ($q) => $q->where('device_type', $request->device_type))
            ->when($request->filled('search'), fn ($q) => $q->where(fn ($q) => $q->where('name', 'like', '%'.$request->search.'%')->orWhere('model_number', 'like', '%'.$request->search.'%')->orWhere('slug', 'like', '%'.$request->search.'%')))
            ->orderBy('device_brand_id')->orderBy('sort_order')->orderBy('name')->paginate(25)->withQueryString();

        $prices = RepairServicePrice::where('repair_service_id', $service->id)
            ->whereIn('device_model_id', $models->pluck('id'))
            ->get()->keyBy('device_model_id');

        return view('admin.phone-repair.prices.edit', [
            'service' => $service,
            'models' => $models,
            'prices' => $prices,
            'brands' => DeviceBrand::orderBy('name')->get(),
            'deviceTypes' => DeviceModel::DEVICE_TYPES,
            'filters' => $request->only(['brand_id', 'device_type', 'search']),
        ]);
    }

    public function update(BulkRepairServicePriceRequest $request, RepairService $service): RedirectResponse
    {
        DB::transaction(function () use ($request, $service) {
            foreach ($request->validated('prices', []) as $deviceModelId => $row) {
                if (! DeviceModel::whereKey($deviceModelId)->exists()) {
                    continue;
                }

                $data = [
                    'price' => $row['price'] ?? null,
                    'compare_at_price' => $row['compare_at_price'] ?? null,
                    'price_label' => $row['price_label'] ?? null,
                    'is_price_visible' => filter_var($row['is_price_visible'] ?? false, FILTER_VALIDATE_BOOLEAN),
                    'estimated_minutes' => $row['estimated_minutes'] ?? null,
                    'warranty_text' => $row['warranty_text'] ?? null,
                    'notes' => $row['notes'] ?? null,
                    'is_available' => filter_var($row['is_available'] ?? false, FILTER_VALIDATE_BOOLEAN),
                    'sort_order' => $row['sort_order'] ?? 0,
                ];

                if (! $this->hasMeaningfulData($data)) {
                    continue;
                }

                $service->prices()->updateOrCreate(['device_model_id' => $deviceModelId], $data);
            }
        });

        return redirect()->route('admin.phone-repair.services.prices.edit', $service)->with('success', 'Repair pricing updated successfully.');
    }

    private function hasMeaningfulData(array $data): bool
    {
        return $data['price'] !== null || $data['compare_at_price'] !== null || filled($data['price_label']) || $data['estimated_minutes'] !== null || filled($data['warranty_text']) || filled($data['notes']) || $data['is_available'] === false || $data['is_price_visible'] === false || (int) $data['sort_order'] > 0;
    }
}
