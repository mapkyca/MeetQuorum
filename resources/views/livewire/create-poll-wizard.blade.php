<div
    x-data
    x-init="
        const tz = Intl.DateTimeFormat().resolvedOptions().timeZone;
        if (tz) {
            $wire.setBrowserTimezone(tz);
        }
    "
    class="mt-6 rounded-xl border border-gray-200 bg-white p-4 shadow-sm sm:p-6"
>
    <div class="mb-6 flex items-center justify-between">
        <div class="text-sm font-medium text-gray-600">Step {{ $step }} of 4</div>
        <div class="text-sm text-gray-500">All times use IANA timezone strings</div>
    </div>

    @if ($step === 1)
        <div class="space-y-4">
            <h2 class="text-xl font-semibold text-gray-900">Event details</h2>

            @guest
                <div class="grid gap-4 sm:grid-cols-2">
                    <div>
                        <label for="guest-name" class="mb-1 block text-sm font-medium text-gray-700">Your name</label>
                        <input id="guest-name" type="text" wire:model="guestName" class="w-full rounded-md border border-gray-300 px-3 py-2 text-sm" />
                        @error('guestName') <p class="mt-1 text-xs text-red-600">{{ $message }}</p> @enderror
                    </div>
                    <div>
                        <label for="guest-email" class="mb-1 block text-sm font-medium text-gray-700">Your email</label>
                        <input id="guest-email" type="email" wire:model="guestEmail" class="w-full rounded-md border border-gray-300 px-3 py-2 text-sm" />
                        @error('guestEmail') <p class="mt-1 text-xs text-red-600">{{ $message }}</p> @enderror
                    </div>
                </div>
            @endguest

            <div>
                <label for="title" class="mb-1 block text-sm font-medium text-gray-700">Title</label>
                <input id="title" type="text" wire:model="title" class="w-full rounded-md border border-gray-300 px-3 py-2 text-sm" />
                @error('title') <p class="mt-1 text-xs text-red-600">{{ $message }}</p> @enderror
            </div>

            <div>
                <label for="description" class="mb-1 block text-sm font-medium text-gray-700">Description</label>
                <textarea id="description" wire:model="description" rows="3" class="w-full rounded-md border border-gray-300 px-3 py-2 text-sm"></textarea>
                @error('description') <p class="mt-1 text-xs text-red-600">{{ $message }}</p> @enderror
            </div>

            <div>
                <label for="meeting-link" class="mb-1 block text-sm font-medium text-gray-700">Meeting link</label>
                <input id="meeting-link" type="url" wire:model="meetingLink" class="w-full rounded-md border border-gray-300 px-3 py-2 text-sm" />
                @error('meetingLink') <p class="mt-1 text-xs text-red-600">{{ $message }}</p> @enderror
            </div>

            <div>
                <label for="timezone" class="mb-1 block text-sm font-medium text-gray-700">Timezone</label>
                <select id="timezone" wire:model.live="timezone" class="w-full rounded-md border border-gray-300 px-3 py-2 text-sm">
                    @foreach($timezones as $tz)
                        <option value="{{ $tz }}">{{ $tz }}</option>
                    @endforeach
                </select>
                @error('timezone') <p class="mt-1 text-xs text-red-600">{{ $message }}</p> @enderror
            </div>
        </div>
    @endif

    @if ($step === 2)
        <div class="space-y-4">
            <h2 class="text-xl font-semibold text-gray-900">Slot granularity</h2>

            <div class="grid gap-3 sm:grid-cols-2">
                @foreach (['30min' => '30 minutes', '1hour' => '1 hour', 'half_day' => 'Half day', 'full_day' => 'Full day'] as $value => $label)
                    <label class="flex cursor-pointer items-center gap-3 rounded-lg border border-gray-300 px-4 py-3 text-sm">
                        <input type="radio" wire:model.live="slotGranularity" value="{{ $value }}" class="border-gray-400" />
                        <span>{{ $label }}</span>
                    </label>
                @endforeach
            </div>
            @error('slotGranularity') <p class="text-xs text-red-600">{{ $message }}</p> @enderror
        </div>
    @endif

    @if ($step === 3)
        <div class="space-y-6">
            <div>
                <h2 class="text-xl font-semibold text-gray-900">Select dates and slots</h2>
                <p class="mt-1 text-sm text-gray-600">Select one or more dates from this month, then refine candidate slots below.</p>
            </div>

            <div class="grid grid-cols-2 gap-2 sm:grid-cols-4 lg:grid-cols-7">
                @foreach($monthDays as $day)
                    <button
                        type="button"
                        wire:click="toggleDate('{{ $day }}')"
                        class="rounded-md border px-2 py-2 text-xs font-medium {{ in_array($day, $selectedDates, true) ? 'border-brand bg-brand text-white' : 'border-gray-300 bg-white text-gray-700' }}"
                    >
                        {{ \Carbon\Carbon::parse($day)->format('D j M') }}
                    </button>
                @endforeach
            </div>
            @error('selectedDates') <p class="text-xs text-red-600">{{ $message }}</p> @enderror

            <div class="space-y-4">
                @foreach($selectedDates as $date)
                    <div class="rounded-lg border border-gray-200 p-4">
                        <div class="mb-3 flex items-center justify-between">
                            <h3 class="text-sm font-semibold text-gray-900">{{ \Carbon\Carbon::parse($date)->format('l, j M Y') }}</h3>
                            @if(!in_array($slotGranularity, ['full_day', 'half_day'], true))
                                <div class="flex items-center gap-2 text-xs">
                                    <label for="start-{{ $date }}" class="text-gray-600">Window</label>
                                    <input id="start-{{ $date }}" type="time" value="{{ $dayWindows[$date]['start'] ?? '09:00' }}" wire:change="updateWindow('{{ $date }}', 'start', $event.target.value)" class="rounded border border-gray-300 px-2 py-1" />
                                    <span>-</span>
                                    <input type="time" value="{{ $dayWindows[$date]['end'] ?? '17:00' }}" wire:change="updateWindow('{{ $date }}', 'end', $event.target.value)" class="rounded border border-gray-300 px-2 py-1" />
                                </div>
                            @endif
                        </div>

                        <div class="flex flex-wrap gap-2">
                            @forelse(($candidateSlots[$date] ?? []) as $slot)
                                <button
                                    type="button"
                                    wire:click="toggleSlot('{{ $slot['key'] }}')"
                                    class="rounded-full border px-3 py-1 text-xs {{ in_array($slot['key'], $selectedSlotKeys, true) ? 'border-brand bg-brand text-white' : 'border-gray-300 bg-gray-50 text-gray-700' }}"
                                >
                                    {{ $slot['label'] }}
                                </button>
                            @empty
                                <p class="text-xs text-red-600">No slots generated for this date and window.</p>
                            @endforelse
                        </div>
                    </div>
                @endforeach
            </div>
            @error('selectedSlotKeys') <p class="text-xs text-red-600">{{ $message }}</p> @enderror
        </div>
    @endif

    @if ($step === 4)
        <div class="space-y-4">
            <h2 class="text-xl font-semibold text-gray-900">Review and publish</h2>
            <div class="rounded-lg border border-gray-200 bg-gray-50 p-4 text-sm text-gray-700">
                <p><span class="font-medium text-gray-900">Title:</span> {{ $title }}</p>
                <p><span class="font-medium text-gray-900">Timezone:</span> {{ $timezone }}</p>
                <p><span class="font-medium text-gray-900">Granularity:</span> {{ $slotGranularity }}</p>
                <p><span class="font-medium text-gray-900">Selected slots:</span> {{ count($selectedSlotRows) }}</p>
            </div>

            <div class="max-h-64 overflow-y-auto rounded-lg border border-gray-200 bg-white p-3">
                <ul class="space-y-2 text-sm text-gray-700">
                    @foreach($selectedSlotRows as $slot)
                        <li>{{ \Carbon\Carbon::parse($slot['starts_at_local'])->format('D j M, H:i') }} - {{ \Carbon\Carbon::parse($slot['ends_at_local'])->format('H:i') }}</li>
                    @endforeach
                </ul>
            </div>
        </div>
    @endif

    <div class="mt-8 flex items-center justify-between">
        <button type="button" wire:click="previousStep" class="rounded-md border border-gray-300 px-4 py-2 text-sm font-medium text-gray-700 disabled:opacity-50" @disabled($step === 1)>Back</button>

        @if ($step < 4)
            <button type="button" wire:click="nextStep" class="rounded-md bg-brand px-4 py-2 text-sm font-semibold text-white">Continue</button>
        @else
            <button type="button" wire:click="publish" class="rounded-md bg-brand px-4 py-2 text-sm font-semibold text-white">Publish poll</button>
        @endif
    </div>
</div>
