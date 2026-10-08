<?php

namespace App\Services;

use App\Models\Business;
use App\Models\Faq;
use App\Models\GalleryItem;
use App\Models\Menu;
use App\Models\MenuItem;
use App\Models\Page;
use App\Models\SeoSetting;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Http\Request;
use Illuminate\Support\Collection;

class FrontendPageService
{
    public const RESERVED_SLUGS = [
        'admin', 'login', 'logout', 'register', 'password', 'api', 'storage',
        'build', 'assets', 'sitemap.xml', 'robots.txt', 'favicon.ico',
    ];

    private HomeFallbackService $homeFallbackService;

    private PageUrlService $pageUrlService;

    private SeoService $seoService;

    private StructuredDataService $structuredDataService;

    public function __construct(
        HomeFallbackService $homeFallbackService,
        PageUrlService $pageUrlService,
        SeoService $seoService,
        StructuredDataService $structuredDataService
    ) {
        $this->homeFallbackService = $homeFallbackService;
        $this->pageUrlService = $pageUrlService;
        $this->seoService = $seoService;
        $this->structuredDataService = $structuredDataService;
    }

    public function forSlug(string $slug, Request $request): array
    {
        $fallback = $this->fallback($slug);
        $page = $this->page($slug);
        $business = $this->business();
        $location = $business?->locations->first();
        $menus = $this->menus();
        $seo = $this->seo($page, $request, $fallback);
        $settings = SeoSetting::current();

        return [
            'page' => $page,
            'sections' => $this->sections($page),
            'business' => $this->businessData($business),
            'location' => $this->locationData($location),
            'menus' => $menus,
            'reviews' => collect(),
            'seo' => $seo,
            'seoSettings' => $settings,
            'structuredData' => $settings->structured_data_enabled ? array_values(array_filter([
                $this->structuredDataService->localBusiness(),
                $this->structuredDataService->website(),
                $this->structuredDataService->breadcrumb([
                    ['name' => 'Home', 'url' => route('home')],
                    ['name' => $fallback['title'], 'url' => $seo['canonical'] ?? $request->url()],
                ]),
            ])) : [],
            'assetsBase' => asset('frontend-asset'),
            'fallback' => $this->homeFallbackService->data(),
            'pageFallback' => $fallback,
            'faqs' => $slug === 'faq' ? $this->faqs() : collect(),
            'galleryItems' => $slug === 'gallery' ? $this->galleryItems() : collect(),
        ];
    }

    private function page(string $slug): ?Page
    {
        return Page::published()
            ->where('slug', $slug)
            ->with([
                'sections' => fn ($query) => $query->active()
                    ->with([
                        'mediaAttachments.media.variants',
                        'items' => fn ($query) => $query->active()->with('mediaAttachments.media.variants'),
                    ]),
                'mediaAttachments.media.variants',
            ])
            ->first();
    }

    private function business(): ?Business
    {
        return Business::with([
            'mediaAttachments.media.variants',
            'locations' => fn ($query) => $query->where('is_active', true)->with('businessHours')->orderByDesc('is_primary')->orderBy('id'),
            'socialLinks' => fn ($query) => $query->where('is_active', true)->orderBy('sort_order')->orderBy('id'),
        ])->where('is_active', true)->first();
    }

    private function menus(): array
    {
        $fallback = $this->homeFallbackService->data();
        $header = $this->menu('header');
        $mobile = $this->menu('mobile');

        return [
            'header' => $header->isNotEmpty() ? $header : collect($fallback['nav']),
            'mobile' => $mobile->isNotEmpty() ? $mobile : ($header->isNotEmpty() ? $header : collect([...$fallback['nav'], ...$fallback['mobile_nav_extra']])),
            'footer_primary' => ($footer = $this->menu('footer_primary'))->isNotEmpty() ? $footer : collect($fallback['footer_primary']),
            'footer_secondary' => ($footerSecondary = $this->menu('footer_secondary'))->isNotEmpty() ? $footerSecondary : collect($fallback['footer_secondary']),
        ];
    }

    private function menu(string $location): Collection
    {
        $menu = Menu::active()
            ->location($location)
            ->with([
                'items' => fn ($query) => $query->active()->with([
                    'page',
                    'children' => fn ($query) => $query->active()->with('page'),
                ]),
            ])
            ->orderBy('sort_order')
            ->orderBy('id')
            ->first();

        return $menu
            ? $menu->items->map(fn (MenuItem $item) => $this->menuItem($item))->filter()->values()
            : collect();
    }

    private function menuItem(MenuItem $item): ?array
    {
        if ($item->link_type === MenuItem::LINK_PAGE && ! $this->isPublicPage($item->page)) {
            return null;
        }

        return [
            'label' => $item->label,
            'url' => $this->pageUrlService->menuItem($item),
            'target' => in_array($item->target, MenuItem::TARGETS, true) ? $item->target : '_self',
            'rel' => $this->rel($item),
            'children' => $item->children->map(fn (MenuItem $child) => $this->menuItem($child))->filter()->values(),
        ];
    }

    private function isPublicPage(?Page $page): bool
    {
        return $page
            && $page->status === Page::STATUS_PUBLISHED
            && (! $page->published_at || $page->published_at->lte(now()));
    }

    private function rel(MenuItem $item): string
    {
        $rel = collect(explode(' ', (string) $item->rel))->filter()->values();

        if ($item->target === '_blank') {
            $rel = $rel->merge(['noopener', 'noreferrer']);
        }

        return $rel->unique()->implode(' ');
    }

    private function sections(?Page $page): Collection
    {
        return $page?->sections
            ->map(function ($section) {
                $section->frontend_image = $this->mediaUrl($section, 'image', $section->image, 'large');
                $section->frontend_background_image = $this->mediaUrl($section, 'background', $section->background_image, 'large');
                $section->items->each(function ($item) {
                    $item->frontend_image = $this->mediaUrl($item, 'image', $item->image, 'medium');
                });

                return $section;
            })
            ->values() ?: collect();
    }

    private function businessData(?Business $business): array
    {
        $fallback = $this->homeFallbackService->data();

        return [
            'name' => $business?->name ?: $fallback['brand']['name'],
            'short_name' => $business?->short_name ?: $fallback['brand']['short_name'],
            'tagline' => $business?->tagline ?: $fallback['brand']['tagline'],
            'description' => $business?->description ?: $fallback['brand']['description'],
            'logo' => $business ? $this->mediaUrl($business, 'logo', $fallback['brand']['logo']) : $fallback['brand']['logo'],
            'adult_notice' => $business?->adult_retail_notice ?: $fallback['brand']['adult_notice'],
            'minimum_age' => $business?->minimum_age,
            'social_links' => $business?->socialLinks->map(fn ($link) => [
                'label' => $link->label ?: ucfirst($link->platform),
                'url' => $this->pageUrlService->safe($link->url),
                'platform' => $link->platform,
            ])->filter(fn ($link) => $link['url'] !== '#')->values() ?: collect(),
        ];
    }

    private function locationData($location): array
    {
        $fallback = $this->homeFallbackService->data();
        $address = $location
            ? collect([$location->address_line_1, $location->address_line_2, $location->city, $location->state, $location->postal_code])->filter()->implode(', ')
            : $fallback['location']['address'];
        $mapUrl = $location?->directions_url ?: $location?->google_maps_url ?: $fallback['location']['map_url'];

        return [
            'name' => $location?->name ?: $fallback['location']['name'],
            'address' => $address,
            'city_line' => $location ? collect([$location->city, trim(($location->state ?: '').' '.($location->postal_code ?: ''))])->filter()->implode(', ') : $fallback['location']['city_line'],
            'badge' => $location ? collect([$location->city, $location->state])->filter()->implode(', ') : $fallback['location']['badge'],
            'phone' => $location?->phone,
            'email' => $location?->email,
            'map_url' => $this->pageUrlService->safe($mapUrl),
            'hours' => $location?->businessHours->sortBy('day_of_week')->map(fn ($hour) => [
                'day' => ['Monday', 'Tuesday', 'Wednesday', 'Thursday', 'Friday', 'Saturday', 'Sunday'][$hour->day_of_week - 1] ?? 'Day',
                'text' => $hour->is_closed ? 'Closed' : ($hour->is_24_hours ? 'Open 24 hours' : trim(substr((string) $hour->opens_at, 0, 5).' - '.substr((string) $hour->closes_at, 0, 5))),
            ])->values() ?: collect(),
        ];
    }

    private function seo(?Page $page, Request $request, array $fallback): array
    {
        $seo = $this->seoService->resolve($page, $request);

        return array_merge($seo, [
            'title' => $seo['title'] ?: $fallback['seo_title'],
            'description' => $seo['description'] ?: $fallback['seo_description'],
            'canonical' => $seo['canonical'] ?: $request->url(),
            'og_title' => $seo['og_title'] ?: $fallback['seo_title'],
            'og_description' => $seo['og_description'] ?: $fallback['seo_description'],
        ]);
    }

    private function faqs(): Collection
    {
        return Faq::active()->orderBy('sort_order')->orderBy('id')->limit(12)->get();
    }

    private function galleryItems(): Collection
    {
        return GalleryItem::active()->with('mediaAttachments.media')->orderBy('sort_order')->orderBy('id')->limit(12)->get()
            ->map(fn (GalleryItem $item) => [
                'title' => $item->title,
                'caption' => $item->caption ?: $item->title,
                'image' => $this->mediaUrl($item, 'image'),
                'alt' => $item->title,
            ])->filter(fn ($item) => $item['image'])->values();
    }

    private function mediaUrl(Model $model, string $collection, ?string $fallback = null, ?string $variant = null): ?string
    {
        if (! $model->relationLoaded('mediaAttachments')) {
            return $fallback;
        }

        $attachment = $model->mediaAttachments
            ->where('collection', $collection)
            ->sortBy([
                ['is_primary', 'desc'],
                ['sort_order', 'asc'],
                ['id', 'asc'],
            ])
            ->first();

        if (! $attachment?->media?->is_active) {
            return $fallback;
        }

        return $variant ? $attachment->media->getVariantUrl($variant) : $attachment->media->url;
    }

    private function fallback(string $slug): array
    {
        return $this->fallbacks()[$slug] ?? $this->fallbacks()['about'];
    }

    private function fallbacks(): array
    {
        $map = $this->homeFallbackService->data()['location']['map_url'];

        return [
            'about' => [
                'slug' => 'about',
                'title' => 'About',
                'eyebrow' => 'About the business',
                'h1' => 'One Oxon Hill location, four clearly defined categories.',
                'intro' => '24/7 IN N OUT is listed at 6168 Oxon Hill Rd in Oxon Hill, Maryland. Its public-facing name combines the business’s main convenience-store identity with phone repair, smoothies and adult-only vape retail.',
                'description_two' => 'The website follows the same structure: each category gets a focused page, while the location and business identity remain consistent throughout the site.',
                'seo_title' => 'About 24/7 IN N OUT',
                'seo_description' => 'Learn about 24/7 IN N OUT in Oxon Hill, Maryland.',
                'image' => asset('frontend-asset/assets/images/storefront-google.webp'),
                'image_alt' => 'Storefront of the convenience store at 6168 Oxon Hill Rd',
                'button_label' => 'Visit the location',
                'button_url' => $map,
                'identity' => [
                    'eyebrow' => 'Business identity',
                    'title' => 'Clear local positioning without invented claims.',
                    'description' => 'The site uses only information supported by the current Google Business Profile snapshot and the services included in the business name.',
                    'cards' => [
                        ['label' => 'Primary profile category', 'title' => 'Convenience store'],
                        ['label' => 'Named service', 'title' => 'Phone repair'],
                        ['label' => 'Named offering', 'title' => 'Smoothies'],
                        ['label' => 'Adult-only category', 'title' => 'Vape/tobacco retail'],
                    ],
                ],
            ],
            'convenience-store' => [
                'slug' => 'convenience-store',
                'title' => 'Store',
                'eyebrow' => 'Convenience store • Oxon Hill',
                'h1' => 'Your local convenience stop on Oxon Hill Rd.',
                'intro' => 'The Google Business Profile identifies 24/7 IN N OUT as a convenience store at 6168 Oxon Hill Rd. This page gives that core service a clear, search-friendly home on the website.',
                'seo_title' => 'Convenience Store in Oxon Hill',
                'seo_description' => 'Visit 24/7 IN N OUT for convenience-store shopping at 6168 Oxon Hill Rd.',
                'image' => asset('frontend-asset/assets/images/convenience-illustration.svg'),
                'image_alt' => 'Illustration of a neighborhood convenience store',
                'gradient' => 'from-amber-50 dark:from-amber-950/10',
                'border' => 'border-amber-100',
                'button_label' => 'Get directions',
                'button_url' => $map,
                'secondary_label' => 'Store information',
                'secondary_url' => route('frontend.contact'),
                'sections' => [
                    ['eyebrow' => 'What to expect', 'title' => 'A simple in-and-out retail experience.', 'description' => 'The store’s main public category is convenience retail, supported by the other services available at the same address.'],
                ],
                'cards' => [
                    ['title' => 'Grab-and-go shopping', 'description' => 'Convenience-store shopping for customers who want a quick local stop. Exact product inventory can change, so in-store selection is the source of truth.', 'icon' => 'bag'],
                    ['title' => 'Phone repair in store', 'description' => 'The same business also advertises phone repair, helping customers handle a device issue while visiting the store.', 'icon' => 'phone'],
                    ['title' => 'Smoothies available', 'description' => 'Smoothies are also listed as part of the business offering. Visit the store for the current selection.', 'icon' => 'smoothie'],
                ],
            ],
            'phone-repair' => [
                'slug' => 'phone-repair',
                'title' => 'Phone Repair',
                'eyebrow' => 'Phone repair • Oxon Hill',
                'h1' => 'Phone repair at 6168 Oxon Hill Rd.',
                'intro' => 'Phone repair is one of the services named directly in the business profile. Bring your device to the store for an assessment and information about repair availability.',
                'seo_title' => 'Phone Repair in Oxon Hill',
                'seo_description' => 'Phone repair information for 24/7 IN N OUT at 6168 Oxon Hill Rd.',
                'image' => asset('frontend-asset/assets/images/phone-repair-illustration.svg'),
                'image_alt' => 'Illustration of phone repair tools and a smartphone',
                'gradient' => 'from-blue-50 dark:from-blue-950/15',
                'border' => 'border-blue-100',
                'button_label' => 'Directions to the repair counter',
                'button_url' => $map,
                'secondary_label' => 'Contact & location',
                'secondary_url' => route('frontend.contact'),
                'steps' => [
                    ['title' => 'Bring your device', 'description' => 'Bring the phone, and if useful, the charger or accessory connected to the problem.'],
                    ['title' => 'Request an assessment', 'description' => 'Ask the repair counter to confirm the issue, parts availability, estimated cost and expected timing before work begins.'],
                    ['title' => 'Confirm repair terms', 'description' => 'Confirm any warranty or return terms offered for the specific repair before authorizing service.'],
                ],
                'problems' => ['Screen or glass damage', 'Battery or charging issues', 'Speaker or microphone issues', 'General device diagnostics'],
            ],
            'smoothies' => [
                'slug' => 'smoothies',
                'title' => 'Smoothies',
                'eyebrow' => 'Smoothies • Oxon Hill',
                'h1' => 'Smoothies at 24/7 IN N OUT in Oxon Hill.',
                'intro' => 'Smoothies are part of the business offering listed in the store name. Visit 6168 Oxon Hill Rd for the current in-store selection and availability.',
                'seo_title' => 'Smoothies in Oxon Hill',
                'seo_description' => 'Smoothie information for 24/7 IN N OUT in Oxon Hill.',
                'image' => asset('frontend-asset/assets/images/smoothie-illustration.svg'),
                'image_alt' => 'Illustration of a fruit smoothie with fresh fruit',
                'gradient' => 'from-rose-50 dark:from-rose-950/15',
                'border' => 'border-rose-100',
                'button_label' => 'Get directions',
                'button_url' => $map,
                'secondary_label' => 'View gallery',
                'secondary_url' => route('frontend.gallery'),
            ],
            'vape-tobacco' => [
                'slug' => 'vape-tobacco',
                'title' => '21+ Vape',
                'eyebrow' => 'Adults 21+ only',
                'h1' => 'Adult-only vape and tobacco retail information.',
                'intro' => 'The business name lists vape retail as one of the in-store categories. This page is intentionally informational and does not promote individual nicotine or tobacco products.',
                'seo_title' => 'Adult-only Vape and Tobacco Retail Information',
                'seo_description' => 'Adult-only vape and tobacco retail information for 24/7 IN N OUT.',
                'image' => asset('frontend-asset/assets/images/adult-21-illustration.svg'),
                'image_alt' => 'Age verification illustration displaying 21 plus',
                'button_label' => 'Store directions',
                'button_url' => $map,
                'secondary_label' => 'FDA Tobacco 21',
                'secondary_url' => 'https://www.fda.gov/tobacco-products/retail-sales-tobacco-products/tobacco-21',
            ],
            'faq' => [
                'slug' => 'faq',
                'title' => 'FAQ',
                'eyebrow' => 'Frequently asked questions',
                'h1' => 'Questions about the Oxon Hill store.',
                'intro' => 'Straightforward answers based on the current business profile and the services named publicly by the store.',
                'seo_title' => 'FAQ | 24/7 IN N OUT',
                'seo_description' => 'Frequently asked questions about 24/7 IN N OUT in Oxon Hill.',
                'questions' => [
                    ['question' => 'Where is 24/7 IN N OUT located?', 'answer' => 'The business is listed at 6168 Oxon Hill Rd, Oxon Hill, MD 20745, United States.'],
                    ['question' => 'What does the business offer?', 'answer' => 'The public business name identifies four categories: convenience store, vape, phone repair and smoothies. Vape/tobacco retail is adult-only.'],
                    ['question' => 'Is phone repair available at this location?', 'answer' => 'Yes. Phone repair is included directly in the business name. For a specific device issue, visit the counter so the store can confirm repair availability, price and timing.'],
                    ['question' => 'Does the store offer smoothies?', 'answer' => 'Yes. Smoothies are included in the business name. Current flavors, prices and availability should be confirmed in store.'],
                    ['question' => 'What is the minimum age for tobacco or vape purchases?', 'answer' => 'Federal law prohibits tobacco and e-cigarette sales to anyone under 21. FDA rules require photo-ID checks for customers under 30 who attempt to purchase tobacco products.'],
                    ['question' => 'Where can I find current hours or the latest contact details?', 'answer' => 'Use the current Google Business Profile for live business information such as hours, phone details and profile updates. The website intentionally does not invent or hard-code information that has not been verified.'],
                ],
            ],
            'gallery' => [
                'slug' => 'gallery',
                'title' => 'Gallery',
                'eyebrow' => 'Gallery',
                'h1' => 'A visual look at the location and service categories.',
                'intro' => 'The storefront image comes from the business-profile screenshot provided for this project. The remaining graphics are lightweight service illustrations used until original in-store photography is supplied.',
                'seo_title' => 'Gallery | 24/7 IN N OUT',
                'seo_description' => 'View 24/7 IN N OUT storefront and service category visuals.',
                'items' => [
                    ['title' => 'Oxon Hill storefront', 'image' => asset('frontend-asset/assets/images/storefront-google.webp'), 'alt' => '24/7 IN N OUT storefront in Oxon Hill', 'ratio' => 'aspect-[4/3]', 'width' => 1424, 'height' => 960],
                    ['title' => 'Convenience retail', 'image' => asset('frontend-asset/assets/images/convenience-illustration.svg'), 'alt' => 'Convenience store service illustration', 'ratio' => 'aspect-[3/2]', 'width' => 720, 'height' => 480],
                    ['title' => 'Phone repair', 'image' => asset('frontend-asset/assets/images/phone-repair-illustration.svg'), 'alt' => 'Phone repair service illustration', 'ratio' => 'aspect-[3/2]', 'width' => 720, 'height' => 480],
                    ['title' => 'Smoothies', 'image' => asset('frontend-asset/assets/images/smoothie-illustration.svg'), 'alt' => 'Smoothie service illustration', 'ratio' => 'aspect-[3/2]', 'width' => 720, 'height' => 480],
                ],
            ],
            'contact' => [
                'slug' => 'contact',
                'title' => 'Contact',
                'eyebrow' => 'Contact & directions',
                'h1' => 'Visit 24/7 IN N OUT in Oxon Hill.',
                'intro' => 'The verified location is 6168 Oxon Hill Rd, Oxon Hill, MD 20745. For live hours, phone details and the latest profile updates, use the Google Business Profile link below.',
                'seo_title' => 'Contact & Directions | 24/7 IN N OUT',
                'seo_description' => 'Contact and directions for 24/7 IN N OUT in Oxon Hill.',
                'map_embed' => 'https://www.google.com/maps?q=6168%20Oxon%20Hill%20Rd%2C%20Oxon%20Hill%2C%20MD%2020745&output=embed',
            ],
        ];
    }
}
