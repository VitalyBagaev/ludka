<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class InventoryItem extends Model
{
    protected $fillable = ['user_id', 'skin_id', 'source'];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function skin(): BelongsTo
    {
        return $this->belongsTo(Skin::class);
    }

    public function toFrontend(): array
    {
        return [
            'id' => $this->id,
            'skin' => $this->skin->toFrontend(),
            'source' => $this->source,
            'obtainedAt' => $this->created_at->toISOString(),
        ];
    }
}
