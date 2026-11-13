<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\CaseBattle;
use App\Models\CaseBattlePlayer;
use App\Models\GameCase;
use App\Models\InventoryItem;
use App\Models\Skin;
use App\Services\BalanceService;
use App\Services\SkinHistoryService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class BattleController extends Controller
{
    public function __construct(
        private BalanceService $balanceService,
        private SkinHistoryService $skinHistoryService,
    ) {}

    public function index()
    {
        return CaseBattle::with('players.user')
            ->latest()
            ->limit(30)
            ->get()
            ->map->toFrontend();
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'max_players' => 'required|integer|in:2,3,4',
            'case_ids' => 'required|array|min:1|max:4',
            'case_ids.*' => 'integer|exists:game_cases,id',
        ]);

        $cases = GameCase::whereIn('id', $validated['case_ids'])
            ->where('category', '!=', 'secret')
            ->get()
            ->keyBy('id');

        $totalPrice = 0;
        $orderedIds = [];
        foreach ($validated['case_ids'] as $cid) {
            $case = $cases->get($cid);
            if (! $case) {
                return response()->json(['message' => 'Некорректный кейс в наборе.'], 422);
            }
            $totalPrice += (int) $case->price;
            $orderedIds[] = (int) $case->id;
        }

        $user = $request->user();
        if ($user->balance < $totalPrice) {
            return response()->json(['message' => 'Недостаточно средств на балансе.'], 422);
        }

        return DB::transaction(function () use ($user, $validated, $orderedIds, $totalPrice) {
            $this->balanceService->deduct($user, $totalPrice, 'battle', 'Создание битвы кейсов');

            $battle = CaseBattle::create([
                'creator_id' => $user->id,
                'max_players' => $validated['max_players'],
                'total_price' => $totalPrice,
                'case_ids' => $orderedIds,
                'status' => 'waiting',
            ]);

            CaseBattlePlayer::create([
                'case_battle_id' => $battle->id,
                'user_id' => $user->id,
                'slot' => 1,
                'is_bot' => false,
            ]);

            return response()->json($battle->fresh('players.user')->toFrontend());
        });
    }
    public function join(Request $request, CaseBattle $battle)
    {
        if ($battle->status !== 'waiting') {
            return response()->json(['message' => 'Эта битва уже завершена.'], 422);
        }

        $user = $request->user();
        if ($battle->players()->where('user_id', $user->id)->exists()) {
            return response()->json(['message' => 'Вы уже участвуете в этой битве.'], 422);
        }

        if ($user->balance < $battle->total_price) {
            return response()->json(['message' => 'Недостаточно средств на балансе.'], 422);
        }

        return DB::transaction(function () use ($battle, $user) {
            $battle = CaseBattle::with('players')->lockForUpdate()->findOrFail($battle->id);

            if ($battle->status !== 'waiting' || $battle->players->count() >= $battle->max_players) {
                return response()->json(['message' => 'Свободных мест больше нет.'], 422);
            }

            $this->balanceService->deduct($user, $battle->total_price, 'battle', "Вход в битву #{$battle->id}");

            $nextSlot = $battle->players->count() + 1;
            CaseBattlePlayer::create([
                'case_battle_id' => $battle->id,
                'user_id' => $user->id,
                'slot' => $nextSlot,
                'is_bot' => false,
            ]);

            $battle->load('players.user');
            if ($battle->players->count() >= $battle->max_players) {
                $this->resolveBattle($battle);
            }

            return response()->json($battle->fresh('players.user')->toFrontend());
        });
    }

    public function fillBots(Request $request, CaseBattle $battle)
    {
        if ($battle->status !== 'waiting') {
            return response()->json(['message' => 'Битва уже завершена.'], 422);
        }

        return DB::transaction(function () use ($battle) {
            $battle = CaseBattle::with('players')->lockForUpdate()->findOrFail($battle->id);
            $botNames = ['Bot_S1mple', 'Bot_ZywOo', 'Bot_NiKo', 'Bot_m0NESY'];
            $currentCount = $battle->players->count();

            for ($slot = $currentCount + 1; $slot <= $battle->max_players; $slot++) {
                CaseBattlePlayer::create([
                    'case_battle_id' => $battle->id,
                    'user_id' => null,
                    'slot' => $slot,
                    'is_bot' => true,
                    'bot_name' => $botNames[($slot - 2) % count($botNames)],
                ]);
            }

            $battle->load('players.user');
            $this->resolveBattle($battle);

            return response()->json($battle->fresh('players.user')->toFrontend());
        });
    }
    private function resolveBattle(CaseBattle $battle): void
    {
        $casesMap = GameCase::with('skins')->whereIn('id', $battle->case_ids ?? [])->get()->keyBy('id');
        $fallbackSkins = Skin::orderBy('price')->limit(20)->get();

        $results = [];
        $allWonSkins = [];
        $winnerPlayer = null;
        $maxTotal = -1;

        foreach ($battle->players as $player) {
            $playerDrops = [];
            $playerTotal = 0;

            foreach ($battle->case_ids as $caseId) {
                $gameCase = $casesMap->get($caseId);
                $pool = ($gameCase && $gameCase->skins->isNotEmpty()) ? $gameCase->skins : $fallbackSkins;
                $picked = $this->pickWeightedSkin($pool);

                $playerTotal += (int) $picked->price;
                $playerDrops[] = $picked->toFrontend();
                $allWonSkins[] = $picked;
            }

            $player->update(['total_drop_value' => $playerTotal]);
            $results[] = [
                'slot' => $player->slot,
                'name' => $player->is_bot ? $player->bot_name : ($player->user?->name ?? 'Игрок'),
                'total' => $playerTotal,
                'drops' => $playerDrops,
            ];

            if ($playerTotal > $maxTotal) {
                $maxTotal = $playerTotal;
                $winnerPlayer = $player;
            }
        }

        if ($winnerPlayer && ! $winnerPlayer->is_bot && $winnerPlayer->user) {
            $winner = $winnerPlayer->user;
            foreach ($allWonSkins as $skin) {
                $invItem = InventoryItem::create([
                    'user_id' => $winner->id,
                    'skin_id' => $skin->id,
                    'source' => 'battle',
                ]);
                $this->skinHistoryService->recordAcquisition($winner, $skin, 'battle', 'inventory', $invItem->id);
            }
        }

        $battle->update([
            'status' => 'finished',
            'winner_user_id' => $winnerPlayer?->user_id,
            'winner_slot' => $winnerPlayer?->slot,
            'results' => $results,
        ]);
    }

    private function pickWeightedSkin($skins): Skin
    {
        $totalWeight = $skins->sum(fn (Skin $s) => max(1, (int) ($s->pivot->drop_weight ?? 100)));
        $roll = random_int(1, max(1, $totalWeight));
        $acc = 0;

        foreach ($skins as $skin) {
            $acc += max(1, (int) ($skin->pivot->drop_weight ?? 100));
            if ($roll <= $acc) {
                return $skin;
            }
        }

        return $skins->first();
    }
}
