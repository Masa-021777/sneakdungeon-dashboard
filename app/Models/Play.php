<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Play extends Model
{
    use HasFactory;

    protected $fillable = [
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
    ];

    protected $casts = [
        'mission1_done' => 'boolean',
        'mission2_done' => 'boolean',
        'mission3_done' => 'boolean',
        'played_at' => 'datetime',
    ];
}