<?php

namespace App\Services;

use App\Models\SeoSetting;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Http\Request;

class SeoService
{
    public function resolve(?Model $content = null, ?Request $request = null): array
    {
        $settings = SeoSetting::current();
        $title = $this->title($content, $settings);

        $description = $this->first([$this->value($content, 'meta_description'), $this->value($content, 'seo_description'), $this->value($content, 'description'), $this->value($content, 'intro_text'), $settings->default_description]);
        $canonical = $this->canonical($content, $request, $settings);
        $metaImage = $this->metaImage($content, $settings);
        $ogTitle = $this->first([$this->value($content, 'og_title'), $title]);
        $ogDescription = $this->first([$this->value($content, 'og_description'), $description]);

        return [
            'title' => $title,
            'description' => $description,
            'canonical' => $canonical,
            'robots' => $this->robots($content, $settings),
            'meta_image' => $metaImage,
            'og_title' => $ogTitle,
            'og_description' => $ogDescription,
            'og_type' => 'website',
            'og_url' => $canonical,
            'og_image' => $metaImage,
            'site_name' => $settings->site_name,
            'twitter_card' => $settings->twitter_card ?: 'summary_large_image',
            'twitter_title' => $ogTitle ?: $title,
            'twitter_description' => $ogDescription ?: $description,
            'twitter_image' => $metaImage,
            'twitter_site' => $settings->twitter_site,
            'google_site_verification' => $settings->google_site_verification,
            'bing_site_verification' => $settings->bing_site_verification,
            'author' => $this->first([$this->value($content, 'author'), $settings->default_author]),
            'publisher' => $this->first([$this->value($content, 'publisher'), $settings->default_publisher]),
            'copyright' => $this->first([$this->value($content, 'copyright'), $settings->default_copyright]),
            'keywords' => $this->first([$this->value($content, 'keywords'), $settings->default_keywords]),
        ];
    }

    public function canonical(?Model $content, ?Request $request, SeoSetting $settings): ?string
    {
        $explicit = $this->value($content, 'canonical_url');

        if ($explicit) {
            return $this->cleanUrl($explicit);
        }

        if (! $request) {
            return null;
        }

        $base = rtrim($settings->canonical_base_url ?: config('app.url'), '/');
        $path = '/'.ltrim($request->path(), '/');
        $path = $path === '/.' ? '/' : $path;

        return $this->cleanUrl($base.($path === '/' ? '' : $path));
    }

    private function title(?Model $content, SeoSetting $settings): ?string
    {
        $title = $this->first([$this->value($content, 'meta_title'), $this->value($content, 'seo_title'), $this->value($content, 'name'), $this->value($content, 'title'), $this->value($content, 'h1'), $settings->default_title, $settings->site_name]);

        if (! $title || ! $settings->site_name || str_contains(strtolower($title), strtolower($settings->site_name))) {
            return $title;
        }

        return $title.' '.($settings->title_separator ?: '|').' '.$settings->site_name;
    }

    private function robots(?Model $content, SeoSetting $settings): string
    {
        $index = $this->value($content, 'robots_index');
        $follow = $this->value($content, 'robots_follow');

        return ($index ?? $settings->default_robots_index ? 'index' : 'noindex').', '.($follow ?? $settings->default_robots_follow ? 'follow' : 'nofollow');
    }

    private function metaImage(?Model $content, SeoSetting $settings): ?string
    {
        if ($content && method_exists($content, 'getMediaUrl') && ($url = $content->getMediaUrl('meta_image'))) {
            return $url;
        }

        if ($content && $this->value($content, 'meta_image')) {
            return $this->value($content, 'meta_image');
        }

        return $settings->getMediaUrl('default_meta_image') ?: $settings->default_meta_image;
    }

    private function value(?Model $content, string $field): mixed
    {
        return $content && isset($content->{$field}) ? $content->{$field} : null;
    }

    private function first(array $values): mixed
    {
        foreach ($values as $value) {
            if ($value !== null && $value !== '') {
                return $value;
            }
        }

        return null;
    }

    private function cleanUrl(string $url): string
    {
        $parts = parse_url($url);
        $scheme = $parts['scheme'] ?? 'https';
        $host = $parts['host'] ?? '';
        $path = isset($parts['path']) ? preg_replace('#/+#', '/', $parts['path']) : '';

        return rtrim($scheme.'://'.$host.$path, '/') ?: $url;
    }
}
