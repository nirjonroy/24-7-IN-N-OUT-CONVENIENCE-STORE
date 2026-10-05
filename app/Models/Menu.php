<?php

namespace App\Models;

use App\Models\Concerns\HasSeoMeta;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class Menu extends Model
{
    use HasFactory, HasSeoMeta, SoftDeletes;

    protected $fillable = [
        'name', 'key', 'location', 'description', 'is_active', 'sort_order',
        'page_name', 'seo_title', 'seo_description', 'meta_title', 'meta_description',
        'meta_image', 'author', 'publisher', 'copyright', 'site_name', 'keywords',
    ];

    protected $casts = [
        'is_active' => 'boolean',
        'sort_order' => 'integer',
    ];

    protected static function booted(): void
    {
        static::deleting(function (Menu $menu) {
            $menu->allItems()->get()->each->delete();
        });
    }

    public function items(): HasMany
    {
        return $this->hasMany(MenuItem::class)
            ->whereNull('parent_id')
            ->orderBy('sort_order')
            ->orderBy('id');
    }

    public function allItems(): HasMany
    {
        return $this->hasMany(MenuItem::class)
            ->orderBy('sort_order')
            ->orderBy('id');
    }

    public function scopeActive(Builder $query): Builder
    {
        return $query->where('is_active', true);
    }

    public function scopeLocation(Builder $query, string $location): Builder
    {
        return $query->where('location', $location);
    }
}
