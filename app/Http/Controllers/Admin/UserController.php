<?php

namespace App\Http\Controllers\Admin;

use App\Enums\UserRole;
use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class UserController extends Controller
{
    public function index(): Response
    {
        $users = User::query()
            ->orderByRaw("CASE role WHEN 'admin' THEN 1 WHEN 'manager' THEN 2 ELSE 3 END")
            ->orderBy('name')
            ->get()
            ->map->toAdminFrontend();

        return Inertia::render('admin/Users/Index', [
            'users' => $users,
            'roles' => collect(UserRole::cases())->map(fn (UserRole $role) => [
                'value' => $role->value,
                'label' => $role->label(),
            ])->values(),
        ]);
    }

    public function updateRole(Request $request, User $user): RedirectResponse
    {
        $validated = $request->validate([
            'role' => ['required', 'in:user,manager,admin'],
        ]);

        if ($user->id === $request->user()->id && $validated['role'] !== UserRole::Admin->value) {
            return back()->with('error', 'Нельзя понизить собственную роль.');
        }

        $user->update(['role' => $validated['role']]);

        return back()->with('success', "Роль пользователя {$user->name} обновлена.");
    }

    public function destroy(Request $request, User $user): RedirectResponse
    {
        if ($user->id === $request->user()->id) {
            return back()->with('error', 'Нельзя удалить свой аккаунт.');
        }

        $user->delete();

        return back()->with('success', 'Пользователь удалён.');
    }
}
