<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>{{ $title ?? $branding['app_name'] }}</title>
    @if (!empty($branding['favicon_url']))
        <link rel="icon" href="{{ $branding['favicon_url'] }}">
    @endif
    <style>
        :root {
            --color-brand: {{ $branding['primary_color'] }};
        }
    </style>
    @livewireStyles
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="bg-gray-50 text-gray-900 antialiased">
    <header class="border-b border-gray-200 bg-white">
        <div class="mx-auto flex max-w-6xl items-center justify-between px-4 py-4 sm:px-6 lg:px-8">
            <a href="{{ route('home') }}" class="inline-flex items-center gap-2 text-lg font-semibold text-gray-900">
                @if (!empty($branding['logo_url']))
                    <img src="{{ $branding['logo_url'] }}" alt="{{ $branding['app_name'] }} logo" class="h-8 w-auto" />
                @else
                    <span>{{ $branding['app_name'] }}</span>
                @endif
            </a>
            <nav class="flex items-center gap-4">
                <a href="{{ route('poll.create') }}" class="rounded-md px-3 py-2 text-sm font-medium text-gray-700 hover:bg-gray-100">Create poll</a>
                @auth
                    <a href="{{ route('dashboard') }}" class="rounded-md px-3 py-2 text-sm font-medium text-gray-700 hover:bg-gray-100">Dashboard</a>
                    <form method="POST" action="{{ route('logout') }}">
                        @csrf
                        <button type="submit" class="rounded-md border border-gray-300 px-3 py-2 text-sm font-medium text-gray-700 hover:bg-gray-100">Logout</button>
                    </form>
                @else
                    <a href="{{ route('login') }}" class="rounded-md border border-gray-300 px-3 py-2 text-sm font-medium text-gray-700 hover:bg-gray-100">Login</a>
                @endauth
            </nav>
        </div>
    </header>

    <main class="mx-auto w-full max-w-6xl px-4 py-8 sm:px-6 lg:px-8">
        @if (session('error'))
            <div class="mb-4 rounded-lg border border-red-200 bg-red-50 px-4 py-3 text-sm text-red-700">
                {{ session('error') }}
            </div>
        @endif

        @if (session('success'))
            <div class="mb-4 rounded-lg border border-green-200 bg-green-50 px-4 py-3 text-sm text-green-700">
                {{ session('success') }}
            </div>
        @endif

        @if (!empty($branding['banner_url']))
            <img src="{{ $branding['banner_url'] }}" alt="Brand banner" class="mb-6 h-40 w-full rounded-xl object-cover" />
        @endif

        {{ $slot ?? '' }}
        @yield('content')
    </main>
    @livewireScripts
</body>
</html>
