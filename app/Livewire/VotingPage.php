<?php

namespace App\Livewire;

use App\Models\Poll;
use App\Models\Voter;
use Carbon\Carbon;
use Illuminate\Contracts\View\View;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Support\Str;
use Livewire\Component;

class VotingPage extends Component
{
    public Poll $poll;

    public ?Voter $voter = null;

    public string $name = '';

    public string $email = '';

    public string $viewerTimezone = 'UTC';

    public bool $useCreatorTimezone = false;

    /** @var array<string, string> */
    public array $responses = [];

    public function mount(string $permalink_token, ?string $magic_token = null): void
    {
        $this->poll = Poll::with(['slots' => fn ($query) => $query->orderBy('starts_at')])->where('permalink_token', $permalink_token)->firstOrFail();
        $this->viewerTimezone = session('viewer_tz', $this->poll->creator_tz);

        if ($magic_token !== null) {
            $this->resolveMagicVoter($magic_token);
            return;
        }

        $sessionToken = session($this->sessionKey());
        if (is_string($sessionToken) && $sessionToken !== '') {
            $this->resolveMagicVoter($sessionToken);
        }
    }

    public function setBrowserTimezone(string $timezone): void
    {
        if (! in_array($timezone, \DateTimeZone::listIdentifiers(\DateTimeZone::ALL), true)) {
            return;
        }

        $this->viewerTimezone = $timezone;
        session(['viewer_tz' => $timezone]);
    }

    public function identifyVoter(): void
    {
        $this->ensureRateLimit('identify', 10, 600);

        $data = $this->validate([
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'email', 'max:255'],
        ]);

        $magicToken = Str::random(64);
        $expiresAt = now()->addDays((int) config('poll.magic_token_days', 90));

        DB::transaction(function () use ($data, $magicToken, $expiresAt): void {
            DB::statement(
                'INSERT INTO voters (id, poll_id, name, email, magic_token, token_expires_at, created_at, updated_at)
                 VALUES (?, ?, ?, ?, ?, ?, NOW(), NOW())
                 ON DUPLICATE KEY UPDATE
                 name = VALUES(name),
                 magic_token = VALUES(magic_token),
                 token_expires_at = VALUES(token_expires_at),
                 updated_at = NOW()',
                [
                    (string) Str::uuid(),
                    $this->poll->id,
                    $data['name'],
                    $data['email'],
                    $magicToken,
                    $expiresAt->format('Y-m-d H:i:s'),
                ]
            );
        }, 3);

        $voter = Voter::where('poll_id', $this->poll->id)->where('email', $data['email'])->firstOrFail();
        session([$this->sessionKey() => $voter->magic_token]);

        $this->redirectRoute('poll.vote.magic', [
            'permalink_token' => $this->poll->permalink_token,
            'magic_token' => $voter->magic_token,
        ], navigate: true);
    }

    public function submitVotes(): void
    {
        if (! $this->voter) {
            abort(403);
        }

        if ($this->poll->status === 'closed') {
            session()->flash('error', 'This poll is closed and no longer accepts votes.');
            return;
        }

        $this->ensureRateLimit('vote', 20, 60);

        foreach ($this->poll->slots as $slot) {
            $response = $this->responses[$slot->id] ?? 'no';
            if (! in_array($response, ['yes', 'if_needed', 'no'], true)) {
                $response = 'no';
            }

            DB::statement(
                'INSERT INTO votes (id, voter_id, slot_id, response, updated_at)
                 VALUES (?, ?, ?, ?, NOW())
                 ON DUPLICATE KEY UPDATE
                 response = VALUES(response),
                 updated_at = NOW()',
                [
                    (string) Str::uuid(),
                    $this->voter->id,
                    $slot->id,
                    $response,
                ]
            );
        }

        $returnLink = route('poll.vote.magic', [
            'permalink_token' => $this->poll->permalink_token,
            'magic_token' => $this->voter->magic_token,
        ]);

        session()->flash('success', 'Your votes have been saved. Bookmark this link to return and change your votes: '.$returnLink);

        $this->redirectRoute('poll.results', ['permalink_token' => $this->poll->permalink_token], navigate: true);
    }

    public function render(): View
    {
        return view('livewire.voting-page');
    }

    private function resolveMagicVoter(string $magicToken): void
    {
        $this->ensureRateLimit('magic', 30, 60);

        $voter = Voter::where('poll_id', $this->poll->id)
            ->where('magic_token', $magicToken)
            ->first();

        if (! $voter || ! hash_equals($voter->magic_token, $magicToken)) {
            abort(404);
        }

        if (Carbon::parse($voter->token_expires_at)->isPast()) {
            abort(403, 'Magic link has expired.');
        }

        $voter->update([
            'token_expires_at' => now()->addDays((int) config('poll.magic_token_days', 90)),
        ]);

        $this->voter = $voter;
        $this->name = $voter->name;
        $this->email = $voter->email;

        $existingVotes = DB::table('votes')
            ->where('voter_id', $voter->id)
            ->pluck('response', 'slot_id')
            ->toArray();

        foreach ($this->poll->slots as $slot) {
            $this->responses[$slot->id] = $existingVotes[$slot->id] ?? 'no';
        }

        session([$this->sessionKey() => $voter->magic_token]);
    }

    private function ensureRateLimit(string $scope, int $maxAttempts, int $decaySeconds): void
    {
        $key = sprintf('poll:%s:%s:%s', $scope, request()->ip(), $this->poll->id);

        if (RateLimiter::tooManyAttempts($key, $maxAttempts)) {
            abort(429, 'Too many requests.');
        }

        RateLimiter::hit($key, $decaySeconds);
    }

    private function sessionKey(): string
    {
        return 'poll_voter_token_'.$this->poll->id;
    }
}
