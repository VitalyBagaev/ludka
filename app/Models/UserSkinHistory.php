<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class UserSkinHistory extends Model
{
    protected $table = 'user_skin_history';

    protected $fillable = [
        'user_id',
        'skin_id',
        'inventory_item_id',
        'live_drop_id',
        'case_name',
        'weapon',
        'skin_name',
        'skin_price',
        'source',
        'status',
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function skin(): BelongsTo
    {
        return $this->belongsTo(Skin::class);
    }

    public function inventoryItem(): BelongsTo
    {
        return $this->belongsTo(InventoryItem::class);
    }

    public static function statusLabel(string $status): string
    {
        return match ($status) {
            'inventory' => 'В инвентаре',
            'sold' => 'Продан',
            'withdrawn' => 'Выведен',
            'withdraw_pending' => 'На выводе',
            'upgrade' => 'Сожжён (апгрейд)',
            'contract' => 'Сожжён (контракт)',
            default => $status,
        };
    }

    public function toFrontend(): array
    {
        return [
            'id' => $this->id,
            'weapon' => $this->weapon,
            'skinName' => $this->skin_name,
            'skinPrice' => $this->skin_price,
            'caseName' => $this->case_name,
            'source' => $this->source,
            'status' => $this->status,
            'statusLabel' => self::statusLabel($this->status),
            'skin' => $this->skin?->toFrontend(),
            'createdAt' => $this->created_at?->toISOString(),
        ];
    }
}
