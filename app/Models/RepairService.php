<?php

namespace App\Models;

use App\Models\Concerns\HasMediaAttachments;
use App\Models\Concerns\HasSeoMeta;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class RepairService extends Model
{
    use HasFactory;
    use HasMediaAttachments;
    use HasSeoMeta;
    use SoftDeletes;

    public const REPAIR_TYPES = ['screen_repair', 'battery_replacement', 'charging_port', 'camera_repair', 'speaker_repair', 'microphone_repair', 'water_damage', 'software_issue', 'back_glass', 'diagnostic', 'other'];

    protected $fillable = ['title', 'slug', 'repair_type', 'short_description', 'description', 'starting_price', 'compare_at_price', 'price_note', 'is_price_visible', 'estimated_minutes_min', 'estimated_minutes_max', 'warranty_text', 'diagnostic_required', 'is_featured', 'is_active', 'sort_order', 'page_name', 'seo_title', 'seo_description', 'meta_title', 'meta_description', 'meta_image', 'author', 'publisher', 'copyright', 'site_name', 'keywords'];

    protected $casts = ['starting_price' => 'decimal:2', 'compare_at_price' => 'decimal:2', 'is_price_visible' => 'boolean', 'estimated_minutes_min' => 'integer', 'estimated_minutes_max' => 'integer', 'diagnostic_required' => 'boolean', 'is_featured' => 'boolean', 'is_active' => 'boolean', 'sort_order' => 'integer'];

    public function prices(): HasMany { return $this->hasMany(RepairServicePrice::class); }
    public function deviceModels(): BelongsToMany
    {
        return $this->belongsToMany(DeviceModel::class, 'repair_service_prices')->withPivot(['price', 'compare_at_price', 'price_label', 'is_price_visible', 'estimated_minutes', 'warranty_text', 'notes', 'is_available', 'sort_order'])->withTimestamps();
    }

    public function scopeActive(Builder $query): Builder { return $query->where('is_active', true); }
    public function scopeFeatured(Builder $query): Builder { return $query->where('is_featured', true); }
    public function scopeRepairType(Builder $query, string $type): Builder { return $query->where('repair_type', $type); }

    public function priceForDevice(DeviceModel $deviceModel): array
    {
        $price = $this->prices()->where('device_model_id', $deviceModel->id)->first();

        return [
            'price' => $price?->price ?? $this->starting_price,
            'compare_at_price' => $price?->compare_at_price ?? $this->compare_at_price,
            'price_label' => $price?->price_label ?? $this->price_note,
            'is_price_visible' => $price?->is_price_visible ?? $this->is_price_visible,
            'estimated_minutes' => $price?->estimated_minutes,
            'estimated_minutes_min' => $price ? null : $this->estimated_minutes_min,
            'estimated_minutes_max' => $price ? null : $this->estimated_minutes_max,
            'warranty_text' => $price?->warranty_text ?? $this->warranty_text,
            'is_available' => $price?->is_available ?? true,
        ];
    }
}
