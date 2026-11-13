<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\BalanceTransaction;
use App\Models\GameCase;
use App\Models\InventoryItem;
use App\Models\LiveDrop;
use App\Models\Skin;
use App\Models\SkinWithdrawal;
use App\Services\BalanceService;
use App\Services\CaseOpenService;
use App\Services\PromoCodeService;
use App\Services\SkinHistoryService;
use App\Support\GameEconomy;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class GameController extends Controller
{
    public function __construct(
        private CaseOpenService $caseOpenService,
        private BalanceService $balanceService,
        private PromoCodeService $promoCodeService,
        private SkinHistoryService $skinHistoryService,
    ) {}

    public function cases()
    {
        return GameCase::withCount('skins')
            ->where('category', '!=', 'secret')
            ->orderByDesc('price')
            ->orderBy('name')
            ->get()
            ->map->toFrontend();
    }

    public function showCase(int $id)
    {
        $gameCase = GameCase::with('skins')->findOrFail($id);

        return $gameCase->toFrontend();
    }

    public function skins()
    {
        return Skin::query()
            ->orderBy('price')
            ->get()
            ->map->toFrontend();
    }

    public function liveDrops()
    {
        return LiveDrop::recent(30)->map->toFrontend();
    }

    public function secretCase(Request $request)
    {
        $gameCase = GameCase::with('skins')
            ->where('category', 'secret')
            ->firstOrFail();

        $cooldown = CaseOpenService::SECRET_CASE_COOLDOWN_SECONDS;
        $readyAt = null;
        $secondsLeft = 0;

        if ($request->user()) {
            $lastAt = $request->user()->stat()->last_secret_case_at;
            if ($lastAt) {
                $readyAt = $lastAt->copy()->addSeconds($cooldown)->timestamp;
                $secondsLeft = max(0, $readyAt - time());
            }
        }

        return response()->json([
            'case' => $gameCase->toFrontend(),
            'cooldownSeconds' => $cooldown,
            'readyAt' => $readyAt,
            'secondsLeft' => $secondsLeft,
            'legendaryPromo' => $this->promoCodeService->getLegendaryPromo(),
        ]);
    }

    public function openSecretCase(Request $request)
    {
        $gameCase = GameCase::where('category', 'secret')->firstOrFail();

        try {
            return response()->json($this->caseOpenService->openSecret($request->user(), $gameCase));
        } catch (\RuntimeException $e) {
            return response()->json(['message' => $e->getMessage()], 422);
        }
    }

    public function battles()
    {
        return [
            ['id' => 1, 'players' => 2, 'maxPlayers' => 2, 'cases' => ['Phoenix Case', 'Chroma Case'], 'totalPrice' => 256, 'status' => 'waiting'],
            ['id' => 2, 'players' => 3, 'maxPlayers' => 4, 'cases' => ['Knife Case'], 'totalPrice' => 796, 'status' => 'waiting'],
            ['id' => 3, 'players' => 4, 'maxPlayers' => 4, 'cases' => ['Gamma Case', 'Spectrum Case'], 'totalPrice' => 632, 'status' => 'active'],
            ['id' => 4, 'players' => 1, 'maxPlayers' => 2, 'cases' => ['Dreams & Nightmares'], 'totalPrice' => 69, 'status' => 'waiting'],
            ['id' => 5, 'players' => 2, 'maxPlayers' => 3, 'cases' => ['Revolution Case', 'Danger Zone'], 'totalPrice' => 128, 'status' => 'waiting'],
        ];
    }

    public function inventory(Request $request)
    {
        return InventoryItem::with('skin')
            ->where('user_id', $request->user()->id)
            ->latest()
            ->get()
            ->map->toFrontend();
    }

    public function promo()
    {
        return response()->json($this->promoCodeService->getPublicPromos());
    }

    public function deposit(Request $request)
    {
        $validated = $request->validate([
            'amount' => 'required|integer|min:1|max:1000000',
            'promo_code' => 'nullable|string|max:32',
        ]);

        $credit = $this->promoCodeService->calculateCredit(
            $validated['amount'],
            $validated['promo_code'] ?? null,
        );

        if ($validated['promo_code'] && ! $credit['promo']) {
            return response()->json(['message' => 'Промокод недействителен или истёк.'], 422);
        }

        $user = $this->balanceService->depositWithPromo(
            $request->user(),
            $credit['total'],
            $credit['amount'],
            $credit['bonus'],
            $credit['promo']['code'] ?? null,
        );

        return response()->json([
            'balance' => $user->balance,
            'credited' => $credit['total'],
            'bonus' => $credit['bonus'],
            'bonusPercent' => $credit['bonusPercent'],
        ]);
    }

    public function withdrawSkins(Request $request)
    {
        $validated = $request->validate([
            'inventory_item_ids' => 'required|array|min:1|max:20',
            'inventory_item_ids.*' => 'integer|exists:inventory_items,id',
        ]);

        $items = InventoryItem::with('skin')
            ->where('user_id', $request->user()->id)
            ->whereIn('id', $validated['inventory_item_ids'])
            ->get();

        if ($items->count() !== count($validated['inventory_item_ids'])) {
            return response()->json(['message' => 'Некорректный выбор скинов.'], 422);
        }

        return DB::transaction(function () use ($request, $items) {
            $user = $request->user();
            $totalValue = 0;
            $withdrawals = [];

            foreach ($items as $item) {
                $price = $item->skin->price;
                $totalValue += $price;

                SkinWithdrawal::create([
                    'user_id' => $user->id,
                    'skin_id' => $item->skin_id,
                    'skin_price' => $price,
                    'weapon' => $item->skin->weapon,
                    'skin_name' => $item->skin->name,
                    'status' => SkinWithdrawal::STATUS_PENDING,
                ]);

                $this->skinHistoryService->markByInventoryItem($item->id, 'withdraw_pending');
                $item->delete();
            }

            return response()->json([
                'message' => 'Заявка на вывод создана. Скины будут отправлены после проверки админом.',
                'skinsValue' => $totalValue,
                'count' => $items->count(),
                'withdrawals' => SkinWithdrawal::query()
                    ->where('user_id', $user->id)
                    ->latest()
                    ->limit($items->count())
                    ->get()
                    ->map->toFrontend(),
            ]);
        });
    }

    public function openCase(Request $request, int $id)
    {
        $validated = $request->validate([
            'count' => 'sometimes|integer|min:1|max:5',
        ]);

        $gameCase = GameCase::findOrFail($id);
        $count = $validated['count'] ?? 1;

        try {
            return response()->json($this->caseOpenService->open($request->user(), $gameCase, $count));
        } catch (\RuntimeException $e) {
            return response()->json(['message' => $e->getMessage()], 422);
        }
    }

    public function upgrade(Request $request)
    {
        $validated = $request->validate([
            'inventory_item_id' => 'required|integer|exists:inventory_items,id',
            'target_skin_id' => 'required|integer|exists:skins,id',
            'boost_amount' => 'sometimes|integer|min:0|max:50000',
        ]);

        $boostAmount = $validated['boost_amount'] ?? 0;

        $item = InventoryItem::with('skin')
            ->where('user_id', $request->user()->id)
            ->findOrFail($validated['inventory_item_id']);

        $targetSkin = Skin::findOrFail($validated['target_skin_id']);

        if ($targetSkin->price <= $item->skin->price) {
            return response()->json(['message' => 'Целевой скин должен быть дороже вашего.'], 422);
        }

        if (! GameEconomy::isUpgradeTargetAllowed($item->skin->price, $targetSkin->price)) {
            return response()->json([
                'message' => 'Нельзя апгрейдить на этот скин: базовый шанс выше '.GameEconomy::UPGRADE_BASE_MAX.'%.',
            ], 422);
        }

        if ($boostAmount > $request->user()->balance) {
            return response()->json(['message' => 'Недостаточно баланса для буста.'], 422);
        }

        $maxBoost = GameEconomy::maxUpgradeBoost(
            $item->skin->price,
            $targetSkin->price,
            $request->user()->balance,
        );

        if ($boostAmount > $maxBoost) {
            return response()->json(['message' => 'Буст превышает допустимый для этой пары скинов.'], 422);
        }

        $chance = GameEconomy::upgradeChance($item->skin->price, $targetSkin->price, $boostAmount);
        $success = GameEconomy::upgradeRollSuccess($chance);

        return DB::transaction(function () use ($request, $item, $targetSkin, $success, $chance, $boostAmount) {
            $user = $request->user();

            if ($boostAmount > 0) {
                $this->balanceService->deduct(
                    $user,
                    $boostAmount,
                    'upgrade_boost',
                    "Буст апгрейда (+{$boostAmount} ₽)",
                );
                $user = $user->fresh();
            }

            $inventoryItemId = $item->id;
            $this->skinHistoryService->markByInventoryItem($inventoryItemId, 'upgrade');
            $item->delete();
            $user->incrementStat('upgrades_count');

            if ($success) {
                $user->incrementStat('upgrades_success');

                $newItem = InventoryItem::create([
                    'user_id' => $request->user()->id,
                    'skin_id' => $targetSkin->id,
                    'source' => 'upgrade',
                ]);

                $this->skinHistoryService->recordAcquisition(
                    $user,
                    $targetSkin,
                    'upgrade',
                    'inventory',
                    $newItem->id,
                );

                return response()->json([
                    'success' => true,
                    'skin' => $targetSkin->toFrontend(),
                    'chance' => $chance,
                ]);
            }

            return response()->json([
                'success' => false,
                'chance' => $chance,
            ]);
        });
    }

    public function contract(Request $request)
    {
        $validated = $request->validate([
            'inventory_item_ids' => 'required|array|min:2|max:10',
            'inventory_item_ids.*' => 'integer|exists:inventory_items,id',
            'boost_amount' => 'sometimes|integer|min:0|max:50000',
        ]);

        $boostAmount = $validated['boost_amount'] ?? 0;

        if ($boostAmount > $request->user()->balance) {
            return response()->json(['message' => 'Недостаточно баланса для буста.'], 422);
        }

        $items = InventoryItem::with('skin')
            ->where('user_id', $request->user()->id)
            ->whereIn('id', $validated['inventory_item_ids'])
            ->get();

        if ($items->count() !== count($validated['inventory_item_ids'])) {
            return response()->json(['message' => 'Некорректный выбор скинов.'], 422);
        }

        $totalValue = $items->sum(fn ($item) => $item->skin->price);
        $range = GameEconomy::contractRange($totalValue, $items->count(), $boostAmount);
        $minPrice = $range['min'];
        $maxPrice = $range['max'];

        $candidates = Skin::query()
            ->whereBetween('price', [$minPrice, $maxPrice])
            ->get();

        if ($candidates->isEmpty()) {
            return response()->json([
                'message' => "Нет скинов в диапазоне {$minPrice}–{$maxPrice} ₽ для контракта.",
            ], 422);
        }

        return DB::transaction(function () use ($request, $items, $candidates, $minPrice, $maxPrice, $boostAmount) {
            $user = $request->user();

            if ($boostAmount > 0) {
                $this->balanceService->deduct(
                    $user,
                    $boostAmount,
                    'contract_boost',
                    "Буст контракта (+{$boostAmount} ₽)",
                );
            }

            $itemIds = $items->pluck('id')->all();
            $this->skinHistoryService->markManyByInventoryItems($itemIds, 'contract');
            InventoryItem::whereIn('id', $itemIds)->delete();
            $user->incrementStat('contracts_count');

            $newSkin = GameEconomy::pickContractSkin($candidates);

            $newItem = InventoryItem::create([
                'user_id' => $request->user()->id,
                'skin_id' => $newSkin->id,
                'source' => 'contract',
            ]);

            $this->skinHistoryService->recordAcquisition(
                $user,
                $newSkin,
                'contract',
                'inventory',
                $newItem->id,
            );

            return response()->json([
                'skin' => $newSkin->toFrontend(),
                'range' => ['min' => $minPrice, 'max' => $maxPrice],
            ]);
        });
    }

    public function sellItem(Request $request, int $id)
    {
        $item = InventoryItem::with('skin')
            ->where('user_id', $request->user()->id)
            ->findOrFail($id);

        return DB::transaction(function () use ($request, $item) {
            $price = $item->skin->price;
            $this->skinHistoryService->markByInventoryItem($item->id, 'sold');
            $item->delete();
            $request->user()->increment('balance', $price);

            BalanceTransaction::create([
                'user_id' => $request->user()->id,
                'amount' => $price,
                'type' => 'sell',
                'description' => "Продажа: {$item->skin->weapon} | {$item->skin->name}",
            ]);

            return response()->json([
                'balance' => $request->user()->fresh()->balance,
                'amount' => $price,
            ]);
        });
    }

    public function sellAllItems(Request $request)
    {
        $items = InventoryItem::with('skin')
            ->where('user_id', $request->user()->id)
            ->get();

        if ($items->isEmpty()) {
            return response()->json(['message' => 'Инвентарь пуст.'], 422);
        }

        return DB::transaction(function () use ($request, $items) {
            $user = $request->user();
            $total = 0;
            $count = $items->count();
            $itemIds = $items->pluck('id')->all();

            foreach ($items as $item) {
                $total += $item->skin->price;
            }

            $this->skinHistoryService->markManyByInventoryItems($itemIds, 'sold');
            InventoryItem::query()->whereIn('id', $itemIds)->delete();
            $user->increment('balance', $total);

            BalanceTransaction::create([
                'user_id' => $user->id,
                'amount' => $total,
                'type' => 'sell',
                'description' => "Продажа всех скинов ({$count} шт.)",
            ]);

            return response()->json([
                'balance' => $user->fresh()->balance,
                'amount' => $total,
                'count' => $count,
            ]);
        });
    }
}
