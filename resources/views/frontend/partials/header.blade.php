<div class="border-b border-red-100 bg-red-50 text-slate-700 dark:border-red-950/60 dark:bg-red-950/30 dark:text-slate-200">
    <div class="mx-auto flex max-w-7xl items-center justify-between gap-4 px-4 py-2 text-xs sm:px-6 lg:px-8">
        <a href="{{ $location['map_url'] }}" target="_blank" rel="noopener" class="flex min-w-0 items-center gap-2 font-semibold hover:text-red-700 dark:hover:text-amber-300">
            <svg class="h-4 w-4 shrink-0" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" d="M12 21s7-4.35 7-11a7 7 0 1 0-14 0c0 6.65 7 11 7 11Z"/><circle cx="12" cy="10" r="2.5"/></svg>
            <span class="truncate">{{ $location['address'] }}</span>
        </a>
        <span class="hidden shrink-0 font-semibold sm:inline">{{ $fallback['top_bar_text'] }}</span>
    </div>
</div>
<header class="sticky top-0 z-50 border-b border-slate-200/80 bg-white/90 backdrop-blur-xl dark:border-slate-800 dark:bg-slate-950/88">
    <div class="mx-auto flex h-20 max-w-7xl items-center justify-between gap-4 px-4 sm:px-6 lg:px-8">
        <a href="{{ route('home') }}" class="flex shrink-0 items-center gap-3" aria-label="{{ $business['name'] }} home">
            <img src="{{ $business['logo'] }}" alt="" width="48" height="48" class="h-11 w-11">
            <span class="leading-tight">
                <span class="block text-base font-extrabold tracking-tight text-slate-950 dark:text-white">{{ $business['short_name'] }}</span>
                <span class="block text-[11px] font-bold uppercase tracking-[0.16em] text-red-700 dark:text-amber-300">{{ $business['tagline'] }}</span>
            </span>
        </a>
        <nav class="hidden items-center gap-0.5 lg:flex" aria-label="Primary navigation">
            @foreach($menus['header'] as $item)
                @php($isHomeLink = $item['url'] === route('home') && request()->routeIs('home'))
                <a href="{{ $item['url'] }}" target="{{ $item['target'] }}" @if($item['rel']) rel="{{ $item['rel'] }}" @endif class="rounded-lg px-2.5 py-2 text-sm font-semibold transition {{ $isHomeLink ? 'text-red-700 dark:text-amber-300' : 'text-slate-700 hover:text-red-700 dark:text-slate-200 dark:hover:text-amber-300' }}" @if($isHomeLink) aria-current="page" @endif>{{ $item['label'] }}</a>
            @endforeach
        </nav>
        <div class="flex items-center gap-2">
            <button type="button" data-theme-toggle class="inline-flex h-10 w-10 items-center justify-center rounded-xl border border-slate-200 bg-white text-slate-700 shadow-sm transition hover:border-red-200 hover:text-red-700 focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-red-600 dark:border-slate-700 dark:bg-slate-900 dark:text-slate-200 dark:hover:border-amber-400 dark:hover:text-amber-300" aria-label="Toggle light and dark mode">
                <span data-theme-icon="light"><svg class="h-5 w-5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" aria-hidden="true"><circle cx="12" cy="12" r="4"/><path stroke-linecap="round" d="M12 2v2M12 20v2M4.93 4.93l1.42 1.42M17.66 17.66l1.41 1.41M2 12h2M20 12h2M4.93 19.07l1.42-1.42M17.66 6.34l1.41-1.41"/></svg></span>
                <span data-theme-icon="dark" hidden><svg class="h-5 w-5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" d="M21 12.8A8.5 8.5 0 1 1 11.2 3 6.8 6.8 0 0 0 21 12.8Z"/></svg></span>
            </button>
            <a href="{{ $location['map_url'] }}" target="_blank" rel="noopener" class="hidden items-center gap-2 rounded-xl bg-red-700 px-4 py-2.5 text-sm font-bold text-white shadow-sm transition hover:bg-red-800 focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-red-600 focus-visible:ring-offset-2 dark:bg-amber-400 dark:text-slate-950 dark:hover:bg-amber-300 sm:inline-flex">Directions <svg class="h-4 w-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" d="M5 12h14M13 6l6 6-6 6"/></svg></a>
            <button type="button" data-menu-toggle class="inline-flex h-10 w-10 items-center justify-center rounded-xl border border-slate-200 bg-white text-slate-800 lg:hidden dark:border-slate-700 dark:bg-slate-900 dark:text-slate-100" aria-label="Open navigation" aria-expanded="false" aria-controls="mobile-menu"><svg class="h-5 w-5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" aria-hidden="true"><path stroke-linecap="round" d="M4 7h16M4 12h16M4 17h16"/></svg></button>
        </div>
    </div>
    @include('frontend.partials.mobile-menu')
</header>
