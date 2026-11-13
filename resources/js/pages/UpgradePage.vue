<template>
    <div class="container-fluid px-lg-4 ludo-page">
        <div class="ludo-hero ludo-upgrade-hero mb-4">
            <div class="row align-items-center g-3">
                <div class="col-lg-8">
                    <div class="d-inline-flex align-items-center gap-2 px-3 py-1 rounded-pill mb-2" style="background: rgba(136, 71, 255, 0.15); border: 1px solid rgba(136, 71, 255, 0.35); font-size: 12px; color: #b794f4;">
                        <span>⚡</span>
                        <span class="fw-semibold">Прокачка скинов · Множитель до x100</span>
                    </div>
                    <h1 class="mb-2">Апгрейд <em>скинов</em></h1>
                    <p class="mb-0">Выбери свой предмет, подбери более дорогую цель и испытай удачу. Можно усилить шанс балансом!</p>
                </div>
                <div class="col-lg-4 text-lg-end">
                    <span v-if="user" class="ludo-stat-pill">Баланс: <strong class="text-warning">{{ balance }} ₽</strong></span>
                </div>
            </div>
        </div>

        <div v-if="!user" class="ludo-alert ludo-alert-warning text-center mx-auto" style="max-width: 480px">
            <a href="#" class="text-warning" @click.prevent="changePage('AuthPage')">Войти</a> для использования апгрейда
        </div>

        <div v-else>
            <div class="ludo-card p-4 mb-4 mx-auto" style="max-width: 960px; border: 1px solid rgba(136, 71, 255, 0.25);">
                <div class="row align-items-center g-4">
                    <div class="col-md-4 text-center">
                        <div class="ludo-section-label mb-2">Ваш скин</div>
                        <div class="ludo-upgrade-slot mx-auto" :class="{ filled: selectedItem }">
                            <SkinItem v-if="selectedItem" :skin="selectedItem.skin" :skinImg="skinImg" :imgError="imgError" compact />
                            <span v-else class="text-secondary small">Выберите из инвентаря</span>
                        </div>
                        <div v-if="selectedItem" class="ludo-case-price mt-2">{{ selectedItem.skin.price }} ₽</div>
                    </div>

                    <div class="col-md-4 text-center">
                        <div class="ludo-upgrade-spinner" :class="{ 'is-spinning': isUpgrading, fast: isUpgrading && fastUpgrade }">
                            <div class="ludo-upgrade-spinner-pin" aria-hidden="true"></div>
                            <svg viewBox="0 0 100 100" class="ludo-upgrade-spinner-ring">
                                <circle cx="50" cy="50" r="42" fill="none" stroke="#3a1f1f" stroke-width="8" />
                                <circle cx="50" cy="50" r="42" fill="none" stroke="#22c55e" stroke-width="8" stroke-linecap="butt" transform="rotate(-90 50 50)" :stroke-dasharray="winZoneDash" />
                            </svg>
                            <div class="ludo-upgrade-spinner-arrow" :class="{ animated: isUpgrading }" :style="{ transform: `rotate(${arrowAngle}deg)` }" aria-hidden="true"></div>
                            <span class="ludo-chance-value">{{ chance }}%</span>
                        </div>
                        <div class="mt-2 d-flex justify-content-center align-items-center gap-2">
                            <span class="small text-secondary">Шанс успеха</span>
                            <span v-if="multiplierLabel" class="badge bg-dark border border-warning-subtle text-warning">{{ multiplierLabel }}</span>
                        </div>
                        <label class="ludo-fast-toggle d-inline-flex mt-3">
                            <input v-model="fastUpgrade" type="checkbox" /> Быстрая прокрутка
                        </label>
                    </div>

                    <div class="col-md-4 text-center">
                        <div class="ludo-section-label mb-2">Цель апгрейда</div>
                        <div class="ludo-upgrade-slot mx-auto" :class="{ filled: targetSkin }">
                            <SkinItem v-if="targetSkin" :skin="targetSkin" :skinImg="skinImg" :imgError="imgError" compact />
                            <span v-else class="text-secondary small">Выберите цель ниже</span>
                        </div>
                        <div v-if="targetSkin" class="ludo-case-price mt-2">{{ targetSkin.price }} ₽</div>
                    </div>
                </div>
                <div v-if="selectedItem" class="mt-4 pt-3 border-top border-secondary-subtle text-center">
                    <div class="small text-secondary mb-2">Быстрый подбор цели по множителю:</div>
                    <div class="d-flex flex-wrap justify-content-center gap-2">
                        <button v-for="mult in quickMultipliers" :key="mult" type="button" class="btn btn-sm btn-ludo-outline" @click="pickTargetByMultiplier(mult)">
                            x{{ mult }} (~{{ Math.round(selectedItem.skin.price * mult) }} ₽)
                        </button>
                    </div>
                </div>

                <div v-if="selectedItem && targetSkin" class="ludo-card p-3 mt-3" style="background: rgba(255,255,255,0.02);">
                    <div class="ludo-section-label mb-2">Буст шанса за баланс</div>
                    <div class="small text-secondary mb-2">
                        Базовый шанс: <strong>{{ baseChance }}%</strong> · Максимум: <strong>{{ maxChance }}%</strong>
                        <span v-if="boostChanceBonus > 0" class="text-success ms-1">(+{{ boostChanceBonus }}% от буста)</span>
                    </div>
                    <div class="d-flex flex-wrap align-items-center gap-3">
                        <input v-model.number="boostAmount" type="range" min="0" :max="maxBoost" :step="boostStep" class="form-range flex-grow-1" :disabled="maxBoost <= 0" @input="updateChance" />
                        <input v-model.number="boostAmount" type="number" min="0" :max="maxBoost" :step="boostStep" class="form-control form-control-sm ludo-form-control" style="width: 7rem" :disabled="maxBoost <= 0" @input="onBoostInput" />
                        <span class="ludo-case-price">{{ boostAmount }} ₽</span>
                    </div>
                </div>

                <div v-if="result !== null" class="text-center mt-4">
                    <div class="ludo-alert" :class="result ? 'ludo-alert-success' : 'ludo-alert-danger'">
                        {{ result ? 'Успех! Вы получили целевой скин' : 'Не повезло — скин потерян' }}
                    </div>
                    <SkinItem v-if="wonSkin" :skin="wonSkin" :skinImg="skinImg" :imgError="imgError" class="d-inline-block mt-2" />
                </div>

                <button class="btn btn-ludo-primary btn-lg w-100 mt-4 ludo-pulse-glow" :disabled="!selectedId || !targetSkinId || isUpgrading" @click="upgrade">
                    {{ isUpgrading ? 'Крутим...' : '⚡ Сделать апгрейд' }}
                </button>
            </div>
            <div class="row g-4">
                <div class="col-lg-5">
                    <div class="ludo-section-label mb-3">Мой инвентарь ({{ inventory.length }})</div>
                    <div v-if="!inventory.length" class="ludo-alert ludo-alert-info">Инвентарь пуст — откройте кейс на главной</div>
                    <div v-else class="row g-2 ludo-upgrade-grid">
                        <div v-for="item in inventory" :key="item.id" class="col-4 col-md-3">
                            <div class="ludo-skin-card" :class="{ 'ludo-card-selected': selectedId === item.id }" @click="selectSource(item)">
                                <img :src="skinImg(item.skin.image)" @error="imgError($event, 'skin')" />
                                <div class="small text-truncate">{{ item.skin.weapon }}</div>
                                <div class="ludo-case-price small">{{ item.skin.price }} ₽</div>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="col-lg-7">
                    <div class="d-flex flex-wrap justify-content-between align-items-center mb-2 gap-2">
                        <div class="ludo-section-label mb-0">Целевые скины ({{ filteredTargets.length }})</div>
                        <div class="d-flex gap-2">
                            <input v-model.trim="targetSearch" type="text" class="form-control form-control-sm ludo-form-control" placeholder="Поиск цели..." style="width: 160px;" />
                            <select v-model="targetSort" class="form-select form-select-sm ludo-form-control" style="width: auto;">
                                <option value="price_asc">Дешевле → Дороже</option>
                                <option value="price_desc">Дороже → Дешевле</option>
                            </select>
                        </div>
                    </div>

                    <div class="ludo-filter-tabs ludo-filter-tabs-sm mb-3">
                        <button class="ludo-filter-tab" :class="{ active: targetFilter === 'all' }" @click="setTargetFilter('all')">Все</button>
                        <button v-for="rarity in rarityFilters" :key="rarity" class="ludo-filter-tab" :class="{ active: targetFilter === rarity }" @click="setTargetFilter(rarity)">
                            {{ rarityLabel[rarity] }}
                        </button>
                    </div>

                    <div v-if="!selectedItem" class="ludo-alert ludo-alert-info">Сначала выберите скин из вашего инвентаря слева</div>
                    <div v-else-if="!filteredTargets.length" class="ludo-alert ludo-alert-warning">
                        Нет целей дороже {{ selectedItem.skin.price }} ₽ по заданным фильтрам
                    </div>
                    <div v-else class="row g-2 ludo-upgrade-grid">
                        <div v-for="skin in filteredTargets" :key="skin.id" class="col-4 col-md-3 col-xl-2">
                            <div class="ludo-skin-card ludo-skin-card-target" :class="[rarityClass[skin.rarity], { 'ludo-card-selected': targetSkinId === skin.id }]" @click="selectTarget(skin)">
                                <img :src="skinImg(skin.image)" @error="imgError($event, 'skin')" />
                                <div class="small text-truncate fw-semibold">{{ skin.weapon }}</div>
                                <div class="small text-secondary text-truncate">{{ skin.name }}</div>
                                <div class="ludo-case-price small">{{ skin.price }} ₽</div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</template>
<script>
import SkinItem from '../components/SkinItem.vue';
import { fireUpgradeConfetti } from '../helpers/confetti.js';
import {
    calcUpgradeChance,
    calcMaxUpgradeBoost,
    calcUpgradeBaseChance,
    calcUpgradeBoostStep,
    formatPrice,
    isUpgradeTargetAllowed,
    UPGRADE_MAX_CHANCE,
    UPGRADE_MIN_CHANCE,
} from '../helpers/game.js';
import { rarityOrder } from '../helpers/images-meta.js';
import { calcUpgradeArrowAngle } from '../helpers/upgradeSpin.js';
import { sounds } from '../helpers/sounds.js';

const SPIN_MS = 4500;
const FAST_SPIN_MS = 600;

export default {
    name: 'UpgradePage',
    components: { SkinItem },
    props: ['datasend', 'datasendJson', 'user', 'userInfo', 'getUser', 'changePage', 'skinImg', 'imgError', 'rarityClass', 'rarityLabel'],
    data() {
        return {
            inventory: [],
            catalog: [],
            selectedId: null,
            selectedItem: null,
            targetSkinId: null,
            targetSkin: null,
            targetFilter: 'all',
            targetSearch: '',
            targetSort: 'price_asc',
            quickMultipliers: [1.5, 2, 3, 5, 10],
            boostAmount: 0,
            chance: 0,
            fastUpgrade: false,
            isUpgrading: false,
            result: null,
            wonSkin: null,
            rarityFilters: rarityOrder,
            spinTimer: null,
            arrowAngle: 0,
            pendingUpgradeResult: null,
        };
    },
    computed: {
        balance() {
            return Number(this.userInfo?.balance) || 0;
        },
        multiplierLabel() {
            if (!this.selectedItem || !this.targetSkin) return '';
            const mult = (this.targetSkin.price / this.selectedItem.skin.price).toFixed(2);
            return 'x' + mult;
        },
        baseChance() {
            if (!this.selectedItem || !this.targetSkin) return 0;
            return calcUpgradeBaseChance(this.selectedItem.skin.price, this.targetSkin.price);
        },
        maxChance() { return UPGRADE_MAX_CHANCE; },
        minChance() { return UPGRADE_MIN_CHANCE; },
        boostStep() {
            if (!this.selectedItem || !this.targetSkin) return 50;
            return calcUpgradeBoostStep(this.selectedItem.skin.price, this.targetSkin.price);
        },
        maxBoost() {
            if (!this.userInfo || this.userInfo.balance === undefined) return 0;
            if (!this.selectedItem || !this.targetSkin) return calcMaxUpgradeBoost(1, 2, this.balance);
            return calcMaxUpgradeBoost(this.selectedItem.skin.price, this.targetSkin.price, this.balance);
        },
        boostChanceBonus() {
            return Math.max(0, this.chance - this.baseChance);
        },
        winZoneDash() {
            const circumference = 2 * Math.PI * 42;
            const winLength = (this.chance / 100) * circumference;
            return `${winLength} ${circumference}`;
        },
        filteredTargets() {
            if (!this.selectedItem) return [];
            const minPrice = this.selectedItem.skin.price;
            const q = this.targetSearch.toLowerCase();
            let list = this.catalog.filter((skin) => skin.price > minPrice && isUpgradeTargetAllowed(minPrice, skin.price));
            if (this.targetFilter !== 'all') list = list.filter((skin) => skin.rarity === this.targetFilter);
            if (q) list = list.filter((s) => s.name.toLowerCase().includes(q) || s.weapon.toLowerCase().includes(q));
            if (this.targetSort === 'price_desc') return [...list].sort((a, b) => b.price - a.price);
            return [...list].sort((a, b) => a.price - b.price);
        },
    },
    mounted() {
        if (this.user) this.loadData();
    },
    unmounted() {
        clearTimeout(this.spinTimer);
    },
    watch: {
        user(val) { if (val) this.loadData(); },
        maxBoost() { this.clampBoost(); this.updateChance(); },
        'userInfo.balance'() { this.clampBoost(); this.updateChance(); },
    },
    methods: {
        formatPrice,
        loadData() {
            this.datasend('inventory').then((res) => { if (res) this.inventory = res; });
            this.datasend('skins').then((res) => { if (res) this.catalog = res; });
        },
        setTargetFilter(rarity) { sounds.click(); this.targetFilter = rarity; },
        pickTargetByMultiplier(mult) {
            if (!this.selectedItem) return;
            const minPrice = this.selectedItem.skin.price;
            const allowed = this.catalog.filter((s) => s.price > minPrice && isUpgradeTargetAllowed(minPrice, s.price));
            if (!allowed.length) return;
            const desired = minPrice * mult;
            const best = allowed.reduce((prev, curr) => Math.abs(curr.price - desired) < Math.abs(prev.price - desired) ? curr : prev);
            this.targetFilter = 'all';
            this.targetSearch = '';
            this.selectTarget(best);
        },
        selectSource(item) {
            sounds.click();
            this.selectedId = item.id;
            this.selectedItem = item;
            this.result = null;
            this.wonSkin = null;
            if (this.targetSkin && (this.targetSkin.price <= item.skin.price || !isUpgradeTargetAllowed(item.skin.price, this.targetSkin.price))) {
                this.targetSkinId = null; this.targetSkin = null; this.chance = 0; this.boostAmount = 0;
            } else {
                this.clampBoost(); this.updateChance();
            }
        },
        selectTarget(skin) {
            if (!this.selectedItem || skin.price <= this.selectedItem.skin.price || !isUpgradeTargetAllowed(this.selectedItem.skin.price, skin.price)) return;
            sounds.click();
            this.targetSkinId = skin.id; this.targetSkin = skin; this.result = null; this.wonSkin = null;
            this.clampBoost(); this.updateChance();
        },
        clampBoost() {
            const cap = Math.max(0, Number(this.maxBoost) || 0);
            if (this.boostAmount > cap) this.boostAmount = cap;
            if (this.boostAmount < 0 || Number.isNaN(this.boostAmount)) this.boostAmount = 0;
        },
        onBoostInput() { this.clampBoost(); this.updateChance(); },
        updateChance() {
            if (!this.selectedItem || !this.targetSkin) { this.chance = 0; return; }
            this.chance = calcUpgradeChance(this.selectedItem.skin.price, this.targetSkin.price, this.boostAmount);
        },
        finishUpgrade(result) {
            this.isUpgrading = false;
            if (!result) return;
            if (result.message) { alert(result.message); return; }
            this.result = result.success;
            if (result.success) { this.wonSkin = result.skin; sounds.win(); fireUpgradeConfetti(true); }
            else { sounds.lose(); }
            this.selectedId = null; this.selectedItem = null; this.targetSkinId = null; this.targetSkin = null; this.boostAmount = 0; this.chance = 0;
            if (this.getUser) this.getUser();
            this.loadData();
        },
        upgrade() {
            if (!this.selectedId || !this.targetSkinId || this.isUpgrading) return;
            sounds.upgrade();
            this.isUpgrading = true; this.result = null; this.wonSkin = null; this.pendingUpgradeResult = null;
            this.datasendJson('upgrade', 'POST', { inventory_item_id: this.selectedId, target_skin_id: this.targetSkinId, boost_amount: this.boostAmount }).then((result) => {
                if (!result) { this.isUpgrading = false; return; }
                if (result.message) { this.isUpgrading = false; alert(result.message); return; }
                this.pendingUpgradeResult = result;
                const startAngle = this.arrowAngle % 360;
                const targetAngle = calcUpgradeArrowAngle(this.chance, !!result.success);
                this.arrowAngle = this.arrowAngle - startAngle + targetAngle;
                clearTimeout(this.spinTimer);
                this.spinTimer = setTimeout(() => { this.finishUpgrade(this.pendingUpgradeResult); this.pendingUpgradeResult = null; }, this.fastUpgrade ? FAST_SPIN_MS : SPIN_MS);
            });
        },
    },
};
</script>

<style scoped>
.ludo-upgrade-hero {
    background: radial-gradient(circle at 85% 20%, rgba(136, 71, 255, 0.16), transparent 55%),
                linear-gradient(135deg, rgba(24, 24, 34, 0.95), rgba(16, 16, 24, 0.98));
    border: 1px solid rgba(136, 71, 255, 0.25);
}
</style>
