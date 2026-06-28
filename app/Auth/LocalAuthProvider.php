<?php

namespace App\Auth;

use App\Models\User;
use Illuminate\Support\Facades\Auth;

class LocalAuthProvider implements AuthProviderInterface
{
    public function driver(): string
    {
        return 'local';
    }

    public function supportsPasswordAuth(): bool
    {
        return true;
    }

    public function attemptLogin(string $email, string $password, bool $remember = false): bool
    {
        return Auth::attempt([
            'email' => $email,
            'password' => $password,
            'auth_provider' => 'local',
        ], $remember);
    }

    public function register(string $name, string $email, string $password): User
    {
        return User::create([
            'name' => $name,
            'email' => $email,
            'password_hash' => $password,
            'auth_provider' => 'local',
        ]);
    }

    public function logout(): void
    {
        Auth::logout();
    }

    public function redirectToProvider()
    {
        abort(404);
    }

    public function handleProviderCallback(): User
    {
        abort(404);
    }
}
