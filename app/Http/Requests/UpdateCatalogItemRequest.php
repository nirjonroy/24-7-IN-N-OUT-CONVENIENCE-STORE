<?php

namespace App\Http\Requests;

use Illuminate\Validation\Rule;

class UpdateCatalogItemRequest extends StoreCatalogItemRequest
{
    public function rules(): array
    {
        $rules = parent::rules();
        $rules['slug'] = ['nullable', 'string', 'max:200', Rule::unique('catalog_items', 'slug')->ignore($this->route('item'))];

        return $rules;
    }
}
