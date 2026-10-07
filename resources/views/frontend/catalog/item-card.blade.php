<article class="flex h-full flex-col overflow-hidden rounded-3xl border border-slate-200 bg-white shadow-sm dark:border-slate-800 dark:bg-slate-900">
    @if(! empty($item['image']))
        <img src="{{ $item['image'] }}" alt="{{ $item['image_alt'] }}" width="720" height="480" loading="lazy" decoding="async" class="aspect-[4/3] w-full object-cover">
    @endif
    <div class="flex flex-1 flex-col p-6">
        <div class="flex flex-wrap items-center gap-2">
            @if(! empty($item['category']))
                <span class="rounded-full bg-slate-100 px-2.5 py-1 text-xs font-extrabold text-slate-600 dark:bg-slate-800 dark:text-slate-300">{{ $item['category'] }}</span>
            @endif
            @if($item['is_featured'])
                <span class="rounded-full bg-red-100 px-2.5 py-1 text-xs font-extrabold text-red-800 dark:bg-red-950/40 dark:text-red-100">Featured</span>
            @endif
            @if(! empty($item['minimum_age']) && $item['is_age_restricted'])
                <span class="rounded-full bg-red-700 px-2.5 py-1 text-xs font-extrabold text-white">{{ $item['minimum_age'] }}+</span>
            @endif
        </div>
        <span role="heading" aria-level="3" class="mt-4 block text-xl font-extrabold text-slate-950 dark:text-white">{{ $item['name'] }}</span>
        @if(! empty($item['brand']))
            <p class="mt-1 text-xs font-bold uppercase tracking-[0.12em] text-slate-400">{{ $item['brand'] }}</p>
        @endif
        @if(! empty($item['short_description'] ?? $item['description']))
            <p class="mt-3 text-sm leading-7 text-slate-600 dark:text-slate-300">{{ $item['short_description'] ?: $item['description'] }}</p>
        @endif
        @if(! empty($item['ingredients']))
            <p class="mt-4 rounded-2xl bg-slate-50 p-3 text-xs font-semibold leading-6 text-slate-600 dark:bg-slate-950 dark:text-slate-300"><span class="font-extrabold text-slate-900 dark:text-white">Ingredients:</span> {{ $item['ingredients'] }}</p>
        @endif
        @if($item['variants']->isNotEmpty())
            <div class="mt-5 grid gap-2">
                @foreach($item['variants'] as $variant)
                    <div class="flex items-center justify-between rounded-2xl border border-slate-200 px-3 py-2 text-sm dark:border-slate-800">
                        <span class="font-bold">{{ $variant['name'] }}</span>
                        <span class="font-extrabold">{{ $variant['price'] ?: $variant['price_label'] }}</span>
                    </div>
                @endforeach
            </div>
        @endif
        <div class="mt-auto flex flex-wrap items-center gap-3 pt-5 text-sm">
            @include('frontend.catalog.price', ['item' => $item])
        </div>
    </div>
</article>
