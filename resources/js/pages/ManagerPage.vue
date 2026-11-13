<template>
    <div class="container ludo-page">
        <h1 class="ludo-page-title">Панель менеджера</h1>
        <p class="ludo-page-sub">Статистика и управление кейсами</p>

        <div class="row g-3 mb-4">
            <div class="col-md-4"><div class="ludo-stat-card">Игроков<br /><strong>{{ stats.totalUsers }}</strong></div></div>
            <div class="col-md-4"><div class="ludo-stat-card">Открытий сегодня<br /><strong style="color: #22c55e">{{ stats.opensToday }}</strong></div></div>
            <div class="col-md-4"><div class="ludo-stat-card">Баланс игроков<br /><strong>{{ stats.totalBalance }} ₽</strong></div></div>
        </div>

        <div class="ludo-section-label">Кейсы</div>
        <div v-for="c in cases" :key="c.id" class="ludo-battle-card">
            <span>{{ c.name }} — <span class="ludo-case-price">{{ c.price }} ₽</span></span>
            <button class="btn btn-sm" :class="c.featured ? 'btn-ludo-primary' : 'btn-ludo-outline'" @click="toggleHot(c.id)">
                {{ c.featured ? '🔥 Hot' : 'Сделать Hot' }}
            </button>
        </div>

        <div class="ludo-section-label mt-4">Live Drop</div>
        <div class="ludo-live-panel">
            <div v-for="d in drops" :key="d.id" class="ludo-drop-item">
                <UserAvatar
                    :avatar="d.avatar"
                    :name="d.user"
                    :initials="d.initials"
                    size="md"
                />
                <img v-if="d.skin" class="ludo-drop-skin-thumb" :src="skinImg(d.skin.image)" @error="imgError($event, 'skin')" />
                <div>
                    <div class="ludo-drop-user">{{ d.user }}</div>
                    <div class="ludo-drop-skin">{{ d.skin.name }} ({{ d.skin.price }} ₽)</div>
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
        };
    },
    mounted() {
        this.getStats();
        this.getCases();
        this.getDrops();
    },
    methods: {
        getStats() {
            this.datasend('manager/stats').then((result) => {
                if (result) {
                    this.stats = result;
                }
            });
        },
        getCases() {
            this.datasend('manager/cases').then((result) => {
                if (result) {
                    this.cases = result;
                }
            });
        },
        getDrops() {
            this.datasend('manager/live-drops').then((result) => {
                if (result) {
                    this.drops = result;
                }
            });
        },
        toggleHot(id) {
            let fd = new FormData();
            this.datasend('manager/cases/' + id + '/featured', 'POST', fd).then(() => {
                this.getCases();
            });
        },
    },
};
</script>
