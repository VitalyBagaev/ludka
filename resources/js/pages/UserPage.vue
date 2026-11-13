<template>
    <div class="container-fluid px-lg-4 ludo-page">
        <div v-if="!user" class="ludo-alert ludo-alert-warning text-center mx-auto" style="max-width: 480px">
            Нужно <a href="#" class="text-warning" @click.prevent="changePage('AuthPage')">войти</a> в аккаунт
        </div>

        <template v-else-if="profile">
            <div class="ludo-hero ludo-profile-hero mb-4">
                <div class="d-flex flex-wrap align-items-center gap-4">
                    <div class="ludo-profile-avatar-wrap text-center">
                        <div class="ludo-profile-avatar mx-auto" :class="{ 'ludo-profile-avatar-img': profile.user.avatar }">
                            <img v-if="profile.user.avatar" :src="profile.user.avatar" alt="avatar" />
                            <span v-else>{{ initials }}</span>
                        </div>
                        <div class="ludo-avatar-actions mt-2 d-flex justify-content-center gap-1">
                            <label class="btn btn-sm btn-ludo-outline mb-0">
                                {{ uploadingAvatar ? '...' : '📷 Фото' }}
                                <input type="file" accept="image/*" class="d-none" @change="uploadAvatar" />
                            </label>
                            <button v-if="profile.user.avatar" type="button" class="btn btn-sm btn-outline-danger" @click="removeAvatar">×</button>
                        </div>
                    </div>

                    <div class="flex-grow-1 min-w-0">
                        <div class="d-flex flex-wrap align-items-center gap-2 mb-1">
                            <h1 class="ludo-page-title mb-0">{{ profile.user.name }}</h1>
                            <span class="badge bg-warning text-dark">{{ profile.user.roleLabel }}</span>
                        </div>
                        <p class="ludo-page-sub mb-3">{{ profile.user.email }} · На сайте с {{ formatDate(profile.stats.registeredAt) }}</p>
                        <div class="d-flex flex-wrap align-items-center gap-2">
                            <span class="ludo-stat-pill">Баланс: <strong class="text-warning fs-6">{{ profile.user.balance }} ₽</strong></span>
                            <button type="button" class="btn btn-ludo-primary btn-sm px-3" @click="openDeposit">+ Пополнить баланс</button>
                            <button type="button" class="btn btn-ludo-outline btn-sm px-3" :disabled="!withdrawSelection.length" @click="withdrawSkins">
                                Вывести выбранное ({{ withdrawTotal }} ₽)
                            </button>
                        </div>
                    </div>
                </div>
            </div>

            <div v-if="profile.promos" class="ludo-promo-box mb-4">
                <div class="d-flex justify-content-between align-items-center mb-2">
                    <span class="ludo-section-label mb-0">Промокод на пополнение</span>
                    <span class="small text-secondary">⏳ {{ promoSeconds }}с</span>
                </div>
                <div class="ludo-promo-row justify-content-between">
                    <div class="d-flex align-items-center gap-2">
                        <span class="ludo-promo-code">{{ profile.promos.rotating.code }}</span>
                        <span class="ludo-promo-bonus">+{{ profile.promos.rotating.bonusPercent }}%</span>
                    </div>
                    <button type="button" class="btn btn-sm" :class="copiedPromo ? 'btn-success' : 'btn-ludo-outline'" @click="copyPromo">
                        {{ copiedPromo ? 'Скопировано ✓' : 'Копировать' }}
                    </button>
                </div>
            </div>
            <div class="ludo-section-label mb-2">Статистика аккаунта</div>
            <div class="row g-3 mb-4">
                <div class="col-6 col-md-4 col-lg-2">
                    <div class="ludo-stat-card text-center h-100">
                        <div class="small text-secondary mb-1">Кейсов открыто</div>
                        <strong class="fs-5">{{ profile.stats.casesOpened }}</strong>
                    </div>
                </div>
                <div class="col-6 col-md-4 col-lg-2">
                    <div class="ludo-stat-card text-center h-100">
                        <div class="small text-secondary mb-1">Апгрейдов</div>
                        <strong class="fs-5">{{ profile.stats.upgradesCount }}</strong>
                        <div class="small text-success">{{ profile.stats.upgradeRate }}% успех</div>
                    </div>
                </div>
                <div class="col-6 col-md-4 col-lg-2">
                    <div class="ludo-stat-card text-center h-100">
                        <div class="small text-secondary mb-1">Контрактов</div>
                        <strong class="fs-5">{{ profile.stats.contractsCount }}</strong>
                    </div>
                </div>
                <div class="col-6 col-md-4 col-lg-2">
                    <div class="ludo-stat-card text-center h-100">
                        <div class="small text-secondary mb-1">Пополнено</div>
                        <strong class="fs-5 text-warning">{{ profile.stats.depositedTotal }} ₽</strong>
                    </div>
                </div>
                <div class="col-6 col-md-4 col-lg-2">
                    <div class="ludo-stat-card text-center h-100">
                        <div class="small text-secondary mb-1">Выведено</div>
                        <strong class="fs-5 text-info">{{ profile.stats.withdrawTotal }} ₽</strong>
                    </div>
                </div>
                <div class="col-6 col-md-4 col-lg-2">
                    <div class="ludo-stat-card text-center h-100">
                        <div class="small text-secondary mb-1">Инвентарь</div>
                        <strong class="fs-5 text-success">{{ profile.stats.inventoryValue }} ₽</strong>
                        <div class="small text-secondary">{{ profile.stats.inventoryCount }} шт.</div>
                    </div>
                </div>
            </div>
            <div class="row g-4 mb-4">
                <div class="col-lg-8">
                    <div class="d-flex flex-wrap justify-content-between align-items-center mb-3 gap-2">
                        <div class="ludo-section-label mb-0">Мои скины ({{ filteredInventory.length }})</div>
                        <div class="d-flex flex-wrap align-items-center gap-2">
                            <button v-if="profile.inventory.length" type="button" class="btn btn-ludo-outline btn-sm" @click="toggleSelectAll">
                                {{ withdrawIds.length === profile.inventory.length ? 'Снять выбор' : 'Выбрать все' }}
                            </button>
                            <button v-if="profile.inventory.length" type="button" class="btn btn-ludo-primary btn-sm" :disabled="sellingAll" @click="sellAllItems">
                                {{ sellingAll ? '...' : 'Продать все (' + profile.stats.inventoryValue + ' ₽)' }}
                            </button>
                        </div>
                    </div>

                    <div v-if="profile.inventory.length" class="row g-2 mb-3">
                        <div class="col-sm-6">
                            <input v-model.trim="invSearch" type="text" class="form-control form-control-sm ludo-form-control" placeholder="Поиск по оружию или скину..." />
                        </div>
                        <div class="col-sm-6">
                            <select v-model="invSort" class="form-select form-select-sm ludo-form-control">
                                <option value="newest">Сначала новые</option>
                                <option value="price_desc">Сначала дорогие</option>
                                <option value="price_asc">Сначала дешёвые</option>
                            </select>
                        </div>
                    </div>

                    <div v-if="profile.inventory.length === 0" class="ludo-alert ludo-alert-info">
                        Инвентарь пуст — открой кейс на главной странице!
                    </div>
                    <div v-else class="row g-2">
                        <div v-for="item in filteredInventory" :key="item.id" class="col-6 col-sm-4 col-md-3">
                            <div class="ludo-skin-card h-100 d-flex flex-column justify-content-between" :class="[rarityClass?.[item.skin.rarity], { 'ludo-card-selected': withdrawIds.includes(item.id) }]" @click="toggleWithdraw(item.id)">
                                <div>
                                    <img :src="skinImg(item.skin.image)" :alt="item.skin.name" @error="imgError($event, 'skin')" />
                                    <div class="small fw-semibold mt-1 text-truncate">{{ item.skin.weapon }}</div>
                                    <div class="small text-secondary text-truncate">{{ item.skin.name }}</div>
                                    <div class="ludo-case-price small mt-1">{{ item.skin.price }} ₽</div>
                                </div>
                                <button type="button" class="btn btn-ludo-outline btn-sm w-100 mt-2" @click.stop="sellItem(item.id)">
                                    Продать за {{ item.skin.price }} ₽
                                </button>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="col-lg-4">
                    <div class="ludo-section-label mb-2">Заявки на вывод</div>
                    <div class="ludo-live-panel mb-3" style="position: static; max-height: 280px">
                        <div v-if="!profile.skinWithdrawals.length" class="text-secondary small text-center py-4">Нет активных заявок</div>
                        <div v-for="w in profile.skinWithdrawals" :key="w.id" class="ludo-drop-item d-flex justify-content-between align-items-center">
                            <div class="min-w-0 pe-2">
                                <div class="ludo-drop-skin text-truncate">{{ w.weapon }} | {{ w.skinName }}</div>
                                <div class="ludo-drop-case">{{ w.skinPrice }} ₽</div>
                            </div>
                            <span class="badge rounded-pill" :class="withdrawStatusClass(w.status)">{{ w.statusLabel || w.status }}</span>
                        </div>
                    </div>

                    <button type="button" class="btn btn-ludo-outline btn-sm w-100" @click="showTransactions = !showTransactions">
                        {{ showTransactions ? 'Скрыть историю операций' : 'Показать историю операций' }}
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
                                            <div class="small fw-semibold">{{ txType[tx.type] || tx.type }}</div>
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

            <div class="ludo-section-label mb-3">История последних дропов</div>
            <div v-if="!profile.skinHistory.length" class="ludo-alert ludo-alert-info">Пока нет открытий</div>
            <div v-else class="row g-3">
                <div v-for="entry in profile.skinHistory" :key="entry.id" class="col-6 col-md-4 col-lg-3 col-xl-2">
                    <div class="ludo-history-card h-100" :class="'ludo-history-' + entry.status">
                        <div class="ludo-history-card-img">
                            <img v-if="entry.skin" :src="skinImg(entry.skin.image)" @error="imgError($event, 'skin')" />
                        </div>
                        <div class="ludo-history-card-body">
                            <div class="small fw-semibold text-truncate">{{ entry.weapon }}</div>
                            <div class="small text-secondary text-truncate">{{ entry.skinName }}</div>
                            <div class="ludo-case-price small">{{ entry.skinPrice }} ₽</div>
                            <span class="ludo-history-badge">{{ entry.statusLabel }}</span>
                            <div class="small mt-1 text-truncate" :class="entry.source === 'upgrade' ? 'text-info fw-semibold' : 'text-secondary'">
                                {{ formatSourceBadge(entry) }}
                            </div>
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
    props: ['changePage', 'datasend', 'datasendJson', 'user', 'getUser', 'openDeposit', 'skinImg', 'imgError', 'rarityClass', 'sourceLabel', 'txType'],
    data() {
        return {
            profile: null,
            withdrawIds: [],
            promoSeconds: 0,
            promoInterval: null,
            copiedPromo: false,
            showTransactions: false,
            uploadingAvatar: false,
            sellingAll: false,
            invSearch: '',
            invSort: 'newest',
        };
    },
    computed: {
        initials() {
            if (!this.profile) return '??';
            const parts = this.profile.user.name.trim().split(/[_\s]+/);
            if (parts.length >= 2) return (parts.at(0).charAt(0) + parts.at(1).charAt(0)).toUpperCase();
            return this.profile.user.name.substring(0, 2).toUpperCase();
        },
        filteredInventory() {
            if (!this.profile?.inventory) return [];
            const q = this.invSearch.toLowerCase();
            let list = [...this.profile.inventory];
            if (q) {
                list = list.filter((i) => i.skin.name.toLowerCase().includes(q) || i.skin.weapon.toLowerCase().includes(q));
            }
            if (this.invSort === 'price_desc') return list.sort((a, b) => b.skin.price - a.skin.price);
            if (this.invSort === 'price_asc') return list.sort((a, b) => a.skin.price - b.skin.price);
            return list;
        },
        withdrawSelection() {
            if (!this.profile) return [];
            return this.profile.inventory.filter((item) => this.withdrawIds.includes(item.id));
        },
        withdrawTotal() {
            return this.withdrawSelection.reduce((sum, item) => sum + item.skin.price, 0);
        },
    },
    mounted() {
        if (this.user) this.loadProfile();
        this.promoInterval = setInterval(() => this.syncPromoTimer(), 1000);
    },
    unmounted() {
        clearInterval(this.promoInterval);
    },
    watch: {
        user(val) { if (val) this.loadProfile(); },
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
            if (!this.profile?.promos?.rotating?.expiresAt) return;
            this.promoSeconds = Math.max(0, this.profile.promos.rotating.expiresAt - Math.floor(Date.now() / 1000));
        },
        copyPromo() {
            const code = this.profile?.promos?.rotating?.code;
            if (!code) return;
            navigator.clipboard?.writeText(code);
            this.copiedPromo = true;
            setTimeout(() => { this.copiedPromo = false; }, 2000);
        },
        withdrawStatusClass(status) {
            return {
                pending: 'bg-warning text-dark',
                processing: 'bg-info text-dark',
                completed: 'bg-success',
                cancelled: 'bg-secondary',
            }[status] || 'bg-secondary';
        },
        formatDate(iso) {
            if (!iso) return '—';
            return new Date(iso).toLocaleString('ru-RU', { day: '2-digit', month: '2-digit', year: 'numeric' });
        },
        formatSourceBadge(entry) {
            if (entry.source === 'upgrade') return '⚡ ' + (entry.caseName || 'Апгрейд');
            if (entry.source === 'contract') return '📜 Контракт';
            if (entry.source === 'battle') return '⚔️ Битва кейсов';
            if (entry.source === 'secret_case') return '🎁 Секретный кейс';
            return entry.caseName ? '📦 ' + entry.caseName : '📦 Кейс';
        },
        uploadAvatar(event) {
            const file = event.target.files ? event.target.files.item(0) : null;
            if (!file) return;
            this.uploadingAvatar = true;
            const fd = new FormData();
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
        toggleSelectAll() {
            if (!this.profile?.inventory?.length) return;
            if (this.withdrawIds.length === this.profile.inventory.length) {
                this.withdrawIds = [];
            } else {
                this.withdrawIds = this.profile.inventory.map((i) => i.id);
            }
        },
        toggleWithdraw(id) {
            const index = this.withdrawIds.indexOf(id);
            if (index >= 0) this.withdrawIds.splice(index, 1);
            else this.withdrawIds.push(id);
        },
        sellItem(id) {
            if (!confirm('Продать скин за его стоимость?')) return;
            this.datasend('inventory/' + id + '/sell', 'POST', new FormData()).then(() => {
                this.withdrawIds = this.withdrawIds.filter((item) => item !== id);
                this.getUser();
                this.loadProfile();
            });
        },
        sellAllItems() {
            if (!this.profile?.inventory?.length) return;
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
            if (!confirm('Создать заявку на вывод ' + this.withdrawIds.length + ' скин(ов) на ' + this.withdrawTotal + ' ₽?')) return;
            this.datasendJson('inventory/withdraw', 'POST', { inventory_item_ids: this.withdrawIds }).then((result) => {
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

<style scoped>
.ludo-profile-hero {
    background: radial-gradient(circle at 85% 20%, rgba(34, 197, 94, 0.14), transparent 55%),
                linear-gradient(135deg, rgba(24, 24, 34, 0.95), rgba(16, 16, 24, 0.98));
    border: 1px solid rgba(34, 197, 94, 0.25);
}
</style>
