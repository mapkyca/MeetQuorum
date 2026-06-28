<?php

namespace App\Auth;

use App\Models\User;
use Illuminate\Support\Facades\Auth;
use Laravel\Socialite\Facades\Socialite;
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
        $this->assertConfigured();

        return Socialite::driver('keycloak')
            ->scopes(['openid', 'profile', 'email'])
            ->redirect();
    }

    public function handleProviderCallback(): User
    {
        $this->assertConfigured();

        $providerUser = Socialite::driver('keycloak')->user();

        $providerSub = (string) $providerUser->getId();
        $email = strtolower(trim((string) $providerUser->getEmail()));

        if ($providerSub === '') {
            throw new RuntimeException('OIDC provider did not return a subject identifier.');
        }

        if ($email === '') {
            throw new RuntimeException('OIDC provider did not return an email address.');
        }

        $name = trim((string) ($providerUser->getName() ?: $providerUser->getNickname() ?: $email));

        $userBySub = User::query()
            ->where('auth_provider', 'oidc')
            ->where('provider_sub', $providerSub)
            ->first();

        if ($userBySub instanceof User) {
            $userBySub->fill([
                'email' => $email,
                'name' => $name,
            ])->save();

            return $userBySub;
        }

        $userByEmail = User::query()->where('email', $email)->first();

        if ($userByEmail instanceof User) {
            if ($userByEmail->auth_provider === 'local') {
                throw new RuntimeException('A local account already exists for this email. Please sign in with email and password.');
            }

            $userByEmail->fill([
                'name' => $name,
                'provider_sub' => $providerSub,
                'auth_provider' => 'oidc',
            ])->save();

            return $userByEmail;
        }

        return User::create([
            'name' => $name,
            'email' => $email,
            'password_hash' => null,
            'auth_provider' => 'oidc',
            'provider_sub' => $providerSub,
        ]);
    }

    private function assertConfigured(): void
    {
        $config = config('services.keycloak');

        $required = ['base_url', 'realms', 'client_id', 'client_secret', 'redirect'];

        foreach ($required as $key) {
            $value = is_array($config) ? ($config[$key] ?? null) : null;
            if (! is_string($value) || trim($value) === '') {
                throw new RuntimeException('Keycloak configuration is incomplete: '.$key);
            }
        }
    }
}
