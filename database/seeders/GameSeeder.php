<?php

namespace Database\Seeders;

use App\Models\GameCase;
use App\Models\LiveDrop;
use App\Models\Skin;
use App\Models\User;
use App\Support\GameEconomy;
use App\Support\GameImages;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class GameSeeder extends Seeder
{
    /** @var array<string, int> */
    private array $skinIds = [];

    public function run(): void
    {
        $this->seedSkins();
        $this->seedCases();
        $this->seedLiveDrops();
    }

    private function seedSkins(): void
    {
        $skins = [
            // Ширпотреб
            ['key' => 'sand_dune', 'name' => 'Sand Dune', 'weapon' => 'P250', 'rarity' => 'consumer', 'price' => 8],
            ['key' => 'safari_mesh', 'name' => 'Safari Mesh', 'weapon' => 'AWP', 'rarity' => 'consumer', 'price' => 18],
            ['key' => 'storm', 'name' => 'Storm', 'weapon' => 'MAG-7', 'rarity' => 'consumer', 'price' => 10],
            ['key' => 'urban_ddpat', 'name' => 'Urban DDPAT', 'weapon' => 'G3SG1', 'rarity' => 'consumer', 'price' => 12],
            ['key' => 'boreal_forest', 'name' => 'Boreal Forest', 'weapon' => 'M4A4', 'rarity' => 'consumer', 'price' => 22],
            ['key' => 'forest_ddpat', 'name' => 'Forest DDPAT', 'weapon' => 'Dual Berettas', 'rarity' => 'consumer', 'price' => 11],

            // Промышленное
            ['key' => 'silver', 'name' => 'Silver', 'weapon' => 'MAC-10', 'rarity' => 'industrial', 'price' => 68],
            ['key' => 'metallic_ddpat', 'name' => 'Metallic DDPAT', 'weapon' => 'Five-SeveN', 'rarity' => 'industrial', 'price' => 82],
            ['key' => 'blue_spruce', 'name' => 'Blue Spruce', 'weapon' => 'PP-Bizon', 'rarity' => 'industrial', 'price' => 55],
            ['key' => 'red_quartz', 'name' => 'Red Quartz', 'weapon' => 'Galil AR', 'rarity' => 'industrial', 'price' => 95],
            ['key' => 'army_mesh', 'name' => 'Army Mesh', 'weapon' => 'Nova', 'rarity' => 'industrial', 'price' => 62],

            // Mil-Spec
            ['key' => 'hyper_beast', 'name' => 'Hyper Beast', 'weapon' => 'Nova', 'rarity' => 'mil-spec', 'price' => 420],
            ['key' => 'elite_build', 'name' => 'Elite Build', 'weapon' => 'AK-47', 'rarity' => 'mil-spec', 'price' => 580],
            ['key' => 'guardian', 'name' => 'Guardian', 'weapon' => 'M4A1-S', 'rarity' => 'mil-spec', 'price' => 520],
            ['key' => 'cartel', 'name' => 'Cartel', 'weapon' => 'Glock-18', 'rarity' => 'mil-spec', 'price' => 480],
            ['key' => 'frontside_misty', 'name' => 'Frontside Misty', 'weapon' => 'M4A4', 'rarity' => 'mil-spec', 'price' => 640],
            ['key' => 'neo_noir', 'name' => 'Neo-Noir', 'weapon' => 'USP-S', 'rarity' => 'mil-spec', 'price' => 560],
            ['key' => 'usp_blueprint', 'name' => 'Blueprint', 'weapon' => 'USP-S', 'rarity' => 'consumer', 'price' => 28],
            ['key' => 'usp_stainless', 'name' => 'Stainless', 'weapon' => 'USP-S', 'rarity' => 'restricted', 'price' => 1850],
            ['key' => 'usp_orion', 'name' => 'Orion', 'weapon' => 'USP-S', 'rarity' => 'covert', 'price' => 12800],

            // Desert Eagle
            ['key' => 'deagle_mudder', 'name' => 'Mudder', 'weapon' => 'Desert Eagle', 'rarity' => 'consumer', 'price' => 24],
            ['key' => 'deagle_bronze', 'name' => 'Bronze Deco', 'weapon' => 'Desert Eagle', 'rarity' => 'industrial', 'price' => 88],
            ['key' => 'deagle_crimson', 'name' => 'Crimson Web', 'weapon' => 'Desert Eagle', 'rarity' => 'mil-spec', 'price' => 620],
            ['key' => 'deagle_koi', 'name' => 'Golden Koi', 'weapon' => 'Desert Eagle', 'rarity' => 'classified', 'price' => 6800],
            ['key' => 'deagle_code_red', 'name' => 'Code Red', 'weapon' => 'Desert Eagle', 'rarity' => 'covert', 'price' => 14200],

            // Glock-18
            ['key' => 'glock_ground', 'name' => 'Groundwater', 'weapon' => 'Glock-18', 'rarity' => 'consumer', 'price' => 19],
            ['key' => 'glock_steel', 'name' => 'Steel Disruption', 'weapon' => 'Glock-18', 'rarity' => 'industrial', 'price' => 92],
            ['key' => 'glock_moonrise', 'name' => 'Moonrise', 'weapon' => 'Glock-18', 'rarity' => 'mil-spec', 'price' => 510],
            ['key' => 'glock_rebel', 'name' => 'Wasteland Rebel', 'weapon' => 'Glock-18', 'rarity' => 'classified', 'price' => 6200],
            ['key' => 'glock_queen', 'name' => 'Bullet Queen', 'weapon' => 'Glock-18', 'rarity' => 'covert', 'price' => 11800],

            // Covert
            ['key' => 'containment_breach', 'name' => 'Containment Breach', 'weapon' => 'AWP', 'rarity' => 'covert', 'price' => 16800],
            ['key' => 'hyper_beast_m4', 'name' => 'Hyper Beast', 'weapon' => 'M4A4', 'rarity' => 'covert', 'price' => 13200],

            // Restricted
            ['key' => 'fade_glock', 'name' => 'Fade', 'weapon' => 'Glock-18', 'rarity' => 'restricted', 'price' => 2100],
            ['key' => 'blaze', 'name' => 'Blaze', 'weapon' => 'Desert Eagle', 'rarity' => 'restricted', 'price' => 2450],
            ['key' => 'redline_ak', 'name' => 'Redline', 'weapon' => 'AK-47', 'rarity' => 'restricted', 'price' => 3200],
            ['key' => 'vulcan', 'name' => 'Vulcan', 'weapon' => 'M4A4', 'rarity' => 'restricted', 'price' => 4100],
            ['key' => 'water_elemental', 'name' => 'Water Elemental', 'weapon' => 'Glock-18', 'rarity' => 'restricted', 'price' => 1680],
            ['key' => 'cyrex', 'name' => 'Cyrex', 'weapon' => 'M4A1-S', 'rarity' => 'restricted', 'price' => 2780],

            // Classified
            ['key' => 'neon_rider', 'name' => 'Neon Rider', 'weapon' => 'AK-47', 'rarity' => 'classified', 'price' => 7800],
            ['key' => 'fuel_injector', 'name' => 'Fuel Injector', 'weapon' => 'AK-47', 'rarity' => 'classified', 'price' => 9200],
            ['key' => 'nightmare', 'name' => 'Nightmare', 'weapon' => 'M4A4', 'rarity' => 'classified', 'price' => 7200],
            ['key' => 'kill_confirmed', 'name' => 'Kill Confirmed', 'weapon' => 'USP-S', 'rarity' => 'classified', 'price' => 6800],
            ['key' => 'empress', 'name' => 'Empress', 'weapon' => 'AK-47', 'rarity' => 'classified', 'price' => 9800],

            // Covert (оружие)
            ['key' => 'asiimov', 'name' => 'Asiimov', 'weapon' => 'AWP', 'rarity' => 'covert', 'price' => 15800],
            ['key' => 'asiimov_m4', 'name' => 'Asiimov', 'weapon' => 'M4A4', 'rarity' => 'covert', 'price' => 12200],
            ['key' => 'printstream', 'name' => 'Printstream', 'weapon' => 'M4A1-S', 'rarity' => 'covert', 'price' => 18500],
            ['key' => 'wild_lotus', 'name' => 'Wild Lotus', 'weapon' => 'AK-47', 'rarity' => 'covert', 'price' => 24800],
            ['key' => 'neo_noir_awp', 'name' => 'Neo-Noir', 'weapon' => 'AWP', 'rarity' => 'covert', 'price' => 13600],

            // ★ Ножи (базовые)
            ['key' => 'doppler_knife', 'name' => 'Doppler', 'weapon' => '★ Karambit', 'rarity' => 'gold', 'price' => 138000],
            ['key' => 'fade_knife', 'name' => 'Fade', 'weapon' => '★ Butterfly Knife', 'rarity' => 'gold', 'price' => 198000],
            ['key' => 'tiger_tooth', 'name' => 'Tiger Tooth', 'weapon' => '★ M9 Bayonet', 'rarity' => 'gold', 'price' => 112000],
            ['key' => 'marble_fade', 'name' => 'Marble Fade', 'weapon' => '★ Bayonet', 'rarity' => 'gold', 'price' => 86000],
            ['key' => 'dragon_lore', 'name' => 'Dragon Lore', 'weapon' => 'AWP', 'rarity' => 'gold', 'price' => 428000],
            ['key' => 'howl', 'name' => 'Howl', 'weapon' => 'M4A4', 'rarity' => 'gold', 'price' => 312000],

            // ★ Перчатки (базовые)
            ['key' => 'gloves_pandora', 'name' => 'Pandora\'s Box', 'weapon' => '★ Sport Gloves', 'rarity' => 'gold', 'price' => 248000],
            ['key' => 'gloves_king_snake', 'name' => 'King Snake', 'weapon' => '★ Driver Gloves', 'rarity' => 'gold', 'price' => 98000],
            ['key' => 'gloves_cobalt', 'name' => 'Cobalt Skulls', 'weapon' => '★ Hand Wraps', 'rarity' => 'gold', 'price' => 88000],
            ['key' => 'gloves_spearmint', 'name' => 'Spearmint', 'weapon' => '★ Moto Gloves', 'rarity' => 'gold', 'price' => 186000],
            ['key' => 'gloves_fade', 'name' => 'Fade', 'weapon' => '★ Specialist Gloves', 'rarity' => 'gold', 'price' => 142000],
        ];

        $skins = array_merge(
            $skins,
            require __DIR__.'/data/extra_consumer_skins.php',
            require __DIR__.'/data/extra_premium_skins.php',
            require __DIR__.'/data/casebattle_skins.php',
        );

        foreach ($skins as $data) {
            $skin = Skin::updateOrCreate(
                ['name' => $data['name'], 'weapon' => $data['weapon']],
                [
                    'rarity' => $data['rarity'],
                    'price' => $data['price'],
                    'image' => GameImages::SKIN_PLACEHOLDER,
                ]
            );
            $this->skinIds[$data['key']] = $skin->id;
        }

        $this->ensureCheapTierFillers();
    }

    private function ensureCheapTierFillers(): void
    {
        $premiumPrices = [5000, 10000, 15000, 20000, 25000, 30000, 40000, 50000, 75000, 100000];
        $weapons = ['AK-47', 'M4A4', 'AWP', 'USP-S', 'Desert Eagle', 'Glock-18', 'Galil AR', 'FAMAS', 'SG 553', 'AUG'];

        foreach ($premiumPrices as $casePrice) {
            $min = GameEconomy::minSkinPriceForCase($casePrice);
            $max = GameEconomy::cheapSkinMaxPrice($casePrice);

            $cheapCount = count(array_filter(
                array_keys($this->skinIds),
                fn (string $key) => GameEconomy::isCheapTierSkinPrice($this->skinPrice($key), $casePrice),
            ));

            $missing = GameEconomy::CASE_CHEAP_SKIN_COUNT - $cheapCount;
            if ($missing <= 0) {
                continue;
            }

            for ($i = 0; $i < $missing; $i++) {
                $idx = $cheapCount + $i + 1;
                $spread = max(1, $max - $min);
                $price = $min + (int) floor($spread * ($i + 1) / ($missing + 1));
                $price = min($max, max($min, $price));
                $weapon = $weapons[$i % count($weapons)];
                $key = "cheap_tier_{$casePrice}_{$idx}";

                $skin = Skin::updateOrCreate(
                    ['name' => "Наполнитель {$casePrice} #{$idx}", 'weapon' => $weapon],
                    [
                        'rarity' => 'restricted',
                        'price' => $price,
                        'image' => GameImages::SKIN_PLACEHOLDER,
                    ]
                );
                $this->skinIds[$key] = $skin->id;
            }
        }
    }

    private function seedCases(): void
    {
        $weights = GameEconomy::dropWeights();

        $cases = require __DIR__.'/data/casebattle_cases.php';
        $seededNames = [];

        foreach ($cases as $caseData) {
            $maxSkinPrice = isset($caseData['max_skin_price']) ? (int) $caseData['max_skin_price'] : null;
            if ($maxSkinPrice === null && ($caseData['category'] ?? '') === 'premium') {
                // Для премиума разрешаем дорогие "джекпоты", чтобы были +50%/+100% окупаемости.
                $maxSkinPrice = min(350_000, max(100_000, (int) $caseData['price'] * 4));
            }

            $skinKeys = $caseData['skins'] ?? $this->buildCasePool(
                (int) $caseData['price'],
                $caseData['jackpots'] ?? [],
                (int) ($caseData['pool_size'] ?? 40),
                $maxSkinPrice,
            );

            $gameCase = GameCase::updateOrCreate(
                ['name' => $caseData['name']],
                [
                    'price' => $caseData['price'],
                    'image' => GameImages::CASE_PLACEHOLDER,
                    'category' => $caseData['category'],
                    'featured' => $caseData['featured'] ?? false,
                ]
            );

            $seededNames[] = $caseData['name'];

            $sync = [];
            $sortedSkinKeys = $this->sortSkinKeysByRarity($skinKeys);
            $minSkinPrice = GameEconomy::minSkinPriceForCase(
                (int) $caseData['price'],
                $caseData['category'] ?? null,
            );
            foreach ($sortedSkinKeys as $skinKey) {
                if (! isset($this->skinIds[$skinKey])) {
                    continue;
                }
                $skinId = $this->skinIds[$skinKey];
                $skin = Skin::find($skinId);
                if ($minSkinPrice > 0 && (int) $skin->price < $minSkinPrice) {
                    continue;
                }
                if ($maxSkinPrice !== null && (int) $skin->price > $maxSkinPrice) {
                    continue;
                }
                $sync[$skinId] = ['drop_weight' => $weights[$skin->rarity] ?? 100];
            }
            $gameCase->skins()->sync($sync);
        }

        GameCase::query()
            ->whereNotIn('name', $seededNames)
            ->where('category', '!=', 'secret')
            ->each(function (GameCase $gameCase) {
                $gameCase->skins()->detach();
                $gameCase->delete();
            });
    }

    /**
     * Сбалансированное наполнение: дешёвые filler + mid + chase по цене кейса.
     *
     * @param  list<string>  $jackpots
     * @return list<string>
     */
    private function buildCasePool(int $casePrice, array $jackpots = [], int $targetCount = 40, ?int $maxSkinPrice = null): array
    {
        $casePrice = max(1, $casePrice);
        $minSkinPrice = GameEconomy::minSkinPriceForCase($casePrice);

        $pool = array_values(array_unique(array_filter(
            $jackpots,
            fn (string $key) => $this->skinPrice($key) >= $minSkinPrice
                && ($maxSkinPrice === null || $this->skinPrice($key) <= $maxSkinPrice),
        )));

        if ($casePrice >= GameEconomy::CASE_MIN_SKIN_PRICE_THRESHOLD) {
            return $this->buildBalancedPool($pool, $targetCount, $minSkinPrice, $casePrice, $maxSkinPrice);
        }

        $cheapMax = max($minSkinPrice + 1, max(30, (int) round($casePrice * 2.5)));
        $midMax = max(200, (int) round($casePrice * 12));
        $highMax = $maxSkinPrice !== null
            ? min($maxSkinPrice, max(5_000, (int) round($casePrice * 6)))
            : max(2_000, (int) round($casePrice * 80));

        $cheap = [];
        $mid = [];
        $high = [];
        $insane = [];

        foreach (array_keys($this->skinIds) as $key) {
            if (in_array($key, $pool, true)) {
                continue;
            }

            $price = $this->skinPrice($key);

            if ($minSkinPrice > 0 && $price < $minSkinPrice) {
                continue;
            }

            if ($maxSkinPrice !== null && $price > $maxSkinPrice) {
                continue;
            }

            if ($price <= $cheapMax) {
                $cheap[] = $key;
            } elseif ($price <= $midMax) {
                $mid[] = $key;
            } elseif ($price <= $highMax) {
                $high[] = $key;
            } else {
                $insane[] = $key;
            }
        }

        shuffle($cheap);
        shuffle($mid);
        shuffle($high);
        shuffle($insane);

        $cheapCount = (int) round($targetCount * 0.55);
        $midCount = (int) round($targetCount * 0.28);
        $highCount = (int) round($targetCount * 0.12);
        $insaneCount = max(1, (int) round($targetCount * 0.05));

        $pool = array_merge(
            $pool,
            array_slice($cheap, 0, $cheapCount),
            array_slice($mid, 0, $midCount),
            array_slice($high, 0, $highCount),
            array_slice($insane, 0, $insaneCount),
        );

        return array_values(array_unique($pool));
    }

    /**
     * Пул для дорогих кейсов: минимум 50% цены кейса + ступени окупаемости.
     *
     * @param  list<string>  $pool
     * @return list<string>
     */
    private function buildBalancedPool(
        array $pool,
        int $targetCount,
        int $minSkinPrice,
        int $casePrice,
        ?int $maxSkinPrice,
    ): array {
        $cheapMax = GameEconomy::cheapSkinMaxPrice($casePrice);
        $t10 = (int) floor($casePrice * 1.1);
        $t25 = (int) floor($casePrice * 1.25);
        $t50 = (int) floor($casePrice * 1.5);
        $t100 = (int) floor($casePrice * 2.0);

        $cheap = [];    // 50%–80% цены кейса
        $loss = [];     // 80%–100% (не окупают)
        $payback = [];  // [casePrice .. +10%)
        $p10 = [];
        $p25 = [];
        $p50 = [];
        $p100 = [];

        foreach (array_keys($this->skinIds) as $key) {
            if (in_array($key, $pool, true)) {
                continue;
            }

            $price = $this->skinPrice($key);

            if ($price < $minSkinPrice) {
                continue;
            }

            if ($maxSkinPrice !== null && $price > $maxSkinPrice) {
                continue;
            }

            if ($price <= $cheapMax) {
                $cheap[] = $key;
            } elseif ($price < $casePrice) {
                $loss[] = $key;
            } elseif ($price < $t10) {
                $payback[] = $key;
            } elseif ($price < $t25) {
                $p10[] = $key;
            } elseif ($price < $t50) {
                $p25[] = $key;
            } elseif ($price < $t100) {
                $p50[] = $key;
            } else {
                $p100[] = $key;
            }
        }

        shuffle($cheap);
        shuffle($loss);
        shuffle($payback);
        shuffle($p10);
        shuffle($p25);
        shuffle($p50);
        shuffle($p100);

        $cheapCount = GameEconomy::CASE_CHEAP_SKIN_COUNT;
        $lossCount = min(count($loss), 8);
        $paybackCount = min(count($payback), 2);
        $p10Count = min(count($p10), 3);
        $p25Count = min(count($p25), 3);
        $p50Count = min(count($p50), 2);
        $p100Count = min(count($p100), 2);

        $picked = array_merge(
            array_slice($cheap, 0, $cheapCount),
            array_slice($loss, 0, $lossCount),
            array_slice($payback, 0, $paybackCount),
            array_slice($p10, 0, $p10Count),
            array_slice($p25, 0, $p25Count),
            array_slice($p50, 0, $p50Count),
            array_slice($p100, 0, $p100Count),
        );

        $rest = array_merge(
            array_slice($cheap, $cheapCount),
            array_slice($loss, $lossCount),
            array_slice($p100, $p100Count),
            array_slice($p50, $p50Count),
            array_slice($p25, $p25Count),
            array_slice($p10, $p10Count),
            array_slice($payback, $paybackCount),
        );

        return array_values(array_unique(array_merge(
            $pool,
            array_slice($picked, 0, max(0, $targetCount - count($pool))),
            array_slice($rest, 0, max(0, $targetCount - count($pool) - count($picked))),
        )));
    }

    /** @param  array<int, string>  $skinKeys */
    private function sortSkinKeysByRarity(array $skinKeys): array
    {
        $rarityOrder = [
            'consumer' => 0,
            'industrial' => 1,
            'mil-spec' => 2,
            'restricted' => 3,
            'classified' => 4,
            'covert' => 5,
            'gold' => 6,
        ];

        $keys = array_values(array_unique($skinKeys));

        usort($keys, function (string $leftKey, string $rightKey) use ($rarityOrder) {
            $leftRarity = $this->skinRarity($leftKey);
            $rightRarity = $this->skinRarity($rightKey);
            $leftOrder = $rarityOrder[$leftRarity] ?? 99;
            $rightOrder = $rarityOrder[$rightRarity] ?? 99;

            if ($leftOrder !== $rightOrder) {
                return $leftOrder <=> $rightOrder;
            }

            $leftPrice = $this->skinPrice($leftKey);
            $rightPrice = $this->skinPrice($rightKey);

            return $leftPrice <=> $rightPrice;
        });

        return $keys;
    }

    private function skinRarity(string $skinKey): string
    {
        if (! isset($this->skinIds[$skinKey])) {
            return 'consumer';
        }

        return Skin::find($this->skinIds[$skinKey])?->rarity ?? 'consumer';
    }

    private function skinPrice(string $skinKey): int
    {
        if (! isset($this->skinIds[$skinKey])) {
            return 0;
        }

        return (int) (Skin::find($this->skinIds[$skinKey])?->price ?? 0);
    }

    private function seedLiveDrops(): void
    {
        $demoUsers = [
            ['name' => 'Ludoman1c_Pro', 'email' => 'pro@casebattle.test'],
            ['name' => 'Ludoman1c_Slayer', 'email' => 'slayer@casebattle.test'],
            ['name' => 'Ludoman1c_Hunter', 'email' => 'hunter@casebattle.test'],
            ['name' => 'Ludoman1c_Lucky', 'email' => 'lucky@casebattle.test'],
        ];

        $allCases = GameCase::with('skins')->where('category', '!=', 'secret')->get();

        foreach ($demoUsers as $i => $userData) {
            $user = User::updateOrCreate(
                ['email' => $userData['email']],
                [
                    'name' => $userData['name'],
                    'password' => Hash::make('password'),
                    'balance' => 50000,
                    'role' => 'user',
                    'email_verified_at' => now(),
                ]
            );
            $user->stat();

            if (LiveDrop::where('user_id', $user->id)->exists()) {
                continue;
            }

            $case = $allCases[$i % max(1, $allCases->count())];
            $skin = $case->skins->random();

            LiveDrop::create([
                'user_id' => $user->id,
                'skin_id' => $skin->id,
                'game_case_id' => $case->id,
                'created_at' => now()->subMinutes($i * 3),
            ]);
        }
    }
}
