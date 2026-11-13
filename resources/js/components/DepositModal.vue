<template>
    <Teleport to="body">
        <div v-if="show" class="ludo-modal-backdrop" @click.self="$emit('close')">
            <div class="ludo-modal" style="max-width: 480px; width: 95%">
                <div class="ludo-modal-header">
                    <h2 class="h5 mb-0">Пополнение баланса</h2>
                    <button type="button" class="btn btn-sm btn-ludo-outline" @click="$emit('close')">×</button>
                </div>
                <div class="p-4">
                    <div class="ludo-stat-card text-center mb-3">
                        <div class="small text-secondary">Текущий баланс</div>
                        <div class="ludo-case-price fs-3">{{ balance }} ₽</div>
                    </div>

                    <div v-if="promos && promos.rotating" class="ludo-promo-box mb-3">
                        <div class="d-flex justify-content-between align-items-center mb-2">
                            <span class="small text-secondary">Активный промокод дня</span>
                            <span class="small text-secondary">⏳ {{ promoTimer }}с</span>
                        </div>
                        <div class="ludo-promo-row justify-content-between">
                            <div class="d-flex align-items-center gap-2">
                                <span class="ludo-promo-code">{{ promos.rotating.code }}</span>
                                <span class="ludo-promo-bonus">+{{ promos.rotating.bonusPercent }}%</span>
                            </div>
                            <button type="button" class="btn btn-sm btn-ludo-outline" @click="applyRotatingPromo">
                                Применить
                            </button>
                        </div>
                    </div>

                    <label class="form-label small text-secondary">Сумма пополнения (₽)</label>
                    <input v-model.number="amount" type="number" min="1" class="form-control ludo-form-control mb-2" />

                    <div class="d-flex flex-wrap gap-2 mb-3">
                        <button
                            v-for="p in presets"
                            :key="p"
                            type="button"
                            class="btn btn-sm flex-grow-1"
                            :class="amount === p ? 'btn-ludo-primary' : 'btn-ludo-outline'"
                            @click="amount = p"
                        >
                            {{ p }} ₽
                        </button>
                    </div>

                    <label class="form-label small text-secondary">Промокод <span class="fw-normal">(необязательно)</span></label>
                    <input v-model="promoCode" type="text" class="form-control ludo-form-control mb-2 text-uppercase" placeholder="Введите промокод" />
                    <div v-if="previewBonus > 0" class="small text-success mb-3">
                        Бонус: +{{ previewBonus }} ₽ · Итого зачислится: <strong>{{ amount + previewBonus }} ₽</strong>
                    </div>
                    <div v-if="promoError" class="ludo-alert ludo-alert-danger py-2 small mb-3">{{ promoError }}</div>

                    <button type="button" class="btn btn-ludo-primary w-100 py-2 fw-semibold" :disabled="loading || amount < 1" @click="submit">
                        {{ loading ? 'Пополняем...' : 'Пополнить ' + (amount + previewBonus) + ' ₽' }}
                    </button>
                    <p class="small text-secondary text-center mt-3 mb-0">Демо-режим: средства зачисляются мгновенно</p>
                </div>
            </div>
        </div>
    </Teleport>
</template>
<script>
import { sounds } from '../helpers/sounds.js';

export default {
    name: 'DepositModal',
    props: ['show', 'balance', 'datasend', 'promos'],
    emits: ['close', 'done'],
    data() {
        return {
            amount: 1000,
            presets: Array.of(100, 250, 500, 1000, 2500, 5000),
            promoCode: '',
            loading: false,
            promoError: null,
            promoTimer: 0,
            promoInterval: null,
        };
    },
    computed: {
        previewBonus() {
            if (!this.promoCode.trim()) return 0;
            const code = this.promoCode.trim().toUpperCase();
            let percent = 0;
            if (code === 'LUDOLEGEND') {
                percent = 777;
            } else if (this.promos && this.promos.rotating && code === this.promos.rotating.code) {
                percent = this.promos.rotating.bonusPercent;
            }
            if (!percent) return 0;
            return Math.round(this.amount * (percent / 100));
        },
    },
    watch: {
        show(val) {
            if (val) {
                this.amount = 1000;
                this.promoCode = '';
                this.promoError = null;
                this.startPromoTimer();
            } else {
                this.stopPromoTimer();
            }
        },
        promos() {
            this.syncPromoTimer();
        },
    },
    unmounted() {
        this.stopPromoTimer();
    },
    methods: {
        applyRotatingPromo() {
            if (this.promos?.rotating?.code) {
                sounds.click();
                this.promoCode = this.promos.rotating.code;
            }
        },
        startPromoTimer() {
            this.syncPromoTimer();
            this.stopPromoTimer();
            this.promoInterval = setInterval(() => this.syncPromoTimer(), 1000);
        },
        stopPromoTimer() {
            clearInterval(this.promoInterval);
            this.promoInterval = null;
        },
        syncPromoTimer() {
            if (!this.promos?.rotating?.expiresAt) return;
            this.promoTimer = Math.max(0, this.promos.rotating.expiresAt - Math.floor(Date.now() / 1000));
        },
        submit() {
            if (this.loading || this.amount < 1) return;
            this.loading = true;
            this.promoError = null;
            sounds.click();
            const fd = new FormData();
            fd.append('amount', this.amount);
            if (this.promoCode.trim()) {
                fd.append('promo_code', this.promoCode.trim().toUpperCase());
            }
            this.datasend('balance/deposit', 'POST', fd).then((result) => {
                this.loading = false;
                if (!result) return;
                if (result.message) {
                    this.promoError = result.message;
                    return;
                }
                sounds.win();
                this.$emit('done');
                this.$emit('close');
            });
        },
    },
};
</script>
