<?php

namespace App\Services;

class HomeFallbackService
{
    public function data(): array
    {
        return [
            'top_bar_text' => 'Convenience • Repair • Smoothies • 21+ retail',
            'brand' => [
                'name' => '24/7 IN N OUT',
                'short_name' => '24/7 IN N OUT',
                'tagline' => 'Oxon Hill, Maryland',
                'description' => 'A local Oxon Hill business bringing convenience-store shopping, phone repair, smoothies and adult-only vape/tobacco retail together at one address.',
                'logo' => asset('frontend-asset/assets/images/logo-mark.svg'),
                'adult_notice' => 'Adult vape/tobacco products: 21+ only. Valid ID requirements apply.',
            ],
            'location' => [
                'name' => '6168 Oxon Hill Rd',
                'address' => '6168 Oxon Hill Rd, Oxon Hill, MD 20745',
                'city_line' => 'Oxon Hill, MD 20745',
                'badge' => 'Oxon Hill, Maryland',
                'map_url' => 'https://www.google.com/maps/search/?api=1&query=24%2F7+IN+N+OUT+CONVENIENCE+STORE+6168+Oxon+Hill+Rd+Oxon+Hill+MD+20745',
                'phone' => null,
                'email' => null,
            ],
            'nav' => [
                ['label' => 'Home', 'url' => route('home'), 'target' => '_self', 'rel' => '', 'children' => collect()],
                ['label' => 'Store', 'url' => url('/convenience-store'), 'target' => '_self', 'rel' => '', 'children' => collect()],
                ['label' => 'Phone Repair', 'url' => url('/phone-repair'), 'target' => '_self', 'rel' => '', 'children' => collect()],
                ['label' => 'Smoothies', 'url' => url('/smoothies'), 'target' => '_self', 'rel' => '', 'children' => collect()],
                ['label' => '21+ Vape', 'url' => url('/vape-tobacco'), 'target' => '_self', 'rel' => '', 'children' => collect()],
                ['label' => 'About', 'url' => url('/about'), 'target' => '_self', 'rel' => '', 'children' => collect()],
                ['label' => 'Contact', 'url' => url('/contact'), 'target' => '_self', 'rel' => '', 'children' => collect()],
            ],
            'mobile_nav_extra' => [
                ['label' => 'Gallery', 'url' => url('/gallery'), 'target' => '_self', 'rel' => '', 'children' => collect()],
                ['label' => 'FAQ', 'url' => url('/faq'), 'target' => '_self', 'rel' => '', 'children' => collect()],
            ],
            'footer_primary' => [
                ['label' => 'Convenience store', 'url' => url('/convenience-store'), 'target' => '_self', 'rel' => '', 'children' => collect()],
                ['label' => 'Phone repair', 'url' => url('/phone-repair'), 'target' => '_self', 'rel' => '', 'children' => collect()],
                ['label' => 'Smoothies', 'url' => url('/smoothies'), 'target' => '_self', 'rel' => '', 'children' => collect()],
                ['label' => '21+ vape/tobacco', 'url' => url('/vape-tobacco'), 'target' => '_self', 'rel' => '', 'children' => collect()],
            ],
            'footer_secondary' => [
                ['label' => 'About', 'url' => url('/about'), 'target' => '_self', 'rel' => '', 'children' => collect()],
                ['label' => 'Gallery', 'url' => url('/gallery'), 'target' => '_self', 'rel' => '', 'children' => collect()],
                ['label' => 'FAQ', 'url' => url('/faq'), 'target' => '_self', 'rel' => '', 'children' => collect()],
                ['label' => 'Contact & directions', 'url' => url('/contact'), 'target' => '_self', 'rel' => '', 'children' => collect()],
            ],
            'hero' => [
                'h1' => 'Convenience, phone repair and smoothies — all at one Oxon Hill stop.',
                'description' => '24/7 IN N OUT brings multiple everyday needs together at 6168 Oxon Hill Rd: a convenience store, phone repair, smoothies and a separate adult-only vape/tobacco retail category.',
                'primary_label' => 'Get directions',
                'secondary_label' => 'Explore phone repair',
                'secondary_url' => url('/phone-repair'),
                'image' => asset('frontend-asset/assets/images/storefront-google.webp'),
                'image_alt' => '24/7 IN N OUT convenience store storefront in Oxon Hill, Maryland',
                'cards' => [
                    ['label' => 'Convenience', 'url' => url('/convenience-store'), 'icon' => 'bag'],
                    ['label' => 'Phone repair', 'url' => url('/phone-repair'), 'icon' => 'phone'],
                    ['label' => 'Smoothies', 'url' => url('/smoothies'), 'icon' => 'smoothie'],
                    ['label' => '21+ retail', 'url' => url('/vape-tobacco'), 'icon' => 'shield'],
                ],
            ],
            'services' => [
                'eyebrow' => 'Four reasons to visit',
                'title' => 'A practical neighborhood stop, built around everyday needs.',
                'description' => 'Instead of sending customers to several locations, the business combines its core categories at one Oxon Hill address.',
                'items' => [
                    ['title' => 'Convenience store', 'description' => 'A local grab-and-go stop for convenience-store shopping at the Oxon Hill Rd location.', 'image' => asset('frontend-asset/assets/images/convenience-illustration.svg'), 'image_alt' => 'Illustration of convenience-store shelves and drinks', 'label' => 'Store details', 'url' => url('/convenience-store')],
                    ['title' => 'Phone repair', 'description' => 'Bring your device to the in-store repair counter for assessment and available repair options.', 'image' => asset('frontend-asset/assets/images/phone-repair-illustration.svg'), 'image_alt' => 'Illustration of a smartphone repair workbench', 'label' => 'Repair information', 'url' => url('/phone-repair')],
                    ['title' => 'Smoothies', 'description' => 'Smoothies are part of the store offering. Current selection and availability can be confirmed in store.', 'image' => asset('frontend-asset/assets/images/smoothie-illustration.svg'), 'image_alt' => 'Illustration of a fruit smoothie', 'label' => 'Smoothie details', 'url' => url('/smoothies')],
                    ['title' => 'Adult-only retail', 'description' => 'The business also lists vape/tobacco retail. Tobacco and nicotine products are restricted to customers age 21+.', 'image' => asset('frontend-asset/assets/images/adult-21-illustration.svg'), 'image_alt' => 'Neutral age-verification illustration showing 21 plus', 'label' => '21+ information', 'url' => url('/vape-tobacco')],
                ],
            ],
            'feature' => [
                'eyebrow' => 'Local SEO focus',
                'title' => 'Easy to find in Oxon Hill.',
                'description' => 'The website structure is intentionally organized around the business’s real location and primary service categories, so customers and search engines can understand each offering clearly.',
                'items' => [
                    ['title' => 'One verified address', 'description' => '6168 Oxon Hill Rd, Oxon Hill, MD 20745.', 'icon' => 'pin'],
                    ['title' => 'Clear service pages', 'description' => 'Dedicated pages for the store, phone repair, smoothies and 21+ retail.', 'icon' => 'check'],
                    ['title' => 'Google profile connection', 'description' => 'Directions and profile links point customers back to the verified business location.', 'icon' => 'external'],
                    ['title' => 'Fast by default', 'description' => 'No frontend framework, minimal JavaScript and a locally compiled Tailwind stylesheet.', 'icon' => 'star'],
                ],
            ],
            'cta' => [
                'eyebrow' => 'Visit the store',
                'title' => 'Find 24/7 IN N OUT on Oxon Hill Rd.',
                'description' => 'Use Google Maps for current directions, traffic and business-profile updates.',
                'primary_label' => 'Open directions',
                'secondary_label' => 'Contact page',
                'secondary_url' => url('/contact'),
            ],
        ];
    }
}
