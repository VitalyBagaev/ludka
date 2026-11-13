<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class LiveDrop extends Model
{
    protected $fillable = ['user_id', 'skin_id', 'game_case_id'];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function skin(): BelongsTo
    {
        return $this->belongsTo(Skin::class);
    }

    public function gameCase(): BelongsTo
    {
        return $this->belongsTo(GameCase::class);
    }

    public function toFrontend(): array
    {
        $name = $this->user->name;

        return [
            'id' => $this->id,
            'userId' => $this->user_id,
            'user' => $name,
            'avatar' => $this->user->avatarUrl(),
            'initials' => User::initialsFromName($name),
            'caseName' => $this->gameCase->name,
            'skin' => $this->skin->toFrontend(),
            'createdAt' => $this->created_at->toISOString(),
        ];
    }

    public static function recent(int $limit = 20): \Illuminate\Database\Eloquent\Collection
    {
        return static::query()
            ->with(['user', 'skin', 'gameCase'])
            ->latest()
            ->limit($limit)
            ->get();
    }
}
