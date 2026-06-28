<div
    x-data="{ useCreator: @entangle('useCreatorTimezone') }"
    x-init="
        const tz = Intl.DateTimeFormat().resolvedOptions().timeZone;
        if (tz) {
            $wire.setBrowserTimezone(tz);
        }
    "
    class="space-y-6"
>
    <div class="rounded-xl border border-gray-200 bg-white p-6 shadow-sm">
        <h1 class="text-2xl font-semibold text-gray-900">{{ $poll->title }}</h1>
        @if($poll->description)
            <p class="mt-2 text-sm text-gray-600">{{ $poll->description }}</p>
        @endif

        <div class="mt-4 rounded-lg border border-gray-200 bg-gray-50 px-4 py-3 text-sm text-gray-700">
            Times are shown in your timezone: <strong>{{ $viewerTimezone }}</strong>
            <button type="button" class="ml-2 text-brand underline" @click="useCreator = !useCreator">
                <span x-show="!useCreator">Switch to creator's timezone ({{ $poll->creator_tz }})</span>
                <span x-show="useCreator">Switch to detected timezone ({{ $viewerTimezone }})</span>
            </button>
        </div>
    </div>

    @if(!$voter)
        <div class="max-w-lg rounded-xl border border-gray-200 bg-white p-6 shadow-sm">
            <h2 class="text-lg font-semibold text-gray-900">Who is voting?</h2>
            <p class="mt-1 text-sm text-gray-600">Enter your details to cast votes and receive your return link.</p>

            <div class="mt-4 space-y-4">
                <div>
                    <label for="voter-name" class="mb-1 block text-sm font-medium text-gray-700">Name</label>
                    <input id="voter-name" type="text" wire:model="name" class="w-full rounded-md border border-gray-300 px-3 py-2 text-sm" />
                    @error('name') <p class="mt-1 text-xs text-red-600">{{ $message }}</p> @enderror
                </div>
                <div>
                    <label for="voter-email" class="mb-1 block text-sm font-medium text-gray-700">Email</label>
                    <input id="voter-email" type="email" wire:model="email" class="w-full rounded-md border border-gray-300 px-3 py-2 text-sm" />
                    @error('email') <p class="mt-1 text-xs text-red-600">{{ $message }}</p> @enderror
                </div>
                <button type="button" wire:click="identifyVoter" class="rounded-md bg-brand px-4 py-2 text-sm font-semibold text-white">Continue to vote</button>
            </div>
        </div>
    @else
        <div class="rounded-xl border border-gray-200 bg-white p-6 shadow-sm">
            <h2 class="text-lg font-semibold text-gray-900">Vote on each slot</h2>
            @if($poll->status === 'closed')
                <p class="mt-2 text-sm text-red-700">This poll is closed. You can view results but cannot change votes.</p>
            @endif

            <div class="mt-4 overflow-x-auto">
                <table class="min-w-full divide-y divide-gray-200 text-sm">
                    <thead>
                        <tr class="text-left text-gray-600">
                            <th class="px-3 py-2">Slot</th>
                            <th class="px-3 py-2">Response</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-100">
                        @foreach($poll->slots as $slot)
                            <tr>
                                <td class="px-3 py-3 align-top text-gray-800">
                                    <span x-show="!useCreator">{{ \Carbon\Carbon::parse($slot->starts_at)->setTimezone($viewerTimezone)->format('D j M, H:i') }} - {{ \Carbon\Carbon::parse($slot->ends_at)->setTimezone($viewerTimezone)->format('H:i') }}</span>
                                    <span x-show="useCreator">{{ \Carbon\Carbon::parse($slot->starts_at)->setTimezone($poll->creator_tz)->format('D j M, H:i') }} - {{ \Carbon\Carbon::parse($slot->ends_at)->setTimezone($poll->creator_tz)->format('H:i') }}</span>
                                </td>
                                <td class="px-3 py-3">
                                    <div class="flex flex-wrap gap-2">
                                        <button type="button" wire:click="$set('responses.{{ $slot->id }}', 'yes')" class="rounded-md border px-3 py-1 text-xs font-semibold {{ ($responses[$slot->id] ?? 'no') === 'yes' ? 'border-green-700 bg-green-600 text-white' : 'border-green-300 bg-green-50 text-green-700' }}">✓ Yes</button>
                                        <button type="button" wire:click="$set('responses.{{ $slot->id }}', 'if_needed')" class="rounded-md border px-3 py-1 text-xs font-semibold {{ ($responses[$slot->id] ?? 'no') === 'if_needed' ? 'border-amber-700 bg-amber-500 text-white' : 'border-amber-300 bg-amber-50 text-amber-700' }}">~ If needed</button>
                                        <button type="button" wire:click="$set('responses.{{ $slot->id }}', 'no')" class="rounded-md border px-3 py-1 text-xs font-semibold {{ ($responses[$slot->id] ?? 'no') === 'no' ? 'border-red-700 bg-red-600 text-white' : 'border-red-300 bg-red-50 text-red-700' }}">✗ No</button>
                                    </div>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>

            <div class="mt-6">
                <button type="button" wire:click="submitVotes" @disabled($poll->status === 'closed') class="rounded-md bg-brand px-4 py-2 text-sm font-semibold text-white disabled:opacity-50">Save votes</button>
            </div>
        </div>
    @endif
</div>
