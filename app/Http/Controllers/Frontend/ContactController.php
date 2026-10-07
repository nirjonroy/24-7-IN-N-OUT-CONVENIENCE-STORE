<?php

namespace App\Http\Controllers\Frontend;

use App\Http\Controllers\Controller;
use App\Services\FrontendPageService;
use Illuminate\Http\Request;
use Illuminate\View\View;

class ContactController extends Controller
{
    public function index(Request $request, FrontendPageService $pages): View
    {
        return view('frontend.contact', $pages->forSlug('contact', $request));
    }
}
