<?php

namespace App\Http\Controllers\Frontend;

use App\Http\Controllers\Controller;
use App\Services\FrontendFaqService;
use App\Services\FrontendPageService;
use Illuminate\Http\Request;
use Illuminate\View\View;

class FaqController extends Controller
{
    public function index(Request $request, FrontendPageService $pages, FrontendFaqService $faqs): View
    {
        $data = $pages->forSlug('faq', $request);
        $data['faqContent'] = $faqs->forPage($data['page'], $data['pageFallback']);

        return view('frontend.faq', $data);
    }
}
