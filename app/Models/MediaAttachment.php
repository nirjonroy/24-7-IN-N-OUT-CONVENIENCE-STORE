<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\MorphTo;

class MediaAttachment extends Model
{
    use HasFactory;

    protected $fillable = [
        'media_asset_id', 'mediable_type', 'mediable_id', 'collection', 'sort_order',
        'is_primary', 'alt_text_override', 'title_override', 'caption_override',
    ];

    protected $casts = [
        'sort_order' => 'integer',
        'is_primary' => 'boolean',
    ];

    public function media(): BelongsTo
    {
        return $this->belongsTo(MediaAsset::class, 'media_asset_id');
    }

    public function mediable(): MorphTo
    {
        return $this->morphTo();
    }
}
