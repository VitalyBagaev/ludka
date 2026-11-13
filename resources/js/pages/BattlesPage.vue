<template>
    <div class="container-fluid px-lg-4 ludo-page">
        <div class="ludo-hero ludo-battles-hero mb-4">
            <div class="row align-items-center g-3">
                <div class="col-lg-8">
                    <div class="d-inline-flex align-items-center gap-2 px-3 py-1 rounded-pill mb-2" style="background: rgba(235, 75, 75, 0.14); border: 1px solid rgba(235, 75, 75, 0.35); font-size: 12px; color: #ff6b6b;">
                        <span>⚔️</span>
                        <span class="fw-semibold">PvP Режим · Победитель забирает весь дроп в инвентарь</span>
                    </div>
                    <h1 class="mb-2">Битвы <em>кейсов</em></h1>
                    <p class="mb-0">Создай комнату и сразись с игроками или ботами. У кого сумма дропа выше — тот забирает все скины участников!</p>
                </div>
                <div class="col-lg-4 text-lg-end">
                    <button type="button" class="btn btn-ludo-primary btn-lg px-4 ludo-pulse-glow" @click="openCreateModal">
                        + Создать битву
                    </button>
                </div>
            </div>
        </div>

        <div class="d-flex flex-wrap justify-content-between align-items-center gap-2 mb-4">
            <div class="ludo-filter-tabs mb-0">
                <button class="ludo-filter-tab" :class="{ active: statusFilter === 'all' }" @click="statusFilter = 'all'">
                    Все ({{ battles.length }})
                </button>
                <button class="ludo-filter-tab" :class="{ active: statusFilter === 'waiting' }" @click="statusFilter = 'waiting'">
                    Ожидают ({{ waitingCount }})
                </button>
                <button class="ludo-filter-tab" :class="{ active: statusFilter === 'finished' }" @click="statusFilter = 'finished'">
                    Завершённые ({{ finishedCount }})
                </button>
            </div>
            <button type="button" class="btn btn-sm btn-ludo-outline" @click="getBattles">↻ Обновить</button>
        </div>
        <div v-if="!filteredBattles.length" class="ludo-card p-5 text-center my-4">
            <div class="fw-semibold mb-2">Нет битв в этом разделе</div>
            <p class="small text-secondary mb-3">Создай свою комнату первым и сразись с игроками или ботами!</p>
            <button type="button" class="btn btn-ludo-primary btn-sm" @click="openCreateModal">+ Создать битву</button>
        </div>

        <div class="d-flex flex-column gap-3">
            <div v-for="b in filteredBattles" :key="b.id" class="ludo-battle-card ludo-battle-card-animated p-3">
                <div class="d-flex flex-wrap align-items-center justify-content-between gap-3 w-100">
                    <div>
                        <div class="d-flex align-items-center gap-2 mb-2">
                            <strong class="fs-6">Битва #{{ b.id }}</strong>
                            <span class="ludo-battle-status" :class="b.status === 'waiting' ? 'waiting' : 'active'">
                                {{ b.status === 'waiting' ? 'Ожидание игроков' : 'Завершена' }}
                            </span>
                            <span class="small text-secondary">· {{ b.cases.length }} раунд(а)</span>
                        </div>
                        <div class="d-flex flex-wrap gap-2 mb-2">
                            <span v-for="(cName, idx) in b.cases" :key="idx" class="badge bg-dark border border-secondary-subtle text-light fw-normal px-2 py-1">
                                📦 {{ cName }}
                            </span>
                        </div>
                        <div class="d-flex flex-wrap align-items-center gap-2">
                            <span v-for="slot in b.slots" :key="slot.slot" class="badge" :class="b.winnerSlot === slot.slot ? 'bg-success' : 'bg-secondary'">
                                {{ slot.name }} <template v-if="b.status === 'finished'">({{ slot.total }} ₽)</template>
                            </span>
                            <span class="small text-secondary ms-1">{{ b.players }} / {{ b.maxPlayers }} игроков</span>
                        </div>
                    </div>

                    <div class="text-end ms-auto">
                        <div class="small text-secondary">Стоимость входа</div>
                        <div class="ludo-case-price fs-5 mb-2">{{ b.totalPrice }} ₽</div>
                        <div class="d-flex flex-wrap justify-content-end gap-2">
                            <button v-if="b.status === 'waiting' && user" class="btn btn-warning btn-sm px-3 fw-semibold" :disabled="actionLoading" @click="callBots(b)">
                                🤖 Бой с ботами
                            </button>
                            <button v-if="b.status === 'waiting' && !isUserInBattle(b)" class="btn btn-ludo-primary btn-sm px-3" :disabled="actionLoading" @click="joinBattle(b)">
                                ⚔️ Вступить
                            </button>
                            <button v-if="b.status === 'finished'" class="btn btn-ludo-outline btn-sm px-4" @click="openArena(b)">
                                🏆 Итоги
                            </button>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <Teleport to="body">
            <div v-if="createOpen" class="ludo-modal-backdrop" @click.self="createOpen = false">
                <div class="ludo-modal" style="max-width: 680px; width: 95%">
                    <div class="ludo-modal-header">
                        <h2 class="h5 mb-0">Создать битву кейсов</h2>
                        <button type="button" class="btn btn-sm btn-ludo-outline" @click="createOpen = false">×</button>
                    </div>
                    <div class="p-4">
                        <div class="mb-3">
                            <div class="ludo-section-label mb-2">Количество игроков</div>
                            <div class="d-flex gap-2">
                                <button v-for="cnt in playerOptions" :key="cnt" type="button" class="btn btn-sm flex-grow-1" :class="newMaxPlayers === cnt ? 'btn-ludo-primary' : 'btn-ludo-outline'" @click="newMaxPlayers = cnt">
                                    {{ cnt === 2 ? '1 vs 1 (2 игрока)' : cnt === 3 ? '3 игрока' : '4 игрока' }}
                                </button>
                            </div>
                        </div>

                        <div class="mb-3">
                            <div class="d-flex justify-content-between align-items-center mb-2">
                                <div class="ludo-section-label mb-0">Выбранные кейсы ({{ selectedCases.length }}/4)</div>
                                <span class="ludo-case-price small mb-0">К списанию: {{ createTotalPrice }} ₽</span>
                            </div>
                            <div v-if="!selectedCases.length" class="small text-secondary py-2">Нажмите на кейсы ниже, чтобы добавить их в битву</div>
                            <div v-else class="d-flex flex-wrap gap-2 mb-2">
                                <span v-for="(c, idx) in selectedCases" :key="'sel-' + idx" class="badge bg-warning text-dark d-flex align-items-center gap-2 px-2 py-1">
                                    {{ c.name }} ({{ c.price }} ₽)
                                    <button type="button" class="btn-close btn-close-dark" style="font-size: 9px;" @click="removeSelectedCase(idx)"></button>
                                </span>
                            </div>
                        </div>

                        <div class="row g-2 mb-3" style="max-height: 230px; overflow-y: auto;">
                            <div v-for="c in availableCases" :key="'pick-' + c.id" class="col-4 col-sm-3">
                                <div class="ludo-skin-card text-center p-2" style="cursor: pointer;" @click="addCaseToBattle(c)">
                                    <div class="small fw-semibold text-truncate">{{ c.name }}</div>
                                    <div class="ludo-case-price small">{{ c.price }} ₽</div>
                                </div>
                            </div>
                        </div>

                        <div v-if="modalError" class="ludo-alert ludo-alert-danger py-2 small mb-3">{{ modalError }}</div>

                        <div class="d-flex gap-2">
                            <button type="button" class="btn btn-ludo-outline flex-grow-1" :disabled="!selectedCases.length || actionLoading" @click="confirmCreateBattle(false)">
                                Создать и ждать
                            </button>
                            <button type="button" class="btn btn-ludo-primary flex-grow-1 fw-semibold" :disabled="!selectedCases.length || actionLoading" @click="confirmCreateBattle(true)">
                                {{ actionLoading ? 'Запуск...' : '🤖 Сразу с ботами за ' + createTotalPrice + ' ₽' }}
                            </button>
                        </div>
                    </div>
                </div>
            </div>
        </Teleport>
        <Teleport to="body">
            <div v-if="arenaOpen && activeBattle" class="ludo-modal-backdrop" @click.self="closeArena">
                <div class="ludo-modal" style="max-width: 780px; width: 95%">
                    <div class="ludo-modal-header">
                        <h2 class="h5 mb-0">⚔️ Битва #{{ activeBattle.id }} · Банк: {{ activeBattle.totalPrice * activeBattle.maxPlayers }} ₽</h2>
                        <button type="button" class="btn btn-sm btn-ludo-outline" @click="closeArena">×</button>
                    </div>
                    <div class="p-4">
                        <div class="small text-secondary text-center mb-3">
                            Кейсы: <strong class="text-white">{{ activeBattle.cases.join(' → ') }}</strong>
                        </div>

                        <div v-if="activeBattle.status === 'waiting'" class="text-center py-3">
                            <p class="mb-3">Ожидание соперников ({{ activeBattle.players }} / {{ activeBattle.maxPlayers }})...</p>
                            <div class="d-flex flex-wrap justify-content-center gap-2 mb-3">
                                <span v-for="s in activeBattle.slots" :key="s.slot" class="ludo-stat-pill">✅ {{ s.name }}</span>
                            </div>
                            <button v-if="user" type="button" class="btn btn-ludo-primary px-4" :disabled="actionLoading" @click="callBots(activeBattle)">
                                {{ actionLoading ? 'Запускаем...' : '🤖 Призвать ботов и начать бой' }}
                            </button>
                        </div>

                        <div v-else-if="activeBattle.results" class="row g-3 mb-3">
                            <div v-for="res in activeBattle.results" :key="res.slot" class="col-6" :class="activeBattle.maxPlayers === 3 ? 'col-md-4' : activeBattle.maxPlayers === 4 ? 'col-md-3' : 'col-md-6'">
                                <div class="ludo-card p-3 text-center h-100" :style="activeBattle.winnerSlot === res.slot ? 'border: 2px solid #22c55e; box-shadow: 0 0 24px rgba(34, 197, 94, 0.25);' : ''">
                                    <div class="fw-bold mb-1">{{ res.name }}</div>
                                    <div class="ludo-case-price fs-5 mb-2">{{ res.total }} ₽</div>
                                    <div class="d-flex flex-column gap-1">
                                        <div v-for="(drop, dIdx) in res.drops" :key="dIdx" class="small px-2 py-1 rounded" style="background: rgba(255,255,255,0.05);">
                                            <div class="text-truncate fw-semibold">{{ drop.weapon }} | {{ drop.name }}</div>
                                            <div class="text-warning">{{ drop.price }} ₽</div>
                                        </div>
                                    </div>
                                    <div v-if="activeBattle.winnerSlot === res.slot" class="badge bg-success mt-2">🏆 ПОБЕДИТЕЛЬ</div>
                                </div>
                            </div>
                        </div>

                        <div v-if="activeBattle.status === 'finished'" class="text-center mt-3">
                            <button type="button" class="btn btn-ludo-primary px-4" @click="closeArena">Закрыть</button>
                        </div>
                    </div>
                </div>
            </div>
        </Teleport>
    </div>
</template>
<script>
import { fireWinConfetti } from '../helpers/confetti.js';
import { sounds } from '../helpers/sounds.js';

export default {
    name: 'BattlesPage',
    props: ['datasend', 'datasendJson', 'user', 'userInfo', 'getUser', 'changePage'],
    data() {
        return {
            battles: [],
            availableCases: [],
            statusFilter: 'all',
            createOpen: false,
            newMaxPlayers: 2,
            playerOptions: Array.of(2, 3, 4),
            selectedCases: [],
            actionLoading: false,
            modalError: null,
            arenaOpen: false,
            activeBattle: null,
            pollTimer: null,
        };
    },
    computed: {
        waitingCount() {
            return this.battles.filter((b) => b.status === 'waiting').length;
        },
        finishedCount() {
            return this.battles.filter((b) => b.status === 'finished').length;
        },
        filteredBattles() {
            if (this.statusFilter === 'all') return this.battles;
            return this.battles.filter((b) => b.status === this.statusFilter);
        },
        createTotalPrice() {
            return this.selectedCases.reduce((sum, c) => sum + (Number(c.price) || 0), 0);
        },
    },
    mounted() {
        this.getBattles();
        this.loadCatalog();
        this.pollTimer = setInterval(() => this.getBattles(), 8000);
    },
    unmounted() {
        clearInterval(this.pollTimer);
    },
    methods: {
        getBattles() {
            this.datasend('battles').then((result) => {
                if (Array.isArray(result)) {
                    this.battles = result;
                    if (this.activeBattle) {
                        const updated = result.find((b) => b.id === this.activeBattle.id);
                        if (updated) this.activeBattle = updated;
                    }
                }
            });
        },
        loadCatalog() {
            this.datasend('cases').then((res) => { if (res) this.availableCases = res; });
        },
        isUserInBattle(b) {
            if (!this.userInfo?.id) return false;
            return (b.slots || []).some((s) => Number(s.userId) === Number(this.userInfo.id));
        },
        openCreateModal() {
            sounds.click();
            if (!this.user) { this.changePage('AuthPage'); return; }
            this.selectedCases = [];
            this.newMaxPlayers = 2;
            this.modalError = null;
            this.createOpen = true;
        },
        addCaseToBattle(c) {
            if (this.selectedCases.length >= 4) return;
            sounds.click();
            this.selectedCases.push(c);
        },
        removeSelectedCase(idx) {
            this.selectedCases.splice(idx, 1);
        },
        confirmCreateBattle(withBots = false) {
            if (!this.selectedCases.length || this.actionLoading) return;
            this.actionLoading = true;
            this.modalError = null;
            sounds.click();
            this.datasendJson('battles', 'POST', {
                max_players: this.newMaxPlayers,
                case_ids: this.selectedCases.map((c) => c.id),
            }).then((res) => {
                if (!res || res.message) {
                    this.actionLoading = false;
                    if (res?.message) { this.modalError = res.message; sounds.lose(); }
                    return;
                }
                this.createOpen = false;
                if (this.getUser) this.getUser();
                if (withBots) {
                    this.actionLoading = false;
                    this.callBots(res);
                } else {
                    this.actionLoading = false;
                    this.getBattles();
                    this.openArena(res);
                }
            });
        },
        joinBattle(b) {
            if (this.actionLoading) return;
            if (!this.user) { this.changePage('AuthPage'); return; }
            this.actionLoading = true;
            sounds.click();
            this.datasendJson('battles/' + b.id + '/join', 'POST', {}).then((res) => {
                this.actionLoading = false;
                if (!res) return;
                if (res.message) { alert(res.message); sounds.lose(); return; }
                if (this.getUser) this.getUser();
                this.getBattles();
                this.handleBattleResult(res);
            });
        },
        callBots(b) {
            if (this.actionLoading) return;
            this.actionLoading = true;
            sounds.click();
            this.datasendJson('battles/' + b.id + '/bots', 'POST', {}).then((res) => {
                this.actionLoading = false;
                if (!res) return;
                if (res.message) { alert(res.message); return; }
                this.getBattles();
                this.handleBattleResult(res);
            });
        },
        handleBattleResult(battle) {
            this.activeBattle = battle;
            this.arenaOpen = true;
            if (battle.status === 'finished') {
                if (this.userInfo?.id && Number(battle.winnerUserId) === Number(this.userInfo.id)) {
                    sounds.win();
                    fireWinConfetti();
                } else {
                    sounds.lose();
                }
            }
        },
        openArena(battle) {
            sounds.click();
            this.activeBattle = battle;
            this.arenaOpen = true;
        },
        closeArena() {
            this.arenaOpen = false;
            this.activeBattle = null;
        },
    },
};
</script>

<style scoped>
.ludo-battles-hero {
    background: radial-gradient(circle at 85% 20%, rgba(235, 75, 75, 0.16), transparent 55%),
                linear-gradient(135deg, rgba(24, 24, 34, 0.95), rgba(16, 16, 24, 0.98));
    border: 1px solid rgba(235, 75, 75, 0.25);
}
</style>
