<template>
    <div class="container ludo-page">
        <div class="d-flex justify-content-between align-items-center mb-4">
            <button class="btn btn-ludo-outline btn-sm" @click="changePage('HomePage')">← Назад к кейсам</button>
            <span v-if="caseItem" class="ludo-stat-pill">Скинов внутри: <strong>{{ caseItem.items?.length || 0 }}</strong></span>
        </div>

        <div v-if="caseItem">
            <div class="ludo-hero text-center mb-4 position-relative overflow-hidden">
                <div class="ludo-case-open-visual mx-auto mb-3" style="filter: drop-shadow(0 0 28px rgba(240, 180, 41, 0.35));">
                    <img :src="caseImg(caseItem.image)" :alt="caseItem.name" style="max-height: 150px;" @error="imgError($event, 'case')" />
                </div>
                <h1 class="ludo-page-title mb-1">{{ caseItem.name }}</h1>
                <div class="d-flex justify-content-center align-items-center gap-2 mt-2">
                    <span class="ludo-stat-pill">Цена: <strong class="text-warning">{{ caseItem.price }} ₽</strong></span>
                    <span v-if="maxDropPrice" class="ludo-stat-pill">Макс. дроп: <strong class="text-success">{{ maxDropPrice }} ₽</strong></span>
                </div>
            </div>

            <div
                v-if="rouletteVisible && roulettes.length"
                class="ludo-roulette-section mb-4"
                :class="{ 'ludo-roulette-section-multi': roulettes.length > 1 }"
            >
                <div
                    v-for="(roulette, rouletteIndex) in roulettes"
                    :key="'roulette-' + rouletteIndex"
                    class="ludo-roulette-window"
                    :class="{ 'ludo-roulette-window-multi': roulettes.length > 1 }"
                >
                    <div v-if="roulettes.length > 1" class="ludo-roulette-row-label">#{{ rouletteIndex + 1 }}</div>
                    <div class="ludo-roulette-marker"></div>
                    <div class="ludo-roulette-fade ludo-roulette-fade-left"></div>
                    <div class="ludo-roulette-fade ludo-roulette-fade-right"></div>
                    <div class="ludo-roulette-viewport">
                        <div class="ludo-roulette-track" :style="{ transform: 'translateX(' + roulette.offset + 'px)', transition: roulette.transition }">
                            <div v-for="(item, index) in roulette.strip" :key="'r' + rouletteIndex + '-' + index" class="ludo-roulette-item" :class="rarityClass[item.rarity]">
                                <img :src="skinImg(item.image)" @error="imgError($event, 'skin')" />
                                <div class="ludo-roulette-item-name">{{ item.weapon }}</div>
                                <div class="ludo-roulette-item-sub">{{ item.name }}</div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <div v-if="message" class="ludo-alert ludo-alert-info mb-3 text-center">{{ message }}</div>

            <div v-if="wonSkins.length && !spinning" class="ludo-win-card mb-4 text-center ludo-win-shake" style="border: 1px solid rgba(240, 180, 41, 0.5); box-shadow: 0 0 30px rgba(240, 180, 41, 0.15);">
                <div class="ludo-section-label text-success mb-2">
                    {{ isDemoWin ? 'Демо-выигрыш (тестовый режим)' : (wonSkins.length > 1 ? 'Вы выиграли ' + wonSkins.length + ' скинов!' : 'Вы выиграли!') }}
                </div>
                <div class="mb-3">
                    <span class="ludo-stat-pill">
                        Общая ценность: <strong class="text-warning">{{ totalWonValue }} ₽</strong>
                        <span v-if="!isDemoWin && winProfit > 0" class="text-success ms-1">(+{{ winProfit }} ₽ профит!)</span>
                    </span>
                </div>
                <div class="row g-3 justify-content-center">
                    <div v-for="(skin, index) in wonSkins" :key="'w' + index + '-' + skin.id" class="col-6 col-md-4 col-lg-3">
                        <SkinItem :skin="skin" :skinImg="skinImg" :imgError="imgError" />
                        <button
                            v-if="!isDemoWin && skin.inventory_item_id"
                            class="btn btn-ludo-outline btn-sm w-100 mt-2"
                            :disabled="sellingIds.includes(skin.inventory_item_id)"
                            @click="sellWonSkin(skin)"
                        >
                            {{ sellingIds.includes(skin.inventory_item_id) ? '...' : 'Продать за ' + skin.price + ' ₽' }}
                        </button>
                    </div>
                </div>
                <div v-if="!isDemoWin && wonSkins.length > 1" class="mt-3">
                    <button type="button" class="btn btn-ludo-primary btn-sm px-4" :disabled="sellingAllWon" @click="sellAllWonSkins">
                        {{ sellingAllWon ? 'Продаём...' : 'Продать все за ' + totalWonValue + ' ₽' }}
                    </button>
                </div>
            </div>

            <div v-if="user" class="text-center mb-3">
                <div class="ludo-section-label mb-2">Количество открытий</div>
                <div class="ludo-open-count-tabs">
                    <button v-for="n in openCountOptions" :key="n" class="ludo-open-count-tab" :class="{ active: openCount === n, disabled: !canAffordCount(n) }" :disabled="loading || !canAffordCount(n)" @click="setOpenCount(n)">
                        x{{ n }}
                    </button>
                </div>
                <div class="small text-secondary mt-2">
                    Итого: <span class="ludo-case-price">{{ totalPrice }} ₽</span>
                    <span v-if="userInfo.balance !== undefined"> · Баланс: <strong>{{ userInfo.balance }} ₽</strong></span>
                </div>
            </div>

            <div class="text-center mb-5 d-flex flex-wrap justify-content-center gap-2 align-items-center">
                <button v-if="spinning" type="button" class="btn btn-warning btn-lg px-5 fw-bold" :disabled="!pendingResult" @click="skipRoulette">
                    ⏭ Пропустить
                </button>
                <div v-else class="d-flex flex-wrap justify-content-center gap-2">
                    <button v-if="user" class="btn btn-ludo-primary btn-lg px-5 ludo-pulse-glow" :disabled="loading || !canAffordCount(openCount)" @click="openCase">
                        {{ loading ? 'Запуск...' : openButtonLabel }}
                    </button>
                    <button v-else class="btn btn-ludo-primary btn-lg px-4" @click="changePage('AuthPage')">Войти для открытия</button>
                    <button type="button" class="btn btn-ludo-outline btn-lg" :disabled="loading" @click="openDemoCase">
                        🎲 Демо
                    </button>
                </div>
                <label class="ludo-fast-toggle ms-2">
                    <input v-model="fastOpen" type="checkbox" /> Мгновенно
                </label>
            </div>
            <div v-if="caseItem.items && caseItem.items.length">
                <div class="d-flex flex-wrap justify-content-between align-items-center gap-2 mb-3">
                    <div class="ludo-section-label mb-0">Возможный дроп ({{ filteredDropItems.length }})</div>
                    <div class="d-flex flex-wrap gap-2">
                        <select v-model="rarityFilter" class="form-select form-select-sm ludo-form-control" style="width: auto;">
                            <option value="all">Все редкости</option>
                            <option v-for="r in availableRarities" :key="r" :value="r">{{ rarityLabel[r] || r }}</option>
                        </select>
                        <select v-model="dropSort" class="form-select form-select-sm ludo-form-control" style="width: auto;">
                            <option value="price_desc">Дороже → Дешевле</option>
                            <option value="price_asc">Дешевле → Дороже</option>
                        </select>
                    </div>
                </div>
                <div class="row g-3">
                    <div v-for="item in filteredDropItems" :key="item.id" class="col-6 col-md-4 col-lg-3 col-xl-2">
                        <div class="ludo-drop-preview h-100" :class="rarityClass[item.rarity]">
                            <div class="ludo-drop-preview-img">
                                <img :src="skinImg(item.image)" @error="imgError($event, 'skin')" />
                            </div>
                            <div class="ludo-drop-preview-body">
                                <div class="small fw-semibold text-truncate">{{ item.weapon }}</div>
                                <div class="small text-secondary text-truncate">{{ item.name }}</div>
                                <div class="ludo-case-price small mt-1">{{ item.price }} ₽</div>
                                <span class="ludo-rarity-badge">{{ rarityLabel[item.rarity] }}</span>
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
import { fireWinConfetti } from '../helpers/confetti.js';
import { rarityOrder } from '../helpers/images-meta.js';
import { sounds } from '../helpers/sounds.js';

const ITEM_WIDTH = 132;
const ITEM_WIDTH_MULTI = 108;
const SPIN_MS = 3400;

export default {
    name: 'CasePage',
    components: { SkinItem },
    props: ['changePage', 'datasend', 'pageId', 'user', 'userInfo', 'getUser', 'onLiveDrop', 'rarityClass', 'rarityLabel', 'skinImg', 'caseImg', 'imgError'],
    data() {
        return {
            caseItem: null,
            wonSkins: [],
            openCount: 1,
            openCountOptions: Array.from({ length: 5 }, (_, i) => i + 1),
            loading: false,
            spinning: false,
            message: null,
            rouletteVisible: false,
            roulettes: [],
            fastOpen: false,
            isDemoWin: false,
            rarityFilter: 'all',
            dropSort: 'price_desc',
            tickTimer: null,
            spinTimer: null,
            pendingResult: null,
            sellingIds: [],
            sellingAllWon: false,
        };
    },
    computed: {
        totalPrice() {
            return this.caseItem ? this.caseItem.price * this.openCount : 0;
        },
        openButtonLabel() {
            return this.openCount === 1 ? 'Открыть кейс' : 'Открыть x' + this.openCount;
        },
        maxDropPrice() {
            if (!this.caseItem?.items?.length) return 0;
            return Math.max(...this.caseItem.items.map((i) => Number(i.price) || 0));
        },
        totalWonValue() {
            return this.wonSkins.reduce((sum, s) => sum + (Number(s.price) || 0), 0);
        },
        winProfit() {
            return this.totalWonValue - this.totalPrice;
        },
        availableRarities() {
            if (!this.caseItem?.items?.length) return [];
            const set = new Set(this.caseItem.items.map((i) => i.rarity));
            return rarityOrder.filter((r) => set.has(r));
        },
        filteredDropItems() {
            if (!this.caseItem?.items?.length) return [];
            let list = [...this.caseItem.items];
            if (this.rarityFilter !== 'all') {
                list = list.filter((i) => i.rarity === this.rarityFilter);
            }
            if (this.dropSort === 'price_asc') {
                return list.sort((a, b) => a.price - b.price);
            }
            return list.sort((a, b) => b.price - a.price);
        },
    },
    mounted() {
        this.getCase();
    },
    unmounted() {
        this.clearSpinTimers();
    },
    methods: {
        getCase() {
            this.datasend('cases/' + this.pageId).then((result) => {
                if (result) {
                    this.caseItem = result;
                    this.clampOpenCount();
                }
            });
        },
        canAffordCount(count) {
            if (!this.caseItem || this.userInfo.balance === undefined) return false;
            return this.userInfo.balance >= this.caseItem.price * count;
        },
        clampOpenCount() {
            if (!this.canAffordCount(this.openCount)) {
                for (let n = 5; n >= 1; n--) {
                    if (this.canAffordCount(n)) {
                        this.openCount = n;
                        return;
                    }
                }
                this.openCount = 1;
            }
        },
        setOpenCount(n) {
            if (!this.canAffordCount(n) || this.loading) return;
            sounds.click();
            this.openCount = n;
        },
        clearSpinTimers() {
            clearInterval(this.tickTimer);
            clearTimeout(this.spinTimer);
            this.tickTimer = null;
            this.spinTimer = null;
        },
        getSkinsFromResult(result) {
            if (result.skins?.length) return result.skins;
            return result.skin ? Array.of(result.skin) : Array.of();
        },
        buildPlaceholderStrip(count = 34) {
            const pool = this.caseItem?.items || Array.of();
            if (!pool.length) return Array.of();
            const strip = Array.of();
            for (let i = 0; i < count; i++) {
                strip.push(pool[Math.floor(Math.random() * pool.length)]);
            }
            return strip;
        },
        buildStripWithWinner(winner, winIndex) {
            const pool = this.caseItem.items || Array.of();
            const strip = Array.of();
            for (let i = 0; i < winIndex + 8; i++) {
                strip.push(i === winIndex ? winner : pool[Math.floor(Math.random() * pool.length)]);
            }
            return strip;
        },
        calcOffset(winIndex, itemWidth) {
            const viewport = Math.min(window.innerWidth - 48, 960);
            return -(winIndex * itemWidth - (viewport / 2 - itemWidth / 2));
        },
        finishOpen(result) {
            this.wonSkins = this.getSkinsFromResult(result);
            if (!this.isDemoWin) {
                this.getUser();
            }
            sounds.win();
            fireWinConfetti();
            const drops = result.liveDrops || (result.liveDrop ? Array.of(result.liveDrop) : Array.of());
            if (!this.isDemoWin && this.onLiveDrop) {
                drops.forEach((d) => this.onLiveDrop(d));
            }
        },
        skipRoulette() {
            if (!this.spinning || !this.pendingResult) return;
            this.clearSpinTimers();
            const winIndex = 26;
            const itemWidth = this.roulettes.length > 1 ? ITEM_WIDTH_MULTI : ITEM_WIDTH;
            const targetOffset = this.calcOffset(winIndex, itemWidth);
            this.roulettes.forEach((r) => {
                r.transition = 'transform 0.14s ease-out';
                r.offset = targetOffset;
            });
            setTimeout(() => {
                const res = this.pendingResult;
                this.pendingResult = null;
                this.loading = false;
                this.spinning = false;
                if (res) this.finishOpen(res);
            }, 150);
        },
        startRouletteAnimation(skins, result) {
            const winIndex = 26;
            const itemWidth = skins.length > 1 ? ITEM_WIDTH_MULTI : ITEM_WIDTH;
            this.pendingResult = result;
            this.roulettes = skins.map((skin) => ({ strip: this.buildStripWithWinner(skin, winIndex), offset: 0, transition: 'none' }));
            this.rouletteVisible = true;
            this.clearSpinTimers();
            this.tickTimer = setInterval(() => sounds.tick(), 105);
            this.$nextTick(() => {
                requestAnimationFrame(() => {
                    const targetOffset = this.calcOffset(winIndex, itemWidth);
                    this.roulettes.forEach((r) => {
                        r.transition = 'transform 3.25s cubic-bezier(0.12, 0.82, 0.22, 1)';
                        r.offset = targetOffset;
                    });
                    this.spinTimer = setTimeout(() => {
                        this.clearSpinTimers();
                        const res = this.pendingResult;
                        this.pendingResult = null;
                        this.loading = false;
                        this.spinning = false;
                        if (res) this.finishOpen(res);
                    }, SPIN_MS);
                });
            });
        },
        openDemoCase() {
            const pool = this.caseItem?.items || Array.of();
            if (!pool.length || this.loading) return;
            sounds.click();
            this.isDemoWin = true;
            this.loading = true;
            this.spinning = true;
            this.message = null;
            this.wonSkins = Array.of();
            const fakeWinner = pool[Math.floor(Math.random() * pool.length)];
            const fakeResult = { skins: Array.of(fakeWinner) };
            if (this.fastOpen) {
                this.rouletteVisible = false;
                this.loading = false;
                this.spinning = false;
                this.finishOpen(fakeResult);
                return;
            }
            this.startRouletteAnimation(Array.of(fakeWinner), fakeResult);
        },
        openCase() {
            if (!this.caseItem?.items?.length || this.loading) return;
            if (!this.canAffordCount(this.openCount)) {
                this.message = 'Недостаточно средств на балансе.';
                sounds.lose();
                return;
            }
            sounds.click();
            this.isDemoWin = false;
            this.loading = true;
            this.spinning = true;
            this.message = null;
            this.wonSkins = Array.of();
            this.pendingResult = null;

            if (!this.fastOpen) {
                this.roulettes = Array.from({ length: this.openCount }, () => ({
                    strip: this.buildPlaceholderStrip(34),
                    offset: -160,
                    transition: 'transform 1.5s linear',
                }));
                this.rouletteVisible = true;
            } else {
                this.rouletteVisible = false;
                this.roulettes = Array.of();
            }

            const formdata = new FormData();
            formdata.append('count', this.openCount);
            this.datasend('cases/' + this.pageId + '/open', 'POST', formdata).then((result) => {
                if (!result || result.message) {
                    this.rouletteVisible = false;
                    this.loading = false;
                    this.spinning = false;
                    if (result?.message) {
                        this.message = result.message;
                        sounds.lose();
                        this.clampOpenCount();
                    }
                    return;
                }
                const skins = this.getSkinsFromResult(result);
                if (!skins.length) {
                    this.rouletteVisible = false;
                    this.loading = false;
                    this.spinning = false;
                    return;
                }
                if (this.fastOpen) {
                    this.rouletteVisible = false;
                    this.loading = false;
                    this.spinning = false;
                    this.finishOpen(result);
                    return;
                }
                this.startRouletteAnimation(skins, result);
            });
        },
        sellWonSkin(skin) {
            if (!skin?.inventory_item_id) return;
            if (!this.sellingIds.includes(skin.inventory_item_id)) {
                this.sellingIds.push(skin.inventory_item_id);
            }
            this.datasend('inventory/' + skin.inventory_item_id + '/sell', 'POST', new FormData()).then((result) => {
                this.sellingIds = this.sellingIds.filter((id) => id !== skin.inventory_item_id);
                if (!result || result.message) return;
                this.wonSkins = this.wonSkins.filter((item) => item.inventory_item_id !== skin.inventory_item_id);
                this.getUser();
            });
        },
        async sellAllWonSkins() {
            if (this.sellingAllWon || !this.wonSkins.length) return;
            this.sellingAllWon = true;
            const toSell = this.wonSkins.filter((s) => s.inventory_item_id);
            for (const skin of toSell) {
                await this.datasend('inventory/' + skin.inventory_item_id + '/sell', 'POST', new FormData());
            }
            this.wonSkins = Array.of();
            this.sellingAllWon = false;
            this.getUser();
        },
    },
};
</script>
