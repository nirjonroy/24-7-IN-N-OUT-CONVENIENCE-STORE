<?php

namespace App\Models;

use App\Models\Concerns\HasSeoMeta;
use App\Models\Concerns\HasMediaAttachments;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Model;

class Business extends Model
{
    use HasFactory;
    use HasMediaAttachments;
    use HasSeoMeta;

    protected $fillable = [
        'name', 'short_name', 'legal_name', 'tagline', 'description', 'primary_category',
        'schema_types', 'currency', 'minimum_age', 'adult_retail_notice', 'is_active',
        'page_name', 'seo_title', 'seo_description', 'meta_title', 'meta_description',
        'meta_image', 'author', 'publisher', 'copyright', 'site_name', 'keywords',
    ];

    protected $casts = [
        'schema_types' => 'array',
        'minimum_age' => 'integer',
        'is_active' => 'boolean',
    ];

    public function locations(): HasMany
    {
        return $this->hasMany(Location::class);
    }

    public function socialLinks(): HasMany
    {
        return $this->hasMany(SocialLink::class);
    }
}
