<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->unsignedInteger('cases_opened')->default(0)->after('balance');
            $table->unsignedInteger('upgrades_count')->default(0)->after('cases_opened');
            $table->unsignedInteger('upgrades_success')->default(0)->after('upgrades_count');
            $table->unsignedInteger('contracts_count')->default(0)->after('upgrades_success');
            $table->unsignedInteger('withdraw_total')->default(0)->after('contracts_count');
        });

        foreach (DB::table('users')->pluck('id') as $userId) {
            DB::table('users')->where('id', $userId)->update([
                'cases_opened' => DB::table('live_drops')->where('user_id', $userId)->count(),
            ]);
        }
    }

    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropColumn([
                'cases_opened',
                'upgrades_count',
                'upgrades_success',
                'contracts_count',
                'withdraw_total',
            ]);
        });
    }
};
