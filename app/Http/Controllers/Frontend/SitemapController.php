<?php

namespace App\Http\Controllers\Frontend;

use App\Http\Controllers\Controller;
use App\Services\SitemapService;

class SitemapController extends Controller
{
    public function __invoke(SitemapService $sitemapService)
    {
        return response($sitemapService->xml(), 200, ['Content-Type' => 'application/xml; charset=UTF-8']);
    }
}
