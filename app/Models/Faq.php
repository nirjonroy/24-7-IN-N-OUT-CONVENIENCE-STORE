<?php

namespace App\Models;

use App\Models\Concerns\HasSeoMeta;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class Faq extends Model
{
    use HasFactory, HasSeoMeta, SoftDeletes;

    protected $fillable = ['faq_category_id', 'question', 'answer', 'is_featured', 'is_active', 'sort_order', 'page_name', 'seo_title', 'seo_description', 'meta_title', 'meta_description', 'meta_image', 'author', 'publisher', 'copyright', 'site_name', 'keywords'];
    protected $casts = ['is_featured' => 'boolean', 'is_active' => 'boolean', 'sort_order' => 'integer'];

    public function category(): BelongsTo { return $this->belongsTo(FaqCategory::class, 'faq_category_id'); }
    public function pages(): BelongsToMany { return $this->belongsToMany(Page::class, 'faq_page')->withPivot('sort_order')->withTimestamps(); }
    public function scopeActive(Builder $query): Builder { return $query->where('is_active', true); }
    public function scopeFeatured(Builder $query): Builder { return $query->where('is_featured', true); }
}
