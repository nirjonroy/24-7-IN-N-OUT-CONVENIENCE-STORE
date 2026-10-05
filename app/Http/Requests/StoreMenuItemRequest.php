<?php

namespace App\Http\Requests;

use App\Models\MenuItem;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;
use Illuminate\Validation\Validator;

class StoreMenuItemRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'label' => ['required', 'string', 'max:150'],
            'link_type' => ['required', Rule::in(MenuItem::LINK_TYPES)],
            'page_id' => ['required_if:link_type,page', 'nullable', 'exists:pages,id'],
            'url' => ['required_if:link_type,custom', 'nullable', 'string', 'max:500'],
            'parent_id' => ['nullable', 'exists:menu_items,id'],
            'icon' => ['nullable', 'string', 'max:100', 'regex:/^[A-Za-z0-9_-]+$/'],
            'badge' => ['nullable', 'string', 'max:100'],
            'target' => ['required', Rule::in(MenuItem::TARGETS)],
            'rel' => ['nullable', 'string', 'max:100'],
            'css_identifier' => ['nullable', 'string', 'max:100', 'regex:/^[A-Za-z0-9_-]+$/'],
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
        ];
    }

    public function withValidator(Validator $validator): void
    {
        $validator->after(function (Validator $validator) {
            $this->validateCustomUrl($validator);
            $this->validateParent($validator);
        });
    }

    private function validateCustomUrl(Validator $validator): void
    {
        if ($this->input('link_type') !== MenuItem::LINK_CUSTOM || ! $this->filled('url')) {
            return;
        }

        $url = strtolower(trim((string) $this->input('url')));
        $allowed = preg_match('/^(https?:\/\/|\/|#|mailto:|tel:)/', $url) === 1;
        $dangerous = preg_match('/^(javascript:|data:|vbscript:)/', $url) === 1;

        if (! $allowed || $dangerous) {
            $validator->errors()->add('url', 'The custom URL must be a safe http, https, local path, anchor, mailto, or tel link.');
        }
    }

    private function validateParent(Validator $validator): void
    {
        if (! $this->filled('parent_id')) {
            return;
        }

        $menu = $this->route('menu');
        $item = $this->route('item');
        $parent = MenuItem::find($this->input('parent_id'));

        if (! $parent || ! $menu || (int) $parent->menu_id !== (int) $menu->id) {
            $validator->errors()->add('parent_id', 'Invalid parent menu item.');
            return;
        }

        if ($item && (int) $parent->id === (int) $item->id) {
            $validator->errors()->add('parent_id', 'A menu item cannot be its own parent.');
            return;
        }

        if ($parent->parent_id !== null) {
            $validator->errors()->add('parent_id', 'Only two menu levels are supported.');
            return;
        }

        if ($item && $item->children()->whereKey($parent->id)->exists()) {
            $validator->errors()->add('parent_id', 'Circular menu relationships are not allowed.');
        }
    }
}
