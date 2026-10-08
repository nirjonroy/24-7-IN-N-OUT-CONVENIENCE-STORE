@extends('frontend.layouts.app')

@section('content')
@php
    $items = collect($galleryContent['items'] ?? []);
@endphp
<section class="py-16">
    <div class="mx-auto max-w-7xl px-4 sm:px-6 lg:px-8">
        <span class="text-sm font-extrabold uppercase tracking-[0.18em] text-red-700 dark:text-amber-300">{{ $pageFallback['eyebrow'] }}</span>
        <h1 class="mt-4 max-w-4xl text-4xl font-extrabold tracking-tight text-slate-950 sm:text-5xl dark:text-white">{{ $page?->h1 ?: $pageFallback['h1'] }}</h1>
        <p class="mt-5 max-w-3xl text-lg leading-8 text-slate-600 dark:text-slate-300">{{ $page?->intro_text ?: $pageFallback['intro'] }}</p>

        <div class="mt-12 grid gap-6 md:grid-cols-2">
            @foreach($items as $item)
                <figure class="overflow-hidden rounded-[2rem] border border-slate-200 bg-white shadow-sm dark:border-slate-800 dark:bg-slate-900" @if(! empty($item['category_id'])) data-gallery-category="{{ $item['category_id'] }}" @endif>
                    <img src="{{ $item['image'] }}" alt="{{ $item['alt'] }}" width="{{ $item['width'] }}" height="{{ $item['height'] }}" @if(! $loop->first) loading="lazy" @endif class="{{ $item['ratio'] }} w-full object-cover" decoding="async">
                    @if(! empty($item['caption']) || ! empty($item['title']) || ! empty($item['category_name']))
                        <figcaption class="p-5 text-sm font-bold">
                            @if(! empty($item['category_name']))
                                <span class="mb-2 block text-xs font-extrabold uppercase tracking-[0.16em] text-red-700 dark:text-amber-300">{{ $item['category_name'] }}</span>
                            @endif
                            {{ $item['caption'] ?: $item['title'] }}
                        </figcaption>
                    @endif
                </figure>
            @endforeach
        </div>
    </div>
</section>
@endsection
