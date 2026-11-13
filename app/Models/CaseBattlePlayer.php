<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class CaseBattlePlayer extends Model
{
    protected $fillable = [
        'case_battle_id',
        'user_id',
        'slot',
        'is_bot',
        'bot_name',
        'total_drop_value',
    ];

    protected $casts = [
        'is_bot' => 'boolean',
    ];

    public function battle(): BelongsTo
    {
        return $this->belongsTo(CaseBattle::class, 'case_battle_id');
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
}
