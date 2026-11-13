<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('case_battles', function (Blueprint $table) {
            $table->id();
            $table->foreignId('creator_id')->constrained('users')->cascadeOnDelete();
            $table->unsignedTinyInteger('max_players')->default(2);
            $table->unsignedInteger('total_price')->default(0);
            $table->json('case_ids');
            $table->string('status', 20)->default('waiting'); // waiting, finished
            $table->foreignId('winner_user_id')->nullable()->constrained('users')->nullOnDelete();
            $table->unsignedTinyInteger('winner_slot')->nullable();
            $table->json('results')->nullable();
            $table->timestamps();
        });

        Schema::create('case_battle_players', function (Blueprint $table) {
            $table->id();
            $table->foreignId('case_battle_id')->constrained('case_battles')->cascadeOnDelete();
            $table->foreignId('user_id')->nullable()->constrained('users')->nullOnDelete();
            $table->unsignedTinyInteger('slot');
            $table->boolean('is_bot')->default(false);
            $table->string('bot_name')->nullable();
            $table->unsignedInteger('total_drop_value')->default(0);
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('case_battle_players');
        Schema::dropIfExists('case_battles');
    }
};
