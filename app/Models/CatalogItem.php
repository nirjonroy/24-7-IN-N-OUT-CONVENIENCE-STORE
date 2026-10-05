<?php

namespace App\Models;

use App\Models\Concerns\HasMediaAttachments;
use App\Models\Concerns\HasSeoMeta;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class CatalogItem extends Model
{
    use HasFactory;
    use HasMediaAttachments;
    use HasSeoMeta;
    use SoftDeletes;

    public const ITEM_TYPES = ['product', 'smoothie', 'phone_accessory', 'adult_product'];

    protected $fillable = [
        'catalog_category_id', 'name', 'slug', 'item_type', 'brand', 'sku',
        'short_description', 'description', 'price', 'compare_at_price', 'price_label',
        'is_price_visible', 'ingredients', 'size_label', 'minimum_age',
        'is_age_restricted', 'is_featured', 'is_available', 'is_active', 'sort_order',
        'details', 'page_name', 'seo_title', 'seo_description', 'meta_title',
        'meta_description', 'meta_image', 'author', 'publisher', 'copyright',
        'site_name', 'keywords',
    ];

    protected $casts = [
        'price' => 'decimal:2',
        'compare_at_price' => 'decimal:2',
        'is_price_visible' => 'boolean',
        'minimum_age' => 'integer',
        'is_age_restricted' => 'boolean',
        'is_featured' => 'boolean',
        'is_available' => 'boolean',
        'is_active' => 'boolean',
        'sort_order' => 'integer',
        'details' => 'array',
    ];

    public function category(): BelongsTo
    {
        return $this->belongsTo(CatalogCategory::class, 'catalog_category_id');
    }

    public function variants(): HasMany
    {
        return $this->hasMany(CatalogItemVariant::class)->orderBy('sort_order')->orderBy('id');
    }

    public function scopeActive(Builder $query): Builder
    {
        return $query->where('is_active', true);
    }

    public function scopeAvailable(Builder $query): Builder
    {
        return $query->where('is_available', true);
    }

    public function scopeFeatured(Builder $query): Builder
    {
        return $query->where('is_featured', true);
    }

    public function scopeBusinessArea(Builder $query, string $area): Builder
    {
        return $query->whereHas('category', fn (Builder $query) => $query->where('business_area', $area));
    }
}
