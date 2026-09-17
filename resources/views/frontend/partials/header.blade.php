<header class="bg-white border-b border-gray-100">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-4 flex items-center justify-between">
        <a href="{{ route('home') }}" class="font-semibold text-gray-900">{{ config('app.name', 'Laravel') }}</a>
        <div class="space-x-4">
            @auth
                <a href="{{ route('dashboard') }}" class="text-sm text-gray-700">Dashboard</a>
            @else
                <a href="{{ route('login') }}" class="text-sm text-gray-700">Login</a>
                <a href="{{ route('register') }}" class="text-sm text-gray-700">Register</a>
            @endauth
        </div>
    </div>
</header>
