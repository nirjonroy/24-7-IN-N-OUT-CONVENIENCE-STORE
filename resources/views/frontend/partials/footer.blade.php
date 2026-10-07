<footer class="border-t border-slate-200 bg-slate-50 dark:border-slate-800 dark:bg-slate-900/60">
    <div class="mx-auto grid max-w-7xl gap-10 px-4 py-12 sm:px-6 md:grid-cols-2 lg:grid-cols-4 lg:px-8">
        <div class="lg:col-span-2">
            <div class="flex items-center gap-3">
                <img src="{{ $business['logo'] }}" alt="" width="44" height="44" class="h-11 w-11">
                <span class="text-lg font-extrabold tracking-tight">{{ $business['short_name'] }}</span>
            </div>
            <p class="mt-4 max-w-xl text-sm leading-7 text-slate-600 dark:text-slate-300">{{ $business['description'] }}</p>
            <a href="{{ $location['map_url'] }}" target="_blank" rel="noopener" class="mt-4 inline-flex items-start gap-2 text-sm font-semibold text-red-700 hover:underline dark:text-amber-300">
                <svg class="mt-0.5 h-4 w-4 shrink-0" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" d="M12 21s7-4.35 7-11a7 7 0 1 0-14 0c0 6.65 7 11 7 11Z"/><circle cx="12" cy="10" r="2.5"/></svg>
                <span>{{ $location['address'] }}, United States</span>
            </a>
            @if($business['social_links']->isNotEmpty())
                <div class="mt-5 flex flex-wrap gap-3">
                    @foreach($business['social_links'] as $link)
                        <a href="{{ $link['url'] }}" target="_blank" rel="noopener noreferrer" class="rounded-lg border border-slate-200 px-3 py-2 text-xs font-bold text-slate-700 hover:border-red-200 hover:text-red-700 dark:border-slate-800 dark:text-slate-200 dark:hover:border-amber-400 dark:hover:text-amber-300">{{ $link['label'] }}</a>
                    @endforeach
                </div>
            @endif
        </div>
        <div>
            <span role="heading" aria-level="2" class="block text-sm font-extrabold uppercase tracking-[0.16em] text-slate-950 dark:text-white">Explore</span>
            <div class="mt-4 grid gap-2 text-sm text-slate-600 dark:text-slate-300">
                @foreach($menus['footer_primary'] as $item)
                    <a class="hover:text-red-700 dark:hover:text-amber-300" href="{{ $item['url'] }}" target="{{ $item['target'] }}" @if($item['rel']) rel="{{ $item['rel'] }}" @endif>{{ $item['label'] }}</a>
                @endforeach
            </div>
        </div>
        <div>
            <span role="heading" aria-level="2" class="block text-sm font-extrabold uppercase tracking-[0.16em] text-slate-950 dark:text-white">Information</span>
            <div class="mt-4 grid gap-2 text-sm text-slate-600 dark:text-slate-300">
                @foreach($menus['footer_secondary'] as $item)
                    <a class="hover:text-red-700 dark:hover:text-amber-300" href="{{ $item['url'] }}" target="{{ $item['target'] }}" @if($item['rel']) rel="{{ $item['rel'] }}" @endif>{{ $item['label'] }}</a>
                @endforeach
            </div>
        </div>
    </div>
    <div class="border-t border-slate-200 dark:border-slate-800">
        <div class="mx-auto flex max-w-7xl flex-col gap-2 px-4 py-5 text-xs text-slate-500 sm:flex-row sm:items-center sm:justify-between sm:px-6 lg:px-8">
            <span>&copy; {{ now()->year }} {{ $business['short_name'] }}. All rights reserved.</span>
            <span>{{ $business['adult_notice'] }}</span>
        </div>
    </div>
</footer>
