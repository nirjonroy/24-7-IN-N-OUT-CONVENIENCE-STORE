<?php

namespace App\Http\Requests;

use App\Models\RepairService;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;
use Illuminate\Validation\Validator;

class StoreRepairServiceRequest extends FormRequest
{
    public function authorize(): bool { return true; }

    public function rules(): array
    {
        return [
            'title' => ['required', 'string', 'max:180'],
            'slug' => ['nullable', 'string', 'max:200', Rule::unique('repair_services', 'slug')],
            'repair_type' => ['required', 'string', 'max:100', Rule::in(RepairService::REPAIR_TYPES)],
            'short_description' => ['nullable', 'string'], 'description' => ['nullable', 'string'],
            'starting_price' => ['nullable', 'numeric', 'min:0'], 'compare_at_price' => ['nullable', 'numeric', 'min:0'], 'price_note' => ['nullable', 'string', 'max:255'], 'is_price_visible' => ['nullable', 'boolean'],
            'estimated_minutes_min' => ['nullable', 'integer', 'min:0'], 'estimated_minutes_max' => ['nullable', 'integer', 'min:0'], 'warranty_text' => ['nullable', 'string', 'max:255'], 'diagnostic_required' => ['nullable', 'boolean'],
            'is_featured' => ['nullable', 'boolean'], 'is_active' => ['nullable', 'boolean'], 'sort_order' => ['nullable', 'integer', 'min:0'],
            'page_name' => ['nullable', 'string', 'max:150'], 'seo_title' => ['nullable', 'string', 'max:255'], 'seo_description' => ['nullable', 'string'], 'meta_title' => ['nullable', 'string', 'max:255'], 'meta_description' => ['nullable', 'string'], 'meta_image' => ['nullable', 'string', 'max:500'], 'author' => ['nullable', 'string', 'max:150'], 'publisher' => ['nullable', 'string', 'max:150'], 'copyright' => ['nullable', 'string', 'max:255'], 'site_name' => ['nullable', 'string', 'max:150'], 'keywords' => ['nullable', 'string'],
            'image_media_id' => ['nullable', 'integer', Rule::exists('media_assets', 'id')->where('is_active', true)], 'image_alt_override' => ['nullable', 'string', 'max:255'], 'icon_image_media_id' => ['nullable', 'integer', Rule::exists('media_assets', 'id')->where('is_active', true)], 'icon_image_alt_override' => ['nullable', 'string', 'max:255'], 'gallery_media_ids' => ['nullable', 'string'],
            'meta_image_media_id' => ['nullable', 'integer', Rule::exists('media_assets', 'id')->where('is_active', true)], 'meta_image_alt_override' => ['nullable', 'string', 'max:255'],
        ];
    }

    protected function prepareForValidation(): void
    {
        if ($source = ($this->input('slug') ?: $this->input('title'))) {
            $this->merge(['slug' => Str::slug($source)]);
        }
    }

    public function withValidator(Validator $validator): void
    {
        $validator->after(function (Validator $validator) {
            $min = $this->input('estimated_minutes_min');
            $max = $this->input('estimated_minutes_max');
            if ($min !== null && $max !== null && (int) $max < (int) $min) {
                $validator->errors()->add('estimated_minutes_max', 'Maximum estimated minutes must be greater than or equal to minimum estimated minutes.');
            }
        });
    }
}
