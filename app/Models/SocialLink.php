<?php

namespace App\Models;

use App\Models\Concerns\HasSeoMeta;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Model;

class SocialLink extends Model
{
    use HasFactory;
    use HasSeoMeta;

    protected $fillable = [
        'business_id', 'platform', 'label', 'url', 'icon', 'sort_order', 'is_active',
        'page_name', 'seo_title', 'seo_description', 'meta_title', 'meta_description',
        'meta_image', 'author', 'publisher', 'copyright', 'site_name', 'keywords',
    ];

    protected $casts = [
        'sort_order' => 'integer',
        'is_active' => 'boolean',
    ];

    public function business(): BelongsTo
    {
        return $this->belongsTo(Business::class);
    }
}
