@extends('frontend.layouts.app')

@section('content')
@php
    $contact = $contactContent;
    $topics = \App\Http\Requests\StoreContactRequest::TOPICS;
@endphp

<section class="py-16">
    <div class="mx-auto max-w-7xl px-4 sm:px-6 lg:px-8">
        <div class="max-w-4xl">
            <span class="text-sm font-extrabold uppercase tracking-[0.18em] text-red-700 dark:text-amber-300">{{ $contact['section_label'] }}</span>
            <h1 class="mt-4 text-4xl font-extrabold tracking-tight text-slate-950 sm:text-5xl dark:text-white">{{ $page?->h1 ?: $contact['title'] }}</h1>
            <p class="mt-5 text-lg leading-8 text-slate-600 dark:text-slate-300">{{ $page?->intro_text ?: $contact['description'] }}</p>
        </div>

        <div class="mt-12 grid gap-8 lg:grid-cols-[.85fr_1.15fr]">
            <div class="space-y-5">
                <div class="rounded-3xl border border-slate-200 p-6 dark:border-slate-800">
                    <span class="text-red-700 dark:text-amber-300"><svg class="h-6 w-6" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" d="M12 21s7-4.35 7-11a7 7 0 1 0-14 0c0 6.65 7 11 7 11Z"/><circle cx="12" cy="10" r="2.5"/></svg></span>
                    <span role="heading" aria-level="2" class="mt-4 block text-xl font-extrabold">{{ $contact['address_title'] }}</span>
                    <p class="mt-3 text-sm leading-7 text-slate-600 dark:text-slate-300">{{ $contact['address'] }}</p>
                    @if($contact['phone'])
                        <p class="mt-3 text-sm leading-7 text-slate-600 dark:text-slate-300"><strong>Phone:</strong> <a class="hover:text-red-700 dark:hover:text-amber-300" href="tel:{{ preg_replace('/[^0-9+]/', '', $contact['phone']) }}">{{ $contact['phone'] }}</a></p>
                    @endif
                    @if($contact['email'])
                        <p class="mt-1 text-sm leading-7 text-slate-600 dark:text-slate-300"><strong>Email:</strong> <a class="hover:text-red-700 dark:hover:text-amber-300" href="mailto:{{ $contact['email'] }}">{{ $contact['email'] }}</a></p>
                    @endif
                    @if($contact['directions_url'] !== '#')
                        <a href="{{ $contact['directions_url'] }}" target="_blank" rel="noopener" class="mt-4 inline-flex items-center gap-2 text-sm font-extrabold text-red-700 dark:text-amber-300">{{ $contact['directions_text'] }} <svg class="h-4 w-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" d="M14 4h6v6M20 4l-9 9"/><path stroke-linecap="round" stroke-linejoin="round" d="M18 13v6a1 1 0 0 1-1 1H5a1 1 0 0 1-1-1V7a1 1 0 0 1 1-1h6"/></svg></a>
                    @endif
                </div>

                <div class="rounded-3xl border border-slate-200 p-6 dark:border-slate-800">
                    <span class="text-red-700 dark:text-amber-300"><svg class="h-6 w-6" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" d="M14 4h6v6M20 4l-9 9"/><path stroke-linecap="round" stroke-linejoin="round" d="M18 13v6a1 1 0 0 1-1 1H5a1 1 0 0 1-1-1V7a1 1 0 0 1 1-1h6"/></svg></span>
                    <span role="heading" aria-level="2" class="mt-4 block text-xl font-extrabold">{{ $contact['business_details_title'] }}</span>
                    <p class="mt-3 text-sm leading-7 text-slate-600 dark:text-slate-300">{{ $contact['business_details_description'] }}</p>
                    @if($contact['hours']->isNotEmpty())
                        <div class="mt-4 grid gap-1 text-sm text-slate-600 dark:text-slate-300">
                            @foreach($contact['hours'] as $hour)
                                <p><strong>{{ $hour['day'] }}:</strong> {{ $hour['text'] }}</p>
                            @endforeach
                        </div>
                    @endif
                    @if($contact['special_hours']->isNotEmpty())
                        <div class="mt-4 rounded-2xl bg-slate-50 p-4 text-sm text-slate-600 ring-1 ring-slate-200 dark:bg-slate-900 dark:text-slate-300 dark:ring-slate-800">
                            <span role="heading" aria-level="3" class="block font-extrabold text-slate-950 dark:text-white">Upcoming special hours</span>
                            @foreach($contact['special_hours'] as $hour)
                                <p class="mt-2"><strong>{{ $hour['date'] }}:</strong> {{ $hour['text'] }} @if($hour['note'])- {{ $hour['note'] }}@endif</p>
                            @endforeach
                        </div>
                    @endif
                    @if($contact['business_profile_url'] !== '#')
                        <a href="{{ $contact['business_profile_url'] }}" target="_blank" rel="noopener" class="mt-4 inline-flex items-center gap-2 text-sm font-extrabold text-red-700 dark:text-amber-300">{{ $contact['business_profile_button_text'] }} <svg class="h-4 w-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" d="M14 4h6v6M20 4l-9 9"/><path stroke-linecap="round" stroke-linejoin="round" d="M18 13v6a1 1 0 0 1-1 1H5a1 1 0 0 1-1-1V7a1 1 0 0 1 1-1h6"/></svg></a>
                    @endif
                    @if($contact['social_links']->isNotEmpty())
                        <div class="mt-4 flex flex-wrap gap-3">
                            @foreach($contact['social_links'] as $social)
                                <a href="{{ $social['url'] }}" target="_blank" rel="noopener" class="text-sm font-extrabold text-red-700 hover:underline dark:text-amber-300">{{ $social['label'] }}</a>
                            @endforeach
                        </div>
                    @endif
                </div>
            </div>

            <div class="overflow-hidden rounded-[2rem] border border-slate-200 bg-slate-100 dark:border-slate-800 dark:bg-slate-900">
                @if($contact['map_embed_url'])
                    <iframe title="Map showing {{ $contact['address'] }}" src="{{ $contact['map_embed_url'] }}" loading="lazy" referrerpolicy="no-referrer-when-downgrade" class="h-[480px] w-full border-0"></iframe>
                @elseif($contact['directions_url'] !== '#')
                    <div class="flex h-[480px] items-center justify-center p-8 text-center">
                        <a href="{{ $contact['directions_url'] }}" target="_blank" rel="noopener" class="inline-flex items-center justify-center gap-2 rounded-xl bg-red-700 px-5 py-3 text-sm font-extrabold text-white shadow-sm transition hover:bg-red-800 focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-red-600 focus-visible:ring-offset-2 dark:bg-amber-400 dark:text-slate-950 dark:hover:bg-amber-300">Get directions</a>
                    </div>
                @endif
            </div>
        </div>
    </div>
</section>

<section class="border-t border-slate-200 bg-slate-50 py-16 dark:border-slate-800 dark:bg-slate-900/50">
    <div class="mx-auto max-w-4xl px-4 sm:px-6 lg:px-8">
        <div class="max-w-3xl">
            <span class="text-sm font-extrabold uppercase tracking-[0.18em] text-red-700 dark:text-amber-300">{{ $contact['form_section_label'] }}</span>
            <span role="heading" aria-level="2" class="mt-3 block text-3xl font-extrabold tracking-tight text-slate-950 sm:text-4xl dark:text-white">{{ $contact['form_title'] }}</span>
            <p class="mt-4 text-base leading-8 text-slate-600 dark:text-slate-300">{{ $contact['form_description'] }}</p>
        </div>

        @if(session('success'))
            <div class="mt-8 rounded-2xl border border-green-200 bg-green-50 p-4 text-sm font-bold text-green-800 dark:border-green-900 dark:bg-green-950/40 dark:text-green-200">{{ session('success') }}</div>
        @endif

        @if($errors->any())
            <div class="mt-8 rounded-2xl border border-red-200 bg-red-50 p-4 text-sm font-bold text-red-800 dark:border-red-900 dark:bg-red-950/40 dark:text-red-200">Please check the highlighted fields and try again.</div>
        @endif

        <form action="{{ route('frontend.contact.store') }}" method="post" class="mt-8 grid gap-5 rounded-[2rem] bg-white p-6 shadow-sm ring-1 ring-slate-200 sm:p-8 dark:bg-slate-950 dark:ring-slate-800" data-contact-form>
            @csrf
            <input type="hidden" name="contact_started_at" value="{{ time() }}">
            <div class="absolute left-[-10000px] top-auto h-px w-px overflow-hidden" aria-hidden="true">
                <label for="contact_company">Company</label>
                <input type="text" name="contact_company" id="contact_company" tabindex="-1" autocomplete="off">
            </div>

            <div class="grid gap-5 sm:grid-cols-2">
                <label for="contact_name" class="grid gap-2 text-sm font-bold">Name <span class="sr-only">required</span>
                    <input id="contact_name" name="name" value="{{ old('name') }}" autocomplete="name" aria-invalid="{{ $errors->has('name') ? 'true' : 'false' }}" @if($errors->has('name')) aria-describedby="contact_name_error" @endif class="rounded-xl border border-slate-300 bg-white px-4 py-3 font-normal outline-none transition focus:border-red-600 focus:ring-2 focus:ring-red-600/20 dark:border-slate-700 dark:bg-slate-900 @error('name') border-red-500 @enderror" placeholder="Your name">
                    @error('name')<span id="contact_name_error" class="text-sm font-semibold text-red-700 dark:text-amber-300">{{ $message }}</span>@enderror
                </label>
                <label for="contact_email" class="grid gap-2 text-sm font-bold">Email <span class="sr-only">required</span>
                    <input id="contact_email" type="email" name="email" value="{{ old('email') }}" autocomplete="email" aria-invalid="{{ $errors->has('email') ? 'true' : 'false' }}" @if($errors->has('email')) aria-describedby="contact_email_error" @endif class="rounded-xl border border-slate-300 bg-white px-4 py-3 font-normal outline-none transition focus:border-red-600 focus:ring-2 focus:ring-red-600/20 dark:border-slate-700 dark:bg-slate-900 @error('email') border-red-500 @enderror" placeholder="you@example.com">
                    @error('email')<span id="contact_email_error" class="text-sm font-semibold text-red-700 dark:text-amber-300">{{ $message }}</span>@enderror
                </label>
            </div>

            <div class="grid gap-5 sm:grid-cols-2">
                <label for="contact_phone" class="grid gap-2 text-sm font-bold">Phone
                    <input id="contact_phone" name="phone" value="{{ old('phone') }}" autocomplete="tel" aria-invalid="{{ $errors->has('phone') ? 'true' : 'false' }}" @if($errors->has('phone')) aria-describedby="contact_phone_error" @endif class="rounded-xl border border-slate-300 bg-white px-4 py-3 font-normal outline-none transition focus:border-red-600 focus:ring-2 focus:ring-red-600/20 dark:border-slate-700 dark:bg-slate-900 @error('phone') border-red-500 @enderror" placeholder="Optional phone number">
                    @error('phone')<span id="contact_phone_error" class="text-sm font-semibold text-red-700 dark:text-amber-300">{{ $message }}</span>@enderror
                </label>
                <label for="contact_topic" class="grid gap-2 text-sm font-bold">Topic
                    <select id="contact_topic" name="topic" aria-invalid="{{ $errors->has('topic') ? 'true' : 'false' }}" @if($errors->has('topic')) aria-describedby="contact_topic_error" @endif class="rounded-xl border border-slate-300 bg-white px-4 py-3 font-normal outline-none transition focus:border-red-600 focus:ring-2 focus:ring-red-600/20 dark:border-slate-700 dark:bg-slate-900 @error('topic') border-red-500 @enderror">
                        @foreach($topics as $value => $label)
                            <option value="{{ $value }}" @selected(old('topic', 'general') === $value)>{{ $label }}</option>
                        @endforeach
                    </select>
                    @error('topic')<span id="contact_topic_error" class="text-sm font-semibold text-red-700 dark:text-amber-300">{{ $message }}</span>@enderror
                </label>
            </div>

            <label for="contact_subject" class="grid gap-2 text-sm font-bold">Subject
                <input id="contact_subject" name="subject" value="{{ old('subject') }}" aria-invalid="{{ $errors->has('subject') ? 'true' : 'false' }}" @if($errors->has('subject')) aria-describedby="contact_subject_error" @endif class="rounded-xl border border-slate-300 bg-white px-4 py-3 font-normal outline-none transition focus:border-red-600 focus:ring-2 focus:ring-red-600/20 dark:border-slate-700 dark:bg-slate-900 @error('subject') border-red-500 @enderror" placeholder="Optional subject">
                @error('subject')<span id="contact_subject_error" class="text-sm font-semibold text-red-700 dark:text-amber-300">{{ $message }}</span>@enderror
            </label>

            <label for="contact_message" class="grid gap-2 text-sm font-bold">Message <span class="sr-only">required</span>
                <textarea id="contact_message" name="message" rows="5" aria-invalid="{{ $errors->has('message') ? 'true' : 'false' }}" @if($errors->has('message')) aria-describedby="contact_message_error" @endif class="rounded-xl border border-slate-300 bg-white px-4 py-3 font-normal outline-none transition focus:border-red-600 focus:ring-2 focus:ring-red-600/20 dark:border-slate-700 dark:bg-slate-900 @error('message') border-red-500 @enderror" placeholder="How can we help?">{{ old('message') }}</textarea>
                @error('message')<span id="contact_message_error" class="text-sm font-semibold text-red-700 dark:text-amber-300">{{ $message }}</span>@enderror
            </label>

            <button class="inline-flex w-fit items-center justify-center gap-2 rounded-xl bg-red-700 px-5 py-3 text-sm font-extrabold text-white shadow-sm transition hover:bg-red-800 focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-red-600 focus-visible:ring-offset-2 disabled:opacity-70 dark:bg-amber-400 dark:text-slate-950 dark:hover:bg-amber-300" type="submit" data-contact-submit>Send message</button>
        </form>
    </div>
</section>
@endsection

@push('scripts')
    <script src="{{ asset('frontend-asset/assets/js/contact.js') }}" defer></script>
@endpush
