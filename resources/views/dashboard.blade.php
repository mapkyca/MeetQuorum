@extends('layouts.app', ['title' => 'Dashboard'])

@section('content')
<div class="space-y-6">
    <div>
        <h1 class="text-2xl font-semibold text-gray-900">My polls</h1>
        <p class="mt-1 text-sm text-gray-600">Polls where you are the authenticated creator.</p>
    </div>

    <div class="overflow-x-auto rounded-xl border border-gray-200 bg-white shadow-sm">
        <table class="min-w-full divide-y divide-gray-200 text-sm">
            <thead>
                <tr class="text-left text-gray-600">
                    <th class="px-4 py-3">Title</th>
                    <th class="px-4 py-3">Status</th>
                    <th class="px-4 py-3">Created</th>
                    <th class="px-4 py-3">Votes</th>
                    <th class="px-4 py-3">Actions</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-gray-100">
                @forelse($polls as $poll)
                    <tr>
                        <td class="px-4 py-3 font-medium text-gray-800">{{ $poll->title }}</td>
                        <td class="px-4 py-3">{{ ucfirst($poll->status) }}</td>
                        <td class="px-4 py-3">{{ $poll->created_at->format('Y-m-d') }}</td>
                        <td class="px-4 py-3">{{ $poll->voters_count }}</td>
                        <td class="px-4 py-3">
                            <div class="flex flex-wrap gap-2">
                                <a href="{{ route('poll.results', ['permalink_token' => $poll->permalink_token]) }}" class="rounded border border-gray-300 px-2 py-1 text-xs font-semibold text-gray-700">Results</a>
                                <a href="{{ route('poll.manage', ['permalink_token' => $poll->permalink_token]) }}" class="rounded border border-gray-300 px-2 py-1 text-xs font-semibold text-gray-700">Manage</a>
                                <a href="{{ route('poll.view', ['permalink_token' => $poll->permalink_token]) }}" class="rounded border border-gray-300 px-2 py-1 text-xs font-semibold text-gray-700">Voting link</a>
                            </div>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="5" class="px-4 py-6 text-center text-gray-500">No polls yet. Create your first poll.</td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>
</div>
@endsection
