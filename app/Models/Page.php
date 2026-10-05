<?php

namespace App\Models;

use App\Models\Concerns\HasSeoMeta;
use App\Models\Concerns\HasMediaAttachments;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class Page extends Model
{
    use HasFactory;
    use HasMediaAttachments;
    use HasSeoMeta;
    use SoftDeletes;

    public const STATUS_DRAFT = 'draft';
    public const STATUS_PUBLISHED = 'published';
    public const STATUS_PRIVATE = 'private';

    public const STATUSES = [
        self::STATUS_DRAFT,
        self::STATUS_PUBLISHED,
        self::STATUS_PRIVATE,
    ];

    protected $fillable = [
        'name', 'slug', 'route_name', 'template', 'h1', 'intro_text', 'canonical_url',
        'og_title', 'og_description', 'og_image', 'robots_index', 'robots_follow',
        'is_home', 'status', 'sort_order', 'published_at', 'page_name', 'seo_title',
        'seo_description', 'meta_title', 'meta_description', 'meta_image', 'author',
        'publisher', 'copyright', 'site_name', 'keywords',
    ];

    protected $casts = [
        'robots_index' => 'boolean',
        'robots_follow' => 'boolean',
        'is_home' => 'boolean',
        'sort_order' => 'integer',
        'published_at' => 'datetime',
    ];

    protected static function booted(): void
    {
        static::deleting(function (Page $page) {
            $page->menuItems()->update(['page_id' => null]);
            $page->sections()->get()->each->delete();
        });
    }

    public function sections(): HasMany
    {
        return $this->hasMany(PageSection::class)->orderBy('sort_order')->orderBy('id');
    }

    public function faqs(): BelongsToMany
    {
        return $this->belongsToMany(Faq::class, 'faq_page')->withPivot('sort_order')->withTimestamps();
    }

    public function menuItems(): HasMany
    {
        return $this->hasMany(MenuItem::class);
    }

    public function scopePublished(Builder $query): Builder
    {
        return $query->where('status', self::STATUS_PUBLISHED)
            ->where(function (Builder $query) {
                $query->whereNull('published_at')->orWhere('published_at', '<=', now());
            });
    }
}
