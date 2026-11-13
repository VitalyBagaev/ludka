<template>
    <div class="container-fluid px-lg-4 ludo-page">
        <div class="ludo-hero ludo-hero-upgraded mb-4">
            <div class="row align-items-center g-4">
                <div class="col-lg-7">
                    <div class="d-inline-flex align-items-center gap-2 px-3 py-1 rounded-pill mb-3" style="background: rgba(240, 180, 41, 0.12); border: 1px solid rgba(240, 180, 41, 0.3); font-size: 12px; color: #f0b429;">
                        <span>🔥</span>
                        <span class="fw-semibold">Обновлённый дроп CS2 · Моментальный вывод</span>
                    </div>
                    <h1 class="mb-2">Открывай кейсы на <em>Лудомании</em></h1>
                    <p class="mb-3">CS2 скины, битвы, апгрейд и контракты — испытай удачу и забери топ-дроп.</p>
                    <div class="d-flex flex-wrap align-items-center gap-2">
                        <span class="ludo-stat-pill">Кейсов: <strong>{{ cases.length }}</strong></span>
                        <button type="button" class="btn btn-ludo-primary btn-sm" @click="openRandomCase">🎲 Мне повезёт</button>
                        <button type="button" class="btn btn-ludo-outline btn-sm" @click="changePage('SecretCasePage')">🎁 Бесплатный кейс</button>
                        <button v-if="user" type="button" class="btn btn-ludo-outline btn-sm" @click="openDeposit">+ Пополнить</button>
                    </div>
                </div>
                <div class="col-lg-5">
                    <div v-if="promoInfo && promoInfo.rotating" class="ludo-promo-box mb-0" style="box-shadow: 0 8px 32px rgba(0, 0, 0, 0.35);">
                        <div class="d-flex justify-content-between align-items-center mb-2">
                            <span class="small text-secondary">Промокод дня</span>
                            <span class="badge bg-dark text-warning border border-warning-subtle">⏳ {{ promoSeconds }}с</span>
                        </div>
                        <div class="ludo-promo-row justify-content-between">
                            <div class="d-flex align-items-center gap-2">
                                <span class="ludo-promo-code">{{ promoInfo.rotating.code }}</span>
                                <span class="ludo-promo-bonus">+{{ promoInfo.rotating.bonusPercent }}%</span>
                            </div>
                            <button type="button" class="btn btn-sm" :class="copied ? 'btn-success' : 'btn-ludo-primary'" @click="copyPromo">
                                {{ copied ? 'Скопировано ✓' : 'Забрать' }}
                            </button>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <div v-if="topDrops.length" class="mb-4">
            <div class="ludo-section-label mb-2">🏆 Топ дропы дня</div>
            <div class="row g-2">
                <div v-for="drop in topDrops" :key="'top-' + drop.id" class="col-6 col-md-3">
                    <div class="ludo-top-drop-card d-flex align-items-center gap-2 p-2 rounded-3" :class="rarityClass ? rarityClass[drop.skin?.rarity] : ''" style="cursor: pointer;" @click="drop.userId && changePage('PlayerPage', drop.userId)">
                        <img :src="skinImg(drop.skin?.image)" :alt="drop.skin?.name" style="width: 54px; height: 42px; object-fit: contain;" @error="imgError($event, 'skin')" />
                        <div class="min-w-0 flex-grow-1">
                            <div class="small fw-semibold text-truncate">{{ drop.skin?.weapon }} | {{ drop.skin?.name }}</div>
                            <div class="d-flex justify-content-between align-items-center">
                                <span class="small text-secondary text-truncate" style="max-width: 90px;">{{ drop.user }}</span>
                                <span class="ludo-case-price small mb-0">{{ drop.skin?.price }} ₽</span>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="ludo-filter-tabs mb-3">
            <button v-for="tab in filterTabs" :key="tab.key" class="ludo-filter-tab" :class="{ active: category === tab.key }" @click="setCategory(tab.key)">
                {{ tab.label }}
            </button>
        </div>

        <div class="row g-2 align-items-center mb-4">
            <div class="col-md-5 col-lg-4">
                <input v-model.trim="searchQuery" type="text" class="form-control form-control-sm ludo-form-control" placeholder="Поиск кейса по названию..." />
            </div>
            <div class="col-sm-6 col-md-4 col-lg-3">
                <select v-model="sortBy" class="form-select form-select-sm ludo-form-control">
                    <option value="price_desc">Сначала дорогие</option>
                    <option value="price_asc">Сначала дешёвые</option>
                    <option value="name_asc">По названию (А–Я)</option>
                </select>
            </div>
            <div class="col-sm-6 col-md-3 col-lg-5 d-flex flex-wrap align-items-center justify-content-md-end gap-2">
                <button v-if="user" type="button" class="btn btn-sm" :class="onlyAffordable ? 'btn-ludo-primary' : 'btn-ludo-outline'" @click="onlyAffordable = !onlyAffordable">
                    Хватает баланса ({{ userInfo?.balance ?? 0 }} ₽)
                </button>
                <button v-if="hasActiveFilters" type="button" class="btn btn-sm btn-ludo-outline" @click="resetFilters">Сбросить</button>
            </div>
        </div>
        <div v-if="showFeaturedBlock" class="ludo-home-section mb-2">
            <div class="ludo-section-label mb-3">Горячие кейсы</div>
            <div class="row g-3 mb-4">
                <div v-for="item in featuredCases" :key="'hot-' + item.id" class="col-6 col-md-4 col-lg-3 col-xl-2">
                    <div class="ludo-case-card ludo-card-featured" :class="caseTierClass(item.price)" @click="openCase(item.id)">
                        <div class="ludo-case-img-wrap">
                            <img :src="caseImg(item.image)" :alt="item.name" @error="imgError($event, 'case')" />
                        </div>
                        <div class="ludo-case-card-body">
                            <div class="ludo-case-name">{{ item.name }}</div>
                            <div v-if="item.skins_count" class="small text-secondary">{{ item.skins_count }} скинов</div>
                            <div class="d-flex justify-content-center align-items-center gap-2 mt-1">
                                <span class="ludo-case-price mb-0">{{ item.price }} ₽</span>
                                <span class="ludo-case-badge">Хит</span>
                            </div>
                            <div class="d-flex gap-1 mt-1">
                                <button class="btn btn-ludo-primary btn-sm flex-grow-1 ludo-case-card-btn">Открыть</button>
                                <button type="button" class="btn btn-ludo-outline btn-sm ludo-case-card-btn" title="Состав" @click.stop="previewCase(item.id)">👁</button>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div v-if="visibleSections.length === 0" class="ludo-card p-5 text-center my-4">
            <div class="fw-semibold mb-2">По вашему запросу кейсов не найдено</div>
            <p class="small text-secondary mb-3">Попробуйте изменить параметры поиска или сбросить фильтры.</p>
            <button type="button" class="btn btn-ludo-primary btn-sm" @click="resetFilters">Сбросить фильтры</button>
        </div>

        <div v-for="section in visibleSections" :key="section.key" class="ludo-home-section" :id="'cases-' + section.key">
            <div class="ludo-section-label mb-3">{{ section.label }} <span class="text-secondary fw-normal ms-1">({{ section.cases.length }})</span></div>
            <div class="row g-3 mb-4">
                <div v-for="item in section.cases" :key="item.id" class="col-6 col-md-4 col-lg-3 col-xl-2">
                    <div class="ludo-case-card" :class="[{ 'ludo-card-featured': item.featured }, caseTierClass(item.price)]" @click="openCase(item.id)">
                        <div class="ludo-case-img-wrap">
                            <img :src="caseImg(item.image)" :alt="item.name" @error="imgError($event, 'case')" />
                        </div>
                        <div class="ludo-case-card-body">
                            <div class="ludo-case-name">{{ item.name }}</div>
                            <div v-if="item.skins_count" class="small text-secondary">{{ item.skins_count }} скинов</div>
                            <div class="d-flex justify-content-center align-items-center gap-2 mt-1">
                                <span class="ludo-case-price mb-0">{{ item.price }} ₽</span>
                                <span v-if="item.featured" class="ludo-case-badge">Хит</span>
                            </div>
                            <div class="d-flex gap-1 mt-1">
                                <button class="btn btn-ludo-primary btn-sm flex-grow-1 ludo-case-card-btn">Открыть</button>
                                <button type="button" class="btn btn-ludo-outline btn-sm ludo-case-card-btn" title="Состав" @click.stop="previewCase(item.id)">👁</button>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <Teleport to="body">
            <div v-if="previewOpen" class="ludo-modal-backdrop" @click.self="closePreview">
                <div class="ludo-modal" style="max-width: 720px; width: 95%">
                    <div class="ludo-modal-header">
                        <h2 class="h5 mb-0">{{ previewData ? previewData.name : 'Загрузка...' }}</h2>
                        <button type="button" class="btn btn-sm btn-ludo-outline" @click="closePreview">×</button>
                    </div>
                    <div class="p-4">
                        <div v-if="previewLoading" class="text-center py-4 text-secondary">Загружаем содержимое кейса...</div>
                        <div v-else-if="previewData">
                            <div class="d-flex justify-content-between align-items-center mb-3">
                                <span class="small text-secondary">Скинов внутри: {{ previewData.items ? previewData.items.length : 0 }}</span>
                                <button type="button" class="btn btn-ludo-primary btn-sm" @click="openFromPreview">Открыть за {{ previewData.price }} ₽</button>
                            </div>
                            <div class="row g-2" style="max-height: 360px; overflow-y: auto">
                                <div v-for="skin in previewData.items" :key="skin.id" class="col-4 col-sm-3">
                                    <div class="ludo-skin-card text-center" :class="rarityClass ? rarityClass[skin.rarity] : ''">
                                        <img :src="skinImg(skin.image)" :alt="skin.name" style="height: 48px" @error="imgError($event, 'skin')" />
                                        <div class="small fw-semibold text-truncate mt-1">{{ skin.weapon }}</div>
                                        <div class="small text-secondary text-truncate">{{ skin.name }}</div>
                                        <div class="ludo-case-price small">{{ skin.price }} ₽</div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </Teleport>
    </div>
</template>
<script>
import { categoryLabels, categoryOrder } from '../helpers/game.js';

export default {
    name: 'HomePage',
    props: ['changePage', 'datasend', 'user', 'userInfo', 'getUser', 'caseImg', 'skinImg', 'rarityClass', 'imgError', 'promoInfo', 'openDeposit', 'liveDrops'],
    data() {
        return {
            cases: [],
            category: 'all',
            searchQuery: '',
            sortBy: 'price_desc',
            onlyAffordable: false,
            promoSeconds: 0,
            promoTimer: null,
            copied: false,
            previewOpen: false,
            previewLoading: false,
            previewData: null,
        };
    },
    mounted() {
        this.getCases();
        this.syncPromoTimer();
        this.promoTimer = setInterval(() => this.syncPromoTimer(), 1000);
    },
    unmounted() {
        clearInterval(this.promoTimer);
    },
    watch: {
        promoInfo() {
            this.syncPromoTimer();
        },
        user(val) {
            if (!val) this.onlyAffordable = false;
        },
    },
    methods: {
        getCases() {
            this.datasend('cases').then((result) => {
                if (result) this.cases = result;
            });
        },
        openRandomCase() {
            const pool = this.filteredCases.length ? this.filteredCases : this.cases;
            if (!pool.length) return;
            const random = pool[Math.floor(Math.random() * pool.length)];
            this.openCase(random.id);
        },
        caseTierClass(price) {
            const p = Number(price) || 0;
            if (p >= 2000) return 'tier-gold';
            if (p >= 1000) return 'tier-red';
            if (p >= 400) return 'tier-purple';
            return 'tier-blue';
        },
        previewCase(id) {
            this.previewOpen = true;
            this.previewLoading = true;
            this.previewData = null;
            this.datasend('cases/' + id).then((result) => {
                this.previewLoading = false;
                if (result && !result.message) this.previewData = result;
            });
        },
        closePreview() {
            this.previewOpen = false;
            this.previewData = null;
        },
        openFromPreview() {
            const id = this.previewData?.id;
            this.closePreview();
            if (id) this.openCase(id);
        },
        syncPromoTimer() {
            if (!this.promoInfo?.rotating?.expiresAt) {
                this.promoSeconds = 0;
                return;
            }
            this.promoSeconds = Math.max(0, this.promoInfo.rotating.expiresAt - Math.floor(Date.now() / 1000));
        },
        copyPromo() {
            const code = this.promoInfo?.rotating?.code;
            if (!code) return;
            navigator.clipboard?.writeText(code);
            this.copied = true;
            setTimeout(() => { this.copied = false; }, 2000);
        },
        setCategory(key) {
            this.category = key;
            if (key !== 'all') {
                this.$nextTick(() => {
                    document.getElementById('cases-' + key)?.scrollIntoView({ behavior: 'smooth', block: 'start' });
                });
            }
        },
        resetFilters() {
            this.category = 'all';
            this.searchQuery = '';
            this.sortBy = 'price_desc';
            this.onlyAffordable = false;
        },
        openCase(id) {
            this.changePage('CasePage', id);
        },
        sortCases(list) {
            const copy = [...list];
            if (this.sortBy === 'price_asc') return copy.sort((a, b) => a.price - b.price || a.name.localeCompare(b.name));
            if (this.sortBy === 'name_asc') return copy.sort((a, b) => a.name.localeCompare(b.name));
            return copy.sort((a, b) => b.price - a.price || a.name.localeCompare(b.name));
        },
    },
    computed: {
        topDrops() {
            if (!Array.isArray(this.liveDrops) || !this.liveDrops.length) return [];
            return [...this.liveDrops]
                .filter((d) => d && d.skin)
                .sort((a, b) => (b.skin?.price || 0) - (a.skin?.price || 0))
                .slice(0, 4);
        },
        hasActiveFilters() {
            return this.category !== 'all' || this.searchQuery !== '' || this.sortBy !== 'price_desc' || this.onlyAffordable;
        },
        filteredCases() {
            const q = this.searchQuery.toLowerCase();
            const balance = Number(this.userInfo?.balance ?? 0);
            return this.cases.filter((item) => {
                if (q && !item.name.toLowerCase().includes(q)) return false;
                if (this.onlyAffordable && item.price > balance) return false;
                return true;
            });
        },
        featuredCases() {
            return this.sortCases(this.filteredCases.filter((item) => item.featured));
        },
        showFeaturedBlock() {
            return this.category === 'all' && !this.searchQuery && !this.onlyAffordable && this.featuredCases.length > 0;
        },
        filterTabs() {
            return [{ key: 'all', label: categoryLabels.all }, ...categoryOrder.map((key) => ({ key, label: categoryLabels[key] }))];
        },
        categorySections() {
            return categoryOrder
                .map((key) => ({ key, label: categoryLabels[key], cases: this.sortCases(this.filteredCases.filter((item) => item.category === key)) }))
                .filter((section) => section.cases.length > 0);
        },
        visibleSections() {
            return this.category === 'all' ? this.categorySections : this.categorySections.filter((section) => section.key === this.category);
        },
    },
};
</script>

<style scoped>
.ludo-hero-upgraded {
    background: radial-gradient(circle at 85% 20%, rgba(240, 180, 41, 0.14), transparent 55%),
                linear-gradient(135deg, rgba(24, 24, 34, 0.95), rgba(16, 16, 24, 0.98));
    border: 1px solid rgba(240, 180, 41, 0.22);
    box-shadow: 0 12px 40px rgba(0, 0, 0, 0.35);
}
.ludo-top-drop-card {
    background: rgba(255, 255, 255, 0.03);
    border: 1px solid rgba(255, 255, 255, 0.08);
    transition: transform 0.2s ease, border-color 0.2s ease, box-shadow 0.2s ease;
}
.ludo-top-drop-card:hover {
    transform: translateY(-2px);
    border-color: rgba(240, 180, 41, 0.45);
    box-shadow: 0 6px 18px rgba(0, 0, 0, 0.3);
}
.ludo-case-card {
    transition: transform 0.25s ease, box-shadow 0.25s ease, border-color 0.25s ease;
}
.ludo-case-card:hover {
    transform: translateY(-5px);
}
.ludo-case-card.tier-blue:hover {
    border-color: rgba(75, 105, 255, 0.6);
    box-shadow: 0 10px 28px rgba(75, 105, 255, 0.18);
}
.ludo-case-card.tier-purple:hover {
    border-color: rgba(136, 71, 255, 0.65);
    box-shadow: 0 10px 28px rgba(136, 71, 255, 0.22);
}
.ludo-case-card.tier-red:hover {
    border-color: rgba(235, 75, 75, 0.65);
    box-shadow: 0 10px 28px rgba(235, 75, 75, 0.22);
}
.ludo-case-card.tier-gold:hover {
    border-color: rgba(240, 180, 41, 0.75);
    box-shadow: 0 10px 32px rgba(240, 180, 41, 0.28);
}
</style>
