@extends('frontend.layouts.app')

@section('content')
@php($items = $galleryItems->isNotEmpty() ? $galleryItems->map(fn ($item) => ['title' => $item['caption'], 'image' => $item['image'], 'alt' => $item['alt'], 'ratio' => 'aspect-[3/2]', 'width' => 720, 'height' => 480]) : collect($pageFallback['items']))
<section class="py-16"><div class="mx-auto max-w-7xl px-4 sm:px-6 lg:px-8"><span class="text-sm font-extrabold uppercase tracking-[0.18em] text-red-700 dark:text-amber-300">{{ $pageFallback['eyebrow'] }}</span><h1 class="mt-4 max-w-4xl text-4xl font-extrabold tracking-tight text-slate-950 sm:text-5xl dark:text-white">{{ $page?->h1 ?: $pageFallback['h1'] }}</h1><p class="mt-5 max-w-3xl text-lg leading-8 text-slate-600 dark:text-slate-300">{{ $page?->intro_text ?: $pageFallback['intro'] }}</p>
<div class="mt-12 grid gap-6 md:grid-cols-2">@foreach($items as $item)<figure class="overflow-hidden rounded-[2rem] border border-slate-200 bg-white shadow-sm dark:border-slate-800 dark:bg-slate-900"><img src="{{ $item['image'] }}" alt="{{ $item['alt'] }}" width="{{ $item['width'] }}" height="{{ $item['height'] }}" @if(! $loop->first) loading="lazy" @endif class="{{ $item['ratio'] }} w-full object-cover" decoding="async"><figcaption class="p-5 text-sm font-bold">{{ $item['title'] }}</figcaption></figure>@endforeach</div></div></section>
@endsection
