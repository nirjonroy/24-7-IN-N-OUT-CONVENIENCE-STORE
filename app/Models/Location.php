<?php

namespace App\Models;

use App\Models\Concerns\HasSeoMeta;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Model;

class Location extends Model
{
    use HasFactory;
    use HasSeoMeta;

    protected $fillable = [
        'business_id', 'name', 'slug', 'phone', 'secondary_phone', 'email',
        'address_line_1', 'address_line_2', 'city', 'state', 'postal_code',
        'country_code', 'latitude', 'longitude', 'timezone', 'price_range',
        'google_place_id', 'google_business_url', 'google_maps_url', 'directions_url',
        'is_primary', 'is_active', 'page_name', 'seo_title', 'seo_description',
        'meta_title', 'meta_description', 'meta_image', 'author', 'publisher',
        'copyright', 'site_name', 'keywords',
    ];

    protected $casts = [
        'latitude' => 'decimal:7',
        'longitude' => 'decimal:7',
        'is_primary' => 'boolean',
        'is_active' => 'boolean',
    ];

    public function business(): BelongsTo
    {
        return $this->belongsTo(Business::class);
    }

    public function businessHours(): HasMany
    {
        return $this->hasMany(BusinessHour::class);
    }

    public function specialBusinessHours(): HasMany
    {
        return $this->hasMany(SpecialBusinessHour::class);
    }
}
