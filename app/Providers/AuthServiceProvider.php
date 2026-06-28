<?php

namespace App\Providers;

use App\Auth\AuthProviderInterface;
use App\Auth\LocalAuthProvider;
use App\Auth\OidcAuthProvider;
use Illuminate\Support\ServiceProvider;

class AuthServiceProvider extends ServiceProvider
{
    /**
     * Register services.
     */
    public function register(): void
    {
        $this->app->bind(AuthProviderInterface::class, function () {
            return match (config('auth_driver.driver')) {
                'oidc' => new OidcAuthProvider(),
                default => new LocalAuthProvider(),
            };
        });
    }
}
