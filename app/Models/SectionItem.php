<?php

namespace App\Models;

use App\Models\Concerns\HasSeoMeta;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class SectionItem extends Model
{
    use HasFactory;
    use HasSeoMeta;

    protected $fillable = [
        'page_section_id', 'item_key', 'title', 'subtitle', 'description', 'badge', 'icon',
        'image', 'image_alt', 'button_label', 'button_url', 'settings', 'sort_order',
        'is_active', 'page_name', 'seo_title', 'seo_description', 'meta_title',
        'meta_description', 'meta_image', 'author', 'publisher', 'copyright', 'site_name',
        'keywords',
    ];

    protected $casts = [
        'settings' => 'array',
        'sort_order' => 'integer',
        'is_active' => 'boolean',
    ];

    public function pageSection(): BelongsTo
    {
        return $this->belongsTo(PageSection::class);
    }

    public function scopeActive(Builder $query): Builder
    {
        return $query->where('is_active', true);
    }
}
