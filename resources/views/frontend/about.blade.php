@extends('frontend.layouts.app')

@section('content')
@php
    $heroSection = $sections->first(fn ($section) => in_array($section->section_type, ['hero', 'image_content'], true) || in_array($section->section_key, ['hero', 'story', 'about'], true));
    $identitySection = $sections->first(fn ($section) => in_array($section->section_type, ['feature_grid', 'business_info'], true) || in_array($section->section_key, ['identity', 'business_identity'], true));
    $extraSections = $sections->reject(fn ($section) => $heroSection && $section->id === $heroSection->id)
        ->reject(fn ($section) => $identitySection && $section->id === $identitySection->id)
        ->values();
    $heroImage = $heroSection?->frontend_image ?: $pageFallback['image'];
    $heroAlt = $heroSection?->image_alt ?: $pageFallback['image_alt'];
    $buttonLabel = $heroSection?->primary_button_label ?: $pageFallback['button_label'];
    $buttonUrl = $heroSection?->primary_button_url ?: $location['map_url'];
    $identityItems = $identitySection?->items?->isNotEmpty()
        ? $identitySection->items->map(fn ($item) => ['label' => $item->subtitle ?: $item->badge, 'title' => $item->title ?: $item->description])
        : collect($pageFallback['identity']['cards']);
@endphp

<section class="py-16">
    <div class="mx-auto grid max-w-7xl items-center gap-12 px-4 sm:px-6 lg:grid-cols-[.9fr_1.1fr] lg:px-8">
        <div>
            <img src="{{ $heroImage }}" alt="{{ $heroAlt }}" width="1424" height="960" class="aspect-[4/3] w-full rounded-[2rem] object-cover shadow-xl" decoding="async">
        </div>
        <div>
            <span class="text-sm font-extrabold uppercase tracking-[0.18em] text-red-700 dark:text-amber-300">{{ $heroSection?->section_label ?: $pageFallback['eyebrow'] }}</span>
            <h1 class="mt-4 text-4xl font-extrabold tracking-tight text-slate-950 sm:text-5xl dark:text-white">{{ $page?->h1 ?: $pageFallback['h1'] }}</h1>
            <p class="mt-5 text-lg leading-8 text-slate-600 dark:text-slate-300">{{ $page?->intro_text ?: $heroSection?->subtitle ?: $pageFallback['intro'] }}</p>
            <p class="mt-4 text-base leading-8 text-slate-600 dark:text-slate-300">{{ $heroSection?->content ?: $business['description'] ?: $pageFallback['description_two'] }}</p>
            <div class="mt-8">
                <a class="inline-flex items-center justify-center gap-2 rounded-xl bg-red-700 px-5 py-3 text-sm font-extrabold text-white shadow-sm transition hover:bg-red-800 focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-red-600 focus-visible:ring-offset-2 dark:bg-amber-400 dark:text-slate-950 dark:hover:bg-amber-300" href="{{ $buttonUrl }}" target="_blank" rel="noopener">
                    {{ $buttonLabel }}
                    <svg class="h-4 w-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" d="M5 12h14M13 6l6 6-6 6"/></svg>
                </a>
            </div>
        </div>
    </div>
</section>

<section class="border-y border-slate-200 bg-slate-50 py-16 dark:border-slate-800 dark:bg-slate-900/50">
    <div class="mx-auto max-w-7xl px-4 sm:px-6 lg:px-8">
        <div class="max-w-3xl">
            <span class="text-sm font-extrabold uppercase tracking-[0.18em] text-red-700 dark:text-amber-300">{{ $identitySection?->section_label ?: $pageFallback['identity']['eyebrow'] }}</span>
            <span role="heading" aria-level="2" class="mt-3 block text-3xl font-extrabold tracking-tight text-slate-950 sm:text-4xl dark:text-white">{{ $identitySection?->title ?: $business['name'] }}</span>
            <p class="mt-4 text-base leading-8 text-slate-600 dark:text-slate-300">{{ $identitySection?->content ?: $pageFallback['identity']['description'] }}</p>
        </div>
        <div class="mt-10 grid gap-4 sm:grid-cols-2 lg:grid-cols-4">
            @foreach($identityItems as $card)
                <div class="rounded-2xl bg-white p-5 shadow-sm ring-1 ring-slate-200 dark:bg-slate-950 dark:ring-slate-800">
                    @if(! empty($card['label']))
                        <span class="text-xs font-extrabold uppercase tracking-[0.16em] text-red-700 dark:text-amber-300">{{ $card['label'] }}</span>
                    @endif
                    <p class="mt-3 font-extrabold">{{ $card['title'] }}</p>
                </div>
            @endforeach
        </div>
    </div>
</section>

@foreach($extraSections as $section)
    <section class="py-16">
        <div class="mx-auto max-w-7xl px-4 sm:px-6 lg:px-8">
            <div class="max-w-3xl">
                @if($section->section_label)
                    <span class="text-sm font-extrabold uppercase tracking-[0.18em] text-red-700 dark:text-amber-300">{{ $section->section_label }}</span>
                @endif
                @if($section->title)
                    <span role="heading" aria-level="2" class="mt-3 block text-3xl font-extrabold tracking-tight text-slate-950 sm:text-4xl dark:text-white">{{ $section->title }}</span>
                @endif
                @if($section->content)
                    <p class="mt-4 text-base leading-8 text-slate-600 dark:text-slate-300">{{ $section->content }}</p>
                @endif
            </div>
            @if($section->items->isNotEmpty())
                <div class="mt-10 grid gap-4 sm:grid-cols-2 lg:grid-cols-4">
                    @foreach($section->items as $item)
                        <div class="rounded-2xl bg-white p-5 shadow-sm ring-1 ring-slate-200 dark:bg-slate-950 dark:ring-slate-800">
                            @if($item->subtitle || $item->badge)
                                <span class="text-xs font-extrabold uppercase tracking-[0.16em] text-red-700 dark:text-amber-300">{{ $item->subtitle ?: $item->badge }}</span>
                            @endif
                            @if($item->title)
                                <p class="mt-3 font-extrabold">{{ $item->title }}</p>
                            @endif
                            @if($item->description)
                                <p class="mt-3 text-sm leading-7 text-slate-600 dark:text-slate-300">{{ $item->description }}</p>
                            @endif
                        </div>
                    @endforeach
                </div>
            @endif
        </div>
    </section>
@endforeach
@endsection
