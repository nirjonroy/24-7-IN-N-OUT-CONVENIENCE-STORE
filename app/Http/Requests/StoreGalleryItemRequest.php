<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;
use Illuminate\Validation\Validator;

class StoreGalleryItemRequest extends FormRequest
{
    public function authorize(): bool { return true; }
    public function rules(): array
    {
        return [
            'gallery_category_id' => ['nullable','exists:gallery_categories,id'], 'title' => ['nullable','string','max:255'], 'caption' => ['nullable','string'], 'description' => ['nullable','string'],
            'photographer' => ['nullable','string','max:150'], 'source_url' => ['nullable','url','max:500'], 'taken_at' => ['nullable','date'], 'is_featured' => ['nullable','boolean'], 'is_active' => ['nullable','boolean'], 'sort_order' => ['nullable','integer','min:0'],
            'image_media_id' => ['required','integer',Rule::exists('media_assets','id')->where('is_active', true)], 'image_alt_override' => ['nullable','string','max:255'],
            'meta_image_media_id' => ['nullable','integer',Rule::exists('media_assets','id')->where('is_active', true)], 'meta_image_alt_override' => ['nullable','string','max:255'],
            'page_name' => ['nullable','string','max:150'], 'seo_title' => ['nullable','string','max:255'], 'seo_description' => ['nullable','string'], 'meta_title' => ['nullable','string','max:255'], 'meta_description' => ['nullable','string'], 'meta_image' => ['nullable','string','max:500'], 'author' => ['nullable','string','max:150'], 'publisher' => ['nullable','string','max:150'], 'copyright' => ['nullable','string','max:255'], 'site_name' => ['nullable','string','max:150'], 'keywords' => ['nullable','string'],
        ];
    }

    public function withValidator(Validator $validator): void
    {
        $validator->after(function (Validator $validator) {
            foreach (['image_media_id', 'meta_image_media_id'] as $field) {
                $mediaId = $this->input($field);

                if (! $mediaId) {
                    continue;
                }

                $isImage = \App\Models\MediaAsset::query()
                    ->whereKey($mediaId)
                    ->where('is_active', true)
                    ->where('mime_type', 'like', 'image/%')
                    ->exists();

                if (! $isImage) {
                    $validator->errors()->add($field, 'The selected media must be an active image.');
                }
            }
        });
    }
}
