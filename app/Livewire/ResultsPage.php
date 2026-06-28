<?php

namespace App\Livewire;

use App\Models\Poll;
use App\Services\VoteScorer;
use Illuminate\Contracts\View\View;
use Livewire\Component;

class ResultsPage extends Component
{
    public Poll $poll;

    public string $viewerTimezone = 'UTC';

    public bool $useCreatorTimezone = false;

    /** @var array<string, array{yes_count:int, if_needed_count:int, no_count:int, score:int, rank:int|null}> */
    public array $scores = [];

    public function mount(string $permalink_token, VoteScorer $scorer): void
    {
        $this->poll = Poll::with([
            'slots' => fn ($query) => $query->orderBy('starts_at'),
            'voters' => fn ($query) => $query->orderBy('name'),
            'voters.votes',
        ])->where('permalink_token', $permalink_token)->firstOrFail();

        $this->scores = $scorer->scoreBySlot($this->poll);
        $this->viewerTimezone = session('viewer_tz', $this->poll->creator_tz);
    }

    public function setBrowserTimezone(string $timezone): void
    {
        if (! in_array($timezone, \DateTimeZone::listIdentifiers(\DateTimeZone::ALL), true)) {
            return;
        }

        $this->viewerTimezone = $timezone;
        session(['viewer_tz' => $timezone]);
    }

    public function render(): View
    {
        return view('livewire.results-page');
    }
}
