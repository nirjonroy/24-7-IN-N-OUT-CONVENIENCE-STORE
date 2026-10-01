<?php

namespace App\Models;

use App\Models\Concerns\HasSeoMeta;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Model;

class BusinessHour extends Model
{
    use HasFactory;
    use HasSeoMeta;

    protected $fillable = [
        'location_id', 'day_of_week', 'opens_at', 'closes_at', 'is_closed', 'is_24_hours',
        'page_name', 'seo_title', 'seo_description', 'meta_title', 'meta_description',
        'meta_image', 'author', 'publisher', 'copyright', 'site_name', 'keywords',
    ];

    protected $casts = [
        'day_of_week' => 'integer',
        'is_closed' => 'boolean',
        'is_24_hours' => 'boolean',
    ];

    public function location(): BelongsTo
    {
        return $this->belongsTo(Location::class);
    }
}
