<script setup lang="ts">
import { useLiveFeed } from '@/composables/useLiveFeed';
import { formatPrice, rarityColors } from '@/lib/game';
import type { LiveDrop } from '@/types';
import { Radio } from 'lucide-vue-next';
import { toRef } from 'vue';

const props = defineProps<{
    drops: LiveDrop[];
}>();

const { drops: liveDrops } = useLiveFeed(toRef(props, 'drops'));
</script>

<template>
    <aside class="hidden w-64 shrink-0 flex-col border-r border-cb-border bg-cb-bg-secondary xl:flex">
        <div class="flex items-center gap-2 border-b border-cb-border px-4 py-3">
            <Radio class="h-4 w-4 animate-pulse text-cb-red" />
            <span class="text-sm font-semibold uppercase tracking-wider">Live Drop</span>
        </div>
        <div class="relative flex-1 overflow-hidden">
            <div class="scrollbar-thin max-h-[calc(100vh-8rem)] space-y-2 overflow-y-auto p-3">
                <TransitionGroup name="drop">
                    <div
                        v-for="drop in liveDrops"
                        :key="drop.id"
                        class="flex items-center gap-3 rounded-lg border border-cb-border bg-cb-bg-card p-2.5 transition-colors hover:bg-cb-bg-hover"
                    >
                        <div
                            class="flex h-8 w-8 shrink-0 items-center justify-center rounded-full bg-cb-bg-hover text-xs font-bold text-cb-orange"
                        >
                            {{ drop.avatar }}
                        </div>
                        <div class="min-w-0 flex-1">
                            <p class="truncate text-xs font-medium">{{ drop.user }}</p>
                            <p class="truncate text-[11px] text-cb-text-muted">{{ drop.caseName }}</p>
                        </div>
                        <div class="shrink-0 text-right">
                            <div
                                class="h-10 w-10 overflow-hidden rounded"
                                :style="{ boxShadow: `0 0 8px ${rarityColors[drop.skin.rarity]}40` }"
                            >
                                <img :src="drop.skin.image" :alt="drop.skin.name" class="h-full w-full object-cover" />
                            </div>
                            <p class="mt-0.5 text-[10px] font-semibold" :style="{ color: rarityColors[drop.skin.rarity] }">
                                {{ formatPrice(drop.skin.price) }}
                            </p>
                        </div>
                    </div>
                </TransitionGroup>
            </div>
        </div>
    </aside>
</template>

<style scoped>
.drop-enter-active {
    transition: all 0.4s ease;
}
.drop-enter-from {
    opacity: 0;
    transform: translateY(-20px);
}
</style>
