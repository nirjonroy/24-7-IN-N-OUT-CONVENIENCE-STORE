<?php

namespace App\Http\Controllers\Frontend;

use App\Http\Controllers\Controller;
use App\Services\RedirectService;
use Illuminate\Http\Request;

class FallbackController extends Controller
{
    public function handle(Request $request, RedirectService $redirectService)
    {
        if ($redirect = $redirectService->findForRequest($request)) {
            $target = $redirectService->buildTargetUrl($redirect, $request);
            $redirectService->recordHit($redirect);

            return redirect()->away($target, $redirect->status_code);
        }

        if ($request->expectsJson()) {
            return response()->json(['message' => 'Not Found'], 404);
        }

        abort(404);
    }
}
