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

class CatalogCategory extends Model
{
    use HasFactory;
    use HasMediaAttachments;
    use HasSeoMeta;
    use SoftDeletes;

    public const BUSINESS_AREAS = ['convenience', 'smoothie', 'phone_accessory', 'adult_retail'];

    protected $fillable = [
        'parent_id', 'name', 'slug', 'business_area', 'description', 'minimum_age',
        'is_featured', 'is_active', 'sort_order', 'page_name', 'seo_title',
        'seo_description', 'meta_title', 'meta_description', 'meta_image', 'author',
        'publisher', 'copyright', 'site_name', 'keywords',
    ];

    protected $casts = [
        'minimum_age' => 'integer',
        'is_featured' => 'boolean',
        'is_active' => 'boolean',
        'sort_order' => 'integer',
    ];

    public function parent(): BelongsTo
    {
        return $this->belongsTo(self::class, 'parent_id');
    }

    public function children(): HasMany
    {
        return $this->hasMany(self::class, 'parent_id')->orderBy('sort_order')->orderBy('name');
    }

    public function items(): HasMany
    {
        return $this->hasMany(CatalogItem::class);
    }

    public function scopeActive(Builder $query): Builder
    {
        return $query->where('is_active', true);
    }

    public function scopeFeatured(Builder $query): Builder
    {
        return $query->where('is_featured', true);
    }

    public function scopeBusinessArea(Builder $query, string $area): Builder
    {
        return $query->where('business_area', $area);
    }
}
