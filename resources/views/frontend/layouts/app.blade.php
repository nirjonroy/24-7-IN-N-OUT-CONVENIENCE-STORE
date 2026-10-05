<!doctype html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
@include('frontend.partials.head')
<body class="min-h-screen bg-white font-sans text-slate-900 antialiased dark:bg-slate-950 dark:text-slate-100">
    <a href="#main-content" class="sr-only focus:not-sr-only focus:fixed focus:left-4 focus:top-4 focus:z-[100] focus:rounded-lg focus:bg-white focus:px-4 focus:py-2 focus:text-slate-900 focus:shadow-lg">Skip to content</a>
    @include('frontend.partials.header')
    <main id="main-content">
        @yield('content')
    </main>
    @include('frontend.partials.footer')
    @include('frontend.partials.structured-data')
    <script src="{{ asset('frontend-asset/assets/js/app.js') }}" defer></script>
</body>
</html>
