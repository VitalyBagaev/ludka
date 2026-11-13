<template>
    <div class="container-fluid px-lg-4 ludo-page">
        <h1 class="ludo-page-title text-center">Апгрейд</h1>
        <p class="ludo-page-sub text-center">Выбери свой скин и цель — как на CaseBattle</p>

        <div v-if="!user" class="ludo-alert ludo-alert-warning text-center mx-auto" style="max-width: 480px">
            <a href="#" class="text-warning" @click.prevent="changePage('AuthPage')">Войти</a> для апгрейда
        </div>

        <div v-else>
            <div class="ludo-card p-4 mb-4 mx-auto" style="max-width: 900px">
                <div class="row align-items-center g-4">
                    <div class="col-md-4 text-center">
                        <div class="small text-secondary mb-2">Ваш скин</div>
                        <div class="ludo-upgrade-slot" :class="{ filled: selectedItem }">
                            <SkinItem v-if="selectedItem" :skin="selectedItem.skin" :skinImg="skinImg" :imgError="imgError" compact />
                            <span v-else class="text-secondary small">Выберите слева</span>
                        </div>
                        <div v-if="selectedItem" class="ludo-case-price mt-2">{{ selectedItem.skin.price }} ₽</div>
                    </div>

                    <div class="col-md-4 text-center">
                        <div
                            class="ludo-upgrade-spinner"
                            :class="{ 'is-spinning': isUpgrading, fast: isUpgrading && fastUpgrade }"
                        >
                            <div class="ludo-upgrade-spinner-pin" aria-hidden="true"></div>
                            <svg viewBox="0 0 100 100" class="ludo-upgrade-spinner-ring">
                                <circle cx="50" cy="50" r="42" fill="none" stroke="#3a1f1f" stroke-width="8" />
                                <circle
                                    cx="50" cy="50" r="42" fill="none" stroke="#22c55e" stroke-width="8"
                                    stroke-linecap="butt"
                                    transform="rotate(-90 50 50)"
                                    :stroke-dasharray="winZoneDash"
                                />
                            </svg>
                            <div
                                class="ludo-upgrade-spinner-arrow"
                                :class="{ animated: isUpgrading }"
                                :style="{ transform: `rotate(${arrowAngle}deg)` }"
                                aria-hidden="true"
                            ></div>
                            <span class="ludo-chance-value">{{ chance }}%</span>
                        </div>
                        <div class="small text-secondary mt-2">Шанс успеха</div>
                        <div class="ludo-upgrade-legend small text-secondary mt-1">
                            <span class="text-success">■</span> зона выигрыша
                        </div>
                        <label class="ludo-fast-toggle d-inline-flex mt-3">
                            <input v-model="fastUpgrade" type="checkbox" /> Быстрая прокрутка
                        </label>
                    </div>

                    <div class="col-md-4 text-center">
                        <div class="small text-secondary mb-2">Цель апгрейда</div>
                        <div class="ludo-upgrade-slot" :class="{ filled: targetSkin }">
                            <SkinItem v-if="targetSkin" :skin="targetSkin" :skinImg="skinImg" :imgError="imgError" compact />
                            <span v-else class="text-secondary small">Выберите справа</span>
                        </div>
                        <div v-if="targetSkin" class="ludo-case-price mt-2">{{ targetSkin.price }} ₽</div>
                    </div>
                </div>

                <div v-if="selectedItem && targetSkin" class="ludo-card p-3 mb-3">
                    <div class="ludo-section-label mb-2">Буст шанса за баланс</div>
                    <div class="small text-secondary mb-2">
                        Базовый шанс {{ baseChance }}% · максимум {{ maxChance }}% · мин. {{ minChance }}%
                        <span v-if="selectedItem && targetSkin"> · баланс добавляется к {{ formatPrice(selectedItem.skin.price) }}</span>
                        <span v-if="chanceBoostRoom <= 0"> · шанс уже максимальный</span>
                        <span v-else-if="maxBoost <= 0"> · <a href="#" class="text-warning" @click.prevent="changePage('UserPage')">пополните баланс</a></span>
                        <span v-else-if="boostLimitedByBalance"> · до {{ maxChance }}% нужно {{ formatPrice(fullBoostCost) }} (есть {{ formatPrice(balance) }})</span>
                    </div>
                    <div class="d-flex flex-wrap align-items-center gap-3">
                        <input
                            v-model.number="boostAmount"
                            type="range"
                            min="0"
                            :max="maxBoost"
                            :step="boostStep"
                            class="form-range flex-grow-1"
                            :disabled="maxBoost <= 0"
                            @input="updateChance"
                        />
                        <input
                            v-model.number="boostAmount"
                            type="number"
                            min="0"
                            :max="maxBoost"
                            :step="boostStep"
                            class="form-control form-control-sm"
                            style="width: 7rem"
                            :disabled="maxBoost <= 0"
                            @input="onBoostInput"
                        />
                        <span class="ludo-case-price">{{ boostAmount }} ₽</span>
                        <span class="small text-secondary">+{{ boostChanceBonus }}% к шансу</span>
                    </div>
                </div>

                <div v-if="result !== null" class="text-center mt-4">
                    <div class="ludo-alert" :class="result ? 'ludo-alert-success' : 'ludo-alert-danger'">
                        {{ result ? 'Успех! Вы получили целевой скин' : 'Не повезло — скин потерян' }}
                    </div>
                    <SkinItem v-if="wonSkin" :skin="wonSkin" :skinImg="skinImg" :imgError="imgError" class="d-inline-block mt-2" />
                </div>

                <button
                    class="btn btn-ludo-primary w-100 mt-4 ludo-pulse-glow"
                    :disabled="!selectedId || !targetSkinId || isUpgrading"
                    @click="upgrade"
                >
                    {{ isUpgrading ? 'Крутим...' : 'Апгрейд' }}
                </button>
            </div>

            <div class="row g-4">
                <div class="col-lg-5">
                    <div class="ludo-section-label mb-3">Мой инвентарь</div>
                    <div v-if="!inventory.length" class="ludo-alert ludo-alert-info">Инвентарь пуст</div>
                    <div v-else class="row g-2 ludo-upgrade-grid">
                        <div v-for="item in inventory" :key="item.id" class="col-4 col-md-3">
                            <div
                                class="ludo-skin-card"
                                :class="{ 'ludo-card-selected': selectedId === item.id }"
                                @click="selectSource(item)"
                            >
                                <img :src="skinImg(item.skin.image)" @error="imgError($event, 'skin')" />
                                <div class="small text-truncate">{{ item.skin.weapon }}</div>
                                <div class="ludo-case-price small">{{ item.skin.price }} ₽</div>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="col-lg-7">
                    <div class="d-flex flex-wrap justify-content-between align-items-center mb-3 gap-2">
                        <div class="ludo-section-label mb-0">Целевые скины</div>
                        <div class="ludo-filter-tabs ludo-filter-tabs-sm">
                            <button
                                class="ludo-filter-tab"
                                :class="{ active: targetFilter === 'all' }"
                                @click="setTargetFilter('all')"
                            >
                                Все
                            </button>
                            <button
                                v-for="rarity in rarityFilters"
                                :key="rarity"
                                class="ludo-filter-tab"
                                :class="{ active: targetFilter === rarity }"
                                @click="setTargetFilter(rarity)"
                            >
                                {{ rarityLabel[rarity] }}
                            </button>
                        </div>
                    </div>

                    <div v-if="!selectedItem" class="ludo-alert ludo-alert-info">Сначала выберите скин из инвентаря</div>
                    <div v-else-if="!filteredTargets.length" class="ludo-alert ludo-alert-warning">
                        Нет целей дороже {{ selectedItem.skin.price }} ₽ с шансом до {{ maxChance }}%
                    </div>
                    <div v-else class="row g-2 ludo-upgrade-grid">
                        <div v-for="skin in filteredTargets" :key="skin.id" class="col-4 col-md-3 col-xl-2">
                            <div
                                class="ludo-skin-card ludo-skin-card-target"
                                :class="[
                                    rarityClass[skin.rarity],
                                    { 'ludo-card-selected': targetSkinId === skin.id },
                                ]"
                                @click="selectTarget(skin)"
                            >
                                <img :src="skinImg(skin.image)" @error="imgError($event, 'skin')" />
                                <div class="small text-truncate fw-semibold">{{ skin.weapon }}</div>
                                <div class="small text-secondary text-truncate">{{ skin.name }}</div>
                                <div class="ludo-case-price small">{{ skin.price }} ₽</div>
                                <span class="ludo-rarity-badge ludo-rarity-badge-xs">{{ rarityLabel[skin.rarity] }}</span>
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
        baseChance() {
            if (!this.selectedItem || !this.targetSkin) {
                return 0;
            }
            return calcUpgradeBaseChance(this.selectedItem.skin.price, this.targetSkin.price);
        },
        maxChance() {
            return UPGRADE_MAX_CHANCE;
        },
        minChance() {
            return UPGRADE_MIN_CHANCE;
        },
        chanceBoostRoom() {
            return Math.max(0, UPGRADE_MAX_CHANCE - this.baseChance);
        },
        boostStep() {
            if (!this.selectedItem || !this.targetSkin) {
                return 50;
            }
            return calcUpgradeBoostStep(this.selectedItem.skin.price, this.targetSkin.price);
        },
        fullBoostCost() {
            if (!this.selectedItem || !this.targetSkin) {
                return 0;
            }
            return calcMaxUpgradeBoost(
                this.selectedItem.skin.price,
                this.targetSkin.price,
                Number.MAX_SAFE_INTEGER,
            );
        },
        maxBoostByChance() {
            return this.fullBoostCost;
        },
        maxBoost() {
            if (!this.userInfo || this.userInfo.balance === undefined) {
                return 0;
            }
            if (!this.selectedItem || !this.targetSkin) {
                return calcMaxUpgradeBoost(1, 2, this.balance);
            }
            return calcMaxUpgradeBoost(
                this.selectedItem.skin.price,
                this.targetSkin.price,
                this.balance,
            );
        },
        boostLimitedByBalance() {
            return this.chanceBoostRoom > 0 && this.maxBoost > 0 && this.maxBoost < this.maxBoostByChance;
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
            if (!this.selectedItem) {
                return [];
            }
            const minPrice = this.selectedItem.skin.price;
            let list = this.catalog.filter((skin) => {
                return skin.price > minPrice && isUpgradeTargetAllowed(minPrice, skin.price);
            });
            if (this.targetFilter !== 'all') {
                list = list.filter((skin) => skin.rarity === this.targetFilter);
            }
            return list;
        },
    },
    mounted() {
        if (this.user) {
            this.loadData();
        }
    },
    unmounted() {
        clearTimeout(this.spinTimer);
    },
    watch: {
        user(val) {
            if (val) {
                this.loadData();
            }
        },
        maxBoost() {
            this.clampBoost();
            this.updateChance();
        },
        'userInfo.balance'() {
            this.clampBoost();
            this.updateChance();
        },
    },
    methods: {
        formatPrice,
        loadData() {
            this.datasend('inventory').then((result) => {
                if (result) {
                    this.inventory = result;
                }
            });
            this.datasend('skins').then((result) => {
                if (result) {
                    this.catalog = result;
                }
            });
        },
        setTargetFilter(rarity) {
            sounds.click();
            this.targetFilter = rarity;
        },
        selectSource(item) {
            sounds.click();
            this.selectedId = item.id;
            this.selectedItem = item;
            this.result = null;
            this.wonSkin = null;

            if (this.targetSkin && this.targetSkin.price <= item.skin.price) {
                this.targetSkinId = null;
                this.targetSkin = null;
                this.chance = 0;
                this.boostAmount = 0;
            } else if (this.targetSkin && !isUpgradeTargetAllowed(item.skin.price, this.targetSkin.price)) {
                this.targetSkinId = null;
                this.targetSkin = null;
                this.chance = 0;
                this.boostAmount = 0;
            } else {
                this.clampBoost();
                this.updateChance();
            }
        },
        selectTarget(skin) {
            if (!this.selectedItem || skin.price <= this.selectedItem.skin.price) {
                return;
            }
            if (!isUpgradeTargetAllowed(this.selectedItem.skin.price, skin.price)) {
                return;
            }
            sounds.click();
            this.targetSkinId = skin.id;
            this.targetSkin = skin;
            this.result = null;
            this.wonSkin = null;
            this.clampBoost();
            this.updateChance();
        },
        clampBoost() {
            const cap = Math.max(0, Number(this.maxBoost) || 0);
            if (this.boostAmount > cap) {
                this.boostAmount = cap;
            }
            if (this.boostAmount < 0 || Number.isNaN(this.boostAmount)) {
                this.boostAmount = 0;
            }
        },
        onBoostInput() {
            this.clampBoost();
            this.updateChance();
        },
        updateChance() {
            if (!this.selectedItem || !this.targetSkin) {
                this.chance = 0;
                return;
            }
            this.chance = calcUpgradeChance(this.selectedItem.skin.price, this.targetSkin.price, this.boostAmount);
        },
        finishUpgrade(result) {
            this.isUpgrading = false;
            if (!result) {
                return;
            }
            if (result.message) {
                alert(result.message);
                return;
            }
            this.result = result.success;
            if (result.success) {
                this.wonSkin = result.skin;
                sounds.win();
                fireUpgradeConfetti(true);
            } else {
                sounds.lose();
            }
            this.selectedId = null;
            this.selectedItem = null;
            this.targetSkinId = null;
            this.targetSkin = null;
            this.boostAmount = 0;
            this.chance = 0;
            if (this.getUser) {
                this.getUser();
            }
            this.loadData();
        },
        upgrade() {
            if (!this.selectedId || !this.targetSkinId || this.isUpgrading) {
                return;
            }
            sounds.upgrade();
            this.isUpgrading = true;
            this.result = null;
            this.wonSkin = null;
            this.pendingUpgradeResult = null;

            this.datasendJson('upgrade', 'POST', {
                inventory_item_id: this.selectedId,
                target_skin_id: this.targetSkinId,
                boost_amount: this.boostAmount,
            }).then((result) => {
                if (!result) {
                    this.isUpgrading = false;
                    return;
                }
                if (result.message) {
                    this.isUpgrading = false;
                    alert(result.message);
                    return;
                }

                this.pendingUpgradeResult = result;
                const startAngle = this.arrowAngle % 360;
                const targetAngle = calcUpgradeArrowAngle(this.chance, !!result.success);
                this.arrowAngle = this.arrowAngle - startAngle + targetAngle;

                clearTimeout(this.spinTimer);
                this.spinTimer = setTimeout(() => {
                    this.finishUpgrade(this.pendingUpgradeResult);
                    this.pendingUpgradeResult = null;
                }, this.fastUpgrade ? FAST_SPIN_MS : SPIN_MS);
            });
        },
    },
};
</script>
