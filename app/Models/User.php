<?php

namespace App\Models;

use App\Enums\UserRole;
use App\Support\RoleCapabilities;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Illuminate\Support\Facades\Storage;
use Laravel\Fortify\TwoFactorAuthenticatable;
use Laravel\Sanctum\HasApiTokens;

class User extends Authenticatable
{
    /** @use HasFactory<\Database\Factories\UserFactory> */
    use HasApiTokens, HasFactory, Notifiable, TwoFactorAuthenticatable;

    protected $fillable = [
        'name',
        'email',
        'password',
        'balance',
        'role',
        'avatar',
    ];

    protected $hidden = [
        'password',
        'two_factor_secret',
        'two_factor_recovery_codes',
        'remember_token',
    ];

    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
            'two_factor_confirmed_at' => 'datetime',
            'balance' => 'integer',
            'role' => UserRole::class,
        ];
    }

    protected static function booted(): void
    {
        static::created(function (User $user) {
            $user->stats()->create([]);
        });
    }

    public function stats(): HasOne
    {
        return $this->hasOne(UserStat::class);
    }

    public function skinHistory(): HasMany
    {
        return $this->hasMany(UserSkinHistory::class);
    }

    public function stat(): UserStat
    {
        return $this->stats()->firstOrCreate([]);
    }

    public function incrementStat(string $column, int $amount = 1): void
    {
        $this->stat()->increment($column, $amount);
    }

    public function isSuperAdmin(): bool
    {
        return $this->role === UserRole::SuperAdmin;
    }

    public function isAdmin(): bool
    {
        return $this->role->isAdminLevel();
    }

    public function isManager(): bool
    {
        return $this->role === UserRole::Manager;
    }

    public function isStaff(): bool
    {
        return $this->role->isStaff();
    }

    public function hasCapability(string $capability): bool
    {
        return RoleCapabilities::can($this, $capability);
    }

    public function inventoryItems(): HasMany
    {
        return $this->hasMany(InventoryItem::class);
    }

    public function liveDrops(): HasMany
    {
        return $this->hasMany(LiveDrop::class);
    }

    public function balanceTransactions(): HasMany
    {
        return $this->hasMany(BalanceTransaction::class);
    }

    public function avatarUrl(): ?string
    {
        if (! $this->avatar) {
            return null;
        }

        if (str_starts_with($this->avatar, 'http') || str_starts_with($this->avatar, '/')) {
            return $this->avatar;
        }

        return Storage::disk('public')->url($this->avatar);
    }

    public static function initialsFromName(?string $name): string
    {
        if (! $name) {
            return '??';
        }

        $parts = preg_split('/[_\s]+/', trim($name), -1, PREG_SPLIT_NO_EMPTY);

        if (count($parts) >= 2) {
            return mb_strtoupper(mb_substr($parts[0], 0, 1).mb_substr($parts[1], 0, 1));
        }

        return mb_strtoupper(mb_substr($name, 0, 2));
    }

    public function toFrontend(): array
    {
        return [
            'id' => $this->id,
            'name' => $this->name,
            'email' => $this->email,
            'balance' => $this->balance,
            'role' => $this->role->value,
            'roleLabel' => $this->role->label(),
            'permissions' => RoleCapabilities::frontendMap($this->role),
            'avatar' => $this->avatarUrl(),
            'initials' => self::initialsFromName($this->name),
        ];
    }

    public function toPublicFrontend(): array
    {
        return [
            'id' => $this->id,
            'name' => $this->name,
            'role' => $this->role->value,
            'roleLabel' => $this->role->label(),
            'avatar' => $this->avatarUrl(),
            'initials' => self::initialsFromName($this->name),
        ];
    }

    public function toAdminFrontend(): array
    {
        return [
            ...$this->toFrontend(),
            'createdAt' => $this->created_at?->toISOString(),
        ];
    }
}
