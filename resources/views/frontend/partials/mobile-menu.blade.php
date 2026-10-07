<div id="mobile-menu" data-mobile-menu hidden class="border-t border-slate-200 bg-white px-4 py-4 dark:border-slate-800 dark:bg-slate-950 lg:hidden">
    <nav class="mx-auto grid max-w-7xl gap-1" aria-label="Mobile navigation">
        @foreach($menus['mobile'] as $item)
            @php($isActiveLink = rtrim(request()->url(), '/') === rtrim(url($item['url']), '/'))
            <a href="{{ $item['url'] }}" target="{{ $item['target'] }}" @if($item['rel']) rel="{{ $item['rel'] }}" @endif class="rounded-xl px-4 py-3 text-sm font-semibold {{ $isActiveLink ? 'bg-red-50 text-red-700 dark:bg-red-950/40 dark:text-amber-300' : 'text-slate-700 hover:bg-slate-100 dark:text-slate-200 dark:hover:bg-slate-800' }}" @if($isActiveLink) aria-current="page" @endif>{{ $item['label'] }}</a>
            @foreach($item['children'] as $child)
                <a href="{{ $child['url'] }}" target="{{ $child['target'] }}" @if($child['rel']) rel="{{ $child['rel'] }}" @endif class="rounded-xl px-8 py-2 text-sm font-semibold text-slate-600 hover:bg-slate-100 dark:text-slate-300 dark:hover:bg-slate-800">{{ $child['label'] }}</a>
            @endforeach
        @endforeach
    </nav>
</div>
