<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class UserStat extends Model
{
    protected $fillable = [
        'user_id',
        'cases_opened',
        'upgrades_count',
        'upgrades_success',
        'contracts_count',
        'withdraw_total',
        'last_secret_case_at',
    ];

    protected function casts(): array
    {
        return [
            'cases_opened' => 'integer',
            'upgrades_count' => 'integer',
            'upgrades_success' => 'integer',
            'contracts_count' => 'integer',
            'withdraw_total' => 'integer',
            'last_secret_case_at' => 'datetime',
        ];
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
}
