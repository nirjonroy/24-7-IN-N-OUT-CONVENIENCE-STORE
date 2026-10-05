<?php

namespace App\Http\Requests;

use Illuminate\Validation\Rule;

class UpdateMenuRequest extends StoreMenuRequest
{
    public function rules(): array
    {
        $rules = parent::rules();
        $rules['key'] = ['nullable', 'string', 'max:120', Rule::unique('menus', 'key')->ignore($this->route('menu'))];

        return $rules;
    }
}
