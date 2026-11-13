<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\GameCase;
use App\Models\InventoryItem;
use App\Models\LiveDrop;
use App\Models\Skin;
use App\Models\SkinWithdrawal;
use App\Models\User;
use App\Models\UserSkinHistory;
use App\Support\GameEconomy;
use App\Support\GameImages;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class AdminController extends Controller
{
    public function stats()
    {
        return [
            'users' => User::count(),
            'superAdmins' => User::where('role', 'super_admin')->count(),
            'admins' => User::where('role', 'admin')->count(),
            'managers' => User::where('role', 'manager')->count(),
            'skins' => Skin::count(),
            'cases' => GameCase::count(),
            'opensToday' => LiveDrop::whereDate('created_at', today())->count(),
        ];
    }

    public function skins()
    {
        return Skin::orderBy('name')->get()->map->toFrontend();
    }

    public function storeSkin(Request $request)
    {
        $validated = $this->validateSkin($request);
        $validated['image'] = $this->resolveSkinImage($request, null);

        $skin = Skin::create($validated);

        return response()->json($skin->toFrontend());
    }

    public function updateSkin(Request $request, Skin $skin)
    {
        $validated = $this->validateSkin($request);
        $validated['image'] = $this->resolveSkinImage($request, $skin->image);
        $skin->update($validated);

        return response()->json($skin->fresh()->toFrontend());
    }

    public function uploadSkinImage(Request $request, Skin $skin)
    {
        $request->validate(['image' => 'required|image|max:2048']);
        $path = GameImages::storeUpload($request->file('image'), 'skins', $skin->image);
        $skin->update(['image' => $path ?? $skin->image]);

        return response()->json($skin->fresh()->toFrontend());
    }

    public function deleteSkin(Skin $skin)
    {
        $skin->delete();

        return response()->json(['message' => 'Скин удалён']);
    }

    public function cases()
    {
        return GameCase::withCount('skins')
            ->where('category', '!=', 'secret')
            ->orderByDesc('price')
            ->orderBy('name')
            ->get()
            ->map(fn (GameCase $gameCase) => $this->caseToAdmin($gameCase));
    }

    public function showCase(GameCase $gameCase)
    {
        if ($gameCase->category === 'secret') {
            abort(404);
        }

        $gameCase->load('skins');

        return $this->caseToAdmin($gameCase, true);
    }

    public function storeCase(Request $request)
    {
        $validated = $this->validateCase($request);
        $validated['image'] = $this->resolveCaseImage($request, null);
        $validated['featured'] = (bool) ($validated['featured'] ?? false);
        $gameCase = GameCase::create($validated);
        try {
            $this->syncSkins($gameCase, $request->input('skins', []));
        } catch (\RuntimeException $e) {
            $gameCase->delete();

            return response()->json(['message' => $e->getMessage()], 422);
        }

        return response()->json($this->caseToAdmin($gameCase->fresh()->loadCount('skins')->load('skins'), true));
    }

    public function updateCase(Request $request, GameCase $gameCase)
    {
        if ($gameCase->category === 'secret') {
            return response()->json(['message' => 'Секретный кейс нельзя редактировать.'], 422);
        }

        $validated = $this->validateCase($request);
        $validated['image'] = $this->resolveCaseImage($request, $gameCase->image);
        $validated['featured'] = (bool) ($validated['featured'] ?? false);
        $gameCase->update($validated);

        try {
            $this->syncSkins($gameCase, $request->input('skins', []));
        } catch (\RuntimeException $e) {
            return response()->json(['message' => $e->getMessage()], 422);
        }

        return response()->json($this->caseToAdmin($gameCase->fresh()->loadCount('skins')->load('skins'), true));
    }

    public function uploadCaseImage(Request $request, GameCase $gameCase)
    {
        if ($gameCase->category === 'secret') {
            return response()->json(['message' => 'Секретный кейс нельзя редактировать.'], 422);
        }

        $request->validate(['image' => 'required|image|max:2048']);
        $path = GameImages::storeUpload($request->file('image'), 'cases', $gameCase->image);
        $gameCase->update(['image' => $path ?? $gameCase->image]);

        return response()->json($this->caseToAdmin($gameCase->fresh()->loadCount('skins')->load('skins'), true));
    }

    public function deleteCase(GameCase $gameCase)
    {
        $gameCase->delete();

        return response()->json(['message' => 'Кейс удалён']);
    }

    public function withdrawals()
    {
        return SkinWithdrawal::query()
            ->with('user')
            ->latest()
            ->limit(200)
            ->get()
            ->map->toFrontend();
    }

    public function updateWithdrawalStatus(Request $request, SkinWithdrawal $withdrawal)
    {
        $validated = $request->validate([
            'status' => 'required|in:pending,processing,completed,cancelled',
        ]);

        $newStatus = $validated['status'];

        if ($withdrawal->status === SkinWithdrawal::STATUS_COMPLETED
            || $withdrawal->status === SkinWithdrawal::STATUS_CANCELLED) {
            return response()->json(['message' => 'Заявка уже закрыта.'], 422);
        }

        return DB::transaction(function () use ($withdrawal, $newStatus) {
            $withdrawal = SkinWithdrawal::query()->lockForUpdate()->findOrFail($withdrawal->id);

            if ($newStatus === SkinWithdrawal::STATUS_CANCELLED) {
                InventoryItem::create([
                    'user_id' => $withdrawal->user_id,
                    'skin_id' => $withdrawal->skin_id,
                    'source' => 'withdraw_return',
                ]);

                UserSkinHistory::query()
                    ->where('user_id', $withdrawal->user_id)
                    ->where('skin_id', $withdrawal->skin_id)
                    ->where('status', 'withdraw_pending')
                    ->latest()
                    ->limit(1)
                    ->update(['status' => 'inventory']);

                $withdrawal->update(['status' => SkinWithdrawal::STATUS_CANCELLED]);

                return response()->json($withdrawal->fresh('user')->toFrontend());
            }

            if ($newStatus === SkinWithdrawal::STATUS_COMPLETED
                && $withdrawal->status !== SkinWithdrawal::STATUS_COMPLETED) {
                UserSkinHistory::query()
                    ->where('user_id', $withdrawal->user_id)
                    ->where('skin_id', $withdrawal->skin_id)
                    ->where('status', 'withdraw_pending')
                    ->latest()
                    ->limit(1)
                    ->update(['status' => 'withdrawn']);

                $withdrawal->user->incrementStat('withdraw_total', $withdrawal->skin_price);
            }

            $withdrawal->update(['status' => $newStatus]);

            return response()->json($withdrawal->fresh('user')->toFrontend());
        });
    }

    private function validateSkin(Request $request): array
    {
        return $request->validate([
            'name' => 'required|string|max:255',
            'weapon' => 'required|string|max:255',
            'rarity' => 'required|in:consumer,industrial,mil-spec,restricted,classified,covert,gold',
            'price' => 'required|integer|min:1',
            'image' => 'nullable|string|max:2048',
        ]);
    }

    private function validateCase(Request $request): array
    {
        return $request->validate([
            'name' => 'required|string|max:255',
            'price' => 'required|integer|min:1',
            'image' => 'nullable|string|max:2048',
            'category' => 'required|in:serial,branded,image,knife,valve,limited,bonus,premium,popular,new,budget',
            'featured' => 'sometimes|boolean',
            'skins' => 'sometimes|array',
            'skins.*.skin_id' => 'required_with:skins|integer|exists:skins,id',
            'skins.*.drop_weight' => 'nullable|integer|min:1',
        ]);
    }

    private function syncSkins(GameCase $gameCase, array $skins): void
    {
        $sync = [];
        foreach ($skins as $item) {
            if (empty($item['skin_id'])) {
                continue;
            }

            $skin = Skin::find($item['skin_id']);
            if (! $skin) {
                continue;
            }

            $minPrice = GameEconomy::minSkinPriceForCase((int) $gameCase->price, $gameCase->category);
            if ($minPrice > 0 && (int) $skin->price < $minPrice) {
                continue;
            }

            $sync[$skin->id] = [
                'drop_weight' => $item['drop_weight'] ?? GameEconomy::dropWeightForRarity($skin->rarity),
            ];
        }
        $gameCase->skins()->sync($sync);

        if ((int) $gameCase->price >= GameEconomy::CASE_MIN_SKIN_PRICE_THRESHOLD && ! empty($sync)) {
            $cheapCount = $gameCase->skins()
                ->get()
                ->filter(fn (Skin $skin) => GameEconomy::isCheapTierSkinPrice((int) $skin->price, (int) $gameCase->price, $gameCase->category))
                ->count();

            if ($cheapCount < GameEconomy::CASE_CHEAP_SKIN_COUNT) {
                throw new \RuntimeException(
                    'В кейсе должно быть минимум '.GameEconomy::CASE_CHEAP_SKIN_COUNT
                    .' скинов в диапазоне 50%–80% от цены кейса (сейчас: '.$cheapCount.').'
                );
            }
        }
    }

    private function resolveSkinImage(Request $request, ?string $current): string
    {
        if ($request->hasFile('image')) {
            $request->validate(['image' => 'image|max:2048']);

            return GameImages::storeUpload($request->file('image'), 'skins', $current)
                ?? GameImages::SKIN_PLACEHOLDER;
        }

        if ($request->filled('image')) {
            return $request->string('image')->toString();
        }

        return $current ?? GameImages::SKIN_PLACEHOLDER;
    }

    private function resolveCaseImage(Request $request, ?string $current): string
    {
        if ($request->hasFile('image')) {
            $request->validate(['image' => 'image|max:2048']);

            return GameImages::storeUpload($request->file('image'), 'cases', $current)
                ?? GameImages::CASE_PLACEHOLDER;
        }

        if ($request->filled('image')) {
            return $request->string('image')->toString();
        }

        return $current ?? GameImages::CASE_PLACEHOLDER;
    }

    private function caseToAdmin(GameCase $gameCase, bool $withSkins = false): array
    {
        $data = [
            'id' => $gameCase->id,
            'name' => $gameCase->name,
            'price' => $gameCase->price,
            'category' => $gameCase->category,
            'featured' => (bool) $gameCase->featured,
            'image' => GameImages::case($gameCase->image),
            'skins_count' => $gameCase->skins_count ?? $gameCase->skins()->count(),
        ];

        if ($withSkins) {
            $gameCase->loadMissing('skins');
            $data['skins'] = $gameCase->skins->map(fn (Skin $skin) => [
                'skin_id' => $skin->id,
                'drop_weight' => (int) ($skin->pivot->drop_weight ?? GameEconomy::dropWeightForRarity($skin->rarity)),
                ...$skin->toFrontend(),
            ])->values()->all();
        }

        return $data;
    }
}
