<script setup lang="ts">
import { formatPrice } from '@/lib/game';
import type { Case } from '@/types';
import { Link } from '@inertiajs/vue3';
import { Sparkles } from 'lucide-vue-next';

defineProps<{
    caseItem: Case;
}>();
</script>

<template>
    <Link
        :href="`/case/${caseItem.id}`"
        class="group relative flex flex-col overflow-hidden rounded-xl border border-cb-border bg-cb-bg-card transition-all duration-300 hover:-translate-y-1 hover:border-cb-orange/50 hover:shadow-lg hover:shadow-cb-orange/10"
    >
        <div v-if="caseItem.featured" class="absolute right-3 top-3 z-10 flex items-center gap-1 rounded-full bg-cb-orange/20 px-2 py-0.5 text-[10px] font-bold uppercase text-cb-orange">
            <Sparkles class="h-3 w-3" />
            Hot
        </div>

        <div class="relative flex items-center justify-center p-6 pb-2">
            <div class="absolute inset-0 bg-gradient-to-b from-cb-orange/5 to-transparent opacity-0 transition-opacity group-hover:opacity-100" />
            <img
                :src="caseItem.image"
                :alt="caseItem.name"
                class="relative h-32 w-32 object-contain transition-transform duration-300 group-hover:scale-110"
            />
        </div>

        <div class="flex flex-1 flex-col items-center gap-2 p-4 pt-2">
            <h3 class="text-center text-sm font-semibold">{{ caseItem.name }}</h3>
            <div class="flex items-center gap-1.5">
                <span class="font-display text-lg font-bold text-cb-gold">{{ formatPrice(caseItem.price) }}</span>
            </div>
            <button class="mt-1 w-full rounded-lg bg-gradient-orange py-2 text-sm font-semibold text-white opacity-0 transition-all group-hover:opacity-100">
                Открыть
            </button>
        </div>
    </Link>
</template>
