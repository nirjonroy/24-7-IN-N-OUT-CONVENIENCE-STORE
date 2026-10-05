<?php

namespace App\Http\Middleware;

use App\Services\RedirectService;
use Closure;
use Illuminate\Http\Request;

class ApplyRedirects
{
    public function handle(Request $request, Closure $next)
    {
        $service = app(RedirectService::class);
        $redirect = $service->findForRequest($request);

        if (! $redirect) {
            return $next($request);
        }

        $target = $service->buildTargetUrl($redirect, $request);
        $service->recordHit($redirect);

        return redirect()->away($target, $redirect->status_code);
    }
}
