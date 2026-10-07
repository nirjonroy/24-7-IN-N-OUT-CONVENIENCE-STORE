<title>{{ $seo['title'] ?? $business['name'] ?? config('app.name') }}</title>
@if(! empty($seo['description']))
    <meta name="description" content="{{ $seo['description'] }}">
@endif
<meta name="robots" content="{{ $seo['robots'] ?? 'index, follow' }}">
@if(! empty($seo['canonical']))
    <link rel="canonical" href="{{ $seo['canonical'] }}">
@endif
<meta property="og:type" content="{{ $seo['og_type'] ?? 'website' }}">
<meta property="og:title" content="{{ $seo['og_title'] ?? $seo['title'] ?? $business['name'] ?? config('app.name') }}">
@if(! empty($seo['og_description']))
    <meta property="og:description" content="{{ $seo['og_description'] }}">
@endif
@if(! empty($seo['og_url'] ?? $seo['canonical'] ?? null))
    <meta property="og:url" content="{{ $seo['og_url'] ?? $seo['canonical'] }}">
@endif
@if(! empty($seo['og_image'] ?? $seo['meta_image'] ?? null))
    <meta property="og:image" content="{{ $seo['og_image'] ?? $seo['meta_image'] }}">
@endif
@if(! empty($seo['site_name']))
    <meta property="og:site_name" content="{{ $seo['site_name'] }}">
@endif
<meta name="twitter:card" content="{{ $seo['twitter_card'] ?? $seoSettings->twitter_card ?? 'summary_large_image' }}">
<meta name="twitter:title" content="{{ $seo['twitter_title'] ?? $seo['og_title'] ?? $seo['title'] ?? $business['name'] ?? config('app.name') }}">
@if(! empty($seo['twitter_description'] ?? $seo['og_description'] ?? null))
    <meta name="twitter:description" content="{{ $seo['twitter_description'] ?? $seo['og_description'] }}">
@endif
@if(! empty($seo['twitter_image'] ?? $seo['og_image'] ?? $seo['meta_image'] ?? null))
    <meta name="twitter:image" content="{{ $seo['twitter_image'] ?? $seo['og_image'] ?? $seo['meta_image'] }}">
@endif
@if(! empty($seo['twitter_site'] ?? $seoSettings->twitter_site ?? null))
    <meta name="twitter:site" content="{{ $seo['twitter_site'] ?? $seoSettings->twitter_site }}">
@endif
@if(! empty($seoSettings->facebook_app_id))
    <meta property="fb:app_id" content="{{ $seoSettings->facebook_app_id }}">
@endif
@if(! empty($seo['google_site_verification'] ?? $seoSettings->google_site_verification ?? null))
    <meta name="google-site-verification" content="{{ $seo['google_site_verification'] ?? $seoSettings->google_site_verification }}">
@endif
@if(! empty($seo['bing_site_verification'] ?? $seoSettings->bing_site_verification ?? null))
    <meta name="msvalidate.01" content="{{ $seo['bing_site_verification'] ?? $seoSettings->bing_site_verification }}">
@endif
