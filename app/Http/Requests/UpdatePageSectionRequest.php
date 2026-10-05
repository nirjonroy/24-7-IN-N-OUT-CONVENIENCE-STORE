<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;

class UpdatePageSectionRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        $page = $this->route('page');
        $section = $this->route('section');

        return [
            'section_key' => ['required', 'string', 'max:120', Rule::unique('page_sections', 'section_key')->where('page_id', $page->id)->ignore($section)],
            'section_type' => ['required', 'string', 'max:100'],
            'section_label' => ['nullable', 'string', 'max:150'],
            'title' => ['nullable', 'string', 'max:255'],
            'subtitle' => ['nullable', 'string', 'max:255'],
            'content' => ['nullable', 'string'],
            'image' => ['nullable', 'string', 'max:500'],
            'image_alt' => ['nullable', 'string', 'max:255'],
            'background_image' => ['nullable', 'string', 'max:500'],
            'primary_button_label' => ['nullable', 'string', 'max:100'],
            'primary_button_url' => ['nullable', 'string', 'max:500'],
            'secondary_button_label' => ['nullable', 'string', 'max:100'],
            'secondary_button_url' => ['nullable', 'string', 'max:500'],
            'settings' => ['nullable', 'json'],
            'sort_order' => ['nullable', 'integer', 'min:0'],
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

    protected function prepareForValidation(): void
    {
        $keySource = $this->input('section_key') ?: ($this->input('title') ?: $this->input('section_type'));

        if ($keySource) {
            $this->merge(['section_key' => Str::slug($keySource, '_')]);
        }
    }
}
