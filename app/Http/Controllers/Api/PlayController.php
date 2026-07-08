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

        return response()->json(['status' => 'ok', 'id' => $play->id], 200);
    }
}