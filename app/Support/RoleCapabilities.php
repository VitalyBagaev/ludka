<?php

namespace App\Support;

use App\Enums\UserRole;
use App\Models\User;

class RoleCapabilities
{
    /** @return list<string> */
    public static function all(): array
    {
        return [
            'manage_roles',
            'delete_users',
            'view_users',
            'manage_skins',
            'manage_cases',
            'manage_withdrawals',
            'manage_featured',
            'view_live_drops',
            'view_stats',
        ];
    }

    public static function can(User $user, string $capability): bool
    {
        return in_array($capability, self::forRole($user->role), true);
    }

    /** @return list<string> */
    public static function forRole(UserRole $role): array
    {
        return match ($role) {
            UserRole::SuperAdmin => self::all(),
            UserRole::Admin => [
                'view_users',
                'manage_skins',
                'manage_cases',
                'manage_withdrawals',
                'manage_featured',
                'view_live_drops',
                'view_stats',
            ],
            UserRole::Manager => [
                'manage_featured',
                'view_live_drops',
                'view_stats',
            ],
            UserRole::User => [],
        };
    }

    /** @return array<string, bool> */
    public static function frontendMap(UserRole $role): array
    {
        $granted = self::forRole($role);

        return [
            'manageRoles' => in_array('manage_roles', $granted, true),
            'deleteUsers' => in_array('delete_users', $granted, true),
            'viewUsers' => in_array('view_users', $granted, true),
            'manageSkins' => in_array('manage_skins', $granted, true),
            'manageCases' => in_array('manage_cases', $granted, true),
            'manageWithdrawals' => in_array('manage_withdrawals', $granted, true),
            'manageFeatured' => in_array('manage_featured', $granted, true),
            'viewLiveDrops' => in_array('view_live_drops', $granted, true),
            'viewStats' => in_array('view_stats', $granted, true),
        ];
    }
}
