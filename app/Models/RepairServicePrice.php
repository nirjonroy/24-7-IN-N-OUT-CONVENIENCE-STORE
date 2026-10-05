<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class RepairServicePrice extends Model
{
    use HasFactory;

    protected $fillable = ['repair_service_id', 'device_model_id', 'price', 'compare_at_price', 'price_label', 'is_price_visible', 'estimated_minutes', 'warranty_text', 'notes', 'is_available', 'sort_order'];

    protected $casts = ['price' => 'decimal:2', 'compare_at_price' => 'decimal:2', 'is_price_visible' => 'boolean', 'estimated_minutes' => 'integer', 'is_available' => 'boolean', 'sort_order' => 'integer'];

    public function repairService(): BelongsTo { return $this->belongsTo(RepairService::class); }
    public function deviceModel(): BelongsTo { return $this->belongsTo(DeviceModel::class); }
}
