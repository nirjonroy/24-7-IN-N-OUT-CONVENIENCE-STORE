<?php

namespace App\Http\Requests;

use Illuminate\Validation\Rule;

class UpdateDeviceModelRequest extends StoreDeviceModelRequest
{
    public function rules(): array
    {
        $rules = parent::rules();
        $rules['slug'] = ['nullable', 'string', 'max:200', Rule::unique('device_models', 'slug')->where('device_brand_id', $this->input('device_brand_id'))->ignore($this->route('model'))];
        return $rules;
    }
}
