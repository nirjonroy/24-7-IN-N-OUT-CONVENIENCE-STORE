@extends('frontend.layouts.app')

@section('content')
<section class="grid min-h-[68vh] place-items-center px-4 py-16 sm:px-6 lg:px-8">
    <div class="mx-auto max-w-xl text-center">
        <img src="{{ asset('frontend-asset/assets/images/logo-mark.svg') }}" alt="" class="mx-auto h-16 w-16">
        <h1 class="mt-6 text-5xl font-extrabold tracking-tight text-slate-950 dark:text-white">Page not found</h1>
        <p class="mt-4 leading-7 text-slate-600 dark:text-slate-300">The page you requested does not exist or has moved.</p>
        <div class="mt-7 flex flex-wrap justify-center gap-3">
            <a href="{{ route('home') }}" class="inline-flex rounded-xl bg-red-700 px-5 py-3 text-sm font-extrabold text-white dark:bg-amber-400 dark:text-slate-950">Back to Home</a>
            <a href="{{ route('frontend.convenience-store') }}" class="inline-flex rounded-xl border border-slate-300 px-5 py-3 text-sm font-extrabold text-slate-900 dark:border-slate-700 dark:text-white">Convenience Store</a>
            <a href="{{ route('frontend.phone-repair') }}" class="inline-flex rounded-xl border border-slate-300 px-5 py-3 text-sm font-extrabold text-slate-900 dark:border-slate-700 dark:text-white">Phone Repair</a>
            <a href="{{ route('frontend.contact') }}" class="inline-flex rounded-xl border border-slate-300 px-5 py-3 text-sm font-extrabold text-slate-900 dark:border-slate-700 dark:text-white">Contact</a>
        </div>
    </div>
</section>
@endsection
