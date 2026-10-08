<?php

namespace App\Services;

use App\Models\Business;
use App\Models\BusinessHour;
use App\Models\SeoSetting;

class StructuredDataService
{
    private const BUSINESS_TYPES = [
        'LocalBusiness',
        'Store',
        'ConvenienceStore',
        'ElectronicsStore',
        'MobilePhoneStore',
        'FoodEstablishment',
    ];

    public function localBusiness(): ?array
    {
        $settings = SeoSetting::current();

        if (! $settings->structured_data_enabled) {
            return null;
        }

        $business = Business::with(['locations.businessHours', 'socialLinks'])
            ->where('is_active', true)
            ->first();

        if (! $business) {
            return null;
        }

        $location = $business->locations->firstWhere('is_primary', true) ?: $business->locations->first();
        $type = $this->schemaType($business->schema_types);

        $data = [
            '@context' => 'https://schema.org',
            '@type' => $type,
            'name' => $business->name,
            'description' => $business->description,
            'url' => rtrim($settings->canonical_base_url ?: config('app.url'), '/'),
            'priceRange' => $location?->price_range,
        ];

        if ($business->getMediaUrl('logo')) {
            $data['logo'] = $this->absoluteUrl($business->getMediaUrl('logo'), $settings);
        }

        if ($business->getMediaUrl('meta_image')) {
            $data['image'] = $this->absoluteUrl($business->getMediaUrl('meta_image'), $settings);
        }

        if ($location) {
            $data['telephone'] = $location->phone ?: null;
            $data['address'] = $this->address($location);
            $data['geo'] = $this->geo($location);
            $data['openingHoursSpecification'] = $this->openingHours($location->businessHours);
        }

        $sameAs = $business->socialLinks
            ->where('is_active', true)
            ->pluck('url')
            ->filter(fn ($url) => $this->isExternalHttpUrl($url))
            ->values()
            ->all();

        if ($sameAs) {
            $data['sameAs'] = $sameAs;
        }

        return $this->filter($data);
    }

    public function website(): ?array
    {
        $settings = SeoSetting::current();

        if (! $settings->structured_data_enabled) {
            return null;
        }

        return $this->filter([
            '@context' => 'https://schema.org',
            '@type' => 'WebSite',
            'name' => $settings->site_name,
            'url' => rtrim($settings->canonical_base_url ?: config('app.url'), '/'),
        ]);
    }

    public function organization(): ?array
    {
        $settings = SeoSetting::current();

        if (! $settings->structured_data_enabled) {
            return null;
        }

        $business = Business::with('socialLinks')->where('is_active', true)->first();

        if (! $business) {
            return null;
        }

        $sameAs = $business->socialLinks->where('is_active', true)->pluck('url')->filter(fn ($url) => $this->isExternalHttpUrl($url))->values()->all();

        return $this->filter([
            '@context' => 'https://schema.org',
            '@type' => 'Organization',
            'name' => $business->name,
            'url' => rtrim($settings->canonical_base_url ?: config('app.url'), '/'),
            'logo' => $this->absoluteUrl($business->getMediaUrl('logo'), $settings),
            'sameAs' => $sameAs ?: null,
        ]);
    }

    public function breadcrumb(array $items): array
    {
        return [
            '@context' => 'https://schema.org',
            '@type' => 'BreadcrumbList',
            'itemListElement' => collect($items)->values()->map(fn ($item, $index) => $this->filter([
                '@type' => 'ListItem',
                'position' => $index + 1,
                'name' => $item['name'] ?? null,
                'item' => $item['url'] ?? null,
            ]))->all(),
        ];
    }

    private function address($location): ?array
    {
        return $this->filter([
            '@type' => 'PostalAddress',
            'streetAddress' => trim($location->address_line_1.' '.$location->address_line_2) ?: null,
            'addressLocality' => $location->city,
            'addressRegion' => $location->state,
            'postalCode' => $location->postal_code,
            'addressCountry' => $location->country_code,
        ]);
    }

    private function geo($location): ?array
    {
        if (! $location->latitude || ! $location->longitude) {
            return null;
        }

        return [
            '@type' => 'GeoCoordinates',
            'latitude' => (string) $location->latitude,
            'longitude' => (string) $location->longitude,
        ];
    }

    private function openingHours($hours): array
    {
        $days = [1 => 'Monday', 2 => 'Tuesday', 3 => 'Wednesday', 4 => 'Thursday', 5 => 'Friday', 6 => 'Saturday', 7 => 'Sunday'];

        return $hours->reject(fn (BusinessHour $hour) => $hour->is_closed)
            ->map(fn (BusinessHour $hour) => $this->filter([
                '@type' => 'OpeningHoursSpecification',
                'dayOfWeek' => $days[$hour->day_of_week] ?? null,
                'opens' => $hour->is_24_hours ? '00:00' : $this->time($hour->opens_at),
                'closes' => $hour->is_24_hours ? '23:59' : $this->time($hour->closes_at),
            ]))
            ->values()
            ->all();
    }

    private function filter(array $data): ?array
    {
        $filtered = array_filter($data, fn ($value) => $value !== null && $value !== '' && $value !== []);

        return $filtered ?: null;
    }

    private function time(?string $time): ?string
    {
        return $time ? substr($time, 0, 5) : null;
    }

    private function schemaType(?array $types): string
    {
        foreach ($types ?: [] as $type) {
            if (in_array($type, self::BUSINESS_TYPES, true)) {
                return $type;
            }
        }

        return 'ConvenienceStore';
    }

    private function isExternalHttpUrl(?string $url): bool
    {
        if (! filter_var($url, FILTER_VALIDATE_URL)) {
            return false;
        }

        return in_array(parse_url($url, PHP_URL_SCHEME), ['http', 'https'], true);
    }

    private function absoluteUrl(?string $url, SeoSetting $settings): ?string
    {
        if (! $url) {
            return null;
        }

        if (filter_var($url, FILTER_VALIDATE_URL)) {
            return $url;
        }

        if (str_starts_with($url, '/')) {
            return rtrim($settings->canonical_base_url ?: config('app.url'), '/').$url;
        }

        return null;
    }
}
