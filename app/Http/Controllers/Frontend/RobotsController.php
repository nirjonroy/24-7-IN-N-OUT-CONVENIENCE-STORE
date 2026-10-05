<?php

namespace App\Http\Controllers\Frontend;

use App\Http\Controllers\Controller;
use App\Services\RobotsService;

class RobotsController extends Controller
{
    public function __invoke(RobotsService $robotsService)
    {
        return response($robotsService->text(), 200, ['Content-Type' => 'text/plain; charset=UTF-8']);
    }
}
