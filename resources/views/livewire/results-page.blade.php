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
        <h1 class="text-2xl font-semibold text-gray-900">Results: {{ $poll->title }}</h1>
        <p class="mt-2 text-sm text-gray-600">{{ $poll->voters->count() }} voters · {{ $poll->slots->count() }} slots</p>
        <div class="mt-3 rounded-lg border border-gray-200 bg-gray-50 px-4 py-3 text-sm text-gray-700">
            Times are shown in your timezone: <strong>{{ $viewerTimezone }}</strong>
            <button type="button" class="ml-2 text-brand underline" @click="useCreator = !useCreator">
                <span x-show="!useCreator">Switch to creator's timezone ({{ $poll->creator_tz }})</span>
                <span x-show="useCreator">Switch to detected timezone ({{ $viewerTimezone }})</span>
            </button>
        </div>
    </div>

    <div class="overflow-x-auto rounded-xl border border-gray-200 bg-white shadow-sm">
        <table class="min-w-full divide-y divide-gray-200 text-sm">
            <thead>
                <tr>
                    <th class="sticky left-0 z-10 border-r border-gray-200 bg-white px-3 py-2 text-left text-gray-600">Voter</th>
                    @foreach($poll->slots as $slot)
                        @php $rank = $scores[$slot->id]['rank'] ?? null; @endphp
                        <th class="px-3 py-2 text-left {{ $rank === 1 ? 'bg-green-100' : ($rank === 2 ? 'bg-teal-50' : 'bg-white') }}">
                            <div class="font-medium text-gray-800">
                                <span x-show="!useCreator">{{ \Carbon\Carbon::parse($slot->starts_at)->setTimezone($viewerTimezone)->format('D j M, H:i') }}</span>
                                <span x-show="useCreator">{{ \Carbon\Carbon::parse($slot->starts_at)->setTimezone($poll->creator_tz)->format('D j M, H:i') }}</span>
                            </div>
                            <div class="text-xs text-gray-500">
                                <span x-show="!useCreator">{{ \Carbon\Carbon::parse($slot->ends_at)->setTimezone($viewerTimezone)->format('H:i') }}</span>
                                <span x-show="useCreator">{{ \Carbon\Carbon::parse($slot->ends_at)->setTimezone($poll->creator_tz)->format('H:i') }}</span>
                            </div>
                            @if($rank === 1)
                                <div class="text-xs font-semibold text-green-700">🥇 Best</div>
                            @elseif($rank === 2)
                                <div class="text-xs font-semibold text-teal-700">🥈 Second</div>
                            @endif
                        </th>
                    @endforeach
                </tr>
            </thead>
            <tbody class="divide-y divide-gray-100">
                @foreach($poll->voters as $voter)
                    <tr>
                        <td class="sticky left-0 z-10 border-r border-gray-200 bg-white px-3 py-2 font-medium text-gray-800">{{ $voter->name }}</td>
                        @foreach($poll->slots as $slot)
                            @php
                                $response = optional($voter->votes->firstWhere('slot_id', $slot->id))->response ?? 'no';
                            @endphp
                            <td class="px-3 py-2 text-center">
                                @if($response === 'yes')
                                    <span class="text-green-700">✓ Yes</span>
                                @elseif($response === 'if_needed')
                                    <span class="text-amber-700">~ If needed</span>
                                @else
                                    <span class="text-red-700">✗ No</span>
                                @endif
                            </td>
                        @endforeach
                    </tr>
                @endforeach
            </tbody>
            <tfoot class="border-t border-gray-200 bg-gray-50">
                <tr>
                    <td class="sticky left-0 z-10 border-r border-gray-200 bg-gray-50 px-3 py-2 font-semibold text-gray-700">Score</td>
                    @foreach($poll->slots as $slot)
                        <td class="px-3 py-2 text-center font-semibold text-gray-800">{{ $scores[$slot->id]['score'] ?? 0 }}</td>
                    @endforeach
                </tr>
            </tfoot>
        </table>
    </div>
</div>
