<!doctype html>
<html lang="en">
@include('admin.partials.head')
<body class="layout-fixed sidebar-expand-lg sidebar-open bg-body-tertiary">
    <div class="app-wrapper">
        @include('admin.partials.header')
        @include('admin.partials.sidebar')
        @yield('content')
        @include('admin.partials.footer')
    </div>
    @include('admin.partials.scripts')
</body>
</html>
