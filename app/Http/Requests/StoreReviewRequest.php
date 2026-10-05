<?php

namespace App\Http\Requests;

use App\Models\MediaAsset;
use App\Models\Review;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;
use Illuminate\Validation\Validator;

class StoreReviewRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'source' => ['required', 'string', 'max:50', Rule::in(Review::SOURCES)],
            'external_id' => ['nullable', 'string', 'max:255'],
            'author_name' => ['required', 'string', 'max:150'],
            'author_url' => ['nullable', 'url', 'max:500'],
            'rating' => ['nullable', 'numeric', 'min:1', 'max:5'],
            'review_text' => ['nullable', 'string'],
            'review_url' => ['nullable', 'url', 'max:500'],
            'reviewed_at' => ['nullable', 'date'],
            'is_featured' => ['nullable', 'boolean'],
            'is_active' => ['nullable', 'boolean'],
            'sort_order' => ['nullable', 'integer', 'min:0'],
            'avatar_media_id' => ['nullable', 'integer', Rule::exists('media_assets', 'id')->where('is_active', true)],
            'avatar_alt_override' => ['nullable', 'string', 'max:255'],
            'meta_image_media_id' => ['nullable', 'integer', Rule::exists('media_assets', 'id')->where('is_active', true)],
            'meta_image_alt_override' => ['nullable', 'string', 'max:255'],
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
            foreach (['avatar_media_id', 'meta_image_media_id'] as $field) {
                $mediaId = $this->input($field);

                if (! $mediaId) {
                    continue;
                }

                if (! MediaAsset::whereKey($mediaId)->where('is_active', true)->where('mime_type', 'like', 'image/%')->exists()) {
                    $validator->errors()->add($field, 'The selected media must be an active image.');
                }
            }
        });
    }
}
