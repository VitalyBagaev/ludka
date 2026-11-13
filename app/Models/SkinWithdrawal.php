<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class SkinWithdrawal extends Model
{
    public const STATUS_PENDING = 'pending';

    public const STATUS_PROCESSING = 'processing';

    public const STATUS_COMPLETED = 'completed';

    public const STATUS_CANCELLED = 'cancelled';

    protected $fillable = [
        'user_id',
        'skin_id',
        'skin_price',
        'weapon',
        'skin_name',
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

    public static function statusLabel(string $status): string
    {
        return match ($status) {
            self::STATUS_PENDING => 'Ожидает',
            self::STATUS_PROCESSING => 'В обработке',
            self::STATUS_COMPLETED => 'Выполнена',
            self::STATUS_CANCELLED => 'Отменена',
            default => $status,
        };
    }

    public function toFrontend(): array
    {
        return [
            'id' => $this->id,
            'userId' => $this->user_id,
            'userName' => $this->user?->name,
            'userEmail' => $this->user?->email,
            'skinPrice' => $this->skin_price,
            'weapon' => $this->weapon,
            'skinName' => $this->skin_name,
            'status' => $this->status,
            'statusLabel' => self::statusLabel($this->status),
            'createdAt' => $this->created_at?->toISOString(),
        ];
    }
}
