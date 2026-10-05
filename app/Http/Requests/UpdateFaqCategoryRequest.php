<?php

namespace App\Http\Requests;

use Illuminate\Validation\Rule;

class UpdateFaqCategoryRequest extends StoreFaqCategoryRequest
{
    public function rules(): array
    {
        $rules = parent::rules();
        $rules['slug'] = ['nullable','string','max:180',Rule::unique('faq_categories','slug')->ignore($this->route('category'))];
        return $rules;
    }
}
