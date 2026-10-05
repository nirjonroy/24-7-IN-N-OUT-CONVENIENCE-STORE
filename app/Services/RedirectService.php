<?php

namespace App\Services;

use App\Models\Redirect;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;

class RedirectService
{
    public const CACHE_KEY_PREFIXES = 'redirects.active.prefixes';

    public function normalizeSourcePath(?string $path): string
    {
        $path = trim((string) $path);
        $path = parse_url($path, PHP_URL_PATH) ?: $path;
        $path = '/'.ltrim($path, '/');
        $path = preg_replace('#/+#', '/', $path);

        return rtrim($path, '/') ?: '/';
    }

    public function normalizePrefixPath(?string $path): string
    {
        $path = $this->normalizeSourcePath($path);

        return $path === '/' ? $path : rtrim($path, '/').'/';
    }

    public function isSafeTarget(string $target): bool
    {
        $target = trim($target);

        if (preg_match('/^(javascript:|data:|vbscript:|file:)/i', $target)) {
            return false;
        }

        return (str_starts_with($target, '/') && ! str_starts_with($target, '//'))
            || preg_match('/^https?:\/\//i', $target) === 1;
    }

    public function equivalentPath(string $source, string $target): bool
    {
        $targetPath = parse_url($target, PHP_URL_PATH) ?: $target;

        return $this->normalizeSourcePath($source) === $this->normalizeSourcePath($targetPath);
    }

    public function wouldCreateSimpleLoop(string $source, string $target, ?int $ignoreId = null): bool
    {
        $targetPath = parse_url($target, PHP_URL_PATH);

        if (! $targetPath || ! str_starts_with($target, '/')) {
            return false;
        }

        return Redirect::active()
            ->exact()
            ->when($ignoreId, fn ($query) => $query->whereKeyNot($ignoreId))
            ->where('source_path', $this->normalizeSourcePath($targetPath))
            ->where('target_url', $this->normalizeSourcePath($source))
            ->exists();
    }

    public function findForRequest(Request $request): ?Redirect
    {
        if (! in_array($request->method(), ['GET', 'HEAD'], true) || $this->shouldSkip($request)) {
            return null;
        }

        $path = $this->normalizeSourcePath('/'.$request->path());

        $exact = Redirect::active()->exact()->where('source_path', $path)->first();

        if ($exact) {
            return $exact;
        }

        foreach ($this->activePrefixes() as $redirect) {
            if (str_starts_with($path, $redirect->source_path)) {
                return $redirect;
            }
        }

        return null;
    }

    public function buildTargetUrl(Redirect $redirect, Request $request): string
    {
        $target = $redirect->target_url;

        if ($redirect->match_type === Redirect::MATCH_PREFIX) {
            $requestPath = $this->normalizeSourcePath('/'.$request->path());
            $suffix = ltrim(substr($requestPath, strlen($redirect->source_path)), '/');
            $target = $this->appendPrefixSuffix($target, $suffix);
        }

        if (! $redirect->preserve_query_string || $request->getQueryString() === null) {
            return $target;
        }

        $separator = str_contains($target, '?') ? '&' : '?';

        return $target.$separator.$request->getQueryString();
    }

    public function recordHit(Redirect $redirect): void
    {
        try {
            $redirect->newQuery()->whereKey($redirect->id)->update([
                'hit_count' => DB::raw('hit_count + 1'),
                'last_hit_at' => now(),
            ]);
        } catch (\Throwable) {
            // Redirect delivery should not fail if analytics tracking cannot update.
        }
    }

    public function clearCache(): void
    {
        Cache::forget(self::CACHE_KEY_PREFIXES);
    }

    private function activePrefixes()
    {
        return Cache::remember(self::CACHE_KEY_PREFIXES, now()->addMinutes(10), fn () => Redirect::active()
            ->prefix()
            ->orderByRaw('CHAR_LENGTH(source_path) DESC')
            ->get());
    }

    private function appendPrefixSuffix(string $target, string $suffix): string
    {
        if ($suffix === '') {
            return $target;
        }

        [$base, $query] = array_pad(explode('?', $target, 2), 2, null);
        $url = rtrim($base, '/').'/'.$suffix;

        return $query ? $url.'?'.$query : $url;
    }

    private function shouldSkip(Request $request): bool
    {
        $path = trim($request->path(), '/');

        if ($path === '' || in_array($path, ['sitemap.xml', 'robots.txt'], true)) {
            return false;
        }

        if (preg_match('#^(admin|login|logout|register|password|forgot-password|reset-password|verify-email|email|profile|api|storage|_ignition|sanctum)#', $path)) {
            return true;
        }

        return preg_match('/\.(css|js|jpg|jpeg|png|gif|webp|svg|ico|xml|txt|map)$/i', $path) === 1;
    }
}
