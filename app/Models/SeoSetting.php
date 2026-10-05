<?php

namespace App\Models;

use App\Models\Concerns\HasMediaAttachments;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Cache;

class SeoSetting extends Model
{
    use HasFactory, HasMediaAttachments;

    public const CACHE_KEY = 'seo_settings.global';

    protected $fillable = [
        'site_name', 'default_title', 'title_separator', 'default_description',
        'default_keywords', 'default_author', 'default_publisher', 'default_copyright',
        'default_meta_image', 'canonical_base_url', 'default_robots_index',
        'default_robots_follow', 'twitter_card', 'twitter_site', 'facebook_app_id',
        'google_site_verification', 'bing_site_verification', 'robots_txt_extra',
        'sitemap_enabled', 'robots_enabled', 'structured_data_enabled',
    ];

    protected $casts = [
        'default_robots_index' => 'boolean',
        'default_robots_follow' => 'boolean',
        'sitemap_enabled' => 'boolean',
        'robots_enabled' => 'boolean',
        'structured_data_enabled' => 'boolean',
    ];

    protected static function booted(): void
    {
        static::saved(fn () => self::clearCache());
        static::deleted(fn () => self::clearCache());
    }

    public static function current(): self
    {
        return Cache::remember(self::CACHE_KEY, now()->addMinutes(30), function () {
            return self::query()->latest('id')->first() ?: self::query()->create([
                'site_name' => '24/7 IN N OUT CONVENIENCE STORE',
                'title_separator' => '|',
                'twitter_card' => 'summary_large_image',
                'default_robots_index' => true,
                'default_robots_follow' => true,
                'sitemap_enabled' => true,
                'robots_enabled' => true,
                'structured_data_enabled' => true,
            ]);
        });
    }

    public static function clearCache(): void
    {
        Cache::forget(self::CACHE_KEY);
    }
}
