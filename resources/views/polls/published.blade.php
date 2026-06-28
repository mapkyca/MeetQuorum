@extends('layouts.app', ['title' => 'Poll Published'])

@section('content')
@php
    $votingLink = route('poll.view', ['permalink_token' => $poll->permalink_token]);
    $managementLink = $poll->mgmt_token
        ? route('poll.manage', ['permalink_token' => $poll->permalink_token, 'mgmt' => $poll->mgmt_token])
        : route('dashboard');
@endphp

<div class="mx-auto max-w-3xl space-y-6 rounded-xl border border-gray-200 bg-white p-6 shadow-sm">
    <div>
        <h1 class="text-2xl font-semibold text-gray-900">Poll published</h1>
        <p class="mt-2 text-sm text-gray-600">Share your voting link now and keep your management access secure.</p>
    </div>

    <div class="space-y-4">
        <div>
            <label class="mb-1 block text-sm font-medium text-gray-700">Voting permalink</label>
            <input readonly value="{{ $votingLink }}" class="w-full rounded-md border border-gray-300 bg-gray-50 px-3 py-2 text-sm text-gray-800" />
        </div>

        <div>
            <label class="mb-1 block text-sm font-medium text-gray-700">Management link</label>
            <input readonly value="{{ $managementLink }}" class="w-full rounded-md border border-gray-300 bg-gray-50 px-3 py-2 text-sm text-gray-800" />
        </div>
    </div>

    <div class="rounded-lg border border-amber-200 bg-amber-50 px-4 py-3 text-sm text-amber-800">
        Share the voting link. <strong>Bookmark your management link</strong> - it is the only way to manage your poll if you are not signed in.
    </div>

    <a href="{{ $votingLink }}" class="inline-flex rounded-md bg-brand px-4 py-2 text-sm font-semibold text-white">Open voting page</a>
</div>
@endsection
