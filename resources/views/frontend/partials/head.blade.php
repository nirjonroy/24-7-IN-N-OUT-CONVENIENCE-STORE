<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <meta name="theme-color" content="#b91c1c">
    <meta name="color-scheme" content="light dark">
    @include('frontend.partials.seo', ['seo' => $seo ?? []])
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
