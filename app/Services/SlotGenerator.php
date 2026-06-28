<?php

namespace App\Services;

use Carbon\CarbonImmutable;
use InvalidArgumentException;

class SlotGenerator
{
    /**
     * @return array<int, array<string, string>>
     */
    public function generateForDate(
        string $date,
        string $timezone,
        string $granularity,
        ?string $windowStart = null,
        ?string $windowEnd = null
    ): array {
        $day = CarbonImmutable::parse($date, $timezone);

        return match ($granularity) {
            '30min' => $this->windowSlots($day, $timezone, '30 minutes', $windowStart, $windowEnd),
            '1hour' => $this->windowSlots($day, $timezone, '1 hour', $windowStart, $windowEnd),
            'half_day' => $this->halfDaySlots($day, $timezone),
            'full_day' => [$this->slot(
                $day->startOfDay(),
                $day->setTime(23, 59, 59),
                $timezone,
            )],
            default => throw new InvalidArgumentException('Unsupported slot granularity.'),
        };
    }

    /**
     * @return array<int, array<string, string>>
     */
    private function windowSlots(
        CarbonImmutable $day,
        string $timezone,
        string $step,
        ?string $windowStart,
        ?string $windowEnd
    ): array {
        $startAt = $windowStart ?? config('poll.window.start');
        $endAt = $windowEnd ?? config('poll.window.end');

        $start = CarbonImmutable::parse($day->format('Y-m-d').' '.$startAt, $timezone);
        $end = CarbonImmutable::parse($day->format('Y-m-d').' '.$endAt, $timezone);

        if ($end->lessThanOrEqualTo($start)) {
            throw new InvalidArgumentException('Slot window end must be after start.');
        }

        $slots = [];
        for ($cursor = $start; $cursor->lessThan($end); $cursor = $cursor->add($step)) {
            $slotEnd = $cursor->add($step);
            if ($slotEnd->greaterThan($end)) {
                break;
            }

            $slots[] = $this->slot($cursor, $slotEnd, $timezone);
        }

        return $slots;
    }

    /**
     * @return array<int, array<string, string>>
     */
    private function halfDaySlots(CarbonImmutable $day, string $timezone): array
    {
        $mStart = CarbonImmutable::parse($day->format('Y-m-d').' '.config('poll.half_day.morning_start'), $timezone);
        $mEnd = CarbonImmutable::parse($day->format('Y-m-d').' '.config('poll.half_day.morning_end'), $timezone);
        $aStart = CarbonImmutable::parse($day->format('Y-m-d').' '.config('poll.half_day.afternoon_start'), $timezone);
        $aEnd = CarbonImmutable::parse($day->format('Y-m-d').' '.config('poll.half_day.afternoon_end'), $timezone);

        return [
            $this->slot($mStart, $mEnd, $timezone),
            $this->slot($aStart, $aEnd, $timezone),
        ];
    }

    /**
     * @return array<string, string>
     */
    private function slot(CarbonImmutable $startsLocal, CarbonImmutable $endsLocal, string $timezone): array
    {
        $startsUtc = $startsLocal->setTimezone('UTC');
        $endsUtc = $endsLocal->setTimezone('UTC');

        return [
            'key' => hash('sha256', $startsUtc->format('Y-m-d H:i:s').'|'.$endsUtc->format('Y-m-d H:i:s')),
            'starts_at_utc' => $startsUtc->format('Y-m-d H:i:s'),
            'ends_at_utc' => $endsUtc->format('Y-m-d H:i:s'),
            'starts_at_local' => $startsLocal->format('Y-m-d H:i:s'),
            'ends_at_local' => $endsLocal->format('Y-m-d H:i:s'),
            'label' => $startsLocal->format('D j M, H:i').' - '.$endsLocal->format('H:i').' ('.$timezone.')',
        ];
    }
}
