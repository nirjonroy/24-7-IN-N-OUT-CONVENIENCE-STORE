<?php

namespace App\Http\Requests;

use Illuminate\Validation\Rule;

class UpdateRepairServiceRequest extends StoreRepairServiceRequest
{
    public function rules(): array
    {
        $rules = parent::rules();
        $rules['slug'] = ['nullable', 'string', 'max:200', Rule::unique('repair_services', 'slug')->ignore($this->route('service'))];
        return $rules;
    }
}
