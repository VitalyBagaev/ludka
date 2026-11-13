<?php

namespace App\Support;

use App\Models\Skin;
use Illuminate\Support\Collection;

class GameEconomy
{
    public const UPGRADE_MIN_CHANCE = 1;

    public const UPGRADE_MAX_CHANCE = 75;

    /** Targets with base chance above this cannot be selected. */
    public const UPGRADE_BASE_MAX = 75;

    public const UPGRADE_HOUSE_EDGE = 0.97;

    public const CONTRACT_MIN_ITEMS = 2;

    public const CONTRACT_MAX_ITEMS = 10;

    public const CONTRACT_BASE_MULTIPLIER = 1.25;

    /** Кейсы от этой цены — расширенная балансировка пула. */
    public const CASE_MIN_SKIN_PRICE_THRESHOLD = 5000;

    /** Самый дешёвый предмет не ниже этой доли от цены кейса. */
    public const CASE_MIN_SKIN_PRICE_RATIO = 0.5;

    /** «Дешёвая» ступень: верхняя граница (80% цены кейса). */
    public const CASE_CHEAP_SKIN_MAX_RATIO = 0.8;

    /** Сколько предметов в диапазоне 50%–80% цены кейса. */
    public const CASE_CHEAP_SKIN_COUNT = 10;

    public static function minSkinPriceForCase(int $casePrice, ?string $category = null): int
    {
        if ($category === 'secret') {
            return 100_000;
        }

        if ($casePrice <= 0) {
            return 0;
        }

        $half = max(1, (int) floor($casePrice * self::CASE_MIN_SKIN_PRICE_RATIO));

        if ($casePrice > 1000) {
            return max(500, $half);
        }

        return $half;
    }

    public static function cheapSkinMaxPrice(int $casePrice): int
    {
        return max(1, (int) floor($casePrice * self::CASE_CHEAP_SKIN_MAX_RATIO));
    }

    public static function isCheapTierSkinPrice(int $skinPrice, int $casePrice, ?string $category = null): bool
    {
        if ($casePrice < self::CASE_MIN_SKIN_PRICE_THRESHOLD) {
            return false;
        }

        $min = self::minSkinPriceForCase($casePrice, $category);
        $max = self::cheapSkinMaxPrice($casePrice);

        return $skinPrice >= $min && $skinPrice <= $max;
    }

    /** @return array<string, int> */
    public static function dropWeights(): array
    {
        return [
            'consumer' => 1000,
            'industrial' => 650,
            'mil-spec' => 640,
            'restricted' => 220,
            'classified' => 76,
            'covert' => 24,
            'gold' => 4,
        ];
    }

    public static function dropWeightForRarity(string $rarity): int
    {
        return self::dropWeights()[$rarity] ?? 100;
    }

    public static function upgradeEffectiveValue(int $sourcePrice, int $boostAmount = 0): int
    {
        return max(0, $sourcePrice + $boostAmount);
    }

    public static function upgradeRawChance(int $sourcePrice, int $targetPrice, int $boostAmount = 0): float
    {
        if ($sourcePrice <= 0 || $targetPrice <= 0) {
            return 0;
        }

        $effectiveValue = self::upgradeEffectiveValue($sourcePrice, $boostAmount);

        return ($effectiveValue / $targetPrice) * 100 * self::UPGRADE_HOUSE_EDGE;
    }

    public static function isUpgradeTargetAllowed(int $sourcePrice, int $targetPrice): bool
    {
        if ($sourcePrice <= 0 || $targetPrice <= $sourcePrice) {
            return false;
        }

        $rawPercent = ($sourcePrice / $targetPrice) * 100;

        return $rawPercent <= self::UPGRADE_BASE_MAX;
    }

    public static function upgradeBaseChance(int $sourcePrice, int $targetPrice): int
    {
        return self::upgradeChance($sourcePrice, $targetPrice, 0);
    }

    public static function upgradeChance(int $sourcePrice, int $targetPrice, int $boostAmount = 0): int
    {
        if ($sourcePrice <= 0 || $targetPrice <= 0) {
            return 0;
        }

        if (! self::isUpgradeTargetAllowed($sourcePrice, $targetPrice)) {
            return 0;
        }

        $chance = (int) round(self::upgradeRawChance($sourcePrice, $targetPrice, $boostAmount));

        return min(
            self::UPGRADE_MAX_CHANCE,
            max(self::UPGRADE_MIN_CHANCE, $chance),
        );
    }

    public static function upgradeRollChance(int $displayChance): int
    {
        if ($displayChance <= 1) {
            return 2;
        }

        if ($displayChance <= 5) {
            return min(self::UPGRADE_MAX_CHANCE, $displayChance + 1);
        }

        return $displayChance;
    }

    public static function upgradeRollSuccess(int $displayChance): bool
    {
        $rollChance = self::upgradeRollChance($displayChance);

        return random_int(1, 100) <= $rollChance;
    }

    public static function maxUpgradeBoost(int $sourcePrice, int $targetPrice, int $balance = 0): int
    {
        if ($sourcePrice <= 0 || $targetPrice <= 0 || $balance <= 0) {
            return 0;
        }

        if (! self::isUpgradeTargetAllowed($sourcePrice, $targetPrice)) {
            return 0;
        }

        $targetEffective = (int) round(
            ($targetPrice * (self::UPGRADE_MAX_CHANCE / 100)) / self::UPGRADE_HOUSE_EDGE,
        );
        $maxByChance = max(0, $targetEffective - $sourcePrice);
        $maxByTarget = (int) round($targetPrice * 0.5);

        return min($balance, $maxByChance, $maxByTarget);
    }

    public static function contractRange(int $totalValue, int $itemCount, int $boostAmount = 0): array
    {
        $boostFactor = min(1.0, $boostAmount / 10000);
        $multiplier = self::CONTRACT_BASE_MULTIPLIER + ($boostFactor * 1.25);

        $min = max(1, (int) round($totalValue / $multiplier));
        $max = max($min, (int) round($totalValue * $multiplier));

        return [
            'min' => $min,
            'max' => $max,
            'avg' => (int) round($totalValue / max(1, $itemCount)),
            'total' => $totalValue,
            'multiplier' => round($multiplier, 2),
        ];
    }

    public static function maxContractBoost(int $balance = 0): int
    {
        if ($balance <= 0) {
            return 0;
        }

        return min(10000, $balance);
    }

    /** @param  Collection<int, Skin>  $candidates */
    public static function pickContractSkin(Collection $candidates): Skin
    {
        if ($candidates->count() === 1) {
            return $candidates->first();
        }

        $minPrice = (int) $candidates->min('price');
        $maxPrice = (int) $candidates->max('price');
        $spread = max(1, $maxPrice - $minPrice + 1);

        $totalWeight = 0;
        $weights = [];

        foreach ($candidates as $skin) {
            $weight = max(1, (int) round((($maxPrice - $skin->price + 1) / $spread) * 100));
            $weights[] = ['skin' => $skin, 'weight' => $weight];
            $totalWeight += $weight;
        }

        $roll = random_int(1, $totalWeight);
        $current = 0;

        foreach ($weights as $entry) {
            $current += $entry['weight'];
            if ($roll <= $current) {
                return $entry['skin'];
            }
        }

        return $weights[0]['skin'];
    }
}
