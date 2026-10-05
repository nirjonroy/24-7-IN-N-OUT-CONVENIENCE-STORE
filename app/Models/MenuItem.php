<?php

namespace App\Models;

use App\Models\Concerns\HasSeoMeta;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class MenuItem extends Model
{
    use HasFactory, HasSeoMeta, SoftDeletes;

    public const LINK_PAGE = 'page';
    public const LINK_CUSTOM = 'custom';
    public const LINK_TYPES = [self::LINK_PAGE, self::LINK_CUSTOM];
    public const TARGETS = ['_self', '_blank'];

    protected $fillable = [
        'menu_id', 'parent_id', 'page_id', 'label', 'url', 'link_type', 'icon', 'badge',
        'target', 'rel', 'css_identifier', 'sort_order', 'is_active', 'page_name',
        'seo_title', 'seo_description', 'meta_title', 'meta_description', 'meta_image',
        'author', 'publisher', 'copyright', 'site_name', 'keywords',
    ];

    protected $casts = [
        'sort_order' => 'integer',
        'is_active' => 'boolean',
    ];

    protected static function booted(): void
    {
        static::deleting(function (MenuItem $item) {
            $item->children()->update(['parent_id' => null]);
        });
    }

    public function menu(): BelongsTo
    {
        return $this->belongsTo(Menu::class);
    }

    public function page(): BelongsTo
    {
        return $this->belongsTo(Page::class);
    }

    public function parent(): BelongsTo
    {
        return $this->belongsTo(MenuItem::class, 'parent_id');
    }

    public function children(): HasMany
    {
        return $this->hasMany(MenuItem::class, 'parent_id')
            ->orderBy('sort_order')
            ->orderBy('id');
    }

    public function scopeActive(Builder $query): Builder
    {
        return $query->where('is_active', true);
    }
}
