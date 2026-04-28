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
        // Force HTTPS for all generated URLs in production.
        // Needed because Traefik terminates SSL before Laravel sees the request,
        // so X-Forwarded-Proto alone isn't always sufficient for URL generation.
        if (config('app.env') === 'production') {
            URL::forceScheme('https');
        }
    }
}
