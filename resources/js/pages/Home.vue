<script setup lang="ts">
import CaseCard from '@/components/CaseCard.vue';
import CaseBattleLayout from '@/layouts/CaseBattleLayout.vue';
import type { Case, LiveDrop } from '@/types';
import { Gift, Percent, Zap } from 'lucide-vue-next';
import { computed, ref } from 'vue';

const props = defineProps<{
    cases: Case[];
    liveDrops: LiveDrop[];
}>();

const activeCategory = ref('all');

const categories = [
    { id: 'all', label: 'Все кейсы' },
    { id: 'popular', label: 'Популярные' },
    { id: 'new', label: 'Новые' },
    { id: 'premium', label: 'Премиум' },
    { id: 'budget', label: 'Бюджетные' },
];

const filteredCases = computed(() => {
    if (activeCategory.value === 'all') return props.cases;
    return props.cases.filter((c) => c.category === activeCategory.value);
});
</script>

<template>
    <CaseBattleLayout :live-drops="liveDrops">
        <!-- Promo Banner -->
        <section class="relative overflow-hidden border-b border-cb-border">
            <div class="absolute inset-0 bg-gradient-to-r from-cb-orange/10 via-transparent to-cb-gold/10" />
            <div class="relative mx-auto max-w-[1600px] px-4 py-10 lg:px-6 lg:py-14">
                <div class="flex flex-col items-start gap-6 lg:flex-row lg:items-center lg:justify-between">
                    <div>
                        <div class="mb-3 inline-flex items-center gap-2 rounded-full border border-cb-orange/30 bg-cb-orange/10 px-3 py-1 text-xs font-semibold text-cb-orange">
                            <Gift class="h-3.5 w-3.5" />
                            Бонус для новых игроков
                        </div>
                        <h1 class="font-display text-3xl font-bold lg:text-5xl">
                            <span class="text-gradient-orange">+30%</span> к первому депозиту
                        </h1>
                        <p class="mt-3 max-w-lg text-cb-text-muted">
                            Открывай кейсы CS2, сражайся в битвах и выигрывай редкие скины. Промокод: <span class="font-mono font-bold text-cb-orange">WELCOME30</span>
                        </p>
                    </div>
                    <div class="flex gap-4">
                        <div class="rounded-xl border border-cb-border bg-cb-bg-card px-5 py-4 text-center">
                            <Zap class="mx-auto h-5 w-5 text-cb-orange" />
                            <p class="mt-1 font-display text-2xl font-bold">50K+</p>
                            <p class="text-xs text-cb-text-muted">Открытий сегодня</p>
                        </div>
                        <div class="rounded-xl border border-cb-border bg-cb-bg-card px-5 py-4 text-center">
                            <Percent class="mx-auto h-5 w-5 text-cb-gold" />
                            <p class="mt-1 font-display text-2xl font-bold">97%</p>
                            <p class="text-xs text-cb-text-muted">RTP</p>
                        </div>
                    </div>
                </div>
            </div>
        </section>

        <!-- Cases -->
        <section class="mx-auto max-w-[1600px] px-4 py-8 lg:px-6">
            <div class="mb-6 flex flex-col gap-4 sm:flex-row sm:items-center sm:justify-between">
                <h2 class="font-display text-2xl font-bold">Кейсы CS2</h2>
                <div class="flex flex-wrap gap-2">
                    <button
                        v-for="cat in categories"
                        :key="cat.id"
                        class="rounded-lg px-4 py-2 text-sm font-medium transition-colors"
                        :class="
                            activeCategory === cat.id
                                ? 'bg-cb-orange text-white'
                                : 'border border-cb-border bg-cb-bg-card text-cb-text-muted hover:bg-cb-bg-hover hover:text-cb-text'
                        "
                        @click="activeCategory = cat.id"
                    >
                        {{ cat.label }}
                    </button>
                </div>
            </div>

            <div class="grid grid-cols-2 gap-4 sm:grid-cols-3 md:grid-cols-4 lg:grid-cols-5 xl:grid-cols-6">
                <CaseCard v-for="caseItem in filteredCases" :key="caseItem.id" :case-item="caseItem" />
            </div>
        </section>
    </CaseBattleLayout>
</template>
