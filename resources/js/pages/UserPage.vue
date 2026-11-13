<template>
    <div class="container ludo-page">
        <div v-if="!user" class="ludo-alert ludo-alert-warning">
            Нужно <a href="#" class="text-warning" @click.prevent="changePage('AuthPage')">войти</a> в аккаунт
        </div>

        <template v-else-if="profile">
            <div class="ludo-profile-header mb-4">
                <div class="ludo-profile-avatar-wrap">
                    <div class="ludo-profile-avatar" :class="{ 'ludo-profile-avatar-img': profile.user.avatar }">
                        <img v-if="profile.user.avatar" :src="profile.user.avatar" alt="avatar" />
                        <span v-else>{{ initials }}</span>
                    </div>
                    <div class="ludo-avatar-actions">
                        <label class="btn btn-sm btn-ludo-outline mb-0">
                            {{ uploadingAvatar ? '...' : 'Фото' }}
                            <input type="file" accept="image/*" class="d-none" @change="uploadAvatar" />
                        </label>
                        <button v-if="profile.user.avatar" class="btn btn-sm btn-outline-danger" @click="removeAvatar">×</button>
                    </div>
                </div>
                <div class="flex-grow-1">
                    <h1 class="ludo-page-title mb-1">{{ profile.user.name }}</h1>
                    <p class="ludo-page-sub mb-2">{{ profile.user.email }}</p>
                    <div class="d-flex flex-wrap gap-2">
                        <span class="ludo-stat-pill">{{ profile.user.roleLabel }}</span>
                        <span class="ludo-stat-pill">Баланс: <strong>{{ profile.user.balance }} ₽</strong></span>
                        <span class="ludo-stat-pill">На сайте с {{ formatDate(profile.stats.registeredAt) }}</span>
                    </div>
                </div>
                <div class="d-flex flex-column gap-2 mt-3 mt-md-0">
                    <button class="btn btn-ludo-primary btn-sm" @click="openDeposit">Пополнить</button>
                    <button class="btn btn-ludo-outline btn-sm" :disabled="!withdrawSelection.length" @click="withdrawSkins">
                        Вывести скины ({{ withdrawTotal }} ₽)
                    </button>
                </div>
            </div>

            <div v-if="profile.promos" class="ludo-promo-box mb-4">
                <div class="ludo-section-label mb-2">Промокод на пополнение</div>
                <div class="ludo-promo-row">
                    <span class="ludo-promo-code">{{ profile.promos.rotating.code }}</span>
                    <span class="ludo-promo-bonus">+{{ profile.promos.rotating.bonusPercent }}%</span>
                    <span class="small text-secondary">{{ promoSeconds }}с</span>
                </div>
            </div>

            <div class="ludo-section-label">Статистика</div>
            <div class="row g-3 mb-4">
                <div class="col-6 col-md-4 col-lg-2">
                    <div class="ludo-stat-card text-center">
                        <div class="small text-secondary mb-1">Кейсов открыто</div>
                        <strong>{{ profile.stats.casesOpened }}</strong>
                    </div>
                </div>
                <div class="col-6 col-md-4 col-lg-2">
                    <div class="ludo-stat-card text-center">
                        <div class="small text-secondary mb-1">Апгрейдов</div>
                        <strong>{{ profile.stats.upgradesCount }}</strong>
                        <div class="small text-secondary">{{ profile.stats.upgradeRate }}% успех</div>
                    </div>
                </div>
                <div class="col-6 col-md-4 col-lg-2">
                    <div class="ludo-stat-card text-center">
                        <div class="small text-secondary mb-1">Контрактов</div>
                        <strong>{{ profile.stats.contractsCount }}</strong>
                    </div>
                </div>
                <div class="col-6 col-md-4 col-lg-2">
                    <div class="ludo-stat-card text-center">
                        <div class="small text-secondary mb-1">Пополнено</div>
                        <strong>{{ profile.stats.depositedTotal }} ₽</strong>
                    </div>
                </div>
                <div class="col-6 col-md-4 col-lg-2">
                    <div class="ludo-stat-card text-center">
                        <div class="small text-secondary mb-1">Выведено скинов</div>
                        <strong>{{ profile.stats.withdrawTotal }} ₽</strong>
                    </div>
                </div>
                <div class="col-6 col-md-4 col-lg-2">
                    <div class="ludo-stat-card text-center">
                        <div class="small text-secondary mb-1">Инвентарь</div>
                        <strong>{{ profile.stats.inventoryValue }} ₽</strong>
                        <div class="small text-secondary">{{ profile.stats.inventoryCount }} скинов</div>
                    </div>
                </div>
            </div>

            <div class="row g-4 mb-4">
                <div class="col-lg-8">
                    <div class="d-flex justify-content-between align-items-center mb-3 flex-wrap gap-2">
                        <div class="ludo-section-label mb-0">Мои скины ({{ profile.inventory.length }})</div>
                        <div class="d-flex align-items-center gap-2">
                            <span v-if="withdrawSelection.length" class="small text-secondary">
                                К выводу: {{ withdrawSelection.length }} · {{ withdrawTotal }} ₽
                            </span>
                            <button
                                v-if="profile.inventory.length"
                                class="btn btn-ludo-outline btn-sm"
                                :disabled="sellingAll"
                                @click="sellAllItems"
                            >
                                {{ sellingAll ? '...' : 'Продать все (' + profile.stats.inventoryValue + ' ₽)' }}
                            </button>
                        </div>
                    </div>
                    <div v-if="profile.inventory.length === 0" class="ludo-alert ludo-alert-info">
                        Инвентарь пуст — открой кейс на главной
                    </div>
                    <div v-else class="row g-2">
                        <div v-for="item in profile.inventory" :key="item.id" class="col-4 col-md-3">
                            <div
                                class="ludo-skin-card"
                                :class="{ 'ludo-card-selected': withdrawIds.includes(item.id) }"
                                @click="toggleWithdraw(item.id)"
                            >
                                <img :src="skinImg(item.skin.image)" :alt="item.skin.name" @error="imgError($event, 'skin')" />
                                <div class="small fw-semibold mt-1">{{ item.skin.weapon }}</div>
                                <div class="small text-secondary">{{ item.skin.name }}</div>
                                <div class="ludo-case-price small">{{ item.skin.price }} ₽</div>
                                <button class="btn btn-ludo-outline btn-sm w-100 mt-1" @click.stop="sellItem(item.id)">Продать</button>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="col-lg-4">
                    <div class="ludo-section-label">Заявки на вывод</div>
                    <div class="ludo-live-panel mb-3" style="position: static; max-height: 260px">
                        <div v-if="!profile.skinWithdrawals.length" class="text-secondary small text-center py-3">Нет заявок</div>
                        <div v-for="w in profile.skinWithdrawals" :key="w.id" class="ludo-drop-item">
                            <div class="flex-grow-1">
                                <div class="ludo-drop-skin">{{ w.weapon }} | {{ w.skinName }}</div>
                                <div class="ludo-drop-case">{{ w.skinPrice }} ₽ · {{ w.statusLabel || w.status }}</div>
                            </div>
                        </div>
                    </div>

                    <button class="btn btn-ludo-outline btn-sm w-100" @click="showTransactions = !showTransactions">
                        {{ showTransactions ? 'Скрыть операции' : 'История операций' }}
                    </button>
                    <div v-if="showTransactions" class="ludo-table-wrap mt-3">
                        <div class="table-responsive">
                            <table class="table ludo-table table-sm mb-0">
                                <thead>
                                    <tr><th>Операция</th><th class="text-end">Сумма</th></tr>
                                </thead>
                                <tbody>
                                    <tr v-if="!profile.transactions.length">
                                        <td colspan="2" class="text-secondary text-center py-3">Нет операций</td>
                                    </tr>
                                    <tr v-for="tx in profile.transactions" :key="tx.id">
                                        <td>
                                            <div class="small">{{ txType[tx.type] || tx.type }}</div>
                                            <div class="small text-secondary">{{ tx.description }}</div>
                                        </td>
                                        <td class="text-end fw-semibold" :class="tx.amount > 0 ? 'text-success' : 'text-danger'">
                                            {{ tx.amount > 0 ? '+' : '' }}{{ tx.amount }} ₽
                                        </td>
                                    </tr>
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>

            <div class="ludo-section-label mb-3">История дропов</div>
            <div v-if="!profile.skinHistory.length" class="ludo-alert ludo-alert-info">Пока нет открытий</div>
            <div v-else class="row g-3">
                <div v-for="entry in profile.skinHistory" :key="entry.id" class="col-6 col-md-4 col-lg-3 col-xl-2">
                    <div class="ludo-history-card" :class="'ludo-history-' + entry.status">
                        <div class="ludo-history-card-img">
                            <img v-if="entry.skin" :src="skinImg(entry.skin.image)" @error="imgError($event, 'skin')" />
                        </div>
                        <div class="ludo-history-card-body">
                            <div class="small fw-semibold text-truncate">{{ entry.weapon }}</div>
                            <div class="small text-secondary text-truncate">{{ entry.skinName }}</div>
                            <div class="ludo-case-price small">{{ entry.skinPrice }} ₽</div>
                            <span class="ludo-history-badge">{{ entry.statusLabel }}</span>
                            <div v-if="entry.caseName" class="small text-secondary text-truncate mt-1">{{ entry.caseName }}</div>
                        </div>
                    </div>
                </div>
            </div>
        </template>

        <div v-else class="text-center py-5 text-secondary">Загрузка профиля...</div>
    </div>
</template>

<script>
export default {
    name: 'UserPage',
    props: ['changePage', 'datasend', 'datasendJson', 'user', 'getUser', 'openDeposit', 'skinImg', 'imgError', 'sourceLabel', 'txType'],
    data() {
        return {
            profile: null,
            withdrawIds: [],
            promoSeconds: 0,
            promoInterval: null,
            showTransactions: false,
            uploadingAvatar: false,
            sellingAll: false,
        };
    },
    computed: {
        initials() {
            if (!this.profile) return '??';
            const parts = this.profile.user.name.trim().split(/[_\s]+/);
            if (parts.length >= 2) return (parts[0][0] + parts[1][0]).toUpperCase();
            return this.profile.user.name.substring(0, 2).toUpperCase();
        },
        withdrawSelection() {
            if (!this.profile) return [];
            return this.profile.inventory.filter((item) => this.withdrawIds.includes(item.id));
        },
        withdrawTotal() {
            return this.withdrawSelection.reduce(function (sum, item) {
                return sum + item.skin.price;
            }, 0);
        },
    },
    mounted() {
        if (this.user) {
            this.loadProfile();
        }
        this.promoInterval = setInterval(() => this.syncPromoTimer(), 1000);
    },
    unmounted() {
        clearInterval(this.promoInterval);
    },
    watch: {
        user(val) {
            if (val) {
                this.loadProfile();
            }
        },
    },
    methods: {
        loadProfile() {
            this.datasend('user/profile').then((result) => {
                if (result) {
                    this.profile = result;
                    this.syncPromoTimer();
                }
            });
        },
        syncPromoTimer() {
            if (!this.profile || !this.profile.promos || !this.profile.promos.rotating) {
                return;
            }
            this.promoSeconds = Math.max(0, this.profile.promos.rotating.expiresAt - Math.floor(Date.now() / 1000));
        },
        formatDate(iso) {
            if (!iso) return '—';
            return new Date(iso).toLocaleString('ru-RU', {
                day: '2-digit',
                month: '2-digit',
                year: 'numeric',
            });
        },
        uploadAvatar(event) {
            const file = event.target.files && event.target.files[0];
            if (!file) {
                return;
            }
            this.uploadingAvatar = true;
            let fd = new FormData();
            fd.append('avatar', file);
            this.datasend('user/avatar', 'POST', fd).then((result) => {
                this.uploadingAvatar = false;
                if (result && result.avatar !== undefined) {
                    this.profile.user.avatar = result.avatar;
                    this.getUser();
                }
            });
        },
        removeAvatar() {
            this.datasend('user/avatar', 'DELETE').then((result) => {
                if (result) {
                    this.profile.user.avatar = null;
                    this.getUser();
                }
            });
        },
        toggleWithdraw(id) {
            const index = this.withdrawIds.indexOf(id);
            if (index >= 0) {
                this.withdrawIds.splice(index, 1);
            } else {
                this.withdrawIds.push(id);
            }
        },
        sellItem(id) {
            if (!confirm('Продать скин за его стоимость?')) return;
            this.datasend('inventory/' + id + '/sell', 'POST', new FormData()).then(() => {
                this.getUser();
                this.loadProfile();
            });
        },
        sellAllItems() {
            if (!this.profile || !this.profile.inventory.length) return;
            const total = this.profile.stats.inventoryValue;
            const count = this.profile.inventory.length;
            if (!confirm('Продать все ' + count + ' скин(ов) за ' + total + ' ₽?')) return;
            this.sellingAll = true;
            this.datasend('inventory/sell-all', 'POST', new FormData()).then((result) => {
                this.sellingAll = false;
                if (!result) return;
                this.withdrawIds = [];
                this.getUser();
                this.loadProfile();
            });
        },
        withdrawSkins() {
            if (!this.withdrawIds.length) return;
            if (!confirm('Создать заявку на вывод ' + this.withdrawIds.length + ' скин(ов) на ' + this.withdrawTotal + ' ₽? Скины уйдут на проверку админу.')) {
                return;
            }
            this.datasendJson('inventory/withdraw', 'POST', {
                inventory_item_ids: this.withdrawIds,
            }).then((result) => {
                if (!result) return;
                alert(result.message || 'Заявка отправлена');
                this.withdrawIds = [];
                this.getUser();
                this.loadProfile();
            });
        },
    },
};
</script>
