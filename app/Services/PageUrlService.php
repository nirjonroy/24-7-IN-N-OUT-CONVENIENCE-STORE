<?php

namespace App\Services;

use App\Models\MenuItem;
use App\Models\Page;
use Illuminate\Support\Str;

class PageUrlService
{
    public function page(?Page $page): string
    {
        if (! $page) {
            return '#';
        }

        if ($page->is_home || $page->slug === 'home') {
            return route('home');
        }

        return url('/'.ltrim($page->slug, '/'));
    }

    public function menuItem(MenuItem $item): string
    {
        if ($item->link_type === MenuItem::LINK_PAGE) {
            return $this->page($item->page);
        }

        return $this->safe($item->url);
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
