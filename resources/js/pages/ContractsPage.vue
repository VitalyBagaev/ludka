<template>
    <div class="container-fluid px-lg-4 ludo-page">
        <div class="ludo-hero ludo-contracts-hero mb-4">
            <div class="row align-items-center g-3">
                <div class="col-lg-8">
                    <div class="d-inline-flex align-items-center gap-2 px-3 py-1 rounded-pill mb-2" style="background: rgba(255, 107, 0, 0.14); border: 1px solid rgba(255, 107, 0, 0.35); font-size: 12px; color: #ff8c42;">
                        <span>📜</span>
                        <span class="fw-semibold">Крафт скинов · От 2 до 10 предметов</span>
                    </div>
                    <h1 class="mb-2">Контракты <em>обмена</em></h1>
                    <p class="mb-0">Собери от 2 до 10 ненужных скинов и получи один гарантированный предмет. Усиль диапазон балансом!</p>
                </div>
                <div class="col-lg-4 text-lg-end">
                    <span v-if="user" class="ludo-stat-pill">Баланс: <strong class="text-warning">{{ userInfo?.balance ?? 0 }} ₽</strong></span>
                </div>
            </div>
        </div>

        <div v-if="!user" class="ludo-alert ludo-alert-warning text-center mx-auto" style="max-width: 480px">
            Нужно <a href="#" class="text-warning" @click.prevent="changePage('AuthPage')">войти</a> в аккаунт
        </div>

        <div v-else class="mx-auto" style="max-width: 900px">
            <div class="ludo-card p-4 mb-4" :class="{ 'ludo-contract-burn': burning }" style="border: 1px solid rgba(255, 107, 0, 0.25);">
                <div class="d-flex flex-wrap justify-content-between align-items-center gap-2 mb-3">
                    <div class="d-flex align-items-center gap-2">
                        <span class="ludo-section-label mb-0">Слоты: {{ selectedIds.length }}/{{ maxSkins }}</span>
                        <span v-if="totalValue" class="ludo-stat-pill">Сумма: <strong class="text-warning">{{ totalValue }} ₽</strong></span>
                    </div>
                    <div class="d-flex flex-wrap gap-2">
                        <button type="button" class="btn btn-sm btn-ludo-outline" :disabled="inventory.length < 2" @click="autoFillCheapest">
                            ⚡ 10 дешёвых
                        </button>
                        <button type="button" class="btn btn-sm btn-ludo-outline" :disabled="inventory.length < 2" @click="autoFillRandom">
                            🎲 5 случайных
                        </button>
                        <button v-if="selectedIds.length" type="button" class="btn btn-sm btn-outline-danger" @click="clearAll">
                            Очистить
                        </button>
                    </div>
                </div>

                <div class="ludo-contract-slots mb-3">
                    <div v-for="i in maxSkins" :key="i" class="ludo-contract-slot" :class="{ filled: selectedItems[i - 1] }" :title="selectedItems[i - 1] ? 'Нажмите, чтобы убрать' : 'Пустой слот'" style="cursor: pointer;" @click="selectedItems[i - 1] && toggle(selectedItems[i - 1].id)">
                        <img v-if="selectedItems[i - 1]" :src="skinImg(selectedItems[i - 1].skin.image)" @error="imgError($event, 'skin')" />
                        <span v-else class="small text-secondary">{{ i }}</span>
                    </div>
                </div>
                <div v-if="expectedRange" class="ludo-alert ludo-alert-info text-center mb-3">
                    <div class="mb-1">
                        Ожидаемый выигрыш: <strong class="text-warning fs-5">{{ formatPrice(expectedRange.min) }} — {{ formatPrice(expectedRange.max) }}</strong>
                        <span class="small text-secondary ms-1">(×{{ expectedRange.multiplier }})</span>
                    </div>
                    <div class="small text-secondary">Нажмите на скин в слоте выше, чтобы вернуть его в инвентарь</div>
                </div>

                <div v-if="selectedItems.length >= minSkins" class="ludo-card p-3 mb-3" style="background: rgba(255,255,255,0.02);">
                    <div class="d-flex justify-content-between align-items-center mb-2">
                        <div class="ludo-section-label mb-0">Буст диапазона за баланс</div>
                        <span class="ludo-case-price">{{ boostAmount }} ₽</span>
                    </div>
                    <input v-model.number="boostAmount" type="range" min="0" :max="maxBoost" step="100" class="form-range" :disabled="maxBoost <= 0" @input="clampBoost" />
                </div>

                <div v-if="message" class="ludo-alert ludo-alert-danger mb-3">{{ message }}</div>

                <button class="btn btn-ludo-primary btn-lg w-100 ludo-pulse-glow" :disabled="selectedIds.length < minSkins || burning" @click="execute">
                    {{ burning ? 'Подписываем контракт...' : selectedIds.length < minSkins ? 'Выберите минимум ' + minSkins + ' скина' : '📜 Подписать контракт (' + selectedIds.length + ' шт.)' }}
                </button>
            </div>

            <div v-if="wonSkin" class="ludo-win-card text-center mb-4 ludo-win-shake" style="border: 1px solid rgba(34, 197, 94, 0.5);">
                <div class="ludo-section-label text-success mb-2">🎉 Контракт выполнен! Ваш новый скин:</div>
                <div class="mx-auto" style="max-width: 240px;">
                    <SkinItem :skin="wonSkin" :skinImg="skinImg" :imgError="imgError" />
                </div>
            </div>
            <div class="d-flex flex-wrap justify-content-between align-items-center gap-2 mb-3">
                <div class="ludo-section-label mb-0">Ваш инвентарь ({{ filteredInventory.length }})</div>
                <div class="d-flex gap-2">
                    <input v-model.trim="invSearch" type="text" class="form-control form-control-sm ludo-form-control" placeholder="Поиск скина..." style="width: 160px;" />
                    <select v-model="invSort" class="form-select form-select-sm ludo-form-control" style="width: auto;">
                        <option value="price_asc">Сначала дешёвые</option>
                        <option value="price_desc">Сначала дорогие</option>
                    </select>
                </div>
            </div>

            <div v-if="!filteredInventory.length" class="ludo-alert ludo-alert-info text-center">
                В инвентаре нет подходящих скинов
            </div>
            <div v-else class="row g-2">
                <div v-for="item in filteredInventory" :key="item.id" class="col-4 col-sm-3 col-md-2">
                    <div class="ludo-skin-card" :class="[rarityClass?.[item.skin.rarity], { 'ludo-card-selected ludo-card-dimmed': selectedIds.includes(item.id) }]" @click="toggle(item.id)">
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
    props: ['datasend', 'datasendJson', 'user', 'skinImg', 'imgError', 'rarityClass', 'rarityLabel', 'userInfo', 'getUser', 'changePage'],
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
            invSearch: '',
            invSort: 'price_asc',
        };
    },
    computed: {
        filteredInventory() {
            const q = this.invSearch.toLowerCase();
            let list = [...this.inventory];
            if (q) {
                list = list.filter((i) => i.skin.name.toLowerCase().includes(q) || i.skin.weapon.toLowerCase().includes(q));
            }
            if (this.invSort === 'price_desc') {
                return list.sort((a, b) => b.skin.price - a.skin.price);
            }
            return list.sort((a, b) => a.skin.price - b.skin.price);
        },
        selectedItems() {
            return this.selectedIds.map((id) => this.inventory.find((item) => item.id === id)).filter(Boolean);
        },
        totalValue() {
            return this.selectedItems.reduce((sum, item) => sum + item.skin.price, 0);
        },
        maxBoost() {
            if (!this.userInfo || this.userInfo.balance === undefined) return 0;
            return calcMaxContractBoost(this.userInfo.balance);
        },
        expectedRange() {
            if (this.selectedItems.length < this.minSkins) return null;
            return calcContractRange(this.totalValue, this.selectedItems.length, this.boostAmount);
        },
    },
    mounted() {
        if (this.user) this.loadInventory();
    },
    watch: {
        user(val) { if (val) this.loadInventory(); },
    },
    methods: {
        formatPrice,
        loadInventory() {
            this.datasend('inventory').then((result) => {
                if (result) this.inventory = result;
            });
        },
        autoFillCheapest() {
            sounds.click();
            const sorted = [...this.inventory].sort((a, b) => a.skin.price - b.skin.price);
            this.selectedIds = sorted.slice(0, this.maxSkins).map((i) => i.id);
            this.clampBoost();
        },
        autoFillRandom() {
            sounds.click();
            const shuffled = [...this.inventory].sort(() => Math.random() - 0.5);
            this.selectedIds = shuffled.slice(0, 5).map((i) => i.id);
            this.clampBoost();
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
            sounds.click();
            this.selectedIds = [];
            this.boostAmount = 0;
        },
        clampBoost() {
            if (this.boostAmount > this.maxBoost) this.boostAmount = this.maxBoost;
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
                    if (!result) return;
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
                    if (this.getUser) this.getUser();
                    this.loadInventory();
                }, 1200);
            });
        },
    },
};
</script>

<style scoped>
.ludo-contracts-hero {
    background: radial-gradient(circle at 85% 20%, rgba(255, 107, 0, 0.16), transparent 55%),
                linear-gradient(135deg, rgba(24, 24, 34, 0.95), rgba(16, 16, 24, 0.98));
    border: 1px solid rgba(255, 107, 0, 0.25);
}
</style>
