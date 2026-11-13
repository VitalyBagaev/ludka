export const rarityColors = {
    consumer: '#b0c3d9',
    industrial: '#5e98d9',
    'mil-spec': '#4b69ff',
    restricted: '#8847ff',
    classified: '#d32ce6',
    covert: '#eb4b4b',
    gold: '#f0b429',
};

export function formatPrice(value) {
    return Number(value).toLocaleString('ru-RU') + ' ₽';
}

export const UPGRADE_MIN_CHANCE = 1;
export const UPGRADE_MAX_CHANCE = 75;
export const UPGRADE_BASE_MAX = 75;
export const UPGRADE_HOUSE_EDGE = 0.97;

export const CONTRACT_MIN_ITEMS = 2;
export const CONTRACT_MAX_ITEMS = 10;
export const CONTRACT_BASE_MULTIPLIER = 1.25;

export function isUpgradeTargetAllowed(skinPrice, targetPrice) {
    const source = Number(skinPrice);
    const target = Number(targetPrice);
    if (!source || !target || target <= source) {
        return false;
    }

    return (source / target) * 100 <= UPGRADE_BASE_MAX;
}

export function calcUpgradeEffectiveValue(skinPrice, boostAmount = 0) {
    return Math.max(0, Number(skinPrice) + Number(boostAmount));
}

export function calcUpgradeRawChance(skinPrice, targetPrice, boostAmount = 0) {
    if (!skinPrice || !targetPrice) {
        return 0;
    }

    const effectiveValue = calcUpgradeEffectiveValue(skinPrice, boostAmount);

    return (effectiveValue / targetPrice) * 100 * UPGRADE_HOUSE_EDGE;
}

export function calcUpgradeBaseChance(skinPrice, targetPrice) {
    return calcUpgradeChance(skinPrice, targetPrice, 0);
}

export function calcUpgradeChance(skinPrice, targetPrice, boostAmount = 0) {
    if (!skinPrice || !targetPrice) {
        return 0;
    }
    if (!isUpgradeTargetAllowed(skinPrice, targetPrice)) {
        return 0;
    }

    const chance = Math.round(calcUpgradeRawChance(skinPrice, targetPrice, boostAmount));

    return Math.min(UPGRADE_MAX_CHANCE, Math.max(UPGRADE_MIN_CHANCE, chance));
}

export function calcMaxUpgradeBoost(skinPrice, targetPrice, balance = 0) {
    const normalizedBalance = Number(balance);
    if (!skinPrice || !targetPrice || !Number.isFinite(normalizedBalance) || normalizedBalance <= 0) {
        return 0;
    }
    if (!isUpgradeTargetAllowed(skinPrice, targetPrice)) {
        return 0;
    }

    const targetEffective = Math.round((targetPrice * (UPGRADE_MAX_CHANCE / 100)) / UPGRADE_HOUSE_EDGE);
    const maxByChance = Math.max(0, targetEffective - Number(skinPrice));
    const maxByTarget = Math.round(Number(targetPrice) * 0.5);

    return Math.min(normalizedBalance, maxByChance, maxByTarget);
}

export function calcUpgradeBoostStep(skinPrice, targetPrice) {
    const target = Number(targetPrice);
    if (target >= 100000) {
        return 1000;
    }
    if (target >= 20000) {
        return 500;
    }
    if (target >= 5000) {
        return 100;
    }

    return 50;
}

export function calcContractRange(totalValue, itemCount, boostAmount = 0) {
    const boostFactor = Math.min(1, boostAmount / 10000);
    const multiplier = CONTRACT_BASE_MULTIPLIER + boostFactor * 1.25;
    const min = Math.max(1, Math.round(totalValue / multiplier));
    const max = Math.max(min, Math.round(totalValue * multiplier));

    return {
        min,
        max,
        avg: Math.round(totalValue / Math.max(1, itemCount)),
        total: totalValue,
        multiplier: Math.round(multiplier * 100) / 100,
    };
}

export function calcMaxContractBoost(balance = 0) {
    if (balance <= 0) {
        return 0;
    }

    return Math.min(10000, balance);
}

export const categoryOrder = ['serial', 'premium', 'image', 'branded', 'knife', 'limited', 'bonus', 'valve'];

export const categoryLabels = {
    all: 'Все',
    serial: 'Серийные',
    premium: 'Премиум',
    image: 'Имиджевые',
    branded: 'Брендовые',
    knife: 'Ножи',
    valve: 'Valve CS2',
    limited: 'Limited',
    bonus: 'Бонусные',
};
