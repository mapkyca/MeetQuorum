@extends('layouts.app', ['title' => 'Create Poll'])

@section('content')
<div class="space-y-4">
    <h1 class="text-2xl font-semibold text-gray-900">Create a new poll</h1>
    <p class="text-sm text-gray-600">Set up event details, choose slot granularity, and publish a sharing link.</p>
</div>

<livewire:create-poll-wizard />
@endsection
