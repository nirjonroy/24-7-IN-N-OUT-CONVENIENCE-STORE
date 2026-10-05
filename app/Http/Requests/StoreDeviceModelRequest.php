<?php

namespace App\Http\Requests;

use App\Models\DeviceModel;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;

class StoreDeviceModelRequest extends FormRequest
{
    public function authorize(): bool { return true; }

    public function rules(): array
    {
        $brandId = $this->input('device_brand_id');
        $maxYear = now()->year + 1;
        return [
            'device_brand_id' => ['required', 'exists:device_brands,id'],
            'name' => ['required', 'string', 'max:180'],
            'slug' => ['nullable', 'string', 'max:200', Rule::unique('device_models', 'slug')->where('device_brand_id', $brandId)],
            'model_number' => ['nullable', 'string', 'max:120'],
            'device_type' => ['required', Rule::in(DeviceModel::DEVICE_TYPES)],
            'release_year' => ['nullable', 'integer', 'min:1990', 'max:'.$maxYear],
            'description' => ['nullable', 'string'],
            'is_featured' => ['nullable', 'boolean'], 'is_active' => ['nullable', 'boolean'], 'sort_order' => ['nullable', 'integer', 'min:0'],
            'page_name' => ['nullable', 'string', 'max:150'], 'seo_title' => ['nullable', 'string', 'max:255'], 'seo_description' => ['nullable', 'string'], 'meta_title' => ['nullable', 'string', 'max:255'], 'meta_description' => ['nullable', 'string'], 'meta_image' => ['nullable', 'string', 'max:500'], 'author' => ['nullable', 'string', 'max:150'], 'publisher' => ['nullable', 'string', 'max:150'], 'copyright' => ['nullable', 'string', 'max:255'], 'site_name' => ['nullable', 'string', 'max:150'], 'keywords' => ['nullable', 'string'],
            'image_media_id' => ['nullable', 'integer', Rule::exists('media_assets', 'id')->where('is_active', true)], 'image_alt_override' => ['nullable', 'string', 'max:255'], 'gallery_media_ids' => ['nullable', 'string'],
            'meta_image_media_id' => ['nullable', 'integer', Rule::exists('media_assets', 'id')->where('is_active', true)], 'meta_image_alt_override' => ['nullable', 'string', 'max:255'],
        ];
    }

    protected function prepareForValidation(): void
    {
        if ($source = ($this->input('slug') ?: $this->input('name'))) {
            $this->merge(['slug' => Str::slug($source)]);
        }
    }
}
