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
            'sneak_time' => 'nullable|numeric|min:0',
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
            'sneak_time' => $validated['sneak_time'] ?? 0,
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
            'scope' => 'nullable|in:all,player,pair',
            'player_name' => 'nullable|string|max:100',
            'pair_key' => 'nullable|string|max:500',
        ]);

        $scope = $validated['scope'] ?? 'all';
        $selectedPlayer = trim($validated['player_name'] ?? '');
        $selectedPairKey = $validated['pair_key'] ?? '';

        $playerNames = Play::query()
            ->whereNotNull('player_name')
            ->where('player_name', '<>', '')
            ->distinct()
            ->orderBy('player_name')
            ->pluck('player_name')
            ->values();

        $sessionPlayers = $this->getSessionPlayers();

        $pairOptions = $sessionPlayers
            ->map(function ($rows) {
                $names = $this->normalizePairNames($rows);

                if (count($names) !== 2) {
                    return null;
                }

                return [
                    'key' => base64_encode(
                        json_encode($names, JSON_UNESCAPED_UNICODE)
                    ),
                    'label' => implode(' × ', $names),
                    'players' => $names,
                ];
            })
            ->filter()
            ->unique('key')
            ->sortBy('label')
            ->values();

        $baseQuery = Play::query();
        $this->applyScope(
            $baseQuery,
            $scope,
            $selectedPlayer,
            $selectedPairKey,
            $sessionPlayers
        );

        $sessionStats = (clone $baseQuery)
            ->selectRaw(
                'session_id,
                 AVG(clear_time) AS clear_time,
                 MAX(mission_count) AS mission_count,
                 MAX(mission1_done) AS mission1_done,
                 MAX(mission2_done) AS mission2_done,
                 MAX(mission3_done) AS mission3_done,
                 MIN(room_id) AS room_id,
                 MIN(played_at) AS played_at'
            )
            ->groupBy('session_id')
            ->get();

        $gameCount = $sessionStats->count();

        $avgClearTime = $sessionStats
            ->whereNotNull('clear_time')
            ->avg('clear_time');

        $avgSneakTime = (clone $baseQuery)->avg('sneak_time');

        $actionAverages = (clone $baseQuery)
            ->selectRaw(
                'AVG(death_count) AS death_count,
                 AVG(punch_count) AS punch_count,
                 AVG(chat_count) AS chat_count,
                 AVG(stamina_item_count) AS stamina_item_count'
            )
            ->first();

        $averageActions = [
            'death_count' => $this->roundAverage(
                $actionAverages->death_count ?? null
            ),
            'punch_count' => $this->roundAverage(
                $actionAverages->punch_count ?? null
            ),
            'chat_count' => $this->roundAverage(
                $actionAverages->chat_count ?? null
            ),
            'stamina_item_count' => $this->roundAverage(
                $actionAverages->stamina_item_count ?? null
            ),
        ];

        $hourCounts = $sessionStats
            ->filter(function ($row) {
                return $row->played_at !== null;
            })
            ->groupBy(function ($row) {
                return (int) date(
                    'G',
                    strtotime((string) $row->played_at)
                );
            })
            ->map(function ($rows) {
                return $rows->count();
            });

        $timeRangeStats = collect(range(0, 23))
            ->map(function ($hour) use ($hourCounts) {
                return [
                    'hour' => $hour,
                    'play_count' => (int) ($hourCounts->get($hour) ?? 0),
                ];
            })
            ->values();

        $missionCounts = $sessionStats
            ->groupBy(function ($row) {
                return (int) $row->mission_count;
            })
            ->map(function ($rows) {
                return $rows->count();
            });

        $missionDistribution = collect(range(0, 3))
            ->map(function ($missionCount) use ($missionCounts) {
                return [
                    'mission_count' => $missionCount,
                    'play_count' => (int) (
                        $missionCounts->get($missionCount) ?? 0
                    ),
                ];
            })
            ->values();

        $missionCompletionRates = collect([
            [
                'mission_number' => 1,
                'name' => 'アイテムを取得してゴール',
                'completed_count' => $sessionStats
                    ->where('mission1_done', 1)
                    ->count(),
            ],
            [
                'mission_number' => 2,
                'name' => '制限時間内にゴール',
                'completed_count' => $sessionStats
                    ->where('mission2_done', 1)
                    ->count(),
            ],
            [
                'mission_number' => 3,
                'name' => '敵に見つからずゴール',
                'completed_count' => $sessionStats
                    ->where('mission3_done', 1)
                    ->count(),
            ],
        ])->map(function ($mission) use ($gameCount) {
            $mission['completion_rate'] = $this->calculateRate(
                $mission['completed_count'],
                $gameCount
            );

            return $mission;
        })->values();

        $roomStats = $sessionStats
            ->groupBy('room_id')
            ->map(function ($rows, $roomId) {
                return [
                    'room_id' => $roomId,
                    'play_count' => $rows->count(),
                ];
            })
            ->sortByDesc('play_count')
            ->values();

        $playerSummaries = Play::query()
            ->selectRaw(
                'player_name,
                 COUNT(DISTINCT session_id) AS play_count,
                 MIN(clear_time) AS best_clear_time,
                 AVG(clear_time) AS avg_clear_time,
                 AVG(sneak_time) AS avg_sneak_time,
                 AVG(mission_count) AS avg_mission_count'
            )
            ->whereNotNull('player_name')
            ->where('player_name', '<>', '')
            ->groupBy('player_name')
            ->orderByDesc('play_count')
            ->orderBy('player_name')
            ->get()
            ->map(function ($row) {
                return [
                    'player_name' => $row->player_name,
                    'play_count' => (int) $row->play_count,
                    'best_clear_time' =>
                        $row->best_clear_time !== null
                            ? round((float) $row->best_clear_time, 2)
                            : null,
                    'avg_clear_time' =>
                        $row->avg_clear_time !== null
                            ? round((float) $row->avg_clear_time, 2)
                            : null,
                    'avg_sneak_time' =>
                        $this->roundAverage($row->avg_sneak_time),
                    'avg_mission_count' =>
                        $this->roundAverage($row->avg_mission_count),
                ];
            })
            ->values();

        $playHistory = (clone $baseQuery)
            ->orderByDesc('played_at')
            ->orderByDesc('id')
            ->limit(100)
            ->get([
                'id',
                'session_id',
                'player_name',
                'clear_time',
                'mission_count',
                'death_count',
                'punch_count',
                'chat_count',
                'stamina_item_count',
                'sneak_time',
                'room_id',
                'played_at',
            ])
            ->map(function ($play) {
                return [
                    'id' => $play->id,
                    'session_id' => $play->session_id,
                    'player_name' => $play->player_name,
                    'clear_time' =>
                        $play->clear_time !== null
                            ? round((float) $play->clear_time, 2)
                            : null,
                    'mission_count' => (int) $play->mission_count,
                    'death_count' => (int) $play->death_count,
                    'punch_count' => (int) $play->punch_count,
                    'chat_count' => (int) $play->chat_count,
                    'stamina_item_count' =>
                        (int) $play->stamina_item_count,
                    'sneak_time' =>
                        round((float) ($play->sneak_time ?? 0), 2),
                    'room_id' => $play->room_id,
                    'played_at' =>
                        $play->played_at !== null
                            ? date(
                                'Y-m-d H:i:s',
                                strtotime((string) $play->played_at)
                            )
                            : null,
                ];
            })
            ->values();

        return response()->json([
            'selected_scope' => $scope,
            'selected_player' => $selectedPlayer,
            'selected_pair_key' => $selectedPairKey,
            'player_names' => $playerNames,
            'pair_options' => $pairOptions,
            'game_count' => $gameCount,
            'avg_clear_time' =>
                $avgClearTime !== null
                    ? round((float) $avgClearTime, 2)
                    : null,
            'avg_sneak_time' =>
                $this->roundAverage($avgSneakTime),
            'average_actions' => $averageActions,
            'time_range_stats' => $timeRangeStats,
            'mission_distribution' => $missionDistribution,
            'mission_completion_rates' => $missionCompletionRates,
            'room_stats' => $roomStats,
            'player_summaries' => $playerSummaries,
            'play_history' => $playHistory,
        ]);
    }

    public function export(Request $request)
    {
        $validated = $request->validate([
            'scope' => 'nullable|in:all,player,pair',
            'player_name' => 'nullable|string|max:100',
            'pair_key' => 'nullable|string|max:500',
        ]);

        $scope = $validated['scope'] ?? 'all';
        $selectedPlayer = trim($validated['player_name'] ?? '');
        $selectedPairKey = $validated['pair_key'] ?? '';

        $query = Play::query();

        $this->applyScope(
            $query,
            $scope,
            $selectedPlayer,
            $selectedPairKey,
            $this->getSessionPlayers()
        );

        $fileName = 'play_logs_' .
            $scope . '_' .
            now()->format('Ymd_His') .
            '.csv';

        $headers = [
            'Content-Type' => 'text/csv; charset=UTF-8',
            'Cache-Control' => 'no-store, no-cache',
        ];

        $callback = function () use ($query) {
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
                'sneak_time',
                'room_id',
                'played_at',
                'created_at',
                'updated_at',
            ]);

            $query
                ->orderBy('played_at')
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
                            $play->sneak_time ?? 0,
                            $play->room_id,
                            $play->played_at !== null
                                ? date(
                                    'Y-m-d H:i:s',
                                    strtotime((string) $play->played_at)
                                )
                                : '',
                            $play->created_at !== null
                                ? date(
                                    'Y-m-d H:i:s',
                                    strtotime((string) $play->created_at)
                                )
                                : '',
                            $play->updated_at !== null
                                ? date(
                                    'Y-m-d H:i:s',
                                    strtotime((string) $play->updated_at)
                                )
                                : '',
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

    private function getSessionPlayers()
    {
        return Play::query()
            ->whereNotNull('session_id')
            ->where('session_id', '<>', '')
            ->whereNotNull('player_name')
            ->where('player_name', '<>', '')
            ->get([
                'session_id',
                'player_name',
            ])
            ->groupBy('session_id');
    }

    private function applyScope(
        $query,
        string $scope,
        string $selectedPlayer,
        string $selectedPairKey,
        $sessionPlayers
    ): void {
        if ($scope === 'player') {
            if ($selectedPlayer === '') {
                $query->whereRaw('1 = 0');
                return;
            }

            $query->where('player_name', $selectedPlayer);
            return;
        }

        if ($scope === 'pair') {
            $pairNames = $this->decodePairKey($selectedPairKey);

            if (count($pairNames) !== 2) {
                $query->whereRaw('1 = 0');
                return;
            }

            $sessionIds = $sessionPlayers
                ->filter(function ($rows) use ($pairNames) {
                    return $this->normalizePairNames($rows) === $pairNames;
                })
                ->keys()
                ->values()
                ->all();

            if (empty($sessionIds)) {
                $query->whereRaw('1 = 0');
                return;
            }

            $query->whereIn('session_id', $sessionIds);
        }
    }

    private function normalizePairNames($rows): array
    {
        $names = $rows
            ->pluck('player_name')
            ->filter(function ($name) {
                return $name !== null
                    && trim((string) $name) !== '';
            })
            ->map(function ($name) {
                return (string) $name;
            })
            ->values()
            ->all();

        natcasesort($names);

        return array_values($names);
    }

    private function decodePairKey(?string $pairKey): array
    {
        if (empty($pairKey)) {
            return [];
        }

        $decoded = base64_decode($pairKey, true);

        if ($decoded === false) {
            return [];
        }

        $names = json_decode($decoded, true);

        if (!is_array($names) || count($names) !== 2) {
            return [];
        }

        $names = array_map('strval', $names);

        natcasesort($names);

        return array_values($names);
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
        return $value !== null
            ? round((float) $value, 2)
            : 0.0;
    }
}
