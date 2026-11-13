<?php

namespace App\Services;

use App\Events\LiveDropCreated;
use App\Models\GameCase;
use App\Models\InventoryItem;
use App\Models\LiveDrop;
use App\Models\Skin;
use App\Models\User;
use App\Support\GameEconomy;
use Illuminate\Support\Facades\DB;

class CaseOpenService
{
    public const SECRET_CASE_COOLDOWN_SECONDS = 1800;

    public function __construct(
        private BalanceService $balanceService,
        private SkinHistoryService $skinHistoryService,
    ) {}

    public function open(User $user, GameCase $gameCase, int $count = 1): array
    {
        $gameCase->load('skins');

        if ($gameCase->skins->isEmpty()) {
            throw new \RuntimeException('Кейс пуст.');
        }

        $count = max(1, min(5, $count));
        $totalCost = $gameCase->price * $count;

        if ($user->balance < $totalCost) {
            throw new \RuntimeException('Недостаточно средств на балансе.');
        }

        return DB::transaction(function () use ($user, $gameCase, $count, $totalCost) {
            $description = $count > 1
                ? "Открытие x{$count}: {$gameCase->name}"
                : "Открытие: {$gameCase->name}";

            $this->balanceService->deduct(
                $user,
                $totalCost,
                'case_open',
                $description,
            );

            $skins = [];
            $liveDrops = [];

            for ($i = 0; $i < $count; $i++) {
                $wonSkin = $this->pickSkin($gameCase->skins, $gameCase);

                $inventoryItem = InventoryItem::create([
                    'user_id' => $user->id,
                    'skin_id' => $wonSkin->id,
                    'source' => 'case_open',
                ]);

                $liveDrop = LiveDrop::create([
                    'user_id' => $user->id,
                    'skin_id' => $wonSkin->id,
                    'game_case_id' => $gameCase->id,
                ]);

                $this->skinHistoryService->recordAcquisition(
                    $user,
                    $wonSkin,
                    'case_open',
                    'inventory',
                    $inventoryItem->id,
                    $liveDrop->id,
                    $gameCase->name,
                );

                $user->incrementStat('cases_opened');
                $liveDrop->load(['user', 'skin', 'gameCase']);

                try {
                    LiveDropCreated::dispatch($liveDrop);
                } catch (\Throwable) {
                    // broadcasting optional
                }

                $skins[] = [
                    ...$wonSkin->toFrontend(),
                    'inventory_item_id' => $inventoryItem->id,
                ];
                $liveDrops[] = $liveDrop->toFrontend();
            }

            $result = [
                'skins' => $skins,
                'liveDrops' => $liveDrops,
                'balance' => $user->fresh()->balance,
                'count' => $count,
                'totalCost' => $totalCost,
            ];

            if ($count === 1) {
                $result['skin'] = $skins[0];
                $result['liveDrop'] = $liveDrops[0];
            }

            return $result;
        });
    }

    public function openSecret(User $user, GameCase $gameCase): array
    {
        if ($gameCase->category !== 'secret') {
            throw new \RuntimeException('Недоступный кейс.');
        }

        $stats = $user->stat();

        if ($stats->last_secret_case_at) {
            $elapsed = $stats->last_secret_case_at->diffInSeconds(now());
            if ($elapsed < self::SECRET_CASE_COOLDOWN_SECONDS) {
                $secondsLeft = self::SECRET_CASE_COOLDOWN_SECONDS - $elapsed;
                throw new \RuntimeException("Подождите {$secondsLeft} сек. перед следующим открытием.");
            }
        }

        $gameCase->load('skins');

        if ($gameCase->skins->isEmpty()) {
            throw new \RuntimeException('Кейс пуст.');
        }

        return DB::transaction(function () use ($user, $gameCase, $stats) {
            $wonSkin = $this->pickSkin($gameCase->skins, $gameCase);

            $inventoryItem = InventoryItem::create([
                'user_id' => $user->id,
                'skin_id' => $wonSkin->id,
                'source' => 'case_open',
            ]);

            $liveDrop = LiveDrop::create([
                'user_id' => $user->id,
                'skin_id' => $wonSkin->id,
                'game_case_id' => $gameCase->id,
            ]);

            $this->skinHistoryService->recordAcquisition(
                $user,
                $wonSkin,
                'case_open',
                'inventory',
                $inventoryItem->id,
                $liveDrop->id,
                $gameCase->name,
            );

            $user->incrementStat('cases_opened');
            $stats->update(['last_secret_case_at' => now()]);

            $liveDrop->load(['user', 'skin', 'gameCase']);

            try {
                LiveDropCreated::dispatch($liveDrop);
            } catch (\Throwable) {
                // broadcasting optional
            }

            $skin = [
                ...$wonSkin->toFrontend(),
                'inventory_item_id' => $inventoryItem->id,
            ];
            $drop = $liveDrop->toFrontend();

            return [
                'skin' => $skin,
                'liveDrop' => $drop,
                'balance' => $user->fresh()->balance,
                'readyAt' => now()->addSeconds(self::SECRET_CASE_COOLDOWN_SECONDS)->timestamp,
                'cooldownSeconds' => self::SECRET_CASE_COOLDOWN_SECONDS,
            ];
        });
    }

    /**
     * @param  \Illuminate\Support\Collection<int, Skin>  $skins
     */
    private function pickSkin($skins, ?GameCase $gameCase = null): Skin
    {
        $minPrice = $gameCase !== null
            ? GameEconomy::minSkinPriceForCase((int) $gameCase->price, $gameCase->category)
            : 0;

        if ($minPrice > 0) {
            $skins = $skins->filter(fn (Skin $skin) => (int) $skin->price >= $minPrice)->values();
        }

        if ($skins->isEmpty()) {
            throw new \RuntimeException('Кейс пуст.');
        }

        $totalWeight = $skins->sum(fn (Skin $skin) => GameEconomy::dropWeightForRarity($skin->rarity));
        $random = random_int(1, max(1, $totalWeight));
        $current = 0;

        foreach ($skins as $skin) {
            $current += GameEconomy::dropWeightForRarity($skin->rarity);
            if ($random <= $current) {
                return $skin;
            }
        }

        return $skins->last();
    }
}
