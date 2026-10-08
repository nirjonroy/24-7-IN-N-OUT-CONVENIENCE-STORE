<?php

namespace App\Services;

use App\Models\ContactInfo;
use Illuminate\Support\Str;

class FrontendContactService
{
    public function __construct(private PageUrlService $urls)
    {
    }

    public function data(array $pageData): array
    {
        $contactInfo = ContactInfo::query()->where('status', true)->first();
        $fallback = $pageData['pageFallback'];
        $location = $pageData['location'];

        return [
            'info' => $contactInfo,
            'section_label' => $contactInfo?->eyebrow ?: $fallback['eyebrow'],
            'title' => $contactInfo?->title ?: $fallback['h1'],
            'description' => $contactInfo?->description ?: $fallback['intro'],
            'address_title' => $contactInfo?->address_title ?: 'Store address',
            'address' => $contactInfo?->address ?: $this->address($location),
            'directions_text' => $contactInfo?->google_map_text ?: 'Google Maps',
            'directions_url' => $this->safeUrl($contactInfo?->google_map_url ?: $location['map_url']),
            'business_details_title' => $contactInfo?->business_details_title ?: 'Live business details',
            'business_details_description' => $contactInfo?->business_details_description ?: 'Use Google for current hours, the latest contact number, reviews and business-profile updates.',
            'business_profile_button_text' => $contactInfo?->business_profile_button_text ?: 'Open Google profile',
            'business_profile_url' => $this->safeUrl($contactInfo?->business_profile_url ?: 'https://www.google.com/search?q=24%2F7+IN+N+OUT+CONVENIENCE+STORE+Oxon+Hill+MD'),
            'map_embed_url' => $this->safeMapEmbed($contactInfo?->map_embed_url ?: ($fallback['map_embed'] ?? null)),
            'form_section_label' => $contactInfo?->form_eyebrow ?: 'Website inquiry form',
            'form_title' => $contactInfo?->form_title ?: 'Send a message to the store.',
            'form_description' => $contactInfo?->form_description ?: 'Use the form below for website inquiries. We will route the message to the business contact configured for the site.',
            'phone' => $location['phone'] ?? null,
            'email' => $location['email'] ?? null,
            'hours' => $location['hours'] ?? collect(),
            'special_hours' => $location['special_hours'] ?? collect(),
            'social_links' => $pageData['business']['social_links'] ?? collect(),
            'recipient_email' => $this->recipientEmail($contactInfo, $location),
        ];
    }

    public function recipientEmail(?ContactInfo $contactInfo, array $location): ?string
    {
        return collect([
            $contactInfo?->recipient_email,
            $location['email'] ?? null,
            config('mail.from.address'),
        ])
            ->filter(fn ($email) => is_string($email) && filter_var($email, FILTER_VALIDATE_EMAIL))
            ->first();
    }

    private function address(array $location): string
    {
        return Str::of($location['address'] ?? '6168 Oxon Hill Rd, Oxon Hill, MD 20745')
            ->replaceMatches('/,\s*United States$/', '')
            ->append(', United States')
            ->toString();
    }

    private function safeUrl(?string $url): string
    {
        return $this->urls->safe($url);
    }

    private function safeMapEmbed(?string $url): ?string
    {
        if (! is_string($url) || trim($url) === '') {
            return null;
        }

        $url = trim($url);

        if (! filter_var($url, FILTER_VALIDATE_URL)) {
            return null;
        }

        $host = parse_url($url, PHP_URL_HOST);

        if (! $host || ! Str::endsWith($host, ['google.com', 'googleusercontent.com'])) {
            return null;
        }

        return $url;
    }
}
