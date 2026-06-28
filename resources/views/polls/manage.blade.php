@extends('layouts.app', ['title' => 'Manage Poll'])

@section('content')
<div class="mx-auto max-w-3xl space-y-6 rounded-xl border border-gray-200 bg-white p-6 shadow-sm">
    <div>
        <h1 class="text-2xl font-semibold text-gray-900">Manage poll</h1>
        <p class="mt-1 text-sm text-gray-600">Update details, open or close voting, or delete this poll.</p>
    </div>

    <form method="POST" action="{{ route('poll.manage.update', ['permalink_token' => $poll->permalink_token, 'mgmt' => request('mgmt')]) }}" class="space-y-4">
        @csrf
        @method('PATCH')

        <div>
            <label for="title" class="mb-1 block text-sm font-medium text-gray-700">Title</label>
            <input id="title" type="text" name="title" value="{{ old('title', $poll->title) }}" class="w-full rounded-md border border-gray-300 px-3 py-2 text-sm" required />
        </div>

        <div>
            <label for="description" class="mb-1 block text-sm font-medium text-gray-700">Description</label>
            <textarea id="description" name="description" rows="3" class="w-full rounded-md border border-gray-300 px-3 py-2 text-sm">{{ old('description', $poll->description) }}</textarea>
        </div>

        <div>
            <label for="meeting_link" class="mb-1 block text-sm font-medium text-gray-700">Meeting link</label>
            <input id="meeting_link" type="url" name="meeting_link" value="{{ old('meeting_link', $poll->meeting_link) }}" class="w-full rounded-md border border-gray-300 px-3 py-2 text-sm" />
        </div>

        <div>
            <label for="status" class="mb-1 block text-sm font-medium text-gray-700">Status</label>
            <select id="status" name="status" class="w-full rounded-md border border-gray-300 px-3 py-2 text-sm">
                <option value="open" @selected(old('status', $poll->status) === 'open')>Open</option>
                <option value="closed" @selected(old('status', $poll->status) === 'closed')>Closed</option>
            </select>
        </div>

        <div class="flex flex-wrap gap-3">
            <button type="submit" class="rounded-md bg-brand px-4 py-2 text-sm font-semibold text-white">Save changes</button>
            <a href="{{ route('poll.results', ['permalink_token' => $poll->permalink_token]) }}" class="rounded-md border border-gray-300 px-4 py-2 text-sm font-semibold text-gray-700">View results</a>
            <a href="{{ route('poll.view', ['permalink_token' => $poll->permalink_token]) }}" class="rounded-md border border-gray-300 px-4 py-2 text-sm font-semibold text-gray-700">Open voting page</a>
        </div>
    </form>

    <form method="POST" action="{{ route('poll.manage.delete', ['permalink_token' => $poll->permalink_token, 'mgmt' => request('mgmt')]) }}" onsubmit="return confirm('Delete this poll and all votes?');">
        @csrf
        @method('DELETE')
        <button type="submit" class="rounded-md border border-red-300 bg-red-50 px-4 py-2 text-sm font-semibold text-red-700">Delete poll</button>
    </form>
</div>
@endsection
