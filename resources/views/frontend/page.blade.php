@extends('frontend.layouts.app')

@section('content')
@php
    $allowedTypes = ['hero', 'text', 'image_content', 'feature_grid', 'service_grid', 'business_info', 'location', 'reviews', 'cta', 'custom'];
    $safeUrl = fn (?string $url, string $fallback = '#') => app(\App\Services\PageUrlService::class)->safe($url, $fallback);
@endphp

<section class="relative overflow-hidden border-b border-slate-200 bg-gradient-to-b from-red-50 via-white to-white py-16 dark:border-slate-800 dark:from-red-950/20 dark:via-slate-950 dark:to-slate-950">
    <div class="mx-auto max-w-7xl px-4 sm:px-6 lg:px-8">
        <nav class="mb-8 flex items-center gap-2 text-sm font-semibold text-slate-500 dark:text-slate-400" aria-label="Breadcrumb">
            <a href="{{ route('home') }}" class="hover:text-red-700 dark:hover:text-amber-300">Home</a>
            <span aria-hidden="true">/</span>
            <span class="text-slate-800 dark:text-slate-100">{{ $page->name }}</span>
        </nav>
        <div class="max-w-3xl">
            <span class="text-sm font-extrabold uppercase tracking-[0.18em] text-red-700 dark:text-amber-300">{{ $page->page_name ?: $pageFallback['title'] }}</span>
            <h1 class="mt-4 text-4xl font-extrabold tracking-tight text-slate-950 sm:text-5xl dark:text-white">{{ $page->h1 }}</h1>
            @if($page->intro_text)
                <p class="mt-5 text-lg leading-8 text-slate-600 dark:text-slate-300">{{ $page->intro_text }}</p>
            @endif
        </div>
    </div>
</section>

@foreach($sections as $section)
    @continue(! in_array($section->section_type, $allowedTypes, true))

    @switch($section->section_type)
        @case('hero')
        @case('image_content')
            <section class="py-16">
                <div class="mx-auto grid max-w-7xl items-center gap-10 px-4 sm:px-6 lg:grid-cols-2 lg:px-8">
                    @if($section->frontend_image)
                        <img src="{{ $section->frontend_image }}" alt="{{ $section->image_alt ?: $section->title }}" class="aspect-[4/3] w-full rounded-[2rem] object-cover shadow-xl" loading="lazy" decoding="async">
                    @endif
                    <div>
                        @if($section->section_label)<span class="text-sm font-extrabold uppercase tracking-[0.18em] text-red-700 dark:text-amber-300">{{ $section->section_label }}</span>@endif
                        @if($section->title)<span role="heading" aria-level="2" class="mt-3 block text-3xl font-extrabold tracking-tight text-slate-950 sm:text-4xl dark:text-white">{{ $section->title }}</span>@endif
                        @if($section->subtitle)<p class="mt-4 text-lg font-semibold text-slate-700 dark:text-slate-200">{{ $section->subtitle }}</p>@endif
                        @if($section->content)<p class="mt-4 text-base leading-8 text-slate-600 dark:text-slate-300">{{ $section->content }}</p>@endif
                        <div class="mt-7 flex flex-wrap gap-3">
                            @if($section->primary_button_label && $section->primary_button_url)
                                <a href="{{ $safeUrl($section->primary_button_url) }}" class="inline-flex items-center justify-center rounded-xl bg-red-700 px-5 py-3 text-sm font-extrabold text-white hover:bg-red-800 dark:bg-amber-400 dark:text-slate-950 dark:hover:bg-amber-300">{{ $section->primary_button_label }}</a>
                            @endif
                            @if($section->secondary_button_label && $section->secondary_button_url)
                                <a href="{{ $safeUrl($section->secondary_button_url) }}" class="inline-flex items-center justify-center rounded-xl border border-slate-300 px-5 py-3 text-sm font-extrabold text-slate-900 hover:border-red-300 hover:text-red-700 dark:border-slate-700 dark:text-white dark:hover:border-amber-400 dark:hover:text-amber-300">{{ $section->secondary_button_label }}</a>
                            @endif
                        </div>
                    </div>
                </div>
            </section>
            @break

        @case('feature_grid')
        @case('service_grid')
        @case('business_info')
            <section class="border-y border-slate-200 bg-slate-50 py-16 dark:border-slate-800 dark:bg-slate-900/50">
                <div class="mx-auto max-w-7xl px-4 sm:px-6 lg:px-8">
                    <div class="max-w-3xl">
                        @if($section->section_label)<span class="text-sm font-extrabold uppercase tracking-[0.18em] text-red-700 dark:text-amber-300">{{ $section->section_label }}</span>@endif
                        @if($section->title)<span role="heading" aria-level="2" class="mt-3 block text-3xl font-extrabold tracking-tight text-slate-950 sm:text-4xl dark:text-white">{{ $section->title }}</span>@endif
                        @if($section->content)<p class="mt-4 text-base leading-8 text-slate-600 dark:text-slate-300">{{ $section->content }}</p>@endif
                    </div>
                    @if($section->items->isNotEmpty())
                        <div class="mt-10 grid gap-5 sm:grid-cols-2 lg:grid-cols-3">
                            @foreach($section->items as $item)
                                <article class="rounded-2xl border border-slate-200 bg-white p-6 shadow-sm dark:border-slate-800 dark:bg-slate-950">
                                    @if($item->frontend_image)<img src="{{ $item->frontend_image }}" alt="{{ $item->image_alt ?: $item->title }}" class="mb-5 aspect-[3/2] w-full rounded-xl object-cover" loading="lazy" decoding="async">@endif
                                    @if($item->badge || $item->subtitle)<span class="text-xs font-extrabold uppercase tracking-[0.16em] text-red-700 dark:text-amber-300">{{ $item->badge ?: $item->subtitle }}</span>@endif
                                    @if($item->title)<span role="heading" aria-level="3" class="mt-3 block text-xl font-extrabold">{{ $item->title }}</span>@endif
                                    @if($item->description)<p class="mt-3 text-sm leading-7 text-slate-600 dark:text-slate-300">{{ $item->description }}</p>@endif
                                    @if($item->button_label && $item->button_url)<a href="{{ $safeUrl($item->button_url) }}" class="mt-4 inline-flex text-sm font-extrabold text-red-700 dark:text-amber-300">{{ $item->button_label }}</a>@endif
                                </article>
                            @endforeach
                        </div>
                    @endif
                </div>
            </section>
            @break

        @case('location')
            <section class="py-16">
                <div class="mx-auto max-w-7xl px-4 sm:px-6 lg:px-8">
                    <div class="rounded-[2rem] bg-slate-950 p-8 text-white sm:p-10">
                        <span role="heading" aria-level="2" class="block text-3xl font-extrabold">{{ $section->title ?: $location['name'] }}</span>
                        <p class="mt-4 max-w-2xl text-slate-300">{{ $section->content ?: $location['address'] }}</p>
                        <a href="{{ $location['map_url'] }}" target="_blank" rel="noopener" class="mt-6 inline-flex rounded-xl bg-amber-400 px-5 py-3 text-sm font-extrabold text-slate-950 hover:bg-amber-300">Get directions</a>
                    </div>
                </div>
            </section>
            @break

        @case('cta')
            <section class="py-16">
                <div class="mx-auto max-w-7xl px-4 sm:px-6 lg:px-8">
                    <div class="rounded-[2rem] bg-red-700 p-8 text-white sm:p-10 dark:bg-amber-400 dark:text-slate-950">
                        @if($section->section_label)<span class="text-sm font-extrabold uppercase tracking-[0.18em]">{{ $section->section_label }}</span>@endif
                        @if($section->title)<span role="heading" aria-level="2" class="mt-3 block text-3xl font-extrabold">{{ $section->title }}</span>@endif
                        @if($section->content)<p class="mt-4 max-w-2xl leading-7 opacity-90">{{ $section->content }}</p>@endif
                        @if($section->primary_button_label && $section->primary_button_url)<a href="{{ $safeUrl($section->primary_button_url) }}" class="mt-6 inline-flex rounded-xl bg-white px-5 py-3 text-sm font-extrabold text-red-700 dark:text-slate-950">{{ $section->primary_button_label }}</a>@endif
                    </div>
                </div>
            </section>
            @break

        @default
            <section class="py-16">
                <div class="mx-auto max-w-7xl px-4 sm:px-6 lg:px-8">
                    @if($section->section_label)<span class="text-sm font-extrabold uppercase tracking-[0.18em] text-red-700 dark:text-amber-300">{{ $section->section_label }}</span>@endif
                    @if($section->title)<span role="heading" aria-level="2" class="mt-3 block text-3xl font-extrabold tracking-tight text-slate-950 sm:text-4xl dark:text-white">{{ $section->title }}</span>@endif
                    @if($section->content)<p class="mt-4 max-w-3xl text-base leading-8 text-slate-600 dark:text-slate-300">{{ $section->content }}</p>@endif
                </div>
            </section>
    @endswitch
@endforeach
@endsection
