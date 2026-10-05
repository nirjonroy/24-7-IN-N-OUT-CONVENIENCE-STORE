<?php

namespace App\Services;

use App\Models\Page;
use App\Models\SeoSetting;
use Illuminate\Support\Facades\Cache;

class SitemapService
{
    public const CACHE_KEY = 'seo.sitemap.xml';

    public function xml(): string
    {
        return Cache::remember(self::CACHE_KEY, now()->addMinutes(45), function () {
            $settings = SeoSetting::current();

            if (! $settings->sitemap_enabled) {
                return $this->render([]);
            }

            return $this->render($this->urls($settings));
        });
    }

    public function clearCache(): void
    {
        Cache::forget(self::CACHE_KEY);
    }

    public function urls(?SeoSetting $settings = null): array
    {
        $settings ??= SeoSetting::current();
        $base = rtrim($settings->canonical_base_url ?: config('app.url'), '/');

        return Page::published()
            ->where('robots_index', true)
            ->orderBy('sort_order')
            ->orderBy('id')
            ->get()
            ->map(function (Page $page) use ($base) {
                $path = $page->is_home ? '' : '/'.ltrim($page->slug, '/');

                return [
                    'loc' => $page->canonical_url ?: $base.$path,
                    'lastmod' => optional($page->updated_at)->toAtomString(),
                ];
            })
            ->all();
    }

    private function render(array $urls): string
    {
        $xml = '<?xml version="1.0" encoding="UTF-8"?>'."\n";
        $xml .= '<urlset xmlns="http://www.sitemaps.org/schemas/sitemap/0.9">'."\n";

        foreach ($urls as $url) {
            $xml .= "  <url>\n";
            $xml .= '    <loc>'.e($url['loc'])."</loc>\n";
            if (! empty($url['lastmod'])) {
                $xml .= '    <lastmod>'.e($url['lastmod'])."</lastmod>\n";
            }
            $xml .= "  </url>\n";
        }

        return $xml."</urlset>\n";
    }
}
