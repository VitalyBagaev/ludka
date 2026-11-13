<template>
    <div class="container ludo-page">
        <div class="d-flex justify-content-between align-items-center mb-4">
            <div>
                <h1 class="ludo-page-title mb-1">Case Battles</h1>
                <p class="ludo-page-sub mb-0">Соревнуйся — победитель забирает всё</p>
            </div>
            <button class="btn btn-ludo-primary btn-sm" @click="creating = !creating">+ Создать битву</button>
        </div>

        <div v-if="creating" class="ludo-alert ludo-alert-info mb-4">Создание битвы скоро будет доступно (демо)</div>

        <div v-for="b in battles" :key="b.id" class="ludo-battle-card ludo-battle-card-animated">
            <div>
                <div class="d-flex align-items-center gap-2 mb-2">
                    <strong>Битва #{{ b.id }}</strong>
                    <span class="ludo-battle-status" :class="b.status">{{ b.status === 'active' ? 'Идёт' : 'Ожидание' }}</span>
                </div>
                <div class="text-secondary small mb-2">{{ b.cases.join(' + ') }}</div>
                <div class="ludo-battle-players">
                    <span v-for="n in b.maxPlayers" :key="n" class="ludo-battle-dot" :class="{ filled: n <= b.players }"></span>
                </div>
            </div>
            <div class="text-end">
                <div class="ludo-case-price mb-2">{{ b.totalPrice }} ₽</div>
                <button v-if="b.status === 'waiting'" class="btn btn-ludo-primary btn-sm me-1" @click="joinBattle(b)">Вступить</button>
                <button v-else class="btn btn-ludo-outline btn-sm" @click="watchBattle(b)">Смотреть</button>
            </div>
        </div>
    </div>
</template>

<script>
import { sounds } from '../helpers/sounds.js';

export default {
    name: 'BattlesPage',
    props: ['datasend', 'user', 'changePage'],
    data() {
        return { battles: [], creating: false };
    },
    mounted() {
        this.getBattles();
    },
    methods: {
        getBattles() {
            this.datasend('battles').then((result) => {
                if (result) {
                    this.battles = result;
                }
            });
        },
        joinBattle(b) {
            sounds.click();
            if (!this.user) {
                this.changePage('AuthPage');
                return;
            }
            alert('Битва #' + b.id + ' — скоро! (демо)');
        },
        watchBattle(b) {
            sounds.click();
            alert('Просмотр битвы #' + b.id + ' — скоро! (демо)');
        },
    },
};
</script>
