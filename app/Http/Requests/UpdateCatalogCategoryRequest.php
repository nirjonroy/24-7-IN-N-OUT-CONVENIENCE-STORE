<?php

namespace App\Http\Requests;

use Illuminate\Validation\Rule;

class UpdateCatalogCategoryRequest extends StoreCatalogCategoryRequest
{
    public function rules(): array
    {
        $rules = parent::rules();
        $rules['slug'] = ['nullable', 'string', 'max:180', Rule::unique('catalog_categories', 'slug')->ignore($this->route('category'))];

        return $rules;
    }
}
