<?php

namespace App\Observers;

use App\Models\Page;
use App\Models\Redirect;
use App\Services\PageUrlService;
use App\Services\RedirectService;
use App\Services\SitemapService;

class PageObserver
{
    public function saved(Page $page): void
    {
        app(SitemapService::class)->clearCache();

        if (! $page->wasChanged('slug')) {
            return;
        }

        $oldSlug = (string) $page->getOriginal('slug');
        $newSlug = (string) $page->slug;
        $urls = app(PageUrlService::class);

        if (! $urls->isGenericSlug($oldSlug) || ! $urls->isGenericSlug($newSlug)) {
            return;
        }

        if ($urls->isSpecialSlug($oldSlug) || $urls->isSpecialSlug($newSlug)) {
            return;
        }

        $redirectService = app(RedirectService::class);
        $source = $redirectService->normalizeSourcePath('/'.$oldSlug);
        $target = '/'.$newSlug;

        if ($redirectService->equivalentPath($source, $target)) {
            return;
        }

        if (Redirect::query()->where('source_path', $source)->where('match_type', Redirect::MATCH_EXACT)->exists()) {
            return;
        }

        Redirect::create([
            'source_path' => $source,
            'target_url' => $target,
            'match_type' => Redirect::MATCH_EXACT,
            'status_code' => 301,
            'preserve_query_string' => true,
            'is_active' => true,
            'note' => 'Automatically created after CMS page slug change.',
        ]);

        $redirectService->clearCache();
    }

    public function deleted(Page $page): void
    {
        app(SitemapService::class)->clearCache();
    }

    public function restored(Page $page): void
    {
        app(SitemapService::class)->clearCache();
    }
}
