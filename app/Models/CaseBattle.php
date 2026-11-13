<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class CaseBattle extends Model
{
    protected $fillable = [
        'creator_id',
        'max_players',
        'total_price',
        'case_ids',
        'status',
        'winner_user_id',
        'winner_slot',
        'results',
    ];

    protected $casts = [
        'case_ids' => 'array',
        'results' => 'array',
    ];

    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'creator_id');
    }

    public function players(): HasMany
    {
        return $this->hasMany(CaseBattlePlayer::class)->orderBy('slot');
    }

    public function toFrontend(): array
    {
        $casesMap = GameCase::whereIn('id', $this->case_ids ?? [])->get()->keyBy('id');
        $caseNames = collect($this->case_ids ?? [])
            ->map(fn ($id) => $casesMap->get($id)?->name ?? 'Кейс')
            ->values()
            ->all();

        return [
            'id' => $this->id,
            'creatorId' => $this->creator_id,
            'players' => $this->players->count(),
            'maxPlayers' => $this->max_players,
            'cases' => $caseNames,
            'caseIds' => $this->case_ids ?? [],
            'totalPrice' => $this->total_price,
            'status' => $this->status,
            'winnerSlot' => $this->winner_slot,
            'winnerUserId' => $this->winner_user_id,
            'results' => $this->results,
            'slots' => $this->players->map(fn (CaseBattlePlayer $p) => [
                'slot' => $p->slot,
                'userId' => $p->user_id,
                'isBot' => $p->is_bot,
                'name' => $p->is_bot ? $p->bot_name : ($p->user?->name ?? 'Игрок'),
                'avatar' => $p->is_bot ? null : $p->user?->avatarUrl(),
                'total' => $p->total_drop_value,
            ])->values()->all(),
        ];
    }
}
