@extends('frontend.layouts.app')

@section('content')
@php
    $hero = $sections->first(fn ($section) => in_array($section->section_type, ['hero', 'home_hero'], true) || $section->section_key === 'hero');
    $contentSections = $sections->reject(fn ($section) => $hero && $section->id === $hero->id);
    $fallbackServices = collect([
        ['title' => 'Convenience store', 'description' => 'A local grab-and-go stop for convenience-store shopping at the Oxon Hill Rd location.', 'image' => asset('frontend-asset/assets/images/convenience-illustration.svg'), 'url' => url('/convenience-store'), 'label' => 'Store details'],
        ['title' => 'Phone repair', 'description' => 'Bring your device to the in-store repair counter for assessment and available repair options.', 'image' => asset('frontend-asset/assets/images/phone-repair-illustration.svg'), 'url' => url('/phone-repair'), 'label' => 'Repair information'],
        ['title' => 'Smoothies', 'description' => 'Smoothies are part of the store offering. Current selection and availability can be confirmed in store.', 'image' => asset('frontend-asset/assets/images/smoothie-illustration.svg'), 'url' => url('/smoothies'), 'label' => 'Smoothie details'],
        ['title' => 'Adult-only retail', 'description' => 'The business also lists vape/tobacco retail. Tobacco and nicotine products are restricted to customers age 21+.', 'image' => asset('frontend-asset/assets/images/adult-21-illustration.svg'), 'url' => url('/vape-tobacco'), 'label' => '21+ information'],
    ]);
@endphp

<section class="relative overflow-hidden">
    <div class="absolute inset-x-0 top-0 -z-10 h-[520px] bg-gradient-to-b from-red-50 via-amber-50/60 to-transparent dark:from-red-950/25 dark:via-amber-950/10"></div>
    <div class="mx-auto grid max-w-7xl gap-10 px-4 py-16 sm:px-6 lg:grid-cols-[1.05fr_0.95fr] lg:px-8 lg:py-20">
        <div class="flex flex-col justify-center">
            <div class="inline-flex w-fit items-center gap-2 rounded-full border border-red-200 bg-white/80 px-3 py-1.5 text-xs font-extrabold uppercase tracking-[0.13em] text-red-700 shadow-sm dark:border-red-900 dark:bg-slate-900/80 dark:text-amber-300">
                <svg class="h-4 w-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" d="M12 21s7-4.35 7-11a7 7 0 1 0-14 0c0 6.65 7 11 7 11Z"/><circle cx="12" cy="10" r="2.5"/></svg>
                {{ $hero?->subtitle ?: $business['tagline'] }}
            </div>
            <h1 class="mt-6 max-w-3xl text-4xl font-extrabold leading-[1.08] tracking-tight text-slate-950 sm:text-5xl lg:text-6xl dark:text-white">{{ $page?->h1 ?: 'Convenience, phone repair and smoothies at one Oxon Hill stop.' }}</h1>
            <p class="mt-6 max-w-2xl text-lg leading-8 text-slate-600 dark:text-slate-300">{{ $hero?->content ?: $page?->intro_text ?: '24/7 IN N OUT brings multiple everyday needs together at 6168 Oxon Hill Rd: a convenience store, phone repair, smoothies and a separate adult-only vape/tobacco retail category.' }}</p>
            <div class="mt-8 flex flex-wrap gap-3">
                <a class="inline-flex items-center justify-center gap-2 rounded-xl bg-red-700 px-5 py-3 text-sm font-extrabold text-white shadow-sm transition hover:bg-red-800 focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-red-600 focus-visible:ring-offset-2 dark:bg-amber-400 dark:text-slate-950 dark:hover:bg-amber-300" href="{{ $hero?->primary_button_url ?: $location['map_url'] }}" target="_blank" rel="noopener">{{ $hero?->primary_button_label ?: 'Get directions' }} <svg class="h-4 w-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" d="M5 12h14M13 6l6 6-6 6"/></svg></a>
                <a class="inline-flex items-center justify-center gap-2 rounded-xl border border-slate-300 bg-white px-5 py-3 text-sm font-extrabold text-slate-900 shadow-sm transition hover:border-red-300 hover:text-red-700 focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-red-600 dark:border-slate-700 dark:bg-slate-900 dark:text-white dark:hover:border-amber-400 dark:hover:text-amber-300" href="{{ $hero?->secondary_button_url ?: url('/phone-repair') }}">{{ $hero?->secondary_button_label ?: 'Explore phone repair' }}</a>
            </div>
            <div class="mt-8 grid max-w-2xl grid-cols-2 gap-3 sm:grid-cols-4">
                @foreach(($hero?->items->isNotEmpty() ? $hero->items : $fallbackServices) as $item)
                    @php
                        $heroItemTitle = is_array($item) ? $item['title'] : $item->title;
                        $heroItemUrl = is_array($item) ? $item['url'] : ($item->button_url ?: '#');
                    @endphp
                    <a href="{{ $heroItemUrl }}" class="rounded-2xl border border-slate-200 bg-white p-4 shadow-sm transition hover:-translate-y-0.5 hover:border-red-200 dark:border-slate-800 dark:bg-slate-900">
                        <span class="mt-2 block text-sm font-extrabold">{{ $heroItemTitle }}</span>
                    </a>
                @endforeach
            </div>
        </div>
        <div class="relative">
            <div class="absolute -inset-4 -z-10 rounded-[2.5rem] bg-gradient-to-br from-red-200/70 to-amber-200/70 blur-2xl dark:from-red-900/30 dark:to-amber-900/20"></div>
            <div class="overflow-hidden rounded-[2rem] border border-slate-200 bg-slate-950 shadow-2xl dark:border-slate-800">
                <img src="{{ $hero?->frontend_image ?: asset('frontend-asset/assets/images/storefront-google.webp') }}" alt="{{ $hero?->image_alt ?: $business['name'].' storefront' }}" width="1424" height="960" fetchpriority="high" decoding="async" class="aspect-[4/3] w-full object-cover">
                <div class="flex items-center justify-between gap-4 border-t border-white/10 bg-slate-950 px-5 py-4 text-white">
                    <div><span class="block text-sm font-extrabold">{{ $location['name'] }}</span><span class="text-xs text-slate-300">{{ $location['address'] }}</span></div>
                    <a href="{{ $location['map_url'] }}" target="_blank" rel="noopener" class="rounded-lg bg-white/10 px-3 py-2 text-xs font-bold hover:bg-white/15">Map</a>
                </div>
            </div>
        </div>
    </div>
</section>

@forelse($contentSections as $section)
    @if(in_array($section->section_type, ['service_grid', 'services', 'card_grid', 'feature_grid'], true))
        <section class="border-y border-slate-200 bg-slate-50 py-16 dark:border-slate-800 dark:bg-slate-900/50">
            <div class="mx-auto max-w-7xl px-4 sm:px-6 lg:px-8">
                <div class="max-w-3xl">
                    @if($section->subtitle)<span class="text-sm font-extrabold uppercase tracking-[0.18em] text-red-700 dark:text-amber-300">{{ $section->subtitle }}</span>@endif
                    @if($section->title)<span role="heading" aria-level="2" class="mt-3 block text-3xl font-extrabold tracking-tight text-slate-950 sm:text-4xl dark:text-white">{{ $section->title }}</span>@endif
                    @if($section->content)<p class="mt-4 text-base leading-8 text-slate-600 dark:text-slate-300">{{ $section->content }}</p>@endif
                </div>
                <div class="mt-10 grid gap-5 md:grid-cols-2 lg:grid-cols-4">
                    @foreach($section->items as $item)
                        <article class="rounded-3xl border border-slate-200 bg-white p-6 shadow-sm dark:border-slate-800 dark:bg-slate-950">
                            @if($item->frontend_image)<img src="{{ $item->frontend_image }}" alt="{{ $item->image_alt ?: $item->title }}" width="720" height="480" loading="lazy" decoding="async" class="aspect-[3/2] w-full rounded-2xl object-cover">@endif
                            @if($item->title)<span role="heading" aria-level="3" class="mt-5 block text-xl font-extrabold">{{ $item->title }}</span>@endif
                            @if($item->description)<p class="mt-2 text-sm leading-7 text-slate-600 dark:text-slate-300">{{ $item->description }}</p>@endif
                            @if($item->button_label && $item->button_url)<a href="{{ $item->button_url }}" class="mt-4 inline-flex items-center gap-2 text-sm font-extrabold text-red-700 dark:text-amber-300">{{ $item->button_label }} <svg class="h-4 w-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" d="M5 12h14M13 6l6 6-6 6"/></svg></a>@endif
                        </article>
                    @endforeach
                </div>
            </div>
        </section>
    @elseif(in_array($section->section_type, ['cta', 'location_cta'], true))
        <section class="pb-20">
            <div class="mx-auto max-w-7xl px-4 sm:px-6 lg:px-8">
                <div class="overflow-hidden rounded-[2rem] bg-slate-950 px-6 py-10 text-white sm:px-10 lg:flex lg:items-center lg:justify-between lg:gap-12">
                    <div>
                        @if($section->subtitle)<span class="text-sm font-extrabold uppercase tracking-[0.18em] text-amber-300">{{ $section->subtitle }}</span>@endif
                        @if($section->title)<span role="heading" aria-level="2" class="mt-3 block text-3xl font-extrabold tracking-tight sm:text-4xl">{{ $section->title }}</span>@endif
                        @if($section->content)<p class="mt-4 max-w-2xl leading-7 text-slate-300">{{ $section->content }}</p>@endif
                    </div>
                    <div class="mt-6 flex flex-wrap gap-3 lg:mt-0">
                        <a class="inline-flex items-center justify-center gap-2 rounded-xl bg-amber-400 px-5 py-3 text-sm font-extrabold text-slate-950 hover:bg-amber-300" href="{{ $section->primary_button_url ?: $location['map_url'] }}" target="_blank" rel="noopener">{{ $section->primary_button_label ?: 'Open directions' }}</a>
                        @if($section->secondary_button_label && $section->secondary_button_url)<a class="inline-flex items-center justify-center gap-2 rounded-xl border border-white/20 px-5 py-3 text-sm font-extrabold text-white hover:bg-white/10" href="{{ $section->secondary_button_url }}">{{ $section->secondary_button_label }}</a>@endif
                    </div>
                </div>
            </div>
        </section>
    @else
        <section class="py-16">
            <div class="mx-auto max-w-7xl px-4 sm:px-6 lg:px-8">
                @if($section->subtitle)<span class="text-sm font-extrabold uppercase tracking-[0.18em] text-red-700 dark:text-amber-300">{{ $section->subtitle }}</span>@endif
                @if($section->title)<span role="heading" aria-level="2" class="mt-3 block text-3xl font-extrabold tracking-tight text-slate-950 sm:text-4xl dark:text-white">{{ $section->title }}</span>@endif
                @if($section->content)<p class="mt-4 max-w-3xl text-base leading-8 text-slate-600 dark:text-slate-300">{{ $section->content }}</p>@endif
            </div>
        </section>
    @endif
@empty
    <section class="border-y border-slate-200 bg-slate-50 py-16 dark:border-slate-800 dark:bg-slate-900/50">
        <div class="mx-auto max-w-7xl px-4 sm:px-6 lg:px-8">
            <div class="max-w-3xl">
                <span class="text-sm font-extrabold uppercase tracking-[0.18em] text-red-700 dark:text-amber-300">Four reasons to visit</span>
                <span role="heading" aria-level="2" class="mt-3 block text-3xl font-extrabold tracking-tight text-slate-950 sm:text-4xl dark:text-white">A practical neighborhood stop, built around everyday needs.</span>
                <p class="mt-4 text-base leading-8 text-slate-600 dark:text-slate-300">Instead of sending customers to several locations, the business combines its core categories at one Oxon Hill address.</p>
            </div>
            <div class="mt-10 grid gap-5 md:grid-cols-2 lg:grid-cols-4">
                @foreach($fallbackServices as $item)
                    <article class="rounded-3xl border border-slate-200 bg-white p-6 shadow-sm dark:border-slate-800 dark:bg-slate-950">
                        <img src="{{ $item['image'] }}" alt="{{ $item['title'] }}" width="720" height="480" loading="lazy" decoding="async" class="aspect-[3/2] w-full rounded-2xl object-cover">
                        <span role="heading" aria-level="3" class="mt-5 block text-xl font-extrabold">{{ $item['title'] }}</span>
                        <p class="mt-2 text-sm leading-7 text-slate-600 dark:text-slate-300">{{ $item['description'] }}</p>
                        <a href="{{ $item['url'] }}" class="mt-4 inline-flex items-center gap-2 text-sm font-extrabold text-red-700 dark:text-amber-300">{{ $item['label'] }}</a>
                    </article>
                @endforeach
            </div>
        </div>
    </section>
@endforelse

@if($reviews->isNotEmpty())
    <section class="py-16">
        <div class="mx-auto max-w-7xl px-4 sm:px-6 lg:px-8">
            <span class="text-sm font-extrabold uppercase tracking-[0.18em] text-red-700 dark:text-amber-300">Customer reviews</span>
            <span role="heading" aria-level="2" class="mt-3 block text-3xl font-extrabold tracking-tight text-slate-950 sm:text-4xl dark:text-white">What customers say.</span>
            <div class="mt-10 grid gap-5 md:grid-cols-3">
                @foreach($reviews as $review)
                    <article class="rounded-3xl border border-slate-200 bg-white p-6 shadow-sm dark:border-slate-800 dark:bg-slate-900">
                        <div class="text-sm font-extrabold text-amber-500">{{ $review['rating'] ? str_repeat('*', (int) round($review['rating'])) : 'Review' }}</div>
                        @if($review['text'])<p class="mt-4 text-sm leading-7 text-slate-600 dark:text-slate-300">{{ $review['text'] }}</p>@endif
                        <span class="mt-5 block text-sm font-extrabold">{{ $review['author'] }}</span>
                    </article>
                @endforeach
            </div>
        </div>
    </section>
@endif

<section class="pb-20">
    <div class="mx-auto max-w-7xl px-4 sm:px-6 lg:px-8">
        <div class="overflow-hidden rounded-[2rem] bg-slate-950 px-6 py-10 text-white sm:px-10 lg:flex lg:items-center lg:justify-between lg:gap-12">
            <div>
                <span class="text-sm font-extrabold uppercase tracking-[0.18em] text-amber-300">Visit the store</span>
                <span role="heading" aria-level="2" class="mt-3 block text-3xl font-extrabold tracking-tight sm:text-4xl">Find {{ $business['short_name'] }} on Oxon Hill Rd.</span>
                <p class="mt-4 max-w-2xl leading-7 text-slate-300">Use Google Maps for current directions, traffic and business-profile updates.</p>
            </div>
            <div class="mt-6 flex flex-wrap gap-3 lg:mt-0">
                <a class="inline-flex items-center justify-center gap-2 rounded-xl bg-amber-400 px-5 py-3 text-sm font-extrabold text-slate-950 hover:bg-amber-300" href="{{ $location['map_url'] }}" target="_blank" rel="noopener">Open directions</a>
                <a class="inline-flex items-center justify-center gap-2 rounded-xl border border-white/20 px-5 py-3 text-sm font-extrabold text-white hover:bg-white/10" href="{{ url('/contact') }}">Contact page</a>
            </div>
        </div>
    </div>
</section>
@endsection
