<article class="rounded-3xl border border-slate-200 bg-white p-6 shadow-sm dark:border-slate-800 dark:bg-slate-900">
    @if(! empty($category['image']))
        <img src="{{ $category['image'] }}" alt="{{ $category['image_alt'] }}" width="720" height="480" loading="lazy" decoding="async" class="aspect-[3/2] w-full rounded-2xl object-cover">
    @endif
    <span role="heading" aria-level="3" class="mt-4 block text-xl font-extrabold text-slate-950 dark:text-white">{{ $category['name'] }}</span>
    @if(! empty($category['description']))
        <p class="mt-3 text-sm leading-7 text-slate-600 dark:text-slate-300">{{ $category['description'] }}</p>
    @endif
    @if(! empty($category['minimum_age']))
        <p class="mt-4 inline-flex rounded-full bg-red-100 px-3 py-1 text-xs font-extrabold text-red-800 dark:bg-red-950/40 dark:text-red-100">{{ $category['minimum_age'] }}+ only</p>
    @endif
</article>
