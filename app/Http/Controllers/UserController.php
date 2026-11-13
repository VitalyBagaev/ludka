<?php

namespace App\Http\Controllers;

use App\Enums\UserRole;
use App\Http\Requests\StoreUserRequest;
use App\Http\Requests\UpdateUserRequest;
use App\Models\BalanceTransaction;
use App\Models\InventoryItem;
use App\Models\SkinWithdrawal;
use App\Models\User;
use App\Models\UserSkinHistory;
use App\Services\PromoCodeService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\Rule;

class UserController extends Controller
{
    public function registerUser(StoreUserRequest $request)
    {
        $user = new User;
        $user->name = $request->name;
        $user->email = $request->email;
        $user->password = $request->password;
        $user->role = UserRole::User;
        $user->balance = 0;
        $user->save();
        $user->stat();
        Auth::login($user);

        return response()->json(['token' => $user->createToken('api')->plainTextToken]);
    }

    public function authUser(UpdateUserRequest $request)
    {
        $user = User::where('email', $request->email)->first();
        if ($user && Hash::check($request->password, $user->password)) {
            Auth::login($user);

            return response()->json(['token' => $user->createToken('api')->plainTextToken]);
        }

        return response()->json(['errors' => ['email' => ['Ошибка авторизации']]]);
    }

    public function logoutUser(Request $request)
    {
        $request->user()->currentAccessToken()->delete();

        return response()->json();
    }

    public function getUsers()
    {
        return User::query()
            ->orderByRaw("CASE role WHEN 'super_admin' THEN 0 WHEN 'admin' THEN 1 WHEN 'manager' THEN 2 ELSE 3 END")
            ->orderBy('name')
            ->get()
            ->map->toFrontend();
    }

    public function updateRole(Request $request, User $user)
    {
        if (! $request->user()->hasCapability('manage_roles')) {
            abort(403, 'Только главный админ может назначать роли.');
        }

        $validated = $request->validate([
            'role' => ['required', Rule::in(['user', 'manager', 'admin'])],
        ]);

        if ($user->isSuperAdmin()) {
            return response()->json(['errors' => ['role' => ['Нельзя изменить роль главного админа.']]], 422);
        }

        if ($user->id === $request->user()->id) {
            return response()->json(['errors' => ['role' => ['Нельзя изменить собственную роль.']]], 422);
        }

        $user->update(['role' => $validated['role']]);

        return response()->json(['message' => 'Роль обновлена']);
    }

    public function deleteUser(Request $request, User $user)
    {
        if (! $request->user()->hasCapability('delete_users')) {
            abort(403, 'Недостаточно прав для удаления пользователей.');
        }

        if ($user->id === $request->user()->id) {
            return response()->json(['errors' => ['user' => ['Нельзя удалить свой аккаунт.']]], 422);
        }

        if ($user->isSuperAdmin()) {
            return response()->json(['errors' => ['user' => ['Нельзя удалить главного админа.']]], 422);
        }

        $user->delete();

        return response()->json(['message' => 'Пользователь удалён']);
    }

    public function updateAvatar(Request $request)
    {
        $validated = $request->validate([
            'avatar' => 'required|image|max:2048',
        ]);

        $user = $request->user();

        if ($user->avatar && Storage::disk('public')->exists($user->avatar)) {
            Storage::disk('public')->delete($user->avatar);
        }

        $path = $validated['avatar']->store('avatars', 'public');
        $user->update(['avatar' => $path]);

        return response()->json([
            'avatar' => $user->fresh()->avatarUrl(),
        ]);
    }

    public function deleteAvatar(Request $request)
    {
        $user = $request->user();

        if ($user->avatar && Storage::disk('public')->exists($user->avatar)) {
            Storage::disk('public')->delete($user->avatar);
        }

        $user->update(['avatar' => null]);

        return response()->json(['avatar' => null]);
    }

    public function profile(Request $request)
    {
        $user = $request->user();
        $stats = $user->stat();

        $inventory = InventoryItem::with('skin')
            ->where('user_id', $user->id)
            ->latest()
            ->get();

        $inventoryValue = $inventory->sum(fn ($item) => $item->skin->price);

        $depositedTotal = (int) BalanceTransaction::query()
            ->where('user_id', $user->id)
            ->where('type', 'deposit')
            ->sum('amount');

        $spentOnCases = (int) abs(BalanceTransaction::query()
            ->where('user_id', $user->id)
            ->where('type', 'case_open')
            ->sum('amount'));

        $skinHistory = UserSkinHistory::query()
            ->with('skin')
            ->where('user_id', $user->id)
            ->latest()
            ->limit(24)
            ->get()
            ->map->toFrontend();

        $transactions = BalanceTransaction::query()
            ->where('user_id', $user->id)
            ->latest()
            ->limit(15)
            ->get()
            ->map(fn (BalanceTransaction $tx) => [
                'id' => $tx->id,
                'amount' => $tx->amount,
                'type' => $tx->type,
                'description' => $tx->description,
                'createdAt' => $tx->created_at->toISOString(),
            ]);

        $upgradeRate = $stats->upgrades_count > 0
            ? (int) round(($stats->upgrades_success / $stats->upgrades_count) * 100)
            : 0;

        $skinWithdrawals = SkinWithdrawal::query()
            ->where('user_id', $user->id)
            ->latest()
            ->limit(20)
            ->get()
            ->map->toFrontend();

        $promoCodeService = app(PromoCodeService::class);

        return response()->json([
            'user' => $user->toFrontend(),
            'stats' => [
                'casesOpened' => $stats->cases_opened,
                'upgradesCount' => $stats->upgrades_count,
                'upgradesSuccess' => $stats->upgrades_success,
                'upgradeRate' => $upgradeRate,
                'contractsCount' => $stats->contracts_count,
                'depositedTotal' => $depositedTotal,
                'withdrawTotal' => $stats->withdraw_total,
                'spentOnCases' => $spentOnCases,
                'inventoryCount' => $inventory->count(),
                'inventoryValue' => $inventoryValue,
                'registeredAt' => $user->created_at?->toISOString(),
            ],
            'inventory' => $inventory->map->toFrontend(),
            'skinHistory' => $skinHistory,
            'transactions' => $transactions,
            'skinWithdrawals' => $skinWithdrawals,
            'promos' => $promoCodeService->getPublicPromos(),
        ]);
    }

    public function publicProfile(User $user)
    {
        $stats = $user->stat();

        $upgradeRate = $stats->upgrades_count > 0
            ? (int) round(($stats->upgrades_success / $stats->upgrades_count) * 100)
            : 0;

        $skinHistory = UserSkinHistory::query()
            ->with('skin')
            ->where('user_id', $user->id)
            ->latest()
            ->limit(24)
            ->get()
            ->map->toFrontend();

        return response()->json([
            'user' => $user->toPublicFrontend(),
            'stats' => [
                'casesOpened' => $stats->cases_opened,
                'upgradesCount' => $stats->upgrades_count,
                'upgradeRate' => $upgradeRate,
                'contractsCount' => $stats->contracts_count,
                'withdrawTotal' => $stats->withdraw_total,
                'registeredAt' => $user->created_at?->toISOString(),
            ],
            'skinHistory' => $skinHistory,
        ]);
    }
}
