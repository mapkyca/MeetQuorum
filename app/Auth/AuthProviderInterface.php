<?php

namespace App\Auth;

use App\Models\User;

interface AuthProviderInterface
{
    public function driver(): string;

    public function supportsPasswordAuth(): bool;

    public function attemptLogin(string $email, string $password, bool $remember = false): bool;

    public function register(string $name, string $email, string $password): User;

    public function logout(): void;

    public function redirectToProvider();

    public function handleProviderCallback(): User;
}
