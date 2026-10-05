<?php

namespace App\Models;

use App\Models\Concerns\HasMediaAttachments;
use App\Models\Concerns\HasSeoMeta;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;

class GalleryItem extends Model
{
    use HasFactory, HasMediaAttachments, HasSeoMeta, SoftDeletes;

    protected $fillable = ['gallery_category_id', 'title', 'caption', 'description', 'photographer', 'source_url', 'taken_at', 'is_featured', 'is_active', 'sort_order', 'page_name', 'seo_title', 'seo_description', 'meta_title', 'meta_description', 'meta_image', 'author', 'publisher', 'copyright', 'site_name', 'keywords'];
    protected $casts = ['taken_at' => 'date', 'is_featured' => 'boolean', 'is_active' => 'boolean', 'sort_order' => 'integer'];

    public function category(): BelongsTo { return $this->belongsTo(GalleryCategory::class, 'gallery_category_id'); }
    public function scopeActive(Builder $query): Builder { return $query->where('is_active', true); }
    public function scopeFeatured(Builder $query): Builder { return $query->where('is_featured', true); }
}
