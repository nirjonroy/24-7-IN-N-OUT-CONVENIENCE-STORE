<?php

namespace App\Http\Controllers\Frontend;

use App\Http\Controllers\Controller;
use App\Services\FrontendCatalogService;
use App\Services\FrontendPageService;
use Illuminate\Http\Request;
use Illuminate\View\View;

class CatalogController extends Controller
{
    public function convenienceStore(Request $request, FrontendPageService $pages, FrontendCatalogService $catalog): View
    {
        $data = $pages->forSlug('convenience-store', $request);
        $data['catalog'] = $catalog->forArea('convenience', $data['pageFallback']);

        return view('frontend.convenience-store', $data);
    }

    public function smoothies(Request $request, FrontendPageService $pages, FrontendCatalogService $catalog): View
    {
        $data = $pages->forSlug('smoothies', $request);
        $data['catalog'] = $catalog->forArea('smoothie', $data['pageFallback']);

        return view('frontend.smoothies', $data);
    }

    public function adultRetail(Request $request, FrontendPageService $pages, FrontendCatalogService $catalog): View
    {
        $data = $pages->forSlug('vape-tobacco', $request);
        $data['catalog'] = $catalog->forArea('adult_retail', $data['pageFallback']);

        return view('frontend.vape-tobacco', $data);
    }
}
