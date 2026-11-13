<template>
    <Teleport to="body">
        <div v-if="show" class="ludo-modal-backdrop" @click.self="$emit('close')">
            <div class="ludo-modal">
                <div class="ludo-modal-header">
                    <h2 class="h5 mb-0">Пополнение баланса</h2>
                    <button class="btn btn-sm btn-ludo-outline" @click="$emit('close')">×</button>
                </div>
                <div class="p-4">
                    <div class="ludo-stat-card text-center mb-3">
                        <div class="small text-secondary">Текущий баланс</div>
                        <div class="ludo-case-price fs-3">{{ balance }} ₽</div>
                    </div>

                    <div v-if="promos" class="ludo-promo-box mb-3">
                        <div class="small text-secondary mb-2">Активный промокод</div>
                        <div class="ludo-promo-row">
                            <span class="ludo-promo-code">{{ promos.rotating.code }}</span>
                            <span class="ludo-promo-bonus">+{{ promos.rotating.bonusPercent }}%</span>
                            <span class="small text-secondary">{{ promoTimer }}с</span>
                        </div>
                    </div>

                    <label class="form-label">Сумма</label>
                    <input v-model.number="amount" type="number" min="1" class="form-control ludo-form-control mb-3" />

                    <label class="form-label">Промокод <span class="text-secondary fw-normal">(необязательно)</span></label>
                    <input v-model="promoCode" type="text" class="form-control ludo-form-control mb-2 text-uppercase" placeholder="Можно оставить пустым" />
                    <div v-if="previewBonus > 0" class="small text-success mb-3">
                        Бонус: +{{ previewBonus }} ₽ · Зачислится: {{ amount + previewBonus }} ₽
                    </div>
                    <div v-if="promoError" class="ludo-alert ludo-alert-danger py-2 small mb-3">{{ promoError }}</div>

                    <div class="d-flex flex-wrap gap-2 mb-4">
                        <button
                            v-for="p in presets"
                            :key="p"
                            class="btn btn-sm"
                            :class="amount === p ? 'btn-ludo-primary' : 'btn-ludo-outline'"
                            @click="amount = p"
                        >
                            {{ p }} ₽
                        </button>
                    </div>
                    <button class="btn btn-ludo-primary w-100" :disabled="loading || amount < 1" @click="submit">
                        {{ loading ? 'Пополняем...' : 'Пополнить ' + amount + ' ₽' }}
                    </button>
                    <p class="small text-secondary text-center mt-3 mb-0">Демо: средства зачисляются мгновенно</p>
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
            promoCode: '',
            loading: false,
            promoError: null,
            promoTimer: 0,
            promoInterval: null,
        };
    },
    computed: {
        previewBonus() {
            if (!this.promos || !this.promoCode.trim()) {
                return 0;
            }
            const code = this.promoCode.trim().toUpperCase();
            let percent = 0;
            if (code === 'LUDOLEGEND') {
                percent = 777;
            } else if (this.promos && code === this.promos.rotating.code) {
                percent = this.promos.rotating.bonusPercent;
            }
            if (!percent) {
                return 0;
            }
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
        startPromoTimer() {
            this.syncPromoTimer();
            this.stopPromoTimer();
            this.promoInterval = setInterval(() => {
                this.syncPromoTimer();
            }, 1000);
        },
        stopPromoTimer() {
            clearInterval(this.promoInterval);
            this.promoInterval = null;
        },
        syncPromoTimer() {
            if (!this.promos || !this.promos.rotating || !this.promos.rotating.expiresAt) {
                return;
            }
            this.promoTimer = Math.max(0, this.promos.rotating.expiresAt - Math.floor(Date.now() / 1000));
        },
        submit() {
            if (this.loading || this.amount < 1) {
                return;
            }
            this.loading = true;
            this.promoError = null;
            sounds.click();
            let fd = new FormData();
            fd.append('amount', this.amount);
            if (this.promoCode.trim()) {
                fd.append('promo_code', this.promoCode.trim().toUpperCase());
            }
            this.datasend('balance/deposit', 'POST', fd).then((result) => {
                this.loading = false;
                if (!result) {
                    return;
                }
                if (result.message) {
                    this.promoError = result.message;
                    return;
                }
                this.$emit('done');
                this.$emit('close');
            });
        },
    },
};
</script>
