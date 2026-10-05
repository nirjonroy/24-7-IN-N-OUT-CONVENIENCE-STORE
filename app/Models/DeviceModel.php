<?php

namespace App\Models;

use App\Models\Concerns\HasMediaAttachments;
use App\Models\Concerns\HasSeoMeta;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class DeviceModel extends Model
{
    use HasFactory;
    use HasMediaAttachments;
    use HasSeoMeta;
    use SoftDeletes;

    public const DEVICE_TYPES = ['phone', 'tablet', 'smartwatch', 'other'];

    protected $fillable = ['device_brand_id', 'name', 'slug', 'model_number', 'device_type', 'release_year', 'description', 'is_featured', 'is_active', 'sort_order', 'page_name', 'seo_title', 'seo_description', 'meta_title', 'meta_description', 'meta_image', 'author', 'publisher', 'copyright', 'site_name', 'keywords'];

    protected $casts = ['release_year' => 'integer', 'is_featured' => 'boolean', 'is_active' => 'boolean', 'sort_order' => 'integer'];

    public function brand(): BelongsTo { return $this->belongsTo(DeviceBrand::class, 'device_brand_id'); }
    public function repairPrices(): HasMany { return $this->hasMany(RepairServicePrice::class); }
    public function repairServices(): BelongsToMany
    {
        return $this->belongsToMany(RepairService::class, 'repair_service_prices')->withPivot(['price', 'compare_at_price', 'price_label', 'is_price_visible', 'estimated_minutes', 'warranty_text', 'notes', 'is_available', 'sort_order'])->withTimestamps();
    }

    public function scopeActive(Builder $query): Builder { return $query->where('is_active', true); }
    public function scopeFeatured(Builder $query): Builder { return $query->where('is_featured', true); }
    public function scopeDeviceType(Builder $query, string $type): Builder { return $query->where('device_type', $type); }
}
