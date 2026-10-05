<?php

namespace App\Http\Requests;

use App\Models\CatalogCategory;
use App\Models\CatalogItem;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;
use Illuminate\Validation\Validator;

class StoreCatalogItemRequest extends FormRequest
{
    private const TYPE_BY_AREA = [
        'convenience' => 'product',
        'smoothie' => 'smoothie',
        'phone_accessory' => 'phone_accessory',
        'adult_retail' => 'adult_product',
    ];

    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'catalog_category_id' => ['required', 'exists:catalog_categories,id'],
            'name' => ['required', 'string', 'max:180'],
            'slug' => ['nullable', 'string', 'max:200', Rule::unique('catalog_items', 'slug')],
            'item_type' => ['required', Rule::in(CatalogItem::ITEM_TYPES)],
            'brand' => ['nullable', 'string', 'max:150'],
            'sku' => ['nullable', 'string', 'max:100'],
            'short_description' => ['nullable', 'string'],
            'description' => ['nullable', 'string'],
            'price' => ['nullable', 'numeric', 'min:0'],
            'compare_at_price' => ['nullable', 'numeric', 'min:0'],
            'price_label' => ['nullable', 'string', 'max:100'],
            'is_price_visible' => ['nullable', 'boolean'],
            'ingredients' => ['nullable', 'string'],
            'size_label' => ['nullable', 'string', 'max:100'],
            'minimum_age' => ['nullable', 'integer', 'min:0', 'max:99'],
            'is_age_restricted' => ['nullable', 'boolean'],
            'is_featured' => ['nullable', 'boolean'],
            'is_available' => ['nullable', 'boolean'],
            'is_active' => ['nullable', 'boolean'],
            'sort_order' => ['nullable', 'integer', 'min:0'],
            'details' => ['nullable', 'json'],
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
            'primary_image_media_id' => ['nullable', 'integer', Rule::exists('media_assets', 'id')->where('is_active', true)],
            'primary_image_alt_override' => ['nullable', 'string', 'max:255'],
            'gallery_media_ids' => ['nullable', 'string'],
            'meta_image_media_id' => ['nullable', 'integer', Rule::exists('media_assets', 'id')->where('is_active', true)],
            'meta_image_alt_override' => ['nullable', 'string', 'max:255'],
        ];
    }

    protected function prepareForValidation(): void
    {
        $slugSource = $this->input('slug') ?: $this->input('name');

        if ($slugSource) {
            $this->merge(['slug' => Str::slug($slugSource)]);
        }
    }

    public function withValidator(Validator $validator): void
    {
        $validator->after(function (Validator $validator) {
            $category = CatalogCategory::find($this->input('catalog_category_id'));

            if (! $category) {
                return;
            }

            $expectedType = self::TYPE_BY_AREA[$category->business_area] ?? null;

            if ($expectedType && $this->input('item_type') !== $expectedType) {
                $validator->errors()->add('item_type', 'The item type must match the selected category business area.');
            }

            if ($category->business_area === 'adult_retail') {
                $minimumAge = $this->input('minimum_age');

                if ($minimumAge !== null && (int) $minimumAge < 18) {
                    $validator->errors()->add('minimum_age', 'Adult retail items must use an appropriate minimum age.');
                }
            }

            $this->validateGalleryMedia($validator);
        });
    }

    private function validateGalleryMedia(Validator $validator): void
    {
        foreach ($this->galleryIds() as $mediaId) {
            $exists = \App\Models\MediaAsset::active()->whereKey($mediaId)->exists();

            if (! $exists) {
                $validator->errors()->add('gallery_media_ids', 'One or more selected gallery images are not available.');
                return;
            }
        }
    }

    private function galleryIds(): array
    {
        return array_values(array_unique(array_filter(array_map(
            fn ($value) => (int) trim($value),
            explode(',', (string) $this->input('gallery_media_ids'))
        ))));
    }
}
