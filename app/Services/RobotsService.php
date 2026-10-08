<?php

namespace App\Services;

use App\Models\SeoSetting;

class RobotsService
{
    public function text(): string
    {
        $settings = SeoSetting::current();

        if (! app()->environment('production')) {
            return "User-agent: *\nDisallow: /\n";
        }

        if (! $settings->robots_enabled) {
            return "User-agent: *\nDisallow: /\n";
        }

        $lines = [
            'User-agent: *',
            'Allow: /',
            'Disallow: /admin/',
            'Disallow: /login',
            'Disallow: /logout',
            'Disallow: /register',
            'Disallow: /password',
            'Disallow: /forgot-password',
            'Disallow: /reset-password',
            'Disallow: /profile',
            'Disallow: /api/',
            'Disallow: /frontend-asset/',
        ];

        if ($settings->robots_txt_extra) {
            $lines[] = trim($settings->robots_txt_extra);
        }

        if ($settings->sitemap_enabled) {
            $base = rtrim($settings->canonical_base_url ?: config('app.url'), '/');
            $lines[] = 'Sitemap: '.$base.'/sitemap.xml';
        }

        return implode("\n", $lines)."\n";
    }
}
