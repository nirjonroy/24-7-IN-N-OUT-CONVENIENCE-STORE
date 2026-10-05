<?php

namespace App\Http\Requests;

use App\Models\Redirect;
use App\Services\RedirectService;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;
use Illuminate\Validation\Validator;

class StoreRedirectRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'source_path' => ['required', 'string', 'max:500'],
            'target_url' => ['required', 'string', 'max:1000'],
            'match_type' => ['required', Rule::in(Redirect::MATCH_TYPES)],
            'status_code' => ['required', 'integer', Rule::in(Redirect::STATUS_CODES)],
            'preserve_query_string' => ['nullable', 'boolean'],
            'note' => ['nullable', 'string', 'max:255'],
            'is_active' => ['nullable', 'boolean'],
        ];
    }

    protected function prepareForValidation(): void
    {
        $service = app(RedirectService::class);
        $matchType = $this->input('match_type') ?: Redirect::MATCH_EXACT;
        $source = $matchType === Redirect::MATCH_PREFIX
            ? $service->normalizePrefixPath($this->input('source_path'))
            : $service->normalizeSourcePath($this->input('source_path'));

        $this->merge([
            'source_path' => $source,
            'match_type' => $matchType,
            'status_code' => $this->input('status_code') ?: 301,
        ]);
    }

    public function withValidator(Validator $validator): void
    {
        $validator->after(function (Validator $validator) {
            $service = app(RedirectService::class);
            $ignoreId = $this->route('redirect')?->id;

            if ($this->input('source_path') === '/') {
                $validator->errors()->add('source_path', 'Redirecting the homepage is not allowed in this step.');
            }

            if (! $service->isSafeTarget((string) $this->input('target_url'))) {
                $validator->errors()->add('target_url', 'The target URL must be an internal path or an http/https URL.');
                return;
            }

            if ($service->equivalentPath((string) $this->input('source_path'), (string) $this->input('target_url'))) {
                $validator->errors()->add('target_url', 'Redirect source and target cannot be the same.');
            }

            if ($service->wouldCreateSimpleLoop((string) $this->input('source_path'), (string) $this->input('target_url'), $ignoreId)) {
                $validator->errors()->add('target_url', 'This redirect would create a simple redirect loop.');
            }

            if ($this->boolean('is_active')) {
                $exists = Redirect::query()
                    ->where('source_path', $this->input('source_path'))
                    ->where('match_type', $this->input('match_type'))
                    ->where('is_active', true)
                    ->when($ignoreId, fn ($query) => $query->whereKeyNot($ignoreId))
                    ->exists();

                if ($exists) {
                    $validator->errors()->add('source_path', 'An active redirect already exists for this source and match type.');
                }
            }
        });
    }
}
