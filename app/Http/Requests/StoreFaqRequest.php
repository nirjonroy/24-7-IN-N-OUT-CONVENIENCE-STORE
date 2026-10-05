<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StoreFaqRequest extends FormRequest
{
    public function authorize(): bool { return true; }
    public function rules(): array
    {
        return [
            'faq_category_id' => ['nullable','exists:faq_categories,id'], 'question' => ['required','string','max:500'], 'answer' => ['required','string'],
            'is_featured' => ['nullable','boolean'], 'is_active' => ['nullable','boolean'], 'sort_order' => ['nullable','integer','min:0'],
            'pages' => ['nullable','array'], 'pages.*' => ['integer','exists:pages,id'],
            'page_name' => ['nullable','string','max:150'], 'seo_title' => ['nullable','string','max:255'], 'seo_description' => ['nullable','string'], 'meta_title' => ['nullable','string','max:255'], 'meta_description' => ['nullable','string'], 'meta_image' => ['nullable','string','max:500'], 'author' => ['nullable','string','max:150'], 'publisher' => ['nullable','string','max:150'], 'copyright' => ['nullable','string','max:255'], 'site_name' => ['nullable','string','max:150'], 'keywords' => ['nullable','string'],
        ];
    }
}
