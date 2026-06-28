<?php

namespace App\Providers;

use App\Services\BrandingConfig;
use Illuminate\Support\Facades\View;
use Illuminate\Support\ServiceProvider;

class BrandingServiceProvider extends ServiceProvider
{
    /**
     * Register services.
     */
    public function register(): void
    {
        $this->app->singleton(BrandingConfig::class);
    }

    /**
     * Bootstrap services.
     */
    public function boot(BrandingConfig $brandingConfig): void
    {
        View::composer('*', function ($view) use ($brandingConfig): void {
            $view->with('branding', $brandingConfig->all());
        });
    }
}
