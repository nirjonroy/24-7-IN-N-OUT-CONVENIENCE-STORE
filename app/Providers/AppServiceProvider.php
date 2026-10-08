<?php

namespace App\Providers;

use App\Models\Page;
use App\Models\Redirect;
use App\Observers\PageObserver;
use App\Observers\RedirectObserver;
use App\Services\FrontendPageService;
use Illuminate\Http\Request;
use Illuminate\Support\ServiceProvider;
use Illuminate\Support\Facades\View;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     *
     * @return void
     */
    public function register()
    {
        //
    }

    /**
     * Bootstrap any application services.
     *
     * @return void
     */
    public function boot()
    {
        Page::observe(PageObserver::class);
        Redirect::observe(RedirectObserver::class);

        View::composer(['errors::404', 'errors.404'], function ($view) {
            $view->with(app(FrontendPageService::class)->sharedFallbackData(
                app(Request::class),
                ['robots' => 'noindex, nofollow']
            ));
        });
    }
}
