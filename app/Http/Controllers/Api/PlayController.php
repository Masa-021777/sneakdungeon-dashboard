<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Play;
use Illuminate\Http\Request;

class PlayController extends Controller
{
    public function store(Request $request)
    {
        $validated = $request->validate([
            'session_id' => 'required|string|max:36',
            'name' => 'required|string|max:100',
            'clear_time' => 'nullable|numeric',
            'mission_count' => 'required|integer|min:0|max:3',
            'mission1_done' => 'required|boolean',
            'mission2_done' => 'required|boolean',
            'mission3_done' => 'required|boolean',
            'death_count' => 'required|integer|min:0',
            'punch_count' => 'required|integer|min:0',
            'chat_count' => 'required|integer|min:0',
            'stamina_item_count' => 'required|integer|min:0',
            'room_id' => 'required|string|max:20',
            'played_at' => 'nullable|date',
        ]);

        $play = Play::create([
            'session_id' => $validated['session_id'],
            'player_name' => $validated['name'],
            'clear_time' => $validated['clear_time'] ?? null,
            'mission_count' => $validated['mission_count'],
            'mission1_done' => $validated['mission1_done'],
            'mission2_done' => $validated['mission2_done'],
            'mission3_done' => $validated['mission3_done'],
            'death_count' => $validated['death_count'],
            'punch_count' => $validated['punch_count'],
            'chat_count' => $validated['chat_count'],
            'stamina_item_count' => $validated['stamina_item_count'],
            'room_id' => $validated['room_id'],
            'played_at' => $validated['played_at'] ?? now(),
        ]);

        return response()->json([
            'status' => 'ok',
            'id' => $play->id,
        ], 200);
    }

    public function rankings(Request $request)
    {
        $sort = $request->query('sort', 'time');
        $limit = (int) $request->query('limit', 50);
        $limit = max(1, min($limit, 100));

        $query = Play::query();

        if ($sort === 'mission') {
            $query->orderByDesc('mission_count')
                ->orderBy('clear_time');
        } else {
            $query->whereNotNull('clear_time')
                ->orderBy('clear_time');
        }

        $plays = $query->limit($limit)->get([
            'player_name',
            'clear_time',
            'mission_count',
            'room_id',
            'played_at',
        ]);

        return response()->json($plays);
    }

    public function stats(Request $request)
    {
        $validated = $request->validate([
            'player_name' => 'nullable|string|max:100',
        ]);

        $selectedPlayer = $validated['player_name'] ?? null;
        $selectedPlayer = $selectedPlayer !== null && $selectedPlayer !== ''
            ? $selectedPlayer
            : null;

        $baseQuery = Play::query();

        if ($selectedPlayer !== null) {
            $baseQuery->where('player_name', $selectedPlayer);
        }

        $playerNames = Play::query()
            ->whereNotNull('player_name')
            ->where('player_name', '<>', '')
            ->distinct()
            ->orderBy('player_name')
            ->pluck('player_name')
            ->values();

        $playerSummaries = Play::query()
            ->selectRaw(
                'player_name,
                 COUNT(*) AS play_count,
                 SUM(CASE WHEN clear_time IS NOT NULL THEN 1 ELSE 0 END) AS cleared_count,
                 MIN(clear_time) AS best_clear_time,
                 AVG(clear_time) AS avg_clear_time,
                 AVG(mission_count) AS avg_mission_count'
            )
            ->whereNotNull('player_name')
            ->where('player_name', '<>', '')
            ->groupBy('player_name')
            ->orderByDesc('play_count')
            ->orderBy('player_name')
            ->get()
            ->map(function ($row) {
                $playCount = (int) $row->play_count;
                $clearedCount = (int) $row->cleared_count;

                return [
                    'player_name' => $row->player_name,
                    'play_count' => $playCount,
                    'cleared_count' => $clearedCount,
                    'clear_rate' => $this->calculateRate($clearedCount, $playCount),
                    'best_clear_time' => $row->best_clear_time !== null
                        ? round((float) $row->best_clear_time, 2)
                        : null,
                    'avg_clear_time' => $row->avg_clear_time !== null
                        ? round((float) $row->avg_clear_time, 2)
                        : null,
                    'avg_mission_count' => $this->roundAverage(
                        $row->avg_mission_count ?? null
                    ),
                ];
            })
            ->values();

        $totalPlays = (clone $baseQuery)->count();
        $clearedPlays = (clone $baseQuery)
            ->whereNotNull('clear_time')
            ->count();

        $clearRate = $this->calculateRate($clearedPlays, $totalPlays);

        $avgClearTime = (clone $baseQuery)
            ->whereNotNull('clear_time')
            ->avg('clear_time');

        $playsByHour = (clone $baseQuery)
            ->selectRaw('HOUR(played_at) AS hour, COUNT(*) AS play_count')
            ->groupByRaw('HOUR(played_at)')
            ->orderBy('hour')
            ->get()
            ->keyBy('hour');

        $timeRangeStats = collect(range(0, 23))
            ->map(function ($hour) use ($playsByHour) {
                $row = $playsByHour->get($hour);

                return [
                    'hour' => $hour,
                    'play_count' => $row ? (int) $row->play_count : 0,
                ];
            })
            ->values();

        $playsByMissionCount = (clone $baseQuery)
            ->selectRaw('mission_count, COUNT(*) AS play_count')
            ->groupBy('mission_count')
            ->orderBy('mission_count')
            ->get()
            ->keyBy('mission_count');

        $missionDistribution = collect(range(0, 3))
            ->map(function ($missionCount) use ($playsByMissionCount) {
                $row = $playsByMissionCount->get($missionCount);

                return [
                    'mission_count' => $missionCount,
                    'play_count' => $row ? (int) $row->play_count : 0,
                ];
            })
            ->values();

        $missionTotals = (clone $baseQuery)
            ->selectRaw(
                'SUM(CASE WHEN mission1_done = 1 THEN 1 ELSE 0 END) AS mission1_completed,
                 SUM(CASE WHEN mission2_done = 1 THEN 1 ELSE 0 END) AS mission2_completed,
                 SUM(CASE WHEN mission3_done = 1 THEN 1 ELSE 0 END) AS mission3_completed'
            )
            ->first();

        $missionCompletionRates = collect([
            [
                'mission_number' => 1,
                'name' => 'アイテムを取得してゴール',
                'completed_count' => (int) ($missionTotals->mission1_completed ?? 0),
            ],
            [
                'mission_number' => 2,
                'name' => '制限時間内にゴール',
                'completed_count' => (int) ($missionTotals->mission2_completed ?? 0),
            ],
            [
                'mission_number' => 3,
                'name' => '敵に見つからずゴール',
                'completed_count' => (int) ($missionTotals->mission3_completed ?? 0),
            ],
        ])->map(function ($mission) use ($totalPlays) {
            $mission['completion_rate'] = $this->calculateRate(
                $mission['completed_count'],
                $totalPlays
            );

            return $mission;
        })->values();

        $actionAverages = (clone $baseQuery)
            ->selectRaw(
                'AVG(death_count) AS death_count,
                 AVG(punch_count) AS punch_count,
                 AVG(chat_count) AS chat_count,
                 AVG(stamina_item_count) AS stamina_item_count'
            )
            ->first();

        $averageActions = [
            'death_count' => $this->roundAverage($actionAverages->death_count ?? null),
            'punch_count' => $this->roundAverage($actionAverages->punch_count ?? null),
            'chat_count' => $this->roundAverage($actionAverages->chat_count ?? null),
            'stamina_item_count' => $this->roundAverage(
                $actionAverages->stamina_item_count ?? null
            ),
        ];

        $roomStats = (clone $baseQuery)
            ->selectRaw('room_id, COUNT(*) AS play_count')
            ->groupBy('room_id')
            ->orderByDesc('play_count')
            ->orderBy('room_id')
            ->get()
            ->map(function ($row) {
                return [
                    'room_id' => $row->room_id,
                    'play_count' => (int) $row->play_count,
                ];
            })
            ->values();

        return response()->json([
            'selected_player' => $selectedPlayer,
            'player_names' => $playerNames,
            'player_summaries' => $playerSummaries,
            'total_plays' => $totalPlays,
            'cleared_plays' => $clearedPlays,
            'clear_rate' => $clearRate,
            'avg_clear_time' => $avgClearTime !== null
                ? round((float) $avgClearTime, 2)
                : null,
            'time_range_stats' => $timeRangeStats,
            'mission_distribution' => $missionDistribution,
            'mission_completion_rates' => $missionCompletionRates,
            'average_actions' => $averageActions,
            'room_stats' => $roomStats,
        ]);
    }

    public function export(Request $request)
    {
        $validated = $request->validate([
            'player_name' => 'nullable|string|max:100',
        ]);

        $selectedPlayer = $validated['player_name'] ?? null;
        $selectedPlayer = $selectedPlayer !== null && $selectedPlayer !== ''
            ? $selectedPlayer
            : null;

        $fileName = $selectedPlayer === null
            ? 'play_logs_all_' . now()->format('Ymd_His') . '.csv'
            : 'play_logs_player_' . now()->format('Ymd_His') . '.csv';

        $headers = [
            'Content-Type' => 'text/csv; charset=UTF-8',
            'Cache-Control' => 'no-store, no-cache',
        ];

        $callback = function () use ($selectedPlayer) {
            $output = fopen('php://output', 'w');

            fwrite($output, "\xEF\xBB\xBF");

            fputcsv($output, [
                'id',
                'session_id',
                'player_name',
                'clear_time',
                'mission_count',
                'mission1_done',
                'mission2_done',
                'mission3_done',
                'death_count',
                'punch_count',
                'chat_count',
                'stamina_item_count',
                'room_id',
                'played_at',
                'created_at',
                'updated_at',
            ]);

            $query = Play::query();

            if ($selectedPlayer !== null) {
                $query->where('player_name', $selectedPlayer);
            }

            $query->orderBy('played_at')
                ->orderBy('id')
                ->chunk(500, function ($plays) use ($output) {
                    foreach ($plays as $play) {
                        fputcsv($output, [
                            $play->id,
                            $play->session_id,
                            $play->player_name,
                            $play->clear_time ?? '',
                            $play->mission_count,
                            $play->mission1_done ? 1 : 0,
                            $play->mission2_done ? 1 : 0,
                            $play->mission3_done ? 1 : 0,
                            $play->death_count,
                            $play->punch_count,
                            $play->chat_count,
                            $play->stamina_item_count,
                            $play->room_id,
                            optional($play->played_at)->format('Y-m-d H:i:s'),
                            optional($play->created_at)->format('Y-m-d H:i:s'),
                            optional($play->updated_at)->format('Y-m-d H:i:s'),
                        ]);
                    }
                });

            fclose($output);
        };

        return response()->streamDownload(
            $callback,
            $fileName,
            $headers
        );
    }

    private function calculateRate(int $completedCount, int $totalCount): float
    {
        if ($totalCount === 0) {
            return 0.0;
        }

        return round(($completedCount / $totalCount) * 100, 1);
    }

    private function roundAverage($value): float
    {
        return $value !== null ? round((float) $value, 2) : 0.0;
    }
}
