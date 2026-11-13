<?php

namespace App\Enums;

enum UserRole: string
{
    case User = 'user';
    case Manager = 'manager';
    case Admin = 'admin';
    case SuperAdmin = 'super_admin';

    public function label(): string
    {
        return match ($this) {
            self::User => 'Пользователь',
            self::Manager => 'Менеджер',
            self::Admin => 'Администратор',
            self::SuperAdmin => 'Главный админ',
        };
    }

    public function sortOrder(): int
    {
        return match ($this) {
            self::SuperAdmin => 0,
            self::Admin => 1,
            self::Manager => 2,
            self::User => 3,
        };
    }

    public function isStaff(): bool
    {
        return $this !== self::User;
    }

    public function isAdminLevel(): bool
    {
        return $this === self::Admin || $this === self::SuperAdmin;
    }
}
