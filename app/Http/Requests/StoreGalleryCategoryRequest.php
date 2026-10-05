<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;

class StoreGalleryCategoryRequest extends FormRequest
{
    public function authorize(): bool { return true; }
    public function rules(): array
    {
        return [
            'name' => ['required','string','max:150'], 'slug' => ['nullable','string','max:180',Rule::unique('gallery_categories','slug')],
            'description' => ['nullable','string'], 'is_featured' => ['nullable','boolean'], 'is_active' => ['nullable','boolean'], 'sort_order' => ['nullable','integer','min:0'],
            'page_name' => ['nullable','string','max:150'], 'seo_title' => ['nullable','string','max:255'], 'seo_description' => ['nullable','string'], 'meta_title' => ['nullable','string','max:255'], 'meta_description' => ['nullable','string'], 'meta_image' => ['nullable','string','max:500'], 'author' => ['nullable','string','max:150'], 'publisher' => ['nullable','string','max:150'], 'copyright' => ['nullable','string','max:255'], 'site_name' => ['nullable','string','max:150'], 'keywords' => ['nullable','string'],
        ];
    }
    protected function prepareForValidation(): void { if ($source = ($this->input('slug') ?: $this->input('name'))) $this->merge(['slug' => Str::slug($source)]); }
}
