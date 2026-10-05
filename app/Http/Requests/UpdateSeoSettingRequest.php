<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdateSeoSettingRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'site_name' => ['nullable', 'string', 'max:150'],
            'default_title' => ['nullable', 'string', 'max:255'],
            'title_separator' => ['nullable', 'string', 'max:20'],
            'default_description' => ['nullable', 'string'],
            'default_keywords' => ['nullable', 'string'],
            'default_author' => ['nullable', 'string', 'max:150'],
            'default_publisher' => ['nullable', 'string', 'max:150'],
            'default_copyright' => ['nullable', 'string', 'max:255'],
            'default_meta_image' => ['nullable', 'string', 'max:500'],
            'canonical_base_url' => ['nullable', 'url', 'max:500'],
            'default_robots_index' => ['nullable', 'boolean'],
            'default_robots_follow' => ['nullable', 'boolean'],
            'twitter_card' => ['nullable', 'string', 'max:50'],
            'twitter_site' => ['nullable', 'string', 'max:100'],
            'facebook_app_id' => ['nullable', 'string', 'max:100'],
            'google_site_verification' => ['nullable', 'string', 'max:255'],
            'bing_site_verification' => ['nullable', 'string', 'max:255'],
            'robots_txt_extra' => ['nullable', 'string'],
            'sitemap_enabled' => ['nullable', 'boolean'],
            'robots_enabled' => ['nullable', 'boolean'],
            'structured_data_enabled' => ['nullable', 'boolean'],
            'default_meta_image_media_id' => ['nullable', 'integer', Rule::exists('media_assets', 'id')->where('is_active', true)],
            'default_meta_image_alt_override' => ['nullable', 'string', 'max:255'],
        ];
    }

    protected function prepareForValidation(): void
    {
        if ($this->filled('canonical_base_url')) {
            $this->merge(['canonical_base_url' => rtrim($this->input('canonical_base_url'), '/')]);
        }
    }
}
