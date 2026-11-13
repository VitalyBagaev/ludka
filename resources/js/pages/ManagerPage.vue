<template>
    <div class="container-fluid px-lg-4 ludo-page">
        <div class="d-flex flex-wrap justify-content-between align-items-center gap-2 mb-4">
            <div>
                <h1 class="ludo-page-title mb-1">Панель менеджера</h1>
                <p class="ludo-page-sub mb-0">Аналитика активности, витрина «Хит»-кейсов и мониторинг дропов</p>
            </div>
            <button type="button" class="btn btn-ludo-outline btn-sm" @click="refreshAll">↻ Обновить данные</button>
        </div>

        <div class="row g-3 mb-4">
            <div class="col-6 col-md-3">
                <div class="ludo-stat-card text-center h-100">
                    <div class="small text-secondary mb-1">Игроков</div>
                    <strong class="fs-4">{{ stats.totalUsers ?? 0 }}</strong>
                </div>
            </div>
            <div class="col-6 col-md-3">
                <div class="ludo-stat-card text-center h-100">
                    <div class="small text-secondary mb-1">Кейсов / Хит</div>
                    <strong class="fs-4 text-warning">{{ stats.totalCases ?? 0 }} / {{ stats.featuredCases ?? 0 }}</strong>
                </div>
            </div>
            <div class="col-6 col-md-3">
                <div class="ludo-stat-card text-center h-100">
                    <div class="small text-secondary mb-1">Открытий сегодня</div>
                    <strong class="fs-4 text-success">{{ stats.opensToday ?? 0 }}</strong>
                </div>
            </div>
            <div class="col-6 col-md-3">
                <div class="ludo-stat-card text-center h-100">
                    <div class="small text-secondary mb-1">Общий баланс игроков</div>
                    <strong class="fs-4 text-info">{{ stats.totalBalance ?? 0 }} ₽</strong>
                </div>
            </div>
        </div>

        <div class="row g-4">
            <div class="col-lg-6">
                <div class="d-flex justify-content-between align-items-center gap-2 mb-3">
                    <div class="ludo-section-label mb-0">Витрина кейсов ({{ filteredCases.length }})</div>
                    <input v-model.trim="caseSearch" type="text" class="form-control form-control-sm ludo-form-control" placeholder="Поиск кейса..." style="width: 180px;" />
                </div>
                <div class="d-flex flex-column gap-2" style="max-height: 520px; overflow-y: auto;">
                    <div v-for="c in filteredCases" :key="c.id" class="ludo-battle-card p-3">
                        <div class="d-flex align-items-center gap-2">
                            <span class="fw-semibold">{{ c.name }}</span>
                            <span class="ludo-case-price small mb-0">{{ c.price }} ₽</span>
                        </div>
                        <button class="btn btn-sm" :class="c.featured ? 'btn-ludo-primary' : 'btn-ludo-outline'" @click="toggleHot(c.id)">
                            {{ c.featured ? '🔥 В топе (Хит)' : 'Сделать Хит' }}
                        </button>
                    </div>
                </div>
            </div>
            <div class="col-lg-6">
                <div class="ludo-section-label mb-3">Последние выпадения ({{ drops.length }})</div>
                <div class="ludo-live-panel" style="position: static; max-height: 520px; overflow-y: auto;">
                    <div v-if="!drops.length" class="text-center text-secondary py-4 small">Нет данных о дропах</div>
                    <div v-for="d in drops" :key="d.id" class="ludo-drop-item d-flex align-items-center gap-3">
                        <UserAvatar :avatar="d.avatar" :name="d.user" :initials="d.initials" size="md" />
                        <img v-if="d.skin" class="ludo-drop-skin-thumb" :src="skinImg(d.skin.image)" @error="imgError($event, 'skin')" />
                        <div class="min-w-0 flex-grow-1">
                            <div class="ludo-drop-user text-truncate">{{ d.user }} <span class="text-secondary fw-normal">· {{ d.caseName }}</span></div>
                            <div class="ludo-drop-skin text-truncate">{{ d.skin?.weapon }} | {{ d.skin?.name }} <span class="text-warning">({{ d.skin?.price }} ₽)</span></div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</template>
<script>
import UserAvatar from '../components/UserAvatar.vue';

export default {
    name: 'ManagerPage',
    components: { UserAvatar },
    props: ['datasend', 'skinImg', 'imgError'],
    data() {
        return {
            stats: {},
            cases: [],
            drops: [],
            caseSearch: '',
        };
    },
    computed: {
        filteredCases() {
            const q = this.caseSearch.toLowerCase();
            if (!q) return this.cases;
            return this.cases.filter((c) => c.name.toLowerCase().includes(q));
        },
    },
    mounted() {
        this.refreshAll();
    },
    methods: {
        refreshAll() {
            this.getStats();
            this.getCases();
            this.getDrops();
        },
        getStats() {
            this.datasend('manager/stats').then((result) => { if (result) this.stats = result; });
        },
        getCases() {
            this.datasend('manager/cases').then((result) => { if (result) this.cases = result; });
        },
        getDrops() {
            this.datasend('manager/live-drops').then((result) => { if (result) this.drops = result; });
        },
        toggleHot(id) {
            this.datasend('manager/cases/' + id + '/featured', 'POST', new FormData()).then(() => {
                this.getCases();
                this.getStats();
            });
        },
    },
};
</script>
