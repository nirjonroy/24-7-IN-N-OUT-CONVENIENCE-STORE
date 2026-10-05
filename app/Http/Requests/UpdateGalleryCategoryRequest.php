<?php

namespace App\Http\Requests;

use Illuminate\Validation\Rule;

class UpdateGalleryCategoryRequest extends StoreGalleryCategoryRequest
{
    public function rules(): array
    {
        $rules = parent::rules();
        $rules['slug'] = ['nullable','string','max:180',Rule::unique('gallery_categories','slug')->ignore($this->route('category'))];
        return $rules;
    }
}
