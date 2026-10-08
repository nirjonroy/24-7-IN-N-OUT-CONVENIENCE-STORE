<?php

namespace App\Observers;

use App\Models\Redirect;
use App\Services\RedirectService;

class RedirectObserver
{
    public function saved(Redirect $redirect): void
    {
        app(RedirectService::class)->clearCache();
    }

    public function deleted(Redirect $redirect): void
    {
        app(RedirectService::class)->clearCache();
    }
}
