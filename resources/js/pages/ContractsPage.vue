<template>
    <div class="container ludo-page">
        <h1 class="ludo-page-title text-center">Контракты</h1>
        <p class="ludo-page-sub text-center">Обменяй {{ minSkins }}–{{ maxSkins }} скинов на один случайный</p>

        <div v-if="!user" class="ludo-alert ludo-alert-warning text-center">Нужно войти</div>

        <div v-else class="mx-auto" style="max-width: 800px">
            <div class="ludo-card p-4 mb-4" :class="{ 'ludo-contract-burn': burning }">
                <div class="d-flex justify-content-between mb-3">
                    <span class="text-secondary">Выбрано: {{ selectedIds.length }}/{{ maxSkins }}</span>
                    <span v-if="totalValue" class="ludo-case-price">Сумма: {{ totalValue }} ₽</span>
                    <button v-if="selectedIds.length" class="btn btn-sm btn-ludo-outline" @click="clearAll">Очистить</button>
                </div>

                <div class="ludo-contract-slots mb-3">
                    <div v-for="i in maxSkins" :key="i" class="ludo-contract-slot" :class="{ filled: selectedItems[i - 1] }">
                        <img v-if="selectedItems[i - 1]" :src="skinImg(selectedItems[i - 1].skin.image)" @error="imgError($event, 'skin')" />
                    </div>
                </div>

                <div v-if="selectedItems.length" class="ludo-card p-3 mb-3" style="background: rgba(255,255,255,0.02)">
                    <div class="d-flex justify-content-between align-items-center mb-2">
                        <div class="ludo-section-label mb-0">Выбранные предметы</div>
                        <div class="small text-secondary">{{ selectedItems.length }} шт.</div>
                    </div>
                    <div class="d-flex flex-column gap-1">
                        <div v-for="item in selectedItems" :key="'s-' + item.id" class="d-flex justify-content-between align-items-center">
                            <div class="small text-truncate" style="max-width: 70%">
                                <span class="text-secondary">{{ item.skin.weapon }}</span>
                                <span class="mx-1 text-secondary">|</span>
                                <span class="fw-semibold">{{ item.skin.name }}</span>
                            </div>
                            <div class="ludo-case-price small">{{ item.skin.price }} ₽</div>
                        </div>
                    </div>
                </div>

                <div v-if="expectedRange" class="ludo-alert ludo-alert-info text-center mb-3">
                    Сумма контракта: <strong>{{ formatPrice(expectedRange.total) }}</strong><br />
                    Диапазон: <strong>{{ formatPrice(expectedRange.min) }} – {{ formatPrice(expectedRange.max) }}</strong>
                    <span class="small text-secondary"> (×{{ expectedRange.multiplier }})</span>
                    <div class="small text-secondary mt-1">Как на CaseBattle: результат около суммы контракта · дешёвые выпадают чаще</div>
                </div>

                <div v-if="selectedItems.length >= minSkins" class="mb-3">
                    <div class="ludo-section-label mb-2">Буст диапазона за баланс</div>
                    <div class="d-flex flex-wrap align-items-center gap-3">
                        <input
                            v-model.number="boostAmount"
                            type="range"
                            min="0"
                            :max="maxBoost"
                            step="100"
                            class="form-range flex-grow-1"
                            :disabled="maxBoost <= 0"
                            @input="clampBoost"
                        />
                        <span class="ludo-case-price">{{ boostAmount }} ₽</span>
                    </div>
                </div>

                <div v-if="message" class="ludo-alert ludo-alert-danger mb-3">{{ message }}</div>

                <button class="btn btn-ludo-primary w-100 ludo-pulse-glow" :disabled="selectedIds.length < minSkins || burning" @click="execute">
                    {{ burning ? 'Подписываем...' : 'Подписать контракт' }}
                </button>
            </div>

            <div v-if="wonSkin" class="ludo-win-card text-center mb-4 ludo-win-shake">
                <div class="ludo-section-label text-success mb-2">Контракт выполнен!</div>
                <SkinItem :skin="wonSkin" :skinImg="skinImg" :imgError="imgError" />
            </div>

            <div class="ludo-section-label mb-3">Инвентарь</div>
            <div class="row g-2">
                <div v-for="item in inventory" :key="item.id" class="col-3 col-md-2">
                    <div
                        class="ludo-skin-card"
                        :class="{ 'ludo-card-selected': selectedIds.includes(item.id), 'ludo-card-dimmed': selectedIds.includes(item.id) }"
                        @click="toggle(item.id)"
                    >
                        <img :src="skinImg(item.skin.image)" style="height: 50px" @error="imgError($event, 'skin')" />
                        <div class="small fw-semibold mt-1 text-truncate">{{ item.skin.weapon }}</div>
                        <div class="small text-secondary text-truncate">{{ item.skin.name }}</div>
                        <div class="ludo-case-price small">{{ item.skin.price }} ₽</div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</template>

<script>
import SkinItem from '../components/SkinItem.vue';
import { fireWinConfetti } from '../helpers/confetti.js';
import { calcContractRange, calcMaxContractBoost, formatPrice } from '../helpers/game.js';
import { sounds } from '../helpers/sounds.js';

export default {
    name: 'ContractsPage',
    components: { SkinItem },
    props: ['datasend', 'datasendJson', 'user', 'skinImg', 'imgError', 'rarityClass', 'rarityLabel', 'userInfo'],
    data() {
        return {
            inventory: [],
            selectedIds: [],
            minSkins: 2,
            maxSkins: 10,
            burning: false,
            wonSkin: null,
            message: null,
            boostAmount: 0,
        };
    },
    computed: {
        selectedItems() {
            return this.inventory.filter((item) => this.selectedIds.includes(item.id));
        },
        totalValue() {
            return this.selectedItems.reduce(function (sum, item) {
                return sum + item.skin.price;
            }, 0);
        },
        maxBoost() {
            if (!this.userInfo || this.userInfo.balance === undefined) {
                return 0;
            }
            return calcMaxContractBoost(this.userInfo.balance);
        },
        expectedRange() {
            if (this.selectedItems.length < this.minSkins) {
                return null;
            }
            return calcContractRange(this.totalValue, this.selectedItems.length, this.boostAmount);
        },
    },
    mounted() {
        if (this.user) {
            this.loadInventory();
        }
    },
    watch: {
        user(val) {
            if (val) {
                this.loadInventory();
            }
        },
    },
    methods: {
        formatPrice,
        loadInventory() {
            this.datasend('inventory').then((result) => {
                if (result) {
                    this.inventory = result;
                }
            });
        },
        toggle(id) {
            sounds.click();
            const index = this.selectedIds.indexOf(id);
            if (index >= 0) {
                this.selectedIds.splice(index, 1);
            } else if (this.selectedIds.length < this.maxSkins) {
                this.selectedIds.push(id);
            }
            this.clampBoost();
        },
        clearAll() {
            this.selectedIds = [];
        },
        clampBoost() {
            if (this.boostAmount > this.maxBoost) {
                this.boostAmount = this.maxBoost;
            }
        },
        execute() {
            sounds.contract();
            this.burning = true;
            this.wonSkin = null;
            this.message = null;
            this.datasendJson('contract', 'POST', {
                inventory_item_ids: this.selectedIds,
                boost_amount: this.boostAmount,
            }).then((result) => {
                setTimeout(() => {
                    this.burning = false;
                    if (!result) {
                        return;
                    }
                    if (result.message) {
                        this.message = result.message;
                        sounds.lose();
                        return;
                    }
                    this.wonSkin = result.skin;
                    this.selectedIds = [];
                    this.boostAmount = 0;
                    sounds.win();
                    fireWinConfetti();
                    this.loadInventory();
                }, 1200);
            });
        },
    },
};
</script>
