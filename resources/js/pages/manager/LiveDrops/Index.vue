<script setup lang="ts">
import ManagerLayout from '@/layouts/ManagerLayout.vue';
import { formatPrice, rarityColors } from '@/lib/game';
import type { LiveDrop } from '@/types';

defineProps<{
    drops: LiveDrop[];
}>();
</script>

<template>
    <ManagerLayout title="Мониторинг Live Drop">
        <p class="mb-4 text-sm text-cb-text-muted">Последние 100 дропов на платформе</p>

        <div class="space-y-2">
            <div
                v-for="drop in drops"
                :key="drop.id"
                class="flex items-center gap-4 rounded-xl border border-cb-border bg-cb-bg-card p-4"
            >
                <div class="flex h-10 w-10 items-center justify-center rounded-full bg-cb-orange/10 text-sm font-bold text-cb-orange">
                    {{ drop.avatar }}
                </div>
                <div class="min-w-0 flex-1">
                    <p class="font-medium">{{ drop.user }}</p>
                    <p class="text-sm text-cb-text-muted">Кейс: {{ drop.caseName }}</p>
                </div>
                <img :src="drop.skin.image" class="h-12 w-12 object-contain" />
                <div class="text-right">
                    <p class="text-sm font-semibold" :style="{ color: rarityColors[drop.skin.rarity] }">
                        {{ drop.skin.weapon }} | {{ drop.skin.name }}
                    </p>
                    <p class="text-sm text-cb-gold">{{ formatPrice(drop.skin.price) }}</p>
                </div>
            </div>
        </div>
    </ManagerLayout>
</template>
