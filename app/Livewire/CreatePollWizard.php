<?php

namespace App\Livewire;

use App\Models\Poll;
use App\Models\PollSlot;
use App\Services\SlotGenerator;
use DateTimeZone;
use Illuminate\Contracts\View\View;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Livewire\Component;

class CreatePollWizard extends Component
{
    public int $step = 1;

    public string $title = '';

    public ?string $description = null;

    public ?string $meetingLink = null;

    public string $timezone = 'UTC';

    public string $slotGranularity = '30min';

    /** @var array<int, string> */
    public array $selectedDates = [];

    /** @var array<string, array{start: string, end: string}> */
    public array $dayWindows = [];

    /** @var array<string, array<int, array<string, string>>> */
    public array $candidateSlots = [];

    /** @var array<int, string> */
    public array $selectedSlotKeys = [];

    public ?string $guestName = null;

    public ?string $guestEmail = null;

    public ?string $browserTimezone = null;

    public function mount(): void
    {
        $this->timezone = config('app.timezone', 'UTC');
    }

    public function setBrowserTimezone(string $timezone): void
    {
        if (in_array($timezone, DateTimeZone::listIdentifiers(DateTimeZone::ALL), true)) {
            $this->browserTimezone = $timezone;
            $this->timezone = $timezone;
            $this->regenerateSlots();
        }
    }

    public function nextStep(): void
    {
        $this->validateStep($this->step);
        if ($this->step < 4) {
            $this->step++;
        }
    }

    public function previousStep(): void
    {
        if ($this->step > 1) {
            $this->step--;
        }
    }

    public function toggleDate(string $date): void
    {
        if (in_array($date, $this->selectedDates, true)) {
            $this->selectedDates = array_values(array_filter(
                $this->selectedDates,
                fn (string $selected) => $selected !== $date,
            ));
            unset($this->dayWindows[$date], $this->candidateSlots[$date]);
        } else {
            $this->selectedDates[] = $date;
            $this->dayWindows[$date] = [
                'start' => config('poll.window.start', '09:00'),
                'end' => config('poll.window.end', '17:00'),
            ];
        }

        sort($this->selectedDates);
        $this->regenerateSlots();
    }

    public function updatedSlotGranularity(): void
    {
        $this->regenerateSlots();
    }

    public function updatedTimezone(): void
    {
        $this->regenerateSlots();
    }

    public function updateWindow(string $date, string $bound, string $value): void
    {
        if (! isset($this->dayWindows[$date])) {
            return;
        }

        if (! in_array($bound, ['start', 'end'], true)) {
            return;
        }

        $this->dayWindows[$date][$bound] = $value;
        $this->regenerateSlots();
    }

    public function toggleSlot(string $key): void
    {
        if (in_array($key, $this->selectedSlotKeys, true)) {
            $this->selectedSlotKeys = array_values(array_filter(
                $this->selectedSlotKeys,
                fn (string $slotKey) => $slotKey !== $key,
            ));

            return;
        }

        $this->selectedSlotKeys[] = $key;
    }

    public function publish(): mixed
    {
        $this->validateStep(1);
        $this->validateStep(2);
        $this->validateStep(3);
        $this->validateStep(4);

        $slotRows = $this->selectedSlotRows();

        $poll = null;
        $mgmtToken = null;

        DB::transaction(function () use (&$poll, &$mgmtToken, $slotRows): void {
            $poll = Poll::create([
                'permalink_token' => Str::random(32),
                'creator_user_id' => Auth::id(),
                'creator_name' => Auth::check() ? null : $this->guestName,
                'creator_email' => Auth::check() ? null : $this->guestEmail,
                'mgmt_token' => Auth::check() ? null : Str::random(64),
                'title' => $this->title,
                'description' => $this->description,
                'meeting_link' => $this->meetingLink,
                'creator_tz' => $this->timezone,
                'slot_granularity' => $this->slotGranularity,
                'status' => 'open',
            ]);

            $mgmtToken = $poll->mgmt_token;

            foreach ($slotRows as $slotRow) {
                PollSlot::create([
                    'poll_id' => $poll->id,
                    'starts_at' => $slotRow['starts_at_utc'],
                    'ends_at' => $slotRow['ends_at_utc'],
                ]);
            }
        }, 3);

        if (! $poll instanceof Poll) {
            throw new \RuntimeException('Failed to create poll.');
        }

        $redirect = redirect()->route('poll.published', [
            'permalink_token' => $poll->permalink_token,
            'mgmt' => $mgmtToken,
        ]);

        if (! Auth::check() && $mgmtToken) {
            $cookieName = 'poll_mgmt_'.$poll->id;
            $redirect->cookie(cookie()->make($cookieName, $mgmtToken, 60 * 24 * 365));
        }

        return $redirect;
    }

    public function render(): View
    {
        return view('livewire.create-poll-wizard', [
            'timezones' => DateTimeZone::listIdentifiers(DateTimeZone::ALL),
            'monthDays' => $this->monthDays(),
            'selectedSlotRows' => $this->selectedSlotRows(),
        ]);
    }

    /**
     * @return array<int, string>
     */
    private function monthDays(): array
    {
        $start = now($this->timezone)->startOfMonth();
        $end = $start->copy()->endOfMonth();

        $days = [];
        while ($start->lessThanOrEqualTo($end)) {
            $days[] = $start->format('Y-m-d');
            $start->addDay();
        }

        return $days;
    }

    private function validateStep(int $step): void
    {
        if ($step === 1) {
            $rules = [
                'title' => ['required', 'string', 'max:255'],
                'description' => ['nullable', 'string'],
                'meetingLink' => ['nullable', 'url', 'max:500'],
                'timezone' => ['required', 'timezone:all'],
            ];

            if (! Auth::check()) {
                $rules['guestName'] = ['required', 'string', 'max:255'];
                $rules['guestEmail'] = ['required', 'email', 'max:255'];
            }

            $this->validate($rules);

            return;
        }

        if ($step === 2) {
            $this->validate([
                'slotGranularity' => ['required', 'in:30min,1hour,half_day,full_day'],
            ]);

            return;
        }

        if ($step === 3) {
            $this->regenerateSlots();

            $this->validate([
                'selectedDates' => ['required', 'array', 'min:1'],
                'selectedSlotKeys' => ['required', 'array', 'min:1'],
            ]);
        }
    }

    private function regenerateSlots(): void
    {
        $generator = app(SlotGenerator::class);
        $candidateSlots = [];
        $allKeys = [];

        foreach ($this->selectedDates as $date) {
            $window = $this->dayWindows[$date] ?? [
                'start' => config('poll.window.start', '09:00'),
                'end' => config('poll.window.end', '17:00'),
            ];

            try {
                $slots = $generator->generateForDate(
                    $date,
                    $this->timezone,
                    $this->slotGranularity,
                    $window['start'],
                    $window['end'],
                );
            } catch (\Throwable $exception) {
                $slots = [];
            }

            $candidateSlots[$date] = $slots;
            foreach ($slots as $slot) {
                $allKeys[] = $slot['key'];
            }
        }

        $this->candidateSlots = $candidateSlots;

        if (empty($this->selectedSlotKeys)) {
            $this->selectedSlotKeys = array_values(array_unique($allKeys));
            return;
        }

        $this->selectedSlotKeys = array_values(array_intersect($this->selectedSlotKeys, $allKeys));
    }

    /**
     * @return array<int, array<string, string>>
     */
    private function selectedSlotRows(): array
    {
        $rows = [];

        foreach ($this->candidateSlots as $slots) {
            foreach ($slots as $slot) {
                if (in_array($slot['key'], $this->selectedSlotKeys, true)) {
                    $rows[] = $slot;
                }
            }
        }

        usort($rows, fn (array $a, array $b): int => strcmp($a['starts_at_utc'], $b['starts_at_utc']));

        return $rows;
    }
}
