@extends('layouts.app', ['title' => 'Register'])

@section('content')
<div class="mx-auto max-w-md rounded-xl border border-gray-200 bg-white p-6 shadow-sm">
    <h1 class="mb-2 text-2xl font-semibold text-gray-900">Create account</h1>
    <p class="mb-6 text-sm text-gray-600">Register to create and manage your polls from the dashboard.</p>

    <form method="POST" action="{{ route('register.store') }}" class="space-y-4">
        @csrf
        <div>
            <label for="name" class="mb-1 block text-sm font-medium text-gray-700">Name</label>
            <input id="name" name="name" type="text" value="{{ old('name') }}" required class="w-full rounded-md border border-gray-300 px-3 py-2 text-sm focus:border-blue-500 focus:outline-none focus:ring-2 focus:ring-blue-100" />
            @error('name')<p class="mt-1 text-xs text-red-600">{{ $message }}</p>@enderror
        </div>

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

        <div>
            <label for="password_confirmation" class="mb-1 block text-sm font-medium text-gray-700">Confirm password</label>
            <input id="password_confirmation" name="password_confirmation" type="password" required class="w-full rounded-md border border-gray-300 px-3 py-2 text-sm focus:border-blue-500 focus:outline-none focus:ring-2 focus:ring-blue-100" />
        </div>

        <button type="submit" class="w-full rounded-md bg-brand px-4 py-2 text-sm font-semibold text-white">Create account</button>
    </form>

    <p class="mt-4 text-center text-sm text-gray-600">
        Already have an account?
        <a href="{{ route('login') }}" class="font-medium text-brand">Sign in</a>
    </p>
</div>
@endsection
