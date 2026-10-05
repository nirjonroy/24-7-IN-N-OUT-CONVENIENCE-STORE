<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <meta name="theme-color" content="#b91c1c">
    <meta name="color-scheme" content="light dark">
    <title>{{ $seo['title'] ?? $business['name'] ?? config('app.name') }}</title>
    @if(! empty($seo['description']))
        <meta name="description" content="{{ $seo['description'] }}">
    @endif
    @if(! empty($seo['keywords']))
        <meta name="keywords" content="{{ $seo['keywords'] }}">
    @endif
    @if(! empty($seo['author']))
        <meta name="author" content="{{ $seo['author'] }}">
    @endif
    <meta name="robots" content="{{ $seo['robots'] ?? 'index, follow' }}">
    @if(! empty($seo['canonical']))
        <link rel="canonical" href="{{ $seo['canonical'] }}">
    @endif
    <meta property="og:type" content="website">
    <meta property="og:title" content="{{ $seo['og_title'] ?? $seo['title'] ?? $business['name'] ?? config('app.name') }}">
    @if(! empty($seo['og_description']))
        <meta property="og:description" content="{{ $seo['og_description'] }}">
    @endif
    @if(! empty($seo['canonical']))
        <meta property="og:url" content="{{ $seo['canonical'] }}">
    @endif
    @if(! empty($seo['site_name']))
        <meta property="og:site_name" content="{{ $seo['site_name'] }}">
    @endif
    @if(! empty($seo['meta_image']))
        <meta property="og:image" content="{{ $seo['meta_image'] }}">
        <meta name="twitter:image" content="{{ $seo['meta_image'] }}">
    @endif
    <meta name="twitter:card" content="{{ $seoSettings->twitter_card ?? 'summary_large_image' }}">
    <meta name="twitter:title" content="{{ $seo['og_title'] ?? $seo['title'] ?? $business['name'] ?? config('app.name') }}">
    @if(! empty($seo['og_description']))
        <meta name="twitter:description" content="{{ $seo['og_description'] }}">
    @endif
    @if(! empty($seoSettings->twitter_site))
        <meta name="twitter:site" content="{{ $seoSettings->twitter_site }}">
    @endif
    @if(! empty($seoSettings->facebook_app_id))
        <meta property="fb:app_id" content="{{ $seoSettings->facebook_app_id }}">
    @endif
    @if(! empty($seoSettings->google_site_verification))
        <meta name="google-site-verification" content="{{ $seoSettings->google_site_verification }}">
    @endif
    @if(! empty($seoSettings->bing_site_verification))
        <meta name="msvalidate.01" content="{{ $seoSettings->bing_site_verification }}">
    @endif
    <link rel="icon" href="{{ asset('frontend-asset/assets/images/logo-mark.svg') }}" type="image/svg+xml">
    <link rel="preload" as="image" href="{{ asset('frontend-asset/assets/images/storefront-google.webp') }}" fetchpriority="high">
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Manrope:wght@400;500;600;700;800&display=swap" rel="stylesheet">
    <script>
        (() => {
            const saved = localStorage.getItem('theme');
            const isDark = saved ? saved === 'dark' : window.matchMedia('(prefers-color-scheme: dark)').matches;
            if (isDark) document.documentElement.classList.add('dark');
        })();
    </script>
    <link rel="stylesheet" href="{{ asset('frontend-asset/assets/css/site.css') }}">
</head>
