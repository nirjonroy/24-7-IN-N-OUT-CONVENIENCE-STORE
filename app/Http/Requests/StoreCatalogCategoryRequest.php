<?php

namespace App\Http\Requests;

use App\Models\CatalogCategory;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;
use Illuminate\Validation\Validator;

class StoreCatalogCategoryRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'name' => ['required', 'string', 'max:150'],
            'slug' => ['nullable', 'string', 'max:180', Rule::unique('catalog_categories', 'slug')],
            'parent_id' => ['nullable', 'exists:catalog_categories,id'],
            'business_area' => ['required', Rule::in(CatalogCategory::BUSINESS_AREAS)],
            'description' => ['nullable', 'string'],
            'minimum_age' => ['nullable', 'integer', 'min:0', 'max:99'],
            'is_featured' => ['nullable', 'boolean'],
            'is_active' => ['nullable', 'boolean'],
            'sort_order' => ['nullable', 'integer', 'min:0'],
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
            'image_media_id' => ['nullable', 'integer', Rule::exists('media_assets', 'id')->where('is_active', true)],
            'image_alt_override' => ['nullable', 'string', 'max:255'],
            'meta_image_media_id' => ['nullable', 'integer', Rule::exists('media_assets', 'id')->where('is_active', true)],
            'meta_image_alt_override' => ['nullable', 'string', 'max:255'],
        ];
    }

    protected function prepareForValidation(): void
    {
        $slugSource = $this->input('slug') ?: $this->input('name');

        if ($slugSource) {
            $this->merge(['slug' => Str::slug($slugSource)]);
        }
    }

    public function withValidator(Validator $validator): void
    {
        $validator->after(function (Validator $validator) {
            $parentId = $this->input('parent_id');
            $category = $this->route('category');

            if ($category && $parentId && (int) $parentId === (int) $category->id) {
                $validator->errors()->add('parent_id', 'A category cannot be its own parent.');
            }

            if ($category && $parentId && $this->wouldCreateCircularParent($category, (int) $parentId)) {
                $validator->errors()->add('parent_id', 'This parent selection would create a circular category relationship.');
            }
        });
    }

    private function wouldCreateCircularParent(CatalogCategory $category, int $parentId): bool
    {
        while ($parentId) {
            if ($parentId === (int) $category->id) {
                return true;
            }

            $parentId = (int) (CatalogCategory::whereKey($parentId)->value('parent_id') ?? 0);
        }

        return false;
    }
}
