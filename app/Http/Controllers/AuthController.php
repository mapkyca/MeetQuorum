<?php

namespace App\Http\Controllers;

use App\Auth\AuthProviderInterface;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\ValidationException;
use Illuminate\View\View;

class AuthController extends Controller
{
    public function __construct(private readonly AuthProviderInterface $authProvider)
    {
    }

    public function showLogin(): View|RedirectResponse
    {
        if (Auth::check()) {
            return redirect()->route('home');
        }

        return view('auth.login', [
            'authDriver' => $this->authProvider->driver(),
        ]);
    }

    public function login(Request $request): RedirectResponse
    {
        if (! $this->authProvider->supportsPasswordAuth()) {
            return redirect()->route('login')->with('error', 'Password sign in is disabled for this deployment.');
        }

        $credentials = $request->validate([
            'email' => ['required', 'email'],
            'password' => ['required', 'string'],
            'remember' => ['nullable', 'boolean'],
        ]);

        $remember = (bool) ($credentials['remember'] ?? false);

        if (! $this->authProvider->attemptLogin($credentials['email'], $credentials['password'], $remember)) {
            throw ValidationException::withMessages([
                'email' => 'The provided credentials are invalid.',
            ]);
        }

        $request->session()->regenerate();

        return redirect()->intended(route('home'));
    }

    public function showRegister(): View|RedirectResponse
    {
        if (Auth::check()) {
            return redirect()->route('home');
        }

        if (! $this->authProvider->supportsPasswordAuth()) {
            return redirect()->route('login');
        }

        return view('auth.register');
    }

    public function register(Request $request): RedirectResponse
    {
        if (! $this->authProvider->supportsPasswordAuth()) {
            return redirect()->route('login')->with('error', 'Registration is disabled for this deployment.');
        }

        $data = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'email', 'max:255', 'unique:users,email'],
            'password' => ['required', 'string', 'min:8', 'confirmed'],
        ]);

        $user = $this->authProvider->register($data['name'], $data['email'], $data['password']);

        Auth::login($user);
        $request->session()->regenerate();

        return redirect()->route('home');
    }

    public function logout(Request $request): RedirectResponse
    {
        $this->authProvider->logout();

        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect()->route('login');
    }

    public function oidcRedirect()
    {
        return $this->authProvider->redirectToProvider();
    }

    public function oidcCallback(Request $request): RedirectResponse
    {
        $user = $this->authProvider->handleProviderCallback();

        Auth::login($user);
        $request->session()->regenerate();

        return redirect()->intended(route('home'));
    }
}
