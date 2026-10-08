@extends('frontend.layouts.app')

@section('content')
@php
    $faqGroups = collect($faqContent['groups'] ?? []);
@endphp
<section class="py-16">
    <div class="mx-auto max-w-4xl px-4 sm:px-6 lg:px-8">
        <span class="text-sm font-extrabold uppercase tracking-[0.18em] text-red-700 dark:text-amber-300">{{ $pageFallback['eyebrow'] }}</span>
        <h1 class="mt-4 text-4xl font-extrabold tracking-tight text-slate-950 sm:text-5xl dark:text-white">{{ $page?->h1 ?: $pageFallback['h1'] }}</h1>
        <p class="mt-5 text-lg leading-8 text-slate-600 dark:text-slate-300">{{ $page?->intro_text ?: $pageFallback['intro'] }}</p>

        <div class="mt-10 grid gap-6" data-faq-accordion>
            @foreach($faqGroups as $group)
                <div>
                    @if(($faqContent['source'] ?? 'fallback') !== 'fallback' || $faqGroups->count() > 1)
                        <span role="heading" aria-level="2" class="mb-4 block text-sm font-extrabold uppercase tracking-[0.16em] text-slate-500 dark:text-slate-400">{{ $group['name'] }}</span>
                    @endif
                    <div class="grid gap-4">
                        @foreach($group['items'] as $item)
                            @php
                                $faqId = 'faq-'.$group['id'].'-'.$loop->index;
                                $answerId = $faqId.'-answer';
                                $buttonId = $faqId.'-button';
                            @endphp
                            <div class="rounded-2xl border border-slate-200 bg-white p-5 shadow-sm dark:border-slate-800 dark:bg-slate-900">
                                <button id="{{ $buttonId }}" type="button" class="flex w-full items-start justify-between gap-4 text-left text-base font-extrabold text-slate-950 transition focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-red-600 focus-visible:ring-offset-4 dark:text-white dark:focus-visible:ring-amber-300 dark:focus-visible:ring-offset-slate-900" aria-expanded="false" aria-controls="{{ $answerId }}" data-faq-trigger>
                                    <span>{{ $item['question'] }}</span>
                                    <svg class="mt-1 h-5 w-5 shrink-0 text-red-700 transition dark:text-amber-300" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" aria-hidden="true" data-faq-icon><path stroke-linecap="round" stroke-linejoin="round" d="M6 9l6 6 6-6"/></svg>
                                </button>
                                <div id="{{ $answerId }}" role="region" aria-labelledby="{{ $buttonId }}" hidden data-faq-panel>
                                    <p class="mt-4 text-sm leading-7 text-slate-600 dark:text-slate-300">{{ $item['answer'] }}</p>
                                </div>
                            </div>
                        @endforeach
                    </div>
                </div>
            @endforeach
        </div>

        <div class="mt-8 flex flex-wrap gap-3">
            <a class="inline-flex items-center justify-center gap-2 rounded-xl bg-red-700 px-5 py-3 text-sm font-extrabold text-white shadow-sm transition hover:bg-red-800 focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-red-600 focus-visible:ring-offset-2 dark:bg-amber-400 dark:text-slate-950 dark:hover:bg-amber-300" href="https://www.google.com/search?q=24%2F7+IN+N+OUT+CONVENIENCE+STORE+Oxon+Hill+MD" target="_blank" rel="noopener">Open Google Business Profile <svg class="h-4 w-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" d="M14 4h6v6M20 4l-9 9"/><path stroke-linecap="round" stroke-linejoin="round" d="M18 13v6a1 1 0 0 1-1 1H5a1 1 0 0 1-1-1V7a1 1 0 0 1 1-1h6"/></svg></a>
            <a class="inline-flex items-center justify-center gap-2 rounded-xl border border-slate-300 bg-white px-5 py-3 text-sm font-extrabold text-slate-900 shadow-sm transition hover:border-red-300 hover:text-red-700 focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-red-600 dark:border-slate-700 dark:bg-slate-900 dark:text-white dark:hover:border-amber-400 dark:hover:text-amber-300" href="{{ route('frontend.contact') }}">Contact & directions</a>
        </div>
    </div>
</section>
@endsection

@push('scripts')
    <script src="{{ asset('frontend-asset/assets/js/faq.js') }}" defer></script>
@endpush
