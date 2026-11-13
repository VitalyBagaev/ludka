<?php

use App\Support\GameEconomy;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /** @var list<string> */
    private array $rarerTiers = ['mil-spec', 'restricted', 'classified', 'covert', 'gold'];

    public function up(): void
    {
        $weights = GameEconomy::dropWeights();

        foreach ($this->rarerTiers as $rarity) {
            DB::table('case_skin')
                ->join('skins', 'skins.id', '=', 'case_skin.skin_id')
                ->where('skins.rarity', $rarity)
                ->update(['case_skin.drop_weight' => $weights[$rarity]]);
        }
    }

    public function down(): void
    {
        $legacy = [
            'mil-spec' => 320,
            'restricted' => 110,
            'classified' => 38,
            'covert' => 12,
            'gold' => 2,
        ];

        foreach ($this->rarerTiers as $rarity) {
            DB::table('case_skin')
                ->join('skins', 'skins.id', '=', 'case_skin.skin_id')
                ->where('skins.rarity', $rarity)
                ->update(['case_skin.drop_weight' => $legacy[$rarity]]);
        }
    }
};
