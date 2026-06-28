<?php

namespace App\Auth;

use App\Models\User;
use Illuminate\Support\Facades\Auth;
use RuntimeException;

class OidcAuthProvider implements AuthProviderInterface
{
    public function driver(): string
    {
        return 'oidc';
    }

    public function supportsPasswordAuth(): bool
    {
        return false;
    }

    public function attemptLogin(string $email, string $password, bool $remember = false): bool
    {
        throw new RuntimeException('Password authentication is not available for OIDC driver.');
    }

    public function register(string $name, string $email, string $password): User
    {
        throw new RuntimeException('Local registration is not available for OIDC driver.');
    }

    public function logout(): void
    {
        Auth::logout();
    }

    public function redirectToProvider()
    {
        throw new RuntimeException('OIDC provider flow is implemented in Phase 16.');
    }

    public function handleProviderCallback(): User
    {
        throw new RuntimeException('OIDC provider callback is implemented in Phase 16.');
    }
}
