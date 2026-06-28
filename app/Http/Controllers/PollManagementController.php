<?php

namespace App\Http\Controllers;

use App\Models\Poll;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\View\View;

class PollManagementController extends Controller
{
    public function show(Request $request, string $permalink_token): View
    {
        $poll = Poll::where('permalink_token', $permalink_token)->firstOrFail();
        $this->authorizeManage($request, $poll);

        return view('polls.manage', ['poll' => $poll]);
    }

    public function update(Request $request, string $permalink_token): RedirectResponse
    {
        $poll = Poll::where('permalink_token', $permalink_token)->firstOrFail();
        $this->authorizeManage($request, $poll);

        $data = $request->validate([
            'title' => ['required', 'string', 'max:255'],
            'description' => ['nullable', 'string'],
            'meeting_link' => ['nullable', 'url', 'max:500'],
            'status' => ['required', 'in:open,closed'],
        ]);

        $poll->update($data);

        return back()->with('success', 'Poll updated.');
    }

    public function destroy(Request $request, string $permalink_token): RedirectResponse
    {
        $poll = Poll::where('permalink_token', $permalink_token)->firstOrFail();
        $this->authorizeManage($request, $poll);

        DB::transaction(function () use ($poll): void {
            $poll->delete();
        }, 3);

        return redirect()->route('home')->with('success', 'Poll deleted.');
    }

    private function authorizeManage(Request $request, Poll $poll): void
    {
        if (Auth::check() && Auth::id() === $poll->creator_user_id) {
            return;
        }

        $provided = (string) $request->query('mgmt', '');
        if ($provided !== '' && $poll->mgmt_token && hash_equals($poll->mgmt_token, $provided)) {
            return;
        }

        abort(403);
    }
}
