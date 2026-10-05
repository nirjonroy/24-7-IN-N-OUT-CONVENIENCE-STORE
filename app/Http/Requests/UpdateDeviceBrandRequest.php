<?php

namespace App\Http\Requests;

use Illuminate\Validation\Rule;

class UpdateDeviceBrandRequest extends StoreDeviceBrandRequest
{
    public function rules(): array
    {
        $rules = parent::rules();
        $rules['slug'] = ['nullable', 'string', 'max:180', Rule::unique('device_brands', 'slug')->ignore($this->route('brand'))];
        return $rules;
    }
}
