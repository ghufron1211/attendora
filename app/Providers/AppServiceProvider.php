<?php

namespace App\Providers;

use Illuminate\Support\Facades\URL;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        //
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        // Force HTTPS in production — Railway terminates SSL at its proxy,
        // so Laravel sees plain HTTP. Without this, asset() and route()
        // generate http:// URLs causing mixed-content browser blocks.
        if ($this->app->environment('production')) {
            URL::forceScheme('https');
        }
    }
}
