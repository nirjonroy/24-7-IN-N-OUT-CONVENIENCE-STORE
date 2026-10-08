<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;

class EnsureAdminUser
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Illuminate\Http\Response|\Illuminate\Http\RedirectResponse)  $next
     */
    public function handle(Request $request, Closure $next)
    {
        if (! $request->user()) {
            return redirect()->route('login');
        }

        if (! hash_equals(strtolower((string) config('admin.email')), strtolower($request->user()->email))) {
            abort(403);
        }

        return $next($request);
    }
}
