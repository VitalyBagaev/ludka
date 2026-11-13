<script setup lang="ts">
import SkinItem from '@/components/SkinItem.vue';
import CaseBattleLayout from '@/layouts/CaseBattleLayout.vue';
import { formatPrice, rarityColors } from '@/lib/game';
import { apiPost } from '@/lib/api';
import type { Case, LiveDrop, Skin } from '@/types';
import { Link, router, usePage } from '@inertiajs/vue3';
import { ArrowLeft, RotateCcw } from 'lucide-vue-next';
import { computed, ref } from 'vue';

const props = defineProps<{
    caseItem: Case;
    liveDrops: LiveDrop[];
}>();

const page = usePage();
const isLoggedIn = computed(() => !!(page.props.auth as { user?: unknown })?.user);

const isOpening = ref(false);
const wonSkin = ref<Skin | null>(null);
const showResult = ref(false);
const errorMessage = ref('');
const rouletteItems = ref<Skin[]>([]);
const rouletteRef = ref<HTMLElement | null>(null);

function generateRouletteItems(winner: Skin): Skin[] {
    const items: Skin[] = [];
    for (let i = 0; i < 50; i++) {
        items.push(props.caseItem.items[Math.floor(Math.random() * props.caseItem.items.length)]);
    }
    items[40] = winner;
    return items;
}

function animateRoulette() {
    setTimeout(() => {
        if (rouletteRef.value) {
            const itemWidth = 140;
            const winIndex = 40;
            const offset = winIndex * itemWidth - rouletteRef.value.parentElement!.offsetWidth / 2 + itemWidth / 2;
            rouletteRef.value.style.setProperty('--spin-distance', `-${offset}px`);
            rouletteRef.value.classList.add('animate-spin-roulette');
        }
    }, 100);
}

async function openCase() {
    if (isOpening.value) return;

    if (!isLoggedIn.value) {
        router.visit('/login');
        return;
    }

    isOpening.value = true;
    showResult.value = false;
    wonSkin.value = null;
    errorMessage.value = '';

    try {
        const data = await apiPost<{ skin: Skin; balance: number }>(`/case/${props.caseItem.id}/open`);

        rouletteItems.value = generateRouletteItems(data.skin);
        animateRoulette();

        setTimeout(() => {
            wonSkin.value = data.skin;
            showResult.value = true;
            isOpening.value = false;
            if (rouletteRef.value) {
                rouletteRef.value.classList.remove('animate-spin-roulette');
            }
            router.reload({ only: ['auth'] });
        }, 4200);
    } catch (error) {
        isOpening.value = false;
        errorMessage.value = error instanceof Error ? error.message : 'Не удалось открыть кейс';
    }
}

function reset() {
    showResult.value = false;
    wonSkin.value = null;
    rouletteItems.value = [];
    errorMessage.value = '';
}
</script>

<template>
    <CaseBattleLayout :live-drops="liveDrops">
        <section class="mx-auto max-w-[1200px] px-4 py-8 lg:px-6">
            <Link href="/" class="mb-6 inline-flex items-center gap-2 text-sm text-cb-text-muted transition-colors hover:text-cb-text">
                <ArrowLeft class="h-4 w-4" />
                Назад к кейсам
            </Link>

            <div class="mb-8 flex flex-col items-center gap-4 sm:flex-row sm:justify-center">
                <img :src="caseItem.image" :alt="caseItem.name" class="h-24 w-24 object-contain" />
                <div class="text-center sm:text-left">
                    <h1 class="font-display text-3xl font-bold">{{ caseItem.name }}</h1>
                    <p class="mt-1 font-display text-xl font-bold text-cb-gold">{{ formatPrice(caseItem.price) }}</p>
                </div>
            </div>

            <div class="relative mb-8 overflow-hidden rounded-2xl border border-cb-border bg-cb-bg-card">
                <div class="absolute left-1/2 top-0 z-10 h-full w-0.5 -translate-x-1/2 bg-cb-orange shadow-[0_0_10px_#ff6b00]" />
                <div class="absolute left-1/2 top-0 z-10 -translate-x-1/2 border-x-[10px] border-t-[14px] border-x-transparent border-t-cb-orange" />

                <div class="overflow-hidden py-8">
                    <div v-if="rouletteItems.length === 0" class="flex items-center justify-center py-12">
                        <p class="text-cb-text-muted">Нажмите «Открыть кейс» чтобы начать</p>
                    </div>
                    <div v-else ref="rouletteRef" class="flex gap-3 px-4" style="width: max-content">
                        <div
                            v-for="(item, index) in rouletteItems"
                            :key="index"
                            class="w-[128px] shrink-0 rounded-lg border bg-cb-bg-secondary p-2"
                            :style="{ borderColor: `${rarityColors[item.rarity]}40` }"
                        >
                            <img :src="item.image" :alt="item.name" class="mx-auto h-20 w-20 object-contain" />
                            <p class="mt-1 truncate text-center text-[10px] font-semibold">{{ item.weapon }}</p>
                            <p class="truncate text-center text-xs">{{ item.name }}</p>
                        </div>
                    </div>
                </div>
            </div>

            <div v-if="showResult && wonSkin" class="mb-8 flex flex-col items-center gap-4">
                <p class="font-display text-2xl font-bold text-cb-green">Вы выиграли!</p>
                <div class="w-48">
                    <SkinItem :skin="wonSkin" />
                </div>
                <button class="flex items-center gap-2 text-sm text-cb-text-muted hover:text-cb-text" @click="reset">
                    <RotateCcw class="h-4 w-4" />
                    Открыть ещё раз
                </button>
            </div>

            <p v-if="errorMessage" class="mb-4 text-center text-sm text-cb-red">{{ errorMessage }}</p>

            <div v-if="!showResult" class="mb-10 flex justify-center">
                <button
                    class="animate-pulse-glow rounded-xl bg-gradient-orange px-12 py-4 font-display text-xl font-bold text-white transition-all hover:opacity-90 disabled:opacity-50"
                    :disabled="isOpening"
                    @click="openCase"
                >
                    {{ isOpening ? 'Открываем...' : `Открыть за ${formatPrice(caseItem.price)}` }}
                </button>
            </div>

            <div>
                <h2 class="mb-4 font-display text-xl font-bold">Содержимое кейса</h2>
                <div class="grid grid-cols-2 gap-3 sm:grid-cols-3 md:grid-cols-4 lg:grid-cols-5">
                    <SkinItem v-for="item in caseItem.items" :key="item.id" :skin="item" />
                </div>
            </div>
        </section>
    </CaseBattleLayout>
</template>
