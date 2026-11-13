<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->unsignedInteger('balance')->default(0)->after('password');
        });

        Schema::create('skins', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('weapon');
            $table->string('rarity');
            $table->unsignedInteger('price');
            $table->string('image');
            $table->timestamps();
        });

        Schema::create('game_cases', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->unsignedInteger('price');
            $table->string('image');
            $table->string('category')->default('popular');
            $table->boolean('featured')->default(false);
            $table->timestamps();
        });

        Schema::create('case_skin', function (Blueprint $table) {
            $table->id();
            $table->foreignId('game_case_id')->constrained()->cascadeOnDelete();
            $table->foreignId('skin_id')->constrained()->cascadeOnDelete();
            $table->unsignedInteger('drop_weight')->default(100);
            $table->unique(['game_case_id', 'skin_id']);
        });

        Schema::create('inventory_items', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->foreignId('skin_id')->constrained()->cascadeOnDelete();
            $table->string('source')->default('case_open');
            $table->timestamps();
        });

        Schema::create('live_drops', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->foreignId('skin_id')->constrained()->cascadeOnDelete();
            $table->foreignId('game_case_id')->constrained()->cascadeOnDelete();
            $table->timestamps();
        });

        Schema::create('balance_transactions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->integer('amount');
            $table->string('type');
            $table->string('description')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('balance_transactions');
        Schema::dropIfExists('live_drops');
        Schema::dropIfExists('inventory_items');
        Schema::dropIfExists('case_skin');
        Schema::dropIfExists('game_cases');
        Schema::dropIfExists('skins');

        Schema::table('users', function (Blueprint $table) {
            $table->dropColumn('balance');
        });
    }
};
