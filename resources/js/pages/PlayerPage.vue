<template>
    <div class="container ludo-page">
        <button class="btn btn-ludo-outline btn-sm mb-4" @click="changePage('HomePage')">← Назад</button>

        <div v-if="!profile" class="text-center py-5 text-secondary">Загрузка профиля...</div>

        <template v-else>
            <div class="ludo-profile-header mb-4">
                <UserAvatar
                    :avatar="profile.user.avatar"
                    :name="profile.user.name"
                    :initials="profile.user.initials"
                    size="lg"
                    class="ludo-player-avatar-lg"
                />
                <div class="flex-grow-1">
                    <h1 class="ludo-page-title mb-1">{{ profile.user.name }}</h1>
                    <div class="d-flex flex-wrap gap-2">
                        <span class="ludo-stat-pill">{{ profile.user.roleLabel }}</span>
                        <span class="ludo-stat-pill">На сайте с {{ formatDate(profile.stats.registeredAt) }}</span>
                    </div>
                </div>
            </div>

            <div class="ludo-section-label">Статистика</div>
            <div class="row g-3 mb-4">
                <div class="col-6 col-md-3">
                    <div class="ludo-stat-card text-center">
                        <div class="small text-secondary mb-1">Кейсов</div>
                        <strong>{{ profile.stats.casesOpened }}</strong>
                    </div>
                </div>
                <div class="col-6 col-md-3">
                    <div class="ludo-stat-card text-center">
                        <div class="small text-secondary mb-1">Апгрейдов</div>
                        <strong>{{ profile.stats.upgradesCount }}</strong>
                        <div class="small text-secondary">{{ profile.stats.upgradeRate }}% успех</div>
                    </div>
                </div>
                <div class="col-6 col-md-3">
                    <div class="ludo-stat-card text-center">
                        <div class="small text-secondary mb-1">Контрактов</div>
                        <strong>{{ profile.stats.contractsCount }}</strong>
                    </div>
                </div>
                <div class="col-6 col-md-3">
                    <div class="ludo-stat-card text-center">
                        <div class="small text-secondary mb-1">Выведено</div>
                        <strong>{{ profile.stats.withdrawTotal }} ₽</strong>
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
        };
    },
    mounted() {
        this.loadProfile();
    },
    watch: {
        pageId() {
            this.loadProfile();
        },
    },
    methods: {
        loadProfile() {
            if (!this.pageId) {
                return;
            }
            this.profile = null;
            this.datasend('users/' + this.pageId).then((result) => {
                if (result) {
                    this.profile = result;
                }
            });
        },
        formatDate(iso) {
            if (!iso) {
                return '—';
            }
            return new Date(iso).toLocaleString('ru-RU', {
                day: '2-digit',
                month: '2-digit',
                year: 'numeric',
            });
        },
    },
};
</script>
