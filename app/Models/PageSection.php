<?php

namespace App\Models;

use App\Models\Concerns\HasSeoMeta;
use App\Models\Concerns\HasMediaAttachments;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class PageSection extends Model
{
    use HasFactory;
    use HasMediaAttachments;
    use HasSeoMeta;

    protected $fillable = [
        'page_id', 'section_key', 'section_type', 'section_label', 'title', 'subtitle',
        'content', 'image', 'image_alt', 'background_image', 'primary_button_label',
        'primary_button_url', 'secondary_button_label', 'secondary_button_url', 'settings',
        'sort_order', 'is_active', 'page_name', 'seo_title', 'seo_description', 'meta_title',
        'meta_description', 'meta_image', 'author', 'publisher', 'copyright', 'site_name',
        'keywords',
    ];

    protected $casts = [
        'settings' => 'array',
        'sort_order' => 'integer',
        'is_active' => 'boolean',
    ];

    public function page(): BelongsTo
    {
        return $this->belongsTo(Page::class);
    }

    public function items(): HasMany
    {
        return $this->hasMany(SectionItem::class)->orderBy('sort_order')->orderBy('id');
    }

    public function scopeActive(Builder $query): Builder
    {
        return $query->where('is_active', true);
    }
}
