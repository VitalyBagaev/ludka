<template>
    <div class="container-fluid px-lg-4 ludo-page">
        <div class="ludo-hero ludo-leader-hero mb-4">
            <div class="row align-items-center g-3">
                <div class="col-lg-8">
                    <div class="d-inline-flex align-items-center gap-2 px-3 py-1 rounded-pill mb-2" style="background: rgba(240, 180, 41, 0.14); border: 1px solid rgba(240, 180, 41, 0.35); font-size: 12px; color: #f0b429;">
                        <span>🏆</span>
                        <span class="fw-semibold">Зал славы · Лучшие игроки Лудомании</span>
                    </div>
                    <h1 class="mb-2">Топ <em>игроков</em></h1>
                    <p class="mb-0">Открывай кейсы, побеждай в битвах и поднимайся на вершину рейтинга. Нажми на любого игрока, чтобы посмотреть его профиль!</p>
                </div>
                <div class="col-lg-4 text-lg-end">
                    <button type="button" class="btn btn-ludo-outline btn-sm" @click="loadLeaderboard">↻ Обновить рейтинг</button>
                </div>
            </div>
        </div>

        <div class="ludo-filter-tabs mb-4">
            <button class="ludo-filter-tab" :class="{ active: sortMode === 'value' }" @click="sortMode = 'value'">
                💰 По сумме дропа
            </button>
            <button class="ludo-filter-tab" :class="{ active: sortMode === 'cases' }" @click="sortMode = 'cases'">
                📦 По открытым кейсам
            </button>
            <button class="ludo-filter-tab" :class="{ active: sortMode === 'upgrades' }" @click="sortMode = 'upgrades'">
                ⚡ По апгрейдам
            </button>
        </div>
        <div v-if="loading" class="text-center py-5 text-secondary">Загружаем таблицу лидеров...</div>

        <template v-else-if="sortedPlayers.length">
            <div class="row g-3 mb-4 justify-content-center">
                <div v-for="(player, idx) in topThree" :key="'podium-' + player.id" class="col-md-4">
                    <div
                        class="ludo-card p-4 text-center h-100 position-relative"
                        :style="podiumStyle(idx)"
                        style="cursor: pointer; transition: transform 0.2s ease;"
                        @click="changePage('PlayerPage', player.id)"
                    >
                        <div class="fs-2 mb-2">{{ idx === 0 ? '🥇' : idx === 1 ? '🥈' : '🥉' }}</div>
                        <div class="d-flex justify-content-center mb-2">
                            <UserAvatar :avatar="player.avatar" :name="player.name" :initials="player.initials" size="lg" />
                        </div>
                        <div class="fw-bold fs-5 text-truncate">{{ player.name }}</div>
                        <div class="small text-secondary mb-2">{{ player.roleLabel }}</div>
                        <div class="ludo-case-price fs-4 mb-2">{{ metricValue(player) }}</div>
                        <div v-if="player.bestDrop" class="small text-secondary text-truncate">
                            Лучший дроп: <span class="text-light">{{ player.bestDrop.weapon }} | {{ player.bestDrop.name }}</span>
                        </div>
                    </div>
                </div>
            </div>
            <div class="ludo-table-wrap">
                <div class="table-responsive">
                    <table class="table ludo-table table-striped table-hover align-middle mb-0">
                        <thead>
                            <tr>
                                <th style="width: 64px;">Место</th>
                                <th>Игрок</th>
                                <th>Кейсов</th>
                                <th>Апгрейдов</th>
                                <th>Лучший дроп</th>
                                <th class="text-end">Ценность дропа</th>
                            </tr>
                        </thead>
                        <tbody>
                            <tr v-for="(player, idx) in sortedPlayers" :key="player.id" style="cursor: pointer;" @click="changePage('PlayerPage', player.id)">
                                <td class="fw-bold">
                                    <span v-if="idx === 0" class="fs-5">🥇</span>
                                    <span v-else-if="idx === 1" class="fs-5">🥈</span>
                                    <span v-else-if="idx === 2" class="fs-5">🥉</span>
                                    <span v-else class="text-secondary">#{{ idx + 1 }}</span>
                                </td>
                                <td>
                                    <div class="d-flex align-items-center gap-2">
                                        <UserAvatar :avatar="player.avatar" :name="player.name" :initials="player.initials" size="sm" />
                                        <span class="fw-semibold">{{ player.name }}</span>
                                    </div>
                                </td>
                                <td>{{ player.casesOpened }}</td>
                                <td>{{ player.upgradesSuccess }} / {{ player.upgradesCount }}</td>
                                <td class="small">
                                    <span v-if="player.bestDrop">{{ player.bestDrop.weapon }} | {{ player.bestDrop.name }} ({{ player.bestDrop.price }} ₽)</span>
                                    <span v-else class="text-secondary">—</span>
                                </td>
                                <td class="text-end ludo-case-price">{{ player.totalDropValue }} ₽</td>
                            </tr>
                        </tbody>
                    </table>
                </div>
            </div>
        </template>

        <div v-else class="ludo-card p-5 text-center text-secondary">Пока нет данных для рейтинга</div>
    </div>
</template>
<script>
import UserAvatar from '../components/UserAvatar.vue';

export default {
    name: 'LeaderboardPage',
    components: { UserAvatar },
    props: ['datasend', 'changePage'],
    data() {
        return {
            players: [],
            loading: false,
            sortMode: 'value',
        };
    },
    computed: {
        sortedPlayers() {
            const copy = [...this.players];
            if (this.sortMode === 'cases') {
                return copy.sort((a, b) => b.casesOpened - a.casesOpened || b.totalDropValue - a.totalDropValue);
            }
            if (this.sortMode === 'upgrades') {
                return copy.sort((a, b) => b.upgradesSuccess - a.upgradesSuccess || b.totalDropValue - a.totalDropValue);
            }
            return copy.sort((a, b) => b.totalDropValue - a.totalDropValue || b.casesOpened - a.casesOpened);
        },
        topThree() {
            return this.sortedPlayers.slice(0, 3);
        },
    },
    mounted() {
        this.loadLeaderboard();
    },
    methods: {
        loadLeaderboard() {
            this.loading = true;
            this.datasend('leaderboard').then((res) => {
                this.loading = false;
                if (Array.isArray(res)) this.players = res;
            });
        },
        metricValue(player) {
            if (this.sortMode === 'cases') return player.casesOpened + ' кейсов';
            if (this.sortMode === 'upgrades') return player.upgradesSuccess + ' апгрейдов';
            return player.totalDropValue + ' ₽';
        },
        podiumStyle(idx) {
            if (idx === 0) return 'border: 2px solid rgba(240, 180, 41, 0.7); box-shadow: 0 10px 30px rgba(240, 180, 41, 0.2);';
            if (idx === 1) return 'border: 1px solid rgba(192, 192, 192, 0.5);';
            return 'border: 1px solid rgba(205, 127, 50, 0.5);';
        },
    },
};
</script>

<style scoped>
.ludo-leader-hero {
    background: radial-gradient(circle at 85% 20%, rgba(240, 180, 41, 0.16), transparent 55%),
                linear-gradient(135deg, rgba(24, 24, 34, 0.95), rgba(16, 16, 24, 0.98));
    border: 1px solid rgba(240, 180, 41, 0.25);
}
</style>
