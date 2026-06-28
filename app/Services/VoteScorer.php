<?php

namespace App\Services;

use App\Models\Poll;
use Illuminate\Support\Facades\DB;

class VoteScorer
{
    /**
     * @return array<string, array{yes_count:int, if_needed_count:int, no_count:int, score:int, rank:int|null}>
     */
    public function scoreBySlot(Poll $poll): array
    {
        $rows = DB::table('poll_slots')
            ->leftJoin('votes', 'votes.slot_id', '=', 'poll_slots.id')
            ->selectRaw('poll_slots.id as slot_id')
            ->selectRaw("SUM(CASE WHEN votes.response = 'yes' THEN 1 ELSE 0 END) as yes_count")
            ->selectRaw("SUM(CASE WHEN votes.response = 'if_needed' THEN 1 ELSE 0 END) as if_needed_count")
            ->selectRaw("SUM(CASE WHEN votes.response = 'no' THEN 1 ELSE 0 END) as no_count")
            ->where('poll_slots.poll_id', $poll->id)
            ->groupBy('poll_slots.id')
            ->get();

        $scores = [];
        foreach ($rows as $row) {
            $yes = (int) $row->yes_count;
            $ifNeeded = (int) $row->if_needed_count;
            $no = (int) $row->no_count;
            $score = ($yes * 2) + ($ifNeeded * 1) + ($no * 0);

            $scores[(string) $row->slot_id] = [
                'yes_count' => $yes,
                'if_needed_count' => $ifNeeded,
                'no_count' => $no,
                'score' => $score,
                'rank' => null,
            ];
        }

        $uniqueScores = array_values(array_unique(array_map(fn (array $r): int => $r['score'], $scores)));
        rsort($uniqueScores);

        $first = $uniqueScores[0] ?? null;
        $second = $uniqueScores[1] ?? null;

        foreach ($scores as $slotId => $data) {
            if ($first !== null && $data['score'] === $first) {
                $scores[$slotId]['rank'] = 1;
                continue;
            }

            if ($second !== null && $data['score'] === $second) {
                $scores[$slotId]['rank'] = 2;
            }
        }

        return $scores;
    }
}
