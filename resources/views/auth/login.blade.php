@extends('layouts.app', ['title' => 'Login'])

@section('content')
<div class="mx-auto max-w-md rounded-xl border border-gray-200 bg-white p-6 shadow-sm">
    <h1 class="mb-2 text-2xl font-semibold text-gray-900">Sign in</h1>
    <p class="mb-6 text-sm text-gray-600">Access your polls and management dashboard.</p>

    @if ($authDriver === 'local')
        <form method="POST" action="{{ route('login.attempt') }}" class="space-y-4">
            @csrf
            <div>
                <label for="email" class="mb-1 block text-sm font-medium text-gray-700">Email</label>
                <input id="email" name="email" type="email" value="{{ old('email') }}" required class="w-full rounded-md border border-gray-300 px-3 py-2 text-sm focus:border-blue-500 focus:outline-none focus:ring-2 focus:ring-blue-100" />
                @error('email')<p class="mt-1 text-xs text-red-600">{{ $message }}</p>@enderror
            </div>

            <div>
                <label for="password" class="mb-1 block text-sm font-medium text-gray-700">Password</label>
                <input id="password" name="password" type="password" required class="w-full rounded-md border border-gray-300 px-3 py-2 text-sm focus:border-blue-500 focus:outline-none focus:ring-2 focus:ring-blue-100" />
                @error('password')<p class="mt-1 text-xs text-red-600">{{ $message }}</p>@enderror
            </div>

            <label class="flex items-center gap-2 text-sm text-gray-600">
                <input type="checkbox" name="remember" value="1" class="rounded border-gray-300" />
                Remember me
            </label>

            <button type="submit" class="w-full rounded-md bg-brand px-4 py-2 text-sm font-semibold text-white">Sign in</button>
        </form>

        <p class="mt-4 text-center text-sm text-gray-600">
            Need an account?
            <a href="{{ route('register') }}" class="font-medium text-brand">Register</a>
        </p>
    @else
        <a href="{{ route('auth.oidc.redirect') }}" class="inline-flex w-full items-center justify-center rounded-md bg-brand px-4 py-2 text-sm font-semibold text-white">Sign in with SSO</a>
    @endif
</div>
@endsection
