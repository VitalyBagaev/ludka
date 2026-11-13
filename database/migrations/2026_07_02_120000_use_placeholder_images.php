<?php

use App\Support\GameImages;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        DB::table('skins')->update(['image' => GameImages::SKIN_PLACEHOLDER]);
        DB::table('game_cases')->update(['image' => GameImages::CASE_PLACEHOLDER]);
    }

    public function down(): void
    {
        //
    }
};
