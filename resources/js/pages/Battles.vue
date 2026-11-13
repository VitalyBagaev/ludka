<script setup lang="ts">
import CaseBattleLayout from '@/layouts/CaseBattleLayout.vue';
import { formatPrice } from '@/data/cases';
import type { Battle, LiveDrop } from '@/types';
import { Link } from '@inertiajs/vue3';
import { Plus, Swords, Users } from 'lucide-vue-next';

defineProps<{
    battles: Battle[];
    liveDrops: LiveDrop[];
}>();

const statusLabels: Record<string, { text: string; class: string }> = {
    waiting: { text: 'Ожидание', class: 'text-cb-gold bg-cb-gold/10' },
    active: { text: 'Идёт битва', class: 'text-cb-green bg-cb-green/10' },
    finished: { text: 'Завершена', class: 'text-cb-text-muted bg-cb-bg-hover' },
};
</script>

<template>
    <CaseBattleLayout :live-drops="liveDrops">
        <section class="mx-auto max-w-[1600px] px-4 py-8 lg:px-6">
            <div class="mb-8 flex flex-col gap-4 sm:flex-row sm:items-center sm:justify-between">
                <div>
                    <h1 class="font-display flex items-center gap-3 text-3xl font-bold">
                        <Swords class="h-8 w-8 text-cb-orange" />
                        Case Battles
                    </h1>
                    <p class="mt-2 text-cb-text-muted">Соревнуйся с другими игроками — победитель забирает все скины</p>
                </div>
                <button class="flex items-center gap-2 rounded-lg bg-gradient-orange px-6 py-3 font-semibold text-white transition-opacity hover:opacity-90">
                    <Plus class="h-5 w-5" />
                    Создать битву
                </button>
            </div>

            <div class="space-y-3">
                <div
                    v-for="battle in battles"
                    :key="battle.id"
                    class="flex flex-col gap-4 rounded-xl border border-cb-border bg-cb-bg-card p-5 transition-colors hover:border-cb-orange/30 sm:flex-row sm:items-center sm:justify-between"
                >
                    <div class="flex items-center gap-4">
                        <div class="flex h-12 w-12 items-center justify-center rounded-xl bg-cb-orange/10">
                            <Swords class="h-6 w-6 text-cb-orange" />
                        </div>
                        <div>
                            <div class="flex items-center gap-2">
                                <span class="font-semibold">Битва #{{ battle.id }}</span>
                                <span
                                    class="rounded-full px-2 py-0.5 text-[10px] font-bold uppercase"
                                    :class="statusLabels[battle.status].class"
                                >
                                    {{ statusLabels[battle.status].text }}
                                </span>
                            </div>
                            <p class="mt-1 text-sm text-cb-text-muted">
                                {{ battle.cases.join(' + ') }}
                            </p>
                        </div>
                    </div>

                    <div class="flex items-center gap-6">
                        <div class="flex items-center gap-2 text-sm">
                            <Users class="h-4 w-4 text-cb-text-muted" />
                            <span>{{ battle.players }}/{{ battle.maxPlayers }}</span>
                            <div class="flex gap-1">
                                <div
                                    v-for="i in battle.maxPlayers"
                                    :key="i"
                                    class="h-2 w-2 rounded-full"
                                    :class="i <= battle.players ? 'bg-cb-green' : 'bg-cb-border'"
                                />
                            </div>
                        </div>
                        <span class="font-display text-lg font-bold text-cb-gold">{{ formatPrice(battle.totalPrice) }}</span>
                        <button
                            v-if="battle.status === 'waiting' && battle.players < battle.maxPlayers"
                            class="rounded-lg bg-gradient-orange px-5 py-2 text-sm font-semibold text-white"
                        >
                            Вступить
                        </button>
                        <Link
                            v-else-if="battle.status === 'active'"
                            :href="`/battles/${battle.id}`"
                            class="rounded-lg border border-cb-orange px-5 py-2 text-sm font-semibold text-cb-orange transition-colors hover:bg-cb-orange/10"
                        >
                            Смотреть
                        </Link>
                    </div>
                </div>
            </div>
        </section>
    </CaseBattleLayout>
</template>
