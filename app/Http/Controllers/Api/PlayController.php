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
        $limit = $request->query('limit', 50);

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

    public function stats()
    {
        $totalPlays = Play::count();

        $clearedPlays = Play::whereNotNull('clear_time')->count();

        $clearRate = $totalPlays > 0
            ? round(($clearedPlays / $totalPlays) * 100, 1)
            : 0.0;

        $avgClearTime = Play::whereNotNull('clear_time')
            ->avg('clear_time');

        // 0時〜23時の時間帯別プレイ数
        $playsByHour = Play::selectRaw(
            'HOUR(played_at) AS hour, COUNT(*) AS play_count'
        )
            ->groupByRaw('HOUR(played_at)')
            ->orderBy('hour')
            ->get()
            ->keyBy('hour');

        $timeRangeStats = collect(range(0, 23))
            ->map(function ($hour) use ($playsByHour) {
                $row = $playsByHour->get($hour);

                return [
                    'hour' => $hour,
                    'play_count' => $row
                        ? (int) $row->play_count
                        : 0,
                ];
            });

        // ミッション達成数0〜3個の分布
        $playsByMissionCount = Play::selectRaw(
            'mission_count, COUNT(*) AS play_count'
        )
            ->groupBy('mission_count')
            ->orderBy('mission_count')
            ->get()
            ->keyBy('mission_count');

        $missionDistribution = collect(range(0, 3))
            ->map(function ($missionCount) use ($playsByMissionCount) {
                $row = $playsByMissionCount->get($missionCount);

                return [
                    'mission_count' => $missionCount,
                    'play_count' => $row
                        ? (int) $row->play_count
                        : 0,
                ];
            });

        return response()->json([
            'total_plays' => $totalPlays,
            'cleared_plays' => $clearedPlays,
            'clear_rate' => $clearRate,
            'avg_clear_time' => $avgClearTime !== null
                ? round((float) $avgClearTime, 2)
                : null,
            'time_range_stats' => $timeRangeStats,
            'mission_distribution' => $missionDistribution,
        ]);
    }
}