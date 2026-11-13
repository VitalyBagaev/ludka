<template>
    <div class="container-fluid px-lg-4 ludo-page">
        <div class="d-flex justify-content-between align-items-center mb-4">
            <button type="button" class="btn btn-ludo-outline btn-sm" @click="changePage('HomePage')">← На главную</button>
            <span v-if="profile" class="ludo-stat-pill">ID игрока: <strong>#{{ profile.user.id }}</strong></span>
        </div>

        <div v-if="!profile" class="text-center py-5 text-secondary">Загрузка профиля игрока...</div>

        <template v-else>
            <div class="ludo-hero ludo-player-hero mb-4">
                <div class="row align-items-center g-4">
                    <div class="col-lg-7">
                        <div class="d-flex align-items-center gap-3">
                            <UserAvatar :avatar="profile.user.avatar" :name="profile.user.name" :initials="profile.user.initials" size="lg" class="ludo-player-avatar-lg" />
                            <div class="min-w-0">
                                <div class="d-flex flex-wrap align-items-center gap-2 mb-1">
                                    <h1 class="ludo-page-title mb-0 text-truncate">{{ profile.user.name }}</h1>
                                    <span class="badge bg-warning text-dark">{{ profile.user.roleLabel }}</span>
                                </div>
                                <div class="small text-secondary">На сайте с {{ formatDate(profile.stats.registeredAt) }}</div>
                            </div>
                        </div>
                    </div>

                    <div class="col-lg-5">
                        <div v-if="bestDrop" class="ludo-card p-3 d-flex align-items-center gap-3" style="background: rgba(240, 180, 41, 0.08); border: 1px solid rgba(240, 180, 41, 0.35);">
                            <img v-if="bestDrop.skin" :src="skinImg(bestDrop.skin.image)" style="width: 68px; height: 52px; object-fit: contain;" @error="imgError($event, 'skin')" />
                            <div class="min-w-0 flex-grow-1">
                                <div class="small text-warning fw-semibold">🏆 Лучший дроп</div>
                                <div class="fw-bold text-truncate">{{ bestDrop.weapon }} | {{ bestDrop.skinName }}</div>
                                <div class="ludo-case-price small mb-0">{{ bestDrop.skinPrice }} ₽</div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <div class="ludo-section-label mb-2">Статистика игрока</div>
            <div class="row g-3 mb-4">
                <div class="col-6 col-md-3">
                    <div class="ludo-stat-card text-center h-100">
                        <div class="small text-secondary mb-1">Кейсов открыто</div>
                        <strong class="fs-4">{{ profile.stats.casesOpened }}</strong>
                    </div>
                </div>
                <div class="col-6 col-md-3">
                    <div class="ludo-stat-card text-center h-100">
                        <div class="small text-secondary mb-1">Апгрейдов</div>
                        <strong class="fs-4">{{ profile.stats.upgradesCount }}</strong>
                        <div class="small text-success">{{ profile.stats.upgradeRate }}% успех</div>
                    </div>
                </div>
                <div class="col-6 col-md-3">
                    <div class="ludo-stat-card text-center h-100">
                        <div class="small text-secondary mb-1">Контрактов</div>
                        <strong class="fs-4">{{ profile.stats.contractsCount }}</strong>
                    </div>
                </div>
                <div class="col-6 col-md-3">
                    <div class="ludo-stat-card text-center h-100">
                        <div class="small text-secondary mb-1">Выведено скинов</div>
                        <strong class="fs-4 text-warning">{{ profile.stats.withdrawTotal }} ₽</strong>
                    </div>
                </div>
            </div>

            <div class="d-flex flex-wrap justify-content-between align-items-center gap-2 mb-3">
                <div class="ludo-section-label mb-0">История дропов ({{ filteredHistory.length }})</div>
                <div class="ludo-filter-tabs ludo-filter-tabs-sm mb-0">
                    <button class="ludo-filter-tab" :class="{ active: historyFilter === 'all' }" @click="historyFilter = 'all'">Все</button>
                    <button class="ludo-filter-tab" :class="{ active: historyFilter === 'inventory' }" @click="historyFilter = 'inventory'">В инвентаре</button>
                    <button class="ludo-filter-tab" :class="{ active: historyFilter === 'sold' }" @click="historyFilter = 'sold'">Продано</button>
                    <button class="ludo-filter-tab" :class="{ active: historyFilter === 'withdrawn' }" @click="historyFilter = 'withdrawn'">Выведено</button>
                </div>
            </div>
            <div v-if="!filteredHistory.length" class="ludo-alert ludo-alert-info text-center">
                В этом разделе пока нет предметов
            </div>
            <div v-else class="row g-3">
                <div v-for="entry in filteredHistory" :key="entry.id" class="col-6 col-md-4 col-lg-3 col-xl-2">
                    <div class="ludo-history-card h-100" :class="'ludo-history-' + entry.status">
                        <div class="ludo-history-card-img">
                            <img v-if="entry.skin" :src="skinImg(entry.skin.image)" @error="imgError($event, 'skin')" />
                        </div>
                        <div class="ludo-history-card-body">
                            <div class="small fw-semibold text-truncate">{{ entry.weapon }}</div>
                            <div class="small text-secondary text-truncate">{{ entry.skinName }}</div>
                            <div class="ludo-case-price small mt-1">{{ entry.skinPrice }} ₽</div>
                            <span class="ludo-history-badge">{{ entry.statusLabel }}</span>
                            <div class="small mt-1 text-truncate" :class="entry.source === 'upgrade' ? 'text-info fw-semibold' : 'text-secondary'">
                                {{ formatSourceBadge(entry) }}
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </template>
    </div>
</template>
<script>
import UserAvatar from '../components/UserAvatar.vue';

export default {
    name: 'PlayerPage',
    components: { UserAvatar },
    props: ['changePage', 'datasend', 'pageId', 'skinImg', 'imgError'],
    data() {
        return {
            profile: null,
            historyFilter: 'all',
        };
    },
    computed: {
        bestDrop() {
            if (!this.profile?.skinHistory?.length) return null;
            return [...this.profile.skinHistory].sort((a, b) => (b.skinPrice || 0) - (a.skinPrice || 0)).at(0);
        },
        filteredHistory() {
            if (!this.profile?.skinHistory?.length) return [];
            if (this.historyFilter === 'all') return this.profile.skinHistory;
            return this.profile.skinHistory.filter((item) => item.status === this.historyFilter);
        },
    },
    mounted() {
        this.loadProfile();
    },
    watch: {
        pageId() {
            this.historyFilter = 'all';
            this.loadProfile();
        },
    },
    methods: {
        loadProfile() {
            if (!this.pageId) return;
            this.profile = null;
            this.datasend('users/' + this.pageId).then((result) => {
                if (result) this.profile = result;
            });
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
    },
};
</script>

<style scoped>
.ludo-player-hero {
    background: radial-gradient(circle at 85% 20%, rgba(59, 130, 246, 0.15), transparent 55%),
                linear-gradient(135deg, rgba(24, 24, 34, 0.95), rgba(16, 16, 24, 0.98));
    border: 1px solid rgba(59, 130, 246, 0.25);
}
</style>
