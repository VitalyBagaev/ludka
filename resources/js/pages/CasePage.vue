<template>
    <div class="container ludo-page">
        <button class="btn btn-ludo-outline btn-sm mb-4" @click="changePage('HomePage')">← Назад к кейсам</button>

        <div v-if="caseItem">
            <div class="ludo-case-open mb-3">
                <div class="ludo-case-open-visual">
                    <img :src="caseImg(caseItem.image)" :alt="caseItem.name" @error="imgError($event, 'case')" />
                </div>
                <h1 class="ludo-page-title">{{ caseItem.name }}</h1>
                <p class="ludo-page-sub">{{ caseItem.price }} ₽ за открытие</p>
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
                        <div
                            class="ludo-roulette-track"
                            :style="{
                                transform: 'translateX(' + roulette.offset + 'px)',
                                transition: roulette.transition,
                            }"
                        >
                            <div
                                v-for="(item, index) in roulette.strip"
                                :key="'r' + rouletteIndex + '-' + index"
                                class="ludo-roulette-item"
                                :class="rarityClass[item.rarity]"
                            >
                                <img :src="skinImg(item.image)" @error="imgError($event, 'skin')" />
                                <div class="ludo-roulette-item-name">{{ item.weapon }}</div>
                                <div class="ludo-roulette-item-sub">{{ item.name }}</div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <div v-if="message" class="ludo-alert ludo-alert-info mb-3">{{ message }}</div>

            <div v-if="wonSkins.length && !spinning" class="ludo-win-card mb-4 text-center ludo-win-shake">
                <div class="ludo-section-label text-success mb-3">
                    {{ wonSkins.length > 1 ? 'Вы выиграли ' + wonSkins.length + ' скинов!' : 'Вы выиграли!' }}
                </div>
                <div class="row g-3 justify-content-center">
                    <div v-for="(skin, index) in wonSkins" :key="'w' + index + '-' + skin.id" class="col-6 col-md-4 col-lg-3">
                        <SkinItem :skin="skin" :skinImg="skinImg" :imgError="imgError" />
                        <button
                            class="btn btn-ludo-outline btn-sm w-100 mt-2"
                            :disabled="sellingIds.includes(skin.inventory_item_id)"
                            @click="sellWonSkin(skin)"
                        >
                            {{ sellingIds.includes(skin.inventory_item_id) ? '...' : 'Продать за ' + skin.price + ' ₽' }}
                        </button>
                    </div>
                </div>
            </div>

            <div v-if="user" class="text-center mb-3">
                <div class="ludo-section-label mb-2">Количество открытий</div>
                <div class="ludo-open-count-tabs">
                    <button
                        v-for="n in 5"
                        :key="n"
                        class="ludo-open-count-tab"
                        :class="{ active: openCount === n, disabled: !canAffordCount(n) }"
                        :disabled="loading || !canAffordCount(n)"
                        @click="setOpenCount(n)"
                    >
                        x{{ n }}
                    </button>
                </div>
                <div class="small text-secondary mt-2">
                    Итого: <span class="ludo-case-price">{{ totalPrice }} ₽</span>
                    <span v-if="userInfo.balance !== undefined"> · Баланс: {{ userInfo.balance }} ₽</span>
                </div>
            </div>

            <div class="text-center mb-4 d-flex flex-wrap justify-content-center gap-2 align-items-center">
                <button
                    v-if="user"
                    class="btn btn-ludo-primary btn-lg px-5 ludo-pulse-glow"
                    :disabled="loading || !canAffordCount(openCount)"
                    @click="openCase"
                >
                    {{ loading ? 'Крутим...' : openButtonLabel }}
                </button>
                <button v-else class="btn btn-ludo-primary" @click="changePage('AuthPage')">Войти</button>
                <label v-if="user" class="ludo-fast-toggle ms-2">
                    <input v-model="fastOpen" type="checkbox" /> Быстрое открытие
                </label>
                <button v-if="wonSkins.length && !spinning" class="btn btn-ludo-outline" @click="resetOpen">Ещё раз</button>
            </div>

            <div v-if="caseItem.items && caseItem.items.length">
                <div class="ludo-section-label mb-3">Возможный дроп</div>
                <div class="row g-3">
                    <div v-for="item in caseItem.items" :key="item.id" class="col-6 col-md-4 col-lg-3 col-xl-2">
                        <div class="ludo-drop-preview" :class="rarityClass[item.rarity]">
                            <div class="ludo-drop-preview-img">
                                <img :src="skinImg(item.image)" @error="imgError($event, 'skin')" />
                            </div>
                            <div class="ludo-drop-preview-body">
                                <div class="small fw-semibold">{{ item.weapon }}</div>
                                <div class="small text-secondary">{{ item.name }}</div>
                                <div class="ludo-case-price small">{{ item.price }} ₽</div>
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
import { sounds } from '../helpers/sounds.js';

const ITEM_WIDTH = 132;
const ITEM_WIDTH_MULTI = 108;
const SPIN_MS = 5200;

export default {
    name: 'CasePage',
    components: { SkinItem },
    props: ['changePage', 'datasend', 'pageId', 'user', 'userInfo', 'getUser', 'onLiveDrop', 'rarityClass', 'rarityLabel', 'skinImg', 'caseImg', 'imgError'],
    data() {
        return {
            caseItem: null,
            wonSkins: [],
            openCount: 1,
            loading: false,
            spinning: false,
            message: null,
            rouletteVisible: false,
            roulettes: [],
            fastOpen: false,
            tickTimer: null,
            spinTimer: null,
            sellingIds: [],
        };
    },
    computed: {
        totalPrice() {
            if (!this.caseItem) {
                return 0;
            }
            return this.caseItem.price * this.openCount;
        },
        openButtonLabel() {
            if (this.openCount === 1) {
                return 'Открыть кейс';
            }
            return 'Открыть x' + this.openCount;
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
            if (!this.caseItem || this.userInfo.balance === undefined) {
                return false;
            }
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
            if (!this.canAffordCount(n) || this.loading) {
                return;
            }
            sounds.click();
            this.openCount = n;
        },
        clearSpinTimers() {
            clearInterval(this.tickTimer);
            clearTimeout(this.spinTimer);
            this.tickTimer = null;
            this.spinTimer = null;
        },
        resetOpen() {
            this.wonSkins = [];
            this.message = null;
            this.rouletteVisible = false;
            this.roulettes = [];
            this.clearSpinTimers();
        },
        getSkinsFromResult(result) {
            if (result.skins && result.skins.length) {
                return result.skins;
            }
            if (result.skin) {
                return [result.skin];
            }
            return [];
        },
        buildStripWithWinner(winner, winIndex) {
            const pool = this.caseItem.items || [];
            const strip = [];
            for (let i = 0; i < winIndex + 8; i++) {
                strip.push(i === winIndex ? winner : pool[Math.floor(Math.random() * pool.length)]);
            }
            return strip;
        },
        getItemWidth(count) {
            return count > 1 ? ITEM_WIDTH_MULTI : ITEM_WIDTH;
        },
        calcOffset(winIndex, itemWidth) {
            const viewport = Math.min(window.innerWidth - 48, 960);
            return -(winIndex * itemWidth - (viewport / 2 - itemWidth / 2));
        },
        finishOpen(result) {
            const skins = this.getSkinsFromResult(result);
            this.wonSkins = skins;
            this.getUser();
            sounds.win();
            fireWinConfetti();

            const drops = result.liveDrops || (result.liveDrop ? [result.liveDrop] : []);
            if (this.onLiveDrop) {
                for (let i = 0; i < drops.length; i++) {
                    this.onLiveDrop(drops[i]);
                }
            }
        },
        startRouletteAnimation(skins, result) {
            const winIndex = 38;
            const itemWidth = this.getItemWidth(skins.length);

            this.roulettes = skins.map(function (skin) {
                return {
                    strip: this.buildStripWithWinner(skin, winIndex),
                    offset: 0,
                    transition: 'none',
                };
            }, this);

            this.rouletteVisible = true;
            this.clearSpinTimers();

            this.tickTimer = setInterval(function () {
                sounds.tick();
            }, 120);

            this.$nextTick(() => {
                requestAnimationFrame(() => {
                    const targetOffset = this.calcOffset(winIndex, itemWidth);
                    this.roulettes.forEach(function (roulette) {
                        roulette.transition = 'transform 5s cubic-bezier(0.12, 0.75, 0.18, 1)';
                        roulette.offset = targetOffset;
                    });

                    this.spinTimer = setTimeout(() => {
                        this.clearSpinTimers();
                        this.loading = false;
                        this.spinning = false;
                        this.finishOpen(result);
                    }, SPIN_MS);
                });
            });
        },
        openCase() {
            if (!this.caseItem.items || !this.caseItem.items.length) {
                return;
            }
            if (!this.canAffordCount(this.openCount)) {
                this.message = 'Недостаточно средств на балансе.';
                sounds.lose();
                return;
            }

            sounds.click();
            this.loading = true;
            this.spinning = true;
            this.message = null;
            this.wonSkins = [];
            this.rouletteVisible = false;
            this.roulettes = [];

            let formdata = new FormData();
            formdata.append('count', this.openCount);

            this.datasend('cases/' + this.pageId + '/open', 'POST', formdata).then((result) => {
                if (!result) {
                    this.loading = false;
                    this.spinning = false;
                    return;
                }
                if (result.message) {
                    this.message = result.message;
                    this.loading = false;
                    this.spinning = false;
                    sounds.lose();
                    this.clampOpenCount();
                    return;
                }

                const skins = this.getSkinsFromResult(result);
                if (!skins.length) {
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
            if (!skin || !skin.inventory_item_id) {
                return;
            }
            if (!confirm('Продать предмет за ' + skin.price + ' ₽?')) {
                return;
            }
            if (!this.sellingIds.includes(skin.inventory_item_id)) {
                this.sellingIds.push(skin.inventory_item_id);
            }
            this.datasend('inventory/' + skin.inventory_item_id + '/sell', 'POST', new FormData()).then((result) => {
                const idx = this.sellingIds.indexOf(skin.inventory_item_id);
                if (idx >= 0) this.sellingIds.splice(idx, 1);
                if (!result || result.message) {
                    return;
                }
                this.wonSkins = this.wonSkins.filter((item) => item.inventory_item_id !== skin.inventory_item_id);
                this.getUser();
            });
        },
    },
};
</script>
