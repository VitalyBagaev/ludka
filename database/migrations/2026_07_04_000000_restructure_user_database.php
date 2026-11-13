<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('user_stats', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->unique()->constrained()->cascadeOnDelete();
            $table->unsignedInteger('cases_opened')->default(0);
            $table->unsignedInteger('upgrades_count')->default(0);
            $table->unsignedInteger('upgrades_success')->default(0);
            $table->unsignedInteger('contracts_count')->default(0);
            $table->unsignedInteger('withdraw_total')->default(0);
            $table->timestamps();
        });

        Schema::table('users', function (Blueprint $table) {
            $table->string('avatar')->nullable()->after('role');
        });

        if (Schema::hasColumn('users', 'cases_opened')) {
            $users = DB::table('users')->get(['id', 'cases_opened', 'upgrades_count', 'upgrades_success', 'contracts_count', 'withdraw_total']);
            foreach ($users as $user) {
                DB::table('user_stats')->insert([
                    'user_id' => $user->id,
                    'cases_opened' => $user->cases_opened ?? 0,
                    'upgrades_count' => $user->upgrades_count ?? 0,
                    'upgrades_success' => $user->upgrades_success ?? 0,
                    'contracts_count' => $user->contracts_count ?? 0,
                    'withdraw_total' => $user->withdraw_total ?? 0,
                    'created_at' => now(),
                    'updated_at' => now(),
                ]);
            }

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

        Schema::create('user_skin_history', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->foreignId('skin_id')->constrained()->cascadeOnDelete();
            $table->foreignId('inventory_item_id')->nullable()->constrained()->nullOnDelete();
            $table->foreignId('live_drop_id')->nullable()->constrained()->nullOnDelete();
            $table->string('case_name')->nullable();
            $table->string('weapon');
            $table->string('skin_name');
            $table->unsignedInteger('skin_price');
            $table->string('source');
            $table->string('status')->default('inventory');
            $table->timestamps();

            $table->index(['user_id', 'created_at']);
            $table->index(['inventory_item_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('user_skin_history');

        Schema::table('users', function (Blueprint $table) {
            $table->unsignedInteger('cases_opened')->default(0);
            $table->unsignedInteger('upgrades_count')->default(0);
            $table->unsignedInteger('upgrades_success')->default(0);
            $table->unsignedInteger('contracts_count')->default(0);
            $table->unsignedInteger('withdraw_total')->default(0);
        });

        foreach (DB::table('user_stats')->get() as $stat) {
            DB::table('users')->where('id', $stat->user_id)->update([
                'cases_opened' => $stat->cases_opened,
                'upgrades_count' => $stat->upgrades_count,
                'upgrades_success' => $stat->upgrades_success,
                'contracts_count' => $stat->contracts_count,
                'withdraw_total' => $stat->withdraw_total,
            ]);
        }

        Schema::dropIfExists('user_stats');

        Schema::table('users', function (Blueprint $table) {
            $table->dropColumn('avatar');
        });
    }
};
