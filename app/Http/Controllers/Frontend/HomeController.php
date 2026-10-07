<?php

namespace App\Http\Controllers\Frontend;

use App\Http\Controllers\Controller;
use App\Models\Business;
use App\Models\Menu;
use App\Models\MenuItem;
use App\Models\Page;
use App\Models\Review;
use App\Models\SeoSetting;
use App\Services\HomeFallbackService;
use App\Services\PageUrlService;
use App\Services\SeoService;
use App\Services\StructuredDataService;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Http\Request;
use Illuminate\Support\Collection;
use Illuminate\View\View;

class HomeController extends Controller
{
    public function index(
        Request $request,
        SeoService $seoService,
        StructuredDataService $structuredDataService,
        PageUrlService $pageUrlService,
        HomeFallbackService $homeFallbackService
    ): View
    {
        $fallback = $homeFallbackService->data();
        $page = Page::published()
            ->where('is_home', true)
            ->with([
                'sections' => fn ($query) => $query->active()
                    ->with([
                        'mediaAttachments.media',
                        'items' => fn ($query) => $query->active()->with('mediaAttachments.media'),
                    ]),
                'mediaAttachments.media',
            ])
            ->first()
            ?: Page::published()
                ->where('slug', 'home')
                ->with([
                    'sections' => fn ($query) => $query->active()
                        ->with([
                            'mediaAttachments.media',
                            'items' => fn ($query) => $query->active()->with('mediaAttachments.media'),
                        ]),
                    'mediaAttachments.media',
                ])
                ->first();

        $business = Business::with([
            'mediaAttachments.media',
            'locations' => fn ($query) => $query->where('is_active', true)->with('businessHours')->orderByDesc('is_primary')->orderBy('id'),
            'socialLinks' => fn ($query) => $query->where('is_active', true)->orderBy('sort_order')->orderBy('id'),
        ])->where('is_active', true)->first();

        $location = $business?->locations->first();
        $menus = [
            'header' => $this->menu('header', $pageUrlService),
            'mobile' => $this->menu('mobile', $pageUrlService),
            'footer_primary' => $this->menu('footer_primary', $pageUrlService),
            'footer_secondary' => $this->menu('footer_secondary', $pageUrlService),
        ];
        $menus['header'] = $menus['header']->isNotEmpty() ? $menus['header'] : collect($fallback['nav']);
        $menus['mobile'] = $menus['mobile']->isNotEmpty()
            ? $menus['mobile']
            : ($this->menu('header', $pageUrlService)->isNotEmpty() ? $menus['header'] : collect([...$fallback['nav'], ...$fallback['mobile_nav_extra']]));
        $menus['footer_primary'] = $menus['footer_primary']->isNotEmpty() ? $menus['footer_primary'] : collect($fallback['footer_primary']);
        $menus['footer_secondary'] = $menus['footer_secondary']->isNotEmpty() ? $menus['footer_secondary'] : collect($fallback['footer_secondary']);

        $reviews = Review::active()
            ->featured()
            ->with('mediaAttachments.media')
            ->orderBy('sort_order')
            ->orderByDesc('reviewed_at')
            ->orderBy('id')
            ->limit(6)
            ->get()
            ->map(fn (Review $review) => [
                'author' => $review->author_name,
                'rating' => $review->rating,
                'text' => $review->review_text,
                'url' => $pageUrlService->safe($review->review_url),
                'source' => $review->source,
                'avatar' => $this->mediaUrl($review, 'avatar'),
            ]);

        $seo = $seoService->resolve($page, $request);
        $seo = array_merge($seo, [
            'title' => $seo['title'] ?: '24/7 IN N OUT Convenience Store',
            'description' => $seo['description'] ?: $fallback['hero']['description'],
            'canonical' => $seo['canonical'] ?: $request->url(),
            'og_title' => $seo['og_title'] ?: '24/7 IN N OUT Convenience Store',
            'og_description' => $seo['og_description'] ?: $fallback['hero']['description'],
        ]);
        $settings = SeoSetting::current();
        $canonicalRoot = rtrim($settings->canonical_base_url ?: config('app.url'), '/');
        $structuredData = $settings->structured_data_enabled
            ? array_values(array_filter([
                $structuredDataService->localBusiness(),
                $structuredDataService->website(),
                $structuredDataService->breadcrumb([
                    ['name' => 'Home', 'url' => $seo['canonical'] ?: $canonicalRoot],
                ]),
            ]))
            : [];

        return view('frontend.home', [
            'page' => $page,
            'sections' => $this->sections($page),
            'business' => $this->businessData($business, $pageUrlService, $fallback),
            'location' => $this->locationData($location, $pageUrlService, $fallback),
            'menus' => $menus,
            'reviews' => $reviews,
            'seo' => $seo,
            'seoSettings' => $settings,
            'structuredData' => $structuredData,
            'assetsBase' => asset('frontend-asset'),
            'fallback' => $fallback,
        ]);
    }

    private function menu(string $location, PageUrlService $pageUrlService): Collection
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
            ? $menu->items->map(fn (MenuItem $item) => $this->menuItem($item, $pageUrlService))->filter()->values()
            : collect();
    }

    private function menuItem(MenuItem $item, PageUrlService $pageUrlService): ?array
    {
        if ($item->link_type === MenuItem::LINK_PAGE && ! $this->isPublicPage($item->page)) {
            return null;
        }

        return [
            'label' => $item->label,
            'url' => $pageUrlService->menuItem($item),
            'target' => in_array($item->target, MenuItem::TARGETS, true) ? $item->target : '_self',
            'rel' => $this->rel($item),
            'children' => $item->children
                ->map(fn (MenuItem $child) => $this->menuItem($child, $pageUrlService))
                ->filter()
                ->values(),
        ];
    }

    private function isPublicPage(?Page $page): bool
    {
        if (! $page) {
            return false;
        }

        return $page->status === Page::STATUS_PUBLISHED
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
                $section->frontend_image = $this->mediaUrl($section, 'image', $section->image);
                $section->frontend_background_image = $this->mediaUrl($section, 'background', $section->background_image);
                $section->items->each(function ($item) {
                    $item->frontend_image = $this->mediaUrl($item, 'image', $item->image);
                });

                return $section;
            })
            ->values() ?: collect();
    }

    private function businessData(?Business $business, PageUrlService $pageUrlService, array $fallback): array
    {
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
                'url' => $pageUrlService->safe($link->url),
                'platform' => $link->platform,
            ])->filter(fn ($link) => $link['url'] !== '#')->values() ?: collect(),
        ];
    }

    private function locationData($location, PageUrlService $pageUrlService, array $fallback): array
    {
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
            'map_url' => $pageUrlService->safe($mapUrl),
            'hours' => $location?->businessHours->sortBy('day_of_week')->map(fn ($hour) => [
                'day' => ['Monday', 'Tuesday', 'Wednesday', 'Thursday', 'Friday', 'Saturday', 'Sunday'][$hour->day_of_week - 1] ?? 'Day',
                'text' => $hour->is_closed ? 'Closed' : ($hour->is_24_hours ? 'Open 24 hours' : trim(substr((string) $hour->opens_at, 0, 5).' - '.substr((string) $hour->closes_at, 0, 5))),
            ])->values() ?: collect(),
        ];
    }

    private function mediaUrl(Model $model, string $collection, ?string $fallback = null): ?string
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

        return $attachment?->media?->url ?: $fallback;
    }
}
