<?php

namespace App\Services;

use App\Models\MenuItem;
use App\Models\Page;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\Str;

class PageUrlService
{
    public const SLUG_PATTERN = '[A-Za-z0-9-]+';

    public function slug(string $slug): string
    {
        $slug = trim($slug, '/');

        if (($routeName = $this->specialRouteName($slug)) && Route::has($routeName)) {
            return route($routeName);
        }

        return route('frontend.page.show', ['slug' => $slug]);
    }

    public function page(?Page $page, bool $requireResolvable = false): string
    {
        if (! $page) {
            return '#';
        }

        if ($page->is_home || $page->slug === 'home') {
            return route('home');
        }

        if ($requireResolvable && ! $this->isPubliclyResolvable($page)) {
            return '#';
        }

        return $this->slug($page->slug);
    }

    public function menuItem(MenuItem $item): string
    {
        if ($item->link_type === MenuItem::LINK_PAGE) {
            return $this->page($item->page);
        }

        return $this->safe($item->url);
    }

    public function isPubliclyResolvable(?Page $page): bool
    {
        if (! $page) {
            return false;
        }

        if ($page->is_home || $page->slug === 'home' || $this->isSpecialSlug($page->slug)) {
            return true;
        }

        return $this->isGenericSlug($page->slug)
            && $page->status === Page::STATUS_PUBLISHED
            && (! $page->published_at || $page->published_at->lte(now()))
            && ! $page->trashed();
    }

    public function isGenericSlug(string $slug): bool
    {
        $slug = trim($slug, '/');

        return preg_match('/^'.self::SLUG_PATTERN.'$/', $slug) === 1
            && ! $this->isSystemReservedSlug($slug);
    }

    public function isSystemReservedSlug(string $slug): bool
    {
        return in_array(Str::lower(trim($slug, '/')), config('public-pages.reserved_slugs', []), true);
    }

    public function isSpecialSlug(string $slug): bool
    {
        return array_key_exists(trim($slug, '/'), $this->specialRoutes());
    }

    public function specialRouteName(string $slug): ?string
    {
        return $this->specialRoutes()[trim($slug, '/')] ?? null;
    }

    public function specialRoutes(): array
    {
        return config('public-pages.special_routes', []);
    }

    public function safe(?string $url, string $fallback = '#'): string
    {
        $url = trim((string) $url);

        if ($url === '') {
            return $fallback;
        }

        $lower = Str::lower($url);

        if (Str::startsWith($lower, ['javascript:', 'data:', 'vbscript:', '//'])) {
            return $fallback;
        }

        if (Str::startsWith($url, ['/', '#'])) {
            return $url;
        }

        if (Str::startsWith($lower, ['mailto:', 'tel:'])) {
            return $url;
        }

        if (filter_var($url, FILTER_VALIDATE_URL)) {
            $scheme = parse_url($url, PHP_URL_SCHEME);

            return in_array($scheme, ['http', 'https'], true) ? $url : $fallback;
        }

        return $fallback;
    }
}
