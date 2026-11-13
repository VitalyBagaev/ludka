<?php

namespace App\Http\Controllers;

use App\Models\GameCase;
use App\Models\InventoryItem;
use App\Models\LiveDrop;
use App\Models\Skin;
use App\Support\GameEconomy;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Inertia\Inertia;
use Inertia\Response;

class GameController extends Controller
{
    public function home(): Response
    {
        return Inertia::render('Home', [
            'cases' => GameCase::with('skins')->get()->map->toFrontend(),
            'liveDrops' => LiveDrop::recent(20)->map->toFrontend(),
        ]);
    }

    public function battles(): Response
    {
        return Inertia::render('Battles', [
            'battles' => $this->mockBattles(),
            'liveDrops' => LiveDrop::recent(20)->map->toFrontend(),
        ]);
    }

    public function upgrade(Request $request): Response
    {
        return Inertia::render('Upgrade', [
            'liveDrops' => LiveDrop::recent(20)->map->toFrontend(),
            'inventory' => $this->userInventory($request),
        ]);
    }

    public function contracts(Request $request): Response
    {
        return Inertia::render('Contracts', [
            'liveDrops' => LiveDrop::recent(20)->map->toFrontend(),
            'inventory' => $this->userInventory($request),
        ]);
    }

    public function showCase(int $id): Response
    {
        $gameCase = GameCase::with('skins')->findOrFail($id);

        return Inertia::render('Case/Show', [
            'caseItem' => $gameCase->toFrontend(),
            'liveDrops' => LiveDrop::recent(20)->map->toFrontend(),
        ]);
    }

    public function performUpgrade(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'inventory_item_id' => ['required', 'integer', 'exists:inventory_items,id'],
            'target_price' => ['required', 'integer', 'min:1'],
        ]);

        $item = InventoryItem::with('skin')
            ->where('user_id', $request->user()->id)
            ->findOrFail($validated['inventory_item_id']);

        $chance = GameEconomy::upgradeChance($item->skin->price, $validated['target_price'], 0);
        $success = GameEconomy::upgradeRollSuccess($chance);

        return DB::transaction(function () use ($request, $item, $validated, $success) {
            $item->delete();

            if ($success) {
                $newSkin = Skin::query()
                    ->where('price', '<=', $validated['target_price'])
                    ->where('price', '>=', $item->skin->price)
                    ->inRandomOrder()
                    ->first() ?? $item->skin;

                InventoryItem::create([
                    'user_id' => $request->user()->id,
                    'skin_id' => $newSkin->id,
                    'source' => 'upgrade',
                ]);

                return response()->json([
                    'success' => true,
                    'skin' => $newSkin->toFrontend(),
                    'balance' => $request->user()->balance,
                ]);
            }

            return response()->json([
                'success' => false,
                'balance' => $request->user()->balance,
            ]);
        });
    }

    public function executeContract(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'inventory_item_ids' => ['required', 'array', 'min:2', 'max:10'],
            'inventory_item_ids.*' => ['integer', 'exists:inventory_items,id'],
        ]);

        $items = InventoryItem::with('skin')
            ->where('user_id', $request->user()->id)
            ->whereIn('id', $validated['inventory_item_ids'])
            ->get();

        if ($items->count() !== count($validated['inventory_item_ids'])) {
            return response()->json(['message' => 'Некорректный выбор скинов.'], 422);
        }

        $totalValue = (int) $items->sum(fn ($item) => $item->skin->price);
        $range = GameEconomy::contractRange($totalValue, $items->count(), 0);
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

        return DB::transaction(function () use ($request, $items, $candidates) {
            InventoryItem::whereIn('id', $items->pluck('id'))->delete();

            $newSkin = GameEconomy::pickContractSkin($candidates);

            InventoryItem::create([
                'user_id' => $request->user()->id,
                'skin_id' => $newSkin->id,
                'source' => 'contract',
            ]);

            return response()->json([
                'skin' => $newSkin->toFrontend(),
                'balance' => $request->user()->balance,
            ]);
        });
    }

    private function userInventory(Request $request): array
    {
        if (! $request->user()) {
            return [];
        }

        return InventoryItem::with('skin')
            ->where('user_id', $request->user()->id)
            ->latest()
            ->get()
            ->map->toFrontend()
            ->all();
    }

    private function mockBattles(): array
    {
        return [
            ['id' => 1, 'players' => 2, 'maxPlayers' => 2, 'cases' => ['Phoenix Case', 'Chroma Case'], 'totalPrice' => 256, 'status' => 'waiting'],
            ['id' => 2, 'players' => 3, 'maxPlayers' => 4, 'cases' => ['Knife Case'], 'totalPrice' => 796, 'status' => 'waiting'],
            ['id' => 3, 'players' => 4, 'maxPlayers' => 4, 'cases' => ['Gamma Case', 'Spectrum Case'], 'totalPrice' => 632, 'status' => 'active'],
            ['id' => 4, 'players' => 1, 'maxPlayers' => 2, 'cases' => ['Danger Zone', 'Dreams & Nightmares'], 'totalPrice' => 216, 'status' => 'waiting'],
            ['id' => 5, 'players' => 2, 'maxPlayers' => 2, 'cases' => ['Revolution Case', 'Knife Case'], 'totalPrice' => 576, 'status' => 'active'],
        ];
    }
}
