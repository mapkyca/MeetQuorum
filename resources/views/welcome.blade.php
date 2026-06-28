@extends('layouts.app', ['title' => $branding['app_name']])

@section('content')
<div class="grid gap-6 lg:grid-cols-3">
    <section class="lg:col-span-2 rounded-xl border border-gray-200 bg-white p-6 shadow-sm">
        <h1 class="text-3xl font-semibold tracking-tight text-gray-900">Simple time slot polling for teams</h1>
        <p class="mt-3 max-w-2xl text-sm leading-6 text-gray-600">Create a poll, share one link, and rank the best meeting time. The app is self-hosted, timezone-aware, and supports guest or account-based workflows.</p>
        <div class="mt-6 flex flex-wrap gap-3">
            <a href="{{ route('poll.create') }}" class="rounded-md bg-brand px-4 py-2 text-sm font-semibold text-white">Create a poll</a>
            @guest
                <a href="{{ route('login') }}" class="rounded-md border border-gray-300 px-4 py-2 text-sm font-semibold text-gray-700">Sign in</a>
            @endguest
        </div>
    </section>

    <section class="rounded-xl border border-gray-200 bg-white p-6 shadow-sm">
        <h2 class="text-lg font-semibold text-gray-900">How it works</h2>
        <ol class="mt-4 space-y-3 text-sm text-gray-600">
            <li>1. Enter event details and timezone.</li>
            <li>2. Choose slot granularity and dates.</li>
            <li>3. Publish and share your voting permalink.</li>
            <li>4. Review ranked results in real time.</li>
        </ol>
    </section>
</div>
@endsection
