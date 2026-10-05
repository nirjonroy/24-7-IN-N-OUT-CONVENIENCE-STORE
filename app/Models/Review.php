<?php

namespace App\Models;

use App\Models\Concerns\HasMediaAttachments;
use App\Models\Concerns\HasSeoMeta;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Review extends Model
{
    use HasFactory, HasMediaAttachments, HasSeoMeta, SoftDeletes;

    public const SOURCES = ['google', 'facebook', 'website', 'manual', 'other'];

    protected $fillable = [
        'source', 'external_id', 'author_name', 'author_url', 'rating', 'review_text',
        'review_url', 'reviewed_at', 'is_featured', 'is_active', 'sort_order',
        'page_name', 'seo_title', 'seo_description', 'meta_title', 'meta_description',
        'meta_image', 'author', 'publisher', 'copyright', 'site_name', 'keywords',
    ];

    protected $casts = [
        'rating' => 'decimal:1',
        'reviewed_at' => 'datetime',
        'is_featured' => 'boolean',
        'is_active' => 'boolean',
        'sort_order' => 'integer',
    ];

    public function scopeActive(Builder $query): Builder
    {
        return $query->where('is_active', true);
    }

    public function scopeFeatured(Builder $query): Builder
    {
        return $query->where('is_featured', true);
    }

    public function scopeSource(Builder $query, string $source): Builder
    {
        return $query->where('source', $source);
    }
}
