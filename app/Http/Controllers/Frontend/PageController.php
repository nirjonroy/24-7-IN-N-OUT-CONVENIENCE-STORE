<?php

namespace App\Http\Controllers\Frontend;

use App\Http\Controllers\Controller;
use App\Models\Page;
use App\Services\FrontendPageService;
use App\Services\PageUrlService;
use Illuminate\Http\Request;
use Illuminate\View\View;

class PageController extends Controller
{
    public function show(string $slug, Request $request, FrontendPageService $pages, PageUrlService $urls): View
    {
        abort_unless($urls->isGenericSlug($slug), 404);

        $page = Page::published()
            ->where('slug', $slug)
            ->with([
                'sections' => fn ($query) => $query->active()
                    ->with([
                        'mediaAttachments.media.variants',
                        'items' => fn ($query) => $query->active()->with('mediaAttachments.media.variants'),
                    ]),
                'mediaAttachments.media.variants',
            ])
            ->firstOrFail();

        return view('frontend.page', $pages->forPage($page, $request));
    }
}
