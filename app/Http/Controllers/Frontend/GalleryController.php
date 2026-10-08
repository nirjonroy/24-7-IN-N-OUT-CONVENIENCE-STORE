<?php

namespace App\Http\Controllers\Frontend;

use App\Http\Controllers\Controller;
use App\Services\FrontendGalleryService;
use App\Services\FrontendPageService;
use Illuminate\Http\Request;
use Illuminate\View\View;

class GalleryController extends Controller
{
    public function index(Request $request, FrontendPageService $pages, FrontendGalleryService $gallery): View
    {
        $data = $pages->forSlug('gallery', $request);
        $data['galleryContent'] = $gallery->forPage($data['pageFallback']);

        return view('frontend.gallery', $data);
    }
}
