<?php

namespace App\Models;

use App\Models\Concerns\HasSeoMeta;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Model;

class SpecialBusinessHour extends Model
{
    use HasFactory;
    use HasSeoMeta;

    protected $fillable = [
        'location_id', 'date', 'opens_at', 'closes_at', 'is_closed', 'note',
        'page_name', 'seo_title', 'seo_description', 'meta_title', 'meta_description',
        'meta_image', 'author', 'publisher', 'copyright', 'site_name', 'keywords',
    ];

    protected $casts = [
        'date' => 'date',
        'is_closed' => 'boolean',
    ];

    public function location(): BelongsTo
    {
        return $this->belongsTo(Location::class);
    }
}
