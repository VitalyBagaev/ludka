<?php

namespace App\Services;

use App\Models\BalanceTransaction;
use App\Models\User;
use Illuminate\Support\Facades\DB;

class BalanceService
{
    public function deposit(User $user, int $amount): User
    {
        return DB::transaction(function () use ($user, $amount) {
            $user->increment('balance', $amount);

            BalanceTransaction::create([
                'user_id' => $user->id,
                'amount' => $amount,
                'type' => 'deposit',
                'description' => 'Мгновенное пополнение',
            ]);

            return $user->fresh();
        });
    }

    public function depositWithPromo(User $user, int $total, int $base, int $bonus, ?string $promoCode): User
    {
        return DB::transaction(function () use ($user, $total, $base, $bonus, $promoCode) {
            $user->increment('balance', $total);

            BalanceTransaction::create([
                'user_id' => $user->id,
                'amount' => $base,
                'type' => 'deposit',
                'description' => $promoCode
                    ? "Пополнение (промо {$promoCode})"
                    : 'Мгновенное пополнение',
            ]);

            if ($bonus > 0) {
                BalanceTransaction::create([
                    'user_id' => $user->id,
                    'amount' => $bonus,
                    'type' => 'promo',
                    'description' => "Бонус промокода {$promoCode} (+{$bonus} ₽)",
                ]);
            }

            return $user->fresh();
        });
    }

    public function deduct(User $user, int $amount, string $type, ?string $description = null): User
    {
        if ($user->balance < $amount) {
            throw new \RuntimeException('Недостаточно средств на балансе.');
        }

        return DB::transaction(function () use ($user, $amount, $type, $description) {
            $user->decrement('balance', $amount);

            BalanceTransaction::create([
                'user_id' => $user->id,
                'amount' => -$amount,
                'type' => $type,
                'description' => $description,
            ]);

            return $user->fresh();
        });
    }

    public function withdraw(User $user, int $amount): User
    {
        if ($amount < 1) {
            throw new \RuntimeException('Минимальная сумма вывода — 1 ₽.');
        }

        if ($user->balance < $amount) {
            throw new \RuntimeException('Недостаточно средств для вывода.');
        }

        return DB::transaction(function () use ($user, $amount) {
            $user->decrement('balance', $amount);
            $user->incrementStat('withdraw_total', $amount);

            BalanceTransaction::create([
                'user_id' => $user->id,
                'amount' => -$amount,
                'type' => 'withdraw',
                'description' => 'Вывод средств',
            ]);

            return $user->fresh();
        });
    }
}
