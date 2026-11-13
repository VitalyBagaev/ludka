<template>
    <div class="container ludo-page ludo-secret-page">
        <div class="ludo-secret-glow mb-4">
            <div class="ludo-case-open-visual mx-auto mb-3">
                <img v-if="data" :src="caseImg(data.case.image)" :alt="data.case.name" @error="imgError($event, 'case')" />
            </div>
            <h1 class="ludo-page-title text-center">{{ data ? data.case.name : '???' }}</h1>
            <p class="ludo-page-sub text-center mb-0">Бесплатный кейс · раз в 30 минут</p>
        </div>

        <div v-if="data && data.legendaryPromo" class="ludo-promo-box ludo-promo-legendary mb-4">
            <div class="ludo-section-label mb-2">Легендарный промокод</div>
            <div class="ludo-promo-row ludo-promo-mega">
                <span class="ludo-promo-code">{{ data.legendaryPromo.code }}</span>
                <span class="ludo-promo-bonus">{{ data.legendaryPromo.label }}</span>
            </div>
            <p class="small text-secondary mb-0 mt-2">Только здесь. Работает при пополнении баланса.</p>
        </div>

        <div v-if="!data" class="text-center py-5 text-secondary">Загрузка...</div>

        <template v-else>
            <div class="ludo-section-label mb-3">Содержимое</div>
            <div class="row g-3 mb-4">
                <div v-for="item in data.case.items" :key="item.id" class="col-6 col-md-4 col-lg-3 col-xl-2">
                    <div class="ludo-skin-card ludo-secret-skin-card" :class="rarityClass[item.rarity]">
                        <img :src="skinImg(item.image)" :alt="item.name" @error="imgError($event, 'skin')" />
                        <div class="small fw-semibold mt-1">{{ item.weapon }}</div>
                        <div class="small text-secondary">{{ item.name }}</div>
                        <div class="ludo-case-price small">{{ item.price }} ₽</div>
                    </div>
                </div>
            </div>

            <div v-if="wonSkin" class="ludo-win-card mb-4 text-center ludo-win-shake">
                <div class="ludo-section-label text-success mb-3">Тебе выпало!</div>
                <img :src="skinImg(wonSkin.image)" class="ludo-secret-win-img mb-2" @error="imgError($event, 'skin')" />
                <div class="fw-semibold">{{ wonSkin.weapon }} | {{ wonSkin.name }}</div>
                <div class="ludo-case-price">{{ wonSkin.price }} ₽</div>
            </div>

            <div v-if="message" class="ludo-alert ludo-alert-info mb-3 text-center">{{ message }}</div>

            <div class="text-center">
                <button
                    v-if="user"
                    class="btn btn-ludo-primary btn-lg px-5"
                    :disabled="loading || secondsLeft > 0"
                    @click="openSecret"
                >
                    <template v-if="loading">Крутим...</template>
                    <template v-else-if="secondsLeft > 0">Подождите {{ formatCooldown(secondsLeft) }}</template>
                    <template v-else>Открыть бесплатно</template>
                </button>
                <button v-else class="btn btn-ludo-primary btn-lg" @click="changePage('AuthPage')">Войти, чтобы открыть</button>
            </div>
        </template>
    </div>
</template>

<script>
import { sounds } from '../helpers/sounds.js';

export default {
    name: 'SecretCasePage',
    props: ['changePage', 'datasend', 'user', 'getUser', 'onLiveDrop', 'skinImg', 'caseImg', 'imgError', 'rarityClass'],
    data() {
        return {
            data: null,
            loading: false,
            message: null,
            wonSkin: null,
            secondsLeft: 0,
            cooldownInterval: null,
        };
    },
    mounted() {
        this.loadCase();
        this.cooldownInterval = setInterval(() => this.tickCooldown(), 1000);
    },
    unmounted() {
        clearInterval(this.cooldownInterval);
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
        syncCooldown() {
            if (!this.data || !this.data.readyAt) {
                this.secondsLeft = this.data ? (this.data.secondsLeft || 0) : 0;
                return;
            }
            this.secondsLeft = Math.max(0, this.data.readyAt - Math.floor(Date.now() / 1000));
        },
        tickCooldown() {
            if (this.secondsLeft > 0) {
                this.secondsLeft -= 1;
            }
        },
        formatCooldown(seconds) {
            const m = Math.floor(seconds / 60);
            const s = seconds % 60;
            if (m > 0) {
                return m + 'м ' + s + 'с';
            }
            return s + 'с';
        },
        openSecret() {
            if (this.loading || this.secondsLeft > 0) {
                return;
            }
            this.loading = true;
            this.message = null;
            this.wonSkin = null;
            this.datasend('secret-case/open', 'POST', new FormData()).then((result) => {
                this.loading = false;
                if (!result) {
                    return;
                }
                if (result.message) {
                    this.message = result.message;
                    return;
                }
                sounds.win();
                this.wonSkin = result.skin;
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
            });
        },
    },
};
</script>
