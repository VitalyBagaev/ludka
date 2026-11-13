<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        $accounts = [
            [
                'name' => 'super',
                'email' => 'super',
                'password' => 'super',
                'balance' => 100000,
                'role' => 'super_admin',
            ],
            [
                'name' => 'admin',
                'email' => 'admin',
                'password' => 'admin',
                'balance' => 50000,
                'role' => 'admin',
            ],
            [
                'name' => 'manager',
                'email' => 'manager',
                'password' => 'manager',
                'balance' => 10000,
                'role' => 'manager',
            ],
            [
                'name' => 'user',
                'email' => 'user',
                'password' => 'user',
                'balance' => 5000,
                'role' => 'user',
            ],
        ];

        foreach ($accounts as $account) {
            $user = User::updateOrCreate(
                ['email' => $account['email']],
                [
                    'name' => $account['name'],
                    'password' => $account['password'],
                    'balance' => $account['balance'],
                    'role' => $account['role'],
                    'email_verified_at' => now(),
                ]
            );
            $user->stat();
        }

        $this->call(GameSeeder::class);
    }
}
