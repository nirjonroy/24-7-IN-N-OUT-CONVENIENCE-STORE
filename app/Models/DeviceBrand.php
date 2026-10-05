<?php

namespace App\Models;

use App\Models\Concerns\HasMediaAttachments;
use App\Models\Concerns\HasSeoMeta;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class DeviceBrand extends Model
{
    use HasFactory;
    use HasMediaAttachments;
    use HasSeoMeta;
    use SoftDeletes;

    protected $fillable = ['name', 'slug', 'description', 'website_url', 'is_featured', 'is_active', 'sort_order', 'page_name', 'seo_title', 'seo_description', 'meta_title', 'meta_description', 'meta_image', 'author', 'publisher', 'copyright', 'site_name', 'keywords'];

    protected $casts = ['is_featured' => 'boolean', 'is_active' => 'boolean', 'sort_order' => 'integer'];

    public function models(): HasMany
    {
        return $this->hasMany(DeviceModel::class)->orderBy('sort_order')->orderBy('name');
    }

    public function scopeActive(Builder $query): Builder { return $query->where('is_active', true); }
    public function scopeFeatured(Builder $query): Builder { return $query->where('is_featured', true); }
}
