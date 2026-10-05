<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class BulkRepairServicePriceRequest extends FormRequest
{
    public function authorize(): bool { return true; }

    public function rules(): array
    {
        return [
            'prices' => ['nullable', 'array'],
            'prices.*.price' => ['nullable', 'numeric', 'min:0'],
            'prices.*.compare_at_price' => ['nullable', 'numeric', 'min:0'],
            'prices.*.price_label' => ['nullable', 'string', 'max:100'],
            'prices.*.is_price_visible' => ['nullable', 'boolean'],
            'prices.*.estimated_minutes' => ['nullable', 'integer', 'min:0'],
            'prices.*.warranty_text' => ['nullable', 'string', 'max:255'],
            'prices.*.notes' => ['nullable', 'string'],
            'prices.*.is_available' => ['nullable', 'boolean'],
            'prices.*.sort_order' => ['nullable', 'integer', 'min:0'],
        ];
    }
}
