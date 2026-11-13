<template>
    <div class="container-fluid px-lg-4 ludo-page ludo-secret-page">
        <div class="d-flex justify-content-between align-items-center mb-4">
            <button type="button" class="btn btn-ludo-outline btn-sm" @click="changePage('HomePage')">← На главную</button>
            <span class="ludo-stat-pill">🎁 Бонус раз в 30 минут</span>
        </div>

        <div class="ludo-hero ludo-secret-hero text-center mb-4">
            <div class="d-inline-flex align-items-center gap-2 px-3 py-1 rounded-pill mb-3" style="background: rgba(240, 180, 41, 0.14); border: 1px solid rgba(240, 180, 41, 0.35); font-size: 12px; color: #f0b429;">
                <span>✨</span>
                <span class="fw-semibold">Тайная комната · Бесплатный дроп</span>
            </div>
            <div class="ludo-case-open-visual mx-auto mb-3" style="filter: drop-shadow(0 0 32px rgba(240, 180, 41, 0.4));">
                <img v-if="data" :src="caseImg(data.case.image)" :alt="data.case.name" style="max-height: 140px;" @error="imgError($event, 'case')" />
            </div>
            <h1 class="ludo-page-title mb-1">{{ data ? data.case.name : 'Секретный кейс' }}</h1>
            <p class="ludo-page-sub mb-0">Бесплатный кейс для зарегистрированных игроков · Обновляется каждые 30 минут</p>
        </div>

        <div v-if="data && data.legendaryPromo" class="ludo-promo-box ludo-promo-legendary mb-4 mx-auto" style="max-width: 720px; border: 1px solid rgba(240, 180, 41, 0.45); box-shadow: 0 8px 30px rgba(240, 180, 41, 0.12);">
            <div class="d-flex justify-content-between align-items-center mb-2">
                <span class="ludo-section-label mb-0 text-warning">🔥 Легендарный секретный промокод</span>
                <span class="badge bg-warning text-dark">Только для нашедших</span>
            </div>
            <div class="ludo-promo-row justify-content-between flex-wrap gap-2">
                <div class="d-flex align-items-center gap-2">
                    <span class="ludo-promo-code fs-5">{{ data.legendaryPromo.code }}</span>
                    <span class="ludo-promo-bonus">{{ data.legendaryPromo.label }}</span>
                </div>
                <button type="button" class="btn btn-sm" :class="copiedPromo ? 'btn-success' : 'btn-ludo-primary'" @click="copyLegendaryPromo">
                    {{ copiedPromo ? 'Скопировано ✓' : 'Копировать код' }}
                </button>
            </div>
            <p class="small text-secondary mb-0 mt-2">Активируется в окне пополнения баланса и даёт максимальный бонус к депозиту.</p>
        </div>
        <div v-if="!data" class="text-center py-5 text-secondary">Загружаем секретный кейс...</div>

        <template v-else>
            <div v-if="rouletteVisible && strip.length" class="ludo-roulette-section mb-4">
                <div class="ludo-roulette-window">
                    <div class="ludo-roulette-marker"></div>
                    <div class="ludo-roulette-fade ludo-roulette-fade-left"></div>
                    <div class="ludo-roulette-fade ludo-roulette-fade-right"></div>
                    <div class="ludo-roulette-viewport">
                        <div class="ludo-roulette-track" :style="{ transform: 'translateX(' + offset + 'px)', transition: transition }">
                            <div v-for="(item, index) in strip" :key="'sec-' + index" class="ludo-roulette-item" :class="rarityClass[item.rarity]">
                                <img :src="skinImg(item.image)" @error="imgError($event, 'skin')" />
                                <div class="ludo-roulette-item-name">{{ item.weapon }}</div>
                                <div class="ludo-roulette-item-sub">{{ item.name }}</div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <div v-if="wonSkin && !spinning" class="ludo-win-card mb-4 text-center ludo-win-shake mx-auto" style="max-width: 420px; border: 1px solid rgba(34, 197, 94, 0.5);">
                <div class="ludo-section-label text-success mb-2">🎉 Тебе выпал бесплатный скин!</div>
                <img :src="skinImg(wonSkin.image)" class="ludo-secret-win-img mb-2" style="max-height: 110px;" @error="imgError($event, 'skin')" />
                <div class="fw-semibold fs-5">{{ wonSkin.weapon }} | {{ wonSkin.name }}</div>
                <div class="ludo-case-price fs-5 mb-3">{{ wonSkin.price }} ₽</div>
                <button v-if="wonSkin.inventory_item_id" type="button" class="btn btn-ludo-outline btn-sm px-4" :disabled="selling" @click="sellWonSkin">
                    {{ selling ? 'Продаём...' : 'Продать сразу за ' + wonSkin.price + ' ₽' }}
                </button>
            </div>

            <div v-if="message" class="ludo-alert ludo-alert-info mb-3 text-center mx-auto" style="max-width: 540px;">{{ message }}</div>

            <div class="text-center mb-5 d-flex flex-wrap justify-content-center align-items-center gap-3">
                <button v-if="user" class="btn btn-ludo-primary btn-lg px-5 ludo-pulse-glow" :disabled="loading || spinning || secondsLeft > 0" @click="openSecret">
                    <template v-if="loading || spinning">Крутим рулетку...</template>
                    <template v-else-if="secondsLeft > 0">⏳ Доступно через {{ formatCooldown(secondsLeft) }}</template>
                    <template v-else>🎁 Открыть бесплатно</template>
                </button>
                <button v-else class="btn btn-ludo-primary btn-lg px-4" @click="changePage('AuthPage')">Войти, чтобы открыть</button>
                <label v-if="user" class="ludo-fast-toggle">
                    <input v-model="fastOpen" type="checkbox" /> Быстро
                </label>
            </div>

            <div class="ludo-section-label mb-3">Возможный бесплатный дроп ({{ data.case.items?.length || 0 }})</div>
            <div class="row g-3 mb-4">
                <div v-for="item in data.case.items" :key="item.id" class="col-6 col-md-4 col-lg-3 col-xl-2">
                    <div class="ludo-skin-card ludo-secret-skin-card h-100" :class="rarityClass[item.rarity]">
                        <img :src="skinImg(item.image)" :alt="item.name" @error="imgError($event, 'skin')" />
                        <div class="small fw-semibold mt-1 text-truncate">{{ item.weapon }}</div>
                        <div class="small text-secondary text-truncate">{{ item.name }}</div>
                        <div class="ludo-case-price small mt-1">{{ item.price }} ₽</div>
                    </div>
                </div>
            </div>
        </template>
    </div>
</template>
<script>
import { fireWinConfetti } from '../helpers/confetti.js';
import { sounds } from '../helpers/sounds.js';

const ITEM_WIDTH = 132;
const SPIN_MS = 4800;

export default {
    name: 'SecretCasePage',
    props: ['changePage', 'datasend', 'user', 'getUser', 'onLiveDrop', 'skinImg', 'caseImg', 'imgError', 'rarityClass'],
    data() {
        return {
            data: null,
            loading: false,
            spinning: false,
            selling: false,
            fastOpen: false,
            copiedPromo: false,
            message: null,
            wonSkin: null,
            rouletteVisible: false,
            strip: [],
            offset: 0,
            transition: 'none',
            secondsLeft: 0,
            cooldownInterval: null,
            tickTimer: null,
            spinTimer: null,
        };
    },
    mounted() {
        this.loadCase();
        this.cooldownInterval = setInterval(() => this.tickCooldown(), 1000);
    },
    unmounted() {
        clearInterval(this.cooldownInterval);
        this.clearSpinTimers();
    },
    methods: {
        loadCase() {
            this.datasend('secret-case').then((result) => {
                if (result) {
                    this.data = result;
                    this.syncCooldown();
                }
            });
        },
        copyLegendaryPromo() {
            const code = this.data?.legendaryPromo?.code;
            if (!code) return;
            navigator.clipboard?.writeText(code);
            this.copiedPromo = true;
            setTimeout(() => { this.copiedPromo = false; }, 2000);
        },
        syncCooldown() {
            if (!this.data || !this.data.readyAt) {
                this.secondsLeft = this.data ? (this.data.secondsLeft || 0) : 0;
                return;
            }
            this.secondsLeft = Math.max(0, this.data.readyAt - Math.floor(Date.now() / 1000));
        },
        tickCooldown() {
            if (this.secondsLeft > 0) this.secondsLeft -= 1;
        },
        formatCooldown(seconds) {
            const m = String(Math.floor(seconds / 60)).padStart(2, '0');
            const s = String(seconds % 60).padStart(2, '0');
            return m + ':' + s;
        },
        clearSpinTimers() {
            clearInterval(this.tickTimer);
            clearTimeout(this.spinTimer);
            this.tickTimer = null;
            this.spinTimer = null;
        },
        finishSecretOpen(result) {
            this.wonSkin = result.skin;
            sounds.win();
            fireWinConfetti();
            if (result.liveDrop && this.onLiveDrop) {
                this.onLiveDrop(result.liveDrop);
            }
            if (result.readyAt) {
                this.data.readyAt = result.readyAt;
                this.syncCooldown();
            } else {
                this.secondsLeft = result.cooldownSeconds || 1800;
            }
            this.getUser();
        },
        startRoulette(winner, result) {
            const pool = this.data?.case?.items || [winner];
            const winIndex = 36;
            const items = [];
            for (let i = 0; i < winIndex + 8; i++) {
                items.push(i === winIndex ? winner : pool[Math.floor(Math.random() * pool.length)]);
            }
            this.strip = items;
            this.offset = 0;
            this.transition = 'none';
            this.rouletteVisible = true;
            this.spinning = true;
            this.clearSpinTimers();
            this.tickTimer = setInterval(() => sounds.tick(), 120);

            this.$nextTick(() => {
                requestAnimationFrame(() => {
                    const viewport = Math.min(window.innerWidth - 48, 960);
                    const targetOffset = -(winIndex * ITEM_WIDTH - (viewport / 2 - ITEM_WIDTH / 2));
                    this.transition = 'transform 4.6s cubic-bezier(0.12, 0.75, 0.18, 1)';
                    this.offset = targetOffset;

                    this.spinTimer = setTimeout(() => {
                        this.clearSpinTimers();
                        this.spinning = false;
                        this.finishSecretOpen(result);
                    }, SPIN_MS);
                });
            });
        },
        openSecret() {
            if (this.loading || this.spinning || this.secondsLeft > 0) return;
            sounds.click();
            this.loading = true;
            this.message = null;
            this.wonSkin = null;
            this.rouletteVisible = false;
            this.datasend('secret-case/open', 'POST', new FormData()).then((result) => {
                this.loading = false;
                if (!result) return;
                if (result.message) {
                    this.message = result.message;
                    sounds.lose();
                    return;
                }
                if (this.fastOpen) {
                    this.finishSecretOpen(result);
                } else {
                    this.startRoulette(result.skin, result);
                }
            });
        },
        sellWonSkin() {
            if (!this.wonSkin?.inventory_item_id || this.selling) return;
            this.selling = true;
            this.datasend('inventory/' + this.wonSkin.inventory_item_id + '/sell', 'POST', new FormData()).then((res) => {
                this.selling = false;
                if (res && !res.message) {
                    this.wonSkin = null;
                    this.getUser();
                }
            });
        },
    },
};
</script>

<style scoped>
.ludo-secret-hero {
    background: radial-gradient(circle at 50% 25%, rgba(240, 180, 41, 0.2), rgba(136, 71, 255, 0.12) 45%, transparent 70%),
                linear-gradient(135deg, rgba(24, 20, 36, 0.96), rgba(14, 14, 22, 0.98));
    border: 1px solid rgba(240, 180, 41, 0.3);
    box-shadow: 0 12px 45px rgba(0, 0, 0, 0.45);
}
</style>
