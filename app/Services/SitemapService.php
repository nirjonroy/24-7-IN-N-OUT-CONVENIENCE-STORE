<?php

namespace App\Services;

use App\Models\Page;
use App\Models\SeoSetting;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Route;

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
        $pageUrls = app(PageUrlService::class);
        $rows = collect();
        $pages = Page::published()
            ->where('robots_index', true)
            ->orderBy('sort_order')
            ->orderBy('id')
            ->get();

        foreach ($pageUrls->specialRoutes() as $slug => $routeName) {
            if (! Route::has($routeName)) {
                continue;
            }

            $page = $slug === 'home'
                ? $pages->first(fn (Page $page) => $page->is_home || $page->slug === 'home')
                : $pages->firstWhere('slug', $slug);

            $rows->push([
                'loc' => $this->canonicalize($pageUrls->slug($slug), $settings),
                'lastmod' => optional($page?->updated_at)->toAtomString(),
            ]);
        }

        $genericRows = $pages
            ->reject(fn (Page $page) => $page->is_home || $page->slug === 'home' || $pageUrls->isSpecialSlug($page->slug))
            ->filter(fn (Page $page) => $pageUrls->isPubliclyResolvable($page))
            ->map(fn (Page $page) => [
                'loc' => $this->canonicalize($pageUrls->page($page, true), $settings),
                'lastmod' => optional($page->updated_at)->toAtomString(),
            ]);

        return $rows
            ->merge($genericRows)
            ->filter(fn (array $url) => $url['loc'] !== '#')
            ->unique('loc')
            ->values()
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

    private function canonicalize(string $url, SeoSetting $settings): string
    {
        $base = rtrim($settings->canonical_base_url ?: config('app.url'), '/');
        $path = parse_url($url, PHP_URL_PATH) ?: '/';

        return $base.($path === '/' ? '' : $path);
    }
}
