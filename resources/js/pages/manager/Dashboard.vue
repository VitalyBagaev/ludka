<script setup lang="ts">
import ManagerLayout from '@/layouts/ManagerLayout.vue';
import { formatPrice, rarityColors } from '@/lib/game';
import type { LiveDrop } from '@/types';

defineProps<{
    stats: {
        totalUsers: number;
        totalCases: number;
        featuredCases: number;
        opensToday: number;
        opensWeek: number;
        totalBalance: number;
    };
    recentDrops: LiveDrop[];
}>();
</script>

<template>
    <ManagerLayout title="Панель менеджера">
        <div class="mb-8 grid gap-4 sm:grid-cols-2 lg:grid-cols-3">
            <div class="rounded-xl border border-cb-border bg-cb-bg-card p-5">
                <p class="text-sm text-cb-text-muted">Игроков</p>
                <p class="font-display mt-1 text-3xl font-bold">{{ stats.totalUsers }}</p>
            </div>
            <div class="rounded-xl border border-cb-border bg-cb-bg-card p-5">
                <p class="text-sm text-cb-text-muted">Кейсов / Hot</p>
                <p class="font-display mt-1 text-3xl font-bold">{{ stats.totalCases }} / {{ stats.featuredCases }}</p>
            </div>
            <div class="rounded-xl border border-cb-border bg-cb-bg-card p-5">
                <p class="text-sm text-cb-text-muted">Открытий сегодня / за неделю</p>
                <p class="font-display mt-1 text-3xl font-bold text-cb-green">{{ stats.opensToday }} / {{ stats.opensWeek }}</p>
            </div>
            <div class="rounded-xl border border-cb-border bg-cb-bg-card p-5 lg:col-span-2">
                <p class="text-sm text-cb-text-muted">Общий баланс игроков</p>
                <p class="font-display mt-1 text-3xl font-bold text-cb-gold">{{ formatPrice(stats.totalBalance) }}</p>
            </div>
        </div>

        <h2 class="mb-4 font-display text-lg font-bold">Последние дропы</h2>
        <div class="space-y-2">
            <div
                v-for="drop in recentDrops"
                :key="drop.id"
                class="flex items-center gap-4 rounded-xl border border-cb-border bg-cb-bg-card p-3"
            >
                <div class="flex h-9 w-9 items-center justify-center rounded-full bg-cb-bg-hover text-xs font-bold text-cb-orange">
                    {{ drop.avatar }}
                </div>
                <div class="min-w-0 flex-1">
                    <p class="text-sm font-medium">{{ drop.user }}</p>
                    <p class="text-xs text-cb-text-muted">{{ drop.caseName }}</p>
                </div>
                <div class="text-right">
                    <p class="text-xs font-semibold" :style="{ color: rarityColors[drop.skin.rarity] }">
                        {{ drop.skin.weapon }} | {{ drop.skin.name }}
                    </p>
                    <p class="text-xs text-cb-gold">{{ formatPrice(drop.skin.price) }}</p>
                </div>
            </div>
        </div>
    </ManagerLayout>
</template>
