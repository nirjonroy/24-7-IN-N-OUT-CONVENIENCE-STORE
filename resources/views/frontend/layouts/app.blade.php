<!doctype html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
@include('frontend.partials.head')
<body class="antialiased bg-gray-50 min-h-screen">
    @include('frontend.partials.header')
    <main>
        @include('frontend.partials.sidebar')
        @yield('content')
    </main>
    @include('frontend.partials.footer')
</body>
</html>
