<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StoreBusinessRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     *
     * @return bool
     */
    public function authorize()
    {
        return true;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, mixed>
     */
    public function rules()
    {
        return [
            'name' => ['required', 'string', 'max:150'],
            'short_name' => ['nullable', 'string', 'max:100'],
            'legal_name' => ['nullable', 'string', 'max:180'],
            'tagline' => ['nullable', 'string', 'max:255'],
            'description' => ['nullable', 'string'],
            'primary_category' => ['nullable', 'string', 'max:120'],
            'schema_types' => ['nullable', 'string'],
            'currency' => ['required', 'string', 'size:3'],
            'minimum_age' => ['nullable', 'integer', 'min:0', 'max:99'],
            'adult_retail_notice' => ['nullable', 'string'],
            'is_active' => ['nullable', 'boolean'],
            'page_name' => ['nullable', 'string', 'max:150'],
            'seo_title' => ['nullable', 'string', 'max:255'],
            'seo_description' => ['nullable', 'string'],
            'meta_title' => ['nullable', 'string', 'max:255'],
            'meta_description' => ['nullable', 'string'],
            'meta_image' => ['nullable', 'string', 'max:500'],
            'author' => ['nullable', 'string', 'max:150'],
            'publisher' => ['nullable', 'string', 'max:150'],
            'copyright' => ['nullable', 'string', 'max:255'],
            'site_name' => ['nullable', 'string', 'max:150'],
            'keywords' => ['nullable', 'string'],
        ];
    }
}
