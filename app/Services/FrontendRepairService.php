<?php

namespace App\Services;

use App\Models\DeviceBrand;
use App\Models\DeviceModel;
use App\Models\RepairService;
use App\Models\RepairServicePrice;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Collection;

class FrontendRepairService
{
    public function brands(): Collection
    {
        return DeviceBrand::active()
            ->with('mediaAttachments.media.variants')
            ->orderBy('sort_order')
            ->orderBy('id')
            ->get()
            ->map(fn (DeviceBrand $brand) => [
                'id' => $brand->id,
                'name' => $brand->name,
                'description' => $brand->description,
                'is_featured' => $brand->is_featured,
                'logo' => $this->mediaUrl($brand, 'logo', null, 'thumbnail'),
                'logo_alt' => $this->mediaAlt($brand, 'logo', $brand->name),
            ]);
    }

    public function services(): Collection
    {
        return RepairService::active()
            ->with('mediaAttachments.media.variants')
            ->orderByDesc('is_featured')
            ->orderBy('sort_order')
            ->orderBy('id')
            ->get()
            ->map(fn (RepairService $service) => $this->serviceData($service));
    }

    public function featuredServices(): Collection
    {
        return $this->services()->where('is_featured', true)->values();
    }

    public function modelsForBrand(int $brandId): Collection
    {
        $brand = DeviceBrand::active()->whereKey($brandId)->firstOrFail();

        return DeviceModel::active()
            ->where('device_brand_id', $brand->id)
            ->orderBy('sort_order')
            ->orderBy('id')
            ->get(['id', 'name', 'model_number', 'device_type'])
            ->map(fn (DeviceModel $model) => [
                'id' => $model->id,
                'name' => $model->name,
                'model_number' => $model->model_number,
                'device_type' => $model->device_type,
            ]);
    }

    public function estimate(int $deviceModelId, int $repairServiceId): array
    {
        $device = DeviceModel::active()
            ->whereKey($deviceModelId)
            ->whereHas('brand', fn ($query) => $query->active())
            ->with('brand')
            ->firstOrFail();
        $service = RepairService::active()->whereKey($repairServiceId)->firstOrFail();
        $specific = RepairServicePrice::where('device_model_id', $device->id)
            ->where('repair_service_id', $service->id)
            ->first();
        $resolved = $service->priceForDevice($device);
        $visible = (bool) ($resolved['is_price_visible'] ?? true);
        $available = (bool) ($resolved['is_available'] ?? true);
        $numericPrice = $visible && $available ? $this->money($resolved['price'] ?? null) : null;
        $priceLabel = $visible ? ($resolved['price_label'] ?? null) : ($specific?->price_label ?: $service->price_note);

        if (! $numericPrice && ! $priceLabel) {
            $priceLabel = 'Request a quote';
        } elseif (! $specific && $numericPrice) {
            $priceLabel = 'Starting at '.$numericPrice;
        }

        return [
            'available' => $available,
            'availability_label' => $available ? 'Available' : 'Currently unavailable',
            'price' => $numericPrice ? number_format((float) $resolved['price'], 2, '.', '') : null,
            'formatted_price' => $specific && $numericPrice ? $numericPrice : null,
            'price_label' => $priceLabel,
            'is_price_visible' => $visible,
            'is_specific_price' => (bool) $specific,
            'estimated_minutes' => $resolved['estimated_minutes'] ?? null,
            'estimated_range' => $this->estimatedRange($resolved),
            'warranty' => $resolved['warranty_text'] ?? null,
            'note' => $specific?->notes,
            'brand' => $device->brand->name,
            'device' => $device->name,
            'model_number' => $device->model_number,
            'service' => $service->title,
            'contact_url' => route('frontend.contact'),
        ];
    }

    private function serviceData(RepairService $service): array
    {
        return [
            'id' => $service->id,
            'title' => $service->title,
            'repair_type' => $service->repair_type,
            'short_description' => $service->short_description,
            'description' => $service->description,
            'starting_price' => $service->is_price_visible ? $this->money($service->starting_price) : null,
            'price_note' => $service->price_note ?: ($service->is_price_visible && $service->starting_price ? 'Starting at '.$this->money($service->starting_price) : 'Request a quote'),
            'is_price_visible' => $service->is_price_visible,
            'estimated_range' => $this->estimatedRange([
                'estimated_minutes_min' => $service->estimated_minutes_min,
                'estimated_minutes_max' => $service->estimated_minutes_max,
            ]),
            'warranty_text' => $service->warranty_text,
            'diagnostic_required' => $service->diagnostic_required,
            'is_featured' => $service->is_featured,
            'image' => $this->mediaUrl($service, 'image', null, 'medium'),
            'image_alt' => $this->mediaAlt($service, 'image', $service->title),
        ];
    }

    private function estimatedRange(array $resolved): ?string
    {
        if (! empty($resolved['estimated_minutes'])) {
            return 'About '.$resolved['estimated_minutes'].' minutes';
        }

        $min = $resolved['estimated_minutes_min'] ?? null;
        $max = $resolved['estimated_minutes_max'] ?? null;

        if ($min && $max && $min !== $max) {
            return $min.'-'.$max.' minutes';
        }

        if ($min || $max) {
            return 'About '.($min ?: $max).' minutes';
        }

        return null;
    }

    private function money($value): ?string
    {
        if ($value === null || $value === '') {
            return null;
        }

        return '$'.number_format((float) $value, 2);
    }

    private function mediaUrl(Model $model, string $collection, ?string $fallback = null, ?string $variant = null): ?string
    {
        if (! $model->relationLoaded('mediaAttachments')) {
            return $fallback;
        }

        $attachment = $model->mediaAttachments
            ->where('collection', $collection)
            ->sortBy([
                ['is_primary', 'desc'],
                ['sort_order', 'asc'],
                ['id', 'asc'],
            ])
            ->first();

        if (! $attachment?->media) {
            return $fallback;
        }

        return $variant ? $attachment->media->getVariantUrl($variant) : $attachment->media->url;
    }

    private function mediaAlt(Model $model, string $collection, string $fallback): string
    {
        if (! $model->relationLoaded('mediaAttachments')) {
            return $fallback;
        }

        $attachment = $model->mediaAttachments
            ->where('collection', $collection)
            ->sortBy([
                ['is_primary', 'desc'],
                ['sort_order', 'asc'],
                ['id', 'asc'],
            ])
            ->first();

        return $attachment?->alt_text_override ?: ($attachment?->media?->alt_text ?: $fallback);
    }
}
