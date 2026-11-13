<script setup lang="ts">
import SkinItem from '@/components/SkinItem.vue';
import CaseBattleLayout from '@/layouts/CaseBattleLayout.vue';
import { apiPost } from '@/lib/api';
import { formatPrice } from '@/lib/game';
import type { InventoryItem, LiveDrop, Skin } from '@/types';
import { Link, router, usePage } from '@inertiajs/vue3';
import { FileText, Trash2 } from 'lucide-vue-next';
import { computed, ref } from 'vue';

const props = defineProps<{
    liveDrops: LiveDrop[];
    inventory: InventoryItem[];
}>();

const page = usePage();
const isLoggedIn = computed(() => !!(page.props.auth as { user?: unknown })?.user);

const selectedIds = ref<number[]>([]);
const minSkins = 3;
const maxSkins = 10;
const isExecuting = ref(false);
const resultSkin = ref<Skin | null>(null);
const errorMessage = ref('');

const selectedItems = computed(() => props.inventory.filter((item) => selectedIds.value.includes(item.id)));

const totalValue = computed(() => selectedItems.value.reduce((sum, item) => sum + item.skin.price, 0));

const contractResult = computed(() => {
    if (selectedItems.value.length < minSkins) return null;
    const avg = totalValue.value / selectedItems.value.length;
    return {
        min: Math.round(avg * 0.5),
        max: Math.round(avg * 2.5),
    };
});

function toggleItem(item: InventoryItem) {
    const idx = selectedIds.value.indexOf(item.id);
    if (idx >= 0) {
        selectedIds.value.splice(idx, 1);
    } else if (selectedIds.value.length < maxSkins) {
        selectedIds.value.push(item.id);
    }
}

function isSelected(item: InventoryItem) {
    return selectedIds.value.includes(item.id);
}

async function executeContract() {
    if (selectedIds.value.length < minSkins || isExecuting.value) return;

    if (!isLoggedIn.value) {
        router.visit('/login');
        return;
    }

    isExecuting.value = true;
    resultSkin.value = null;
    errorMessage.value = '';

    try {
        const data = await apiPost<{ skin: Skin }>('/contracts', {
            inventory_item_ids: selectedIds.value,
        });

        resultSkin.value = data.skin;
        selectedIds.value = [];
        router.reload({ only: ['inventory'] });
    } catch (error) {
        errorMessage.value = error instanceof Error ? error.message : 'Ошибка контракта';
    } finally {
        isExecuting.value = false;
    }
}
</script>

<template>
    <CaseBattleLayout :live-drops="liveDrops">
        <section class="mx-auto max-w-[1200px] px-4 py-8 lg:px-6">
            <div class="mb-8 text-center">
                <h1 class="font-display flex items-center justify-center gap-3 text-3xl font-bold">
                    <FileText class="h-8 w-8 text-cb-orange" />
                    Контракты
                </h1>
                <p class="mt-2 text-cb-text-muted">
                    Обменяй {{ minSkins }}–{{ maxSkins }} скинов на один случайный предмет
                </p>
            </div>

            <div v-if="!isLoggedIn" class="mb-8 rounded-xl border border-cb-orange/30 bg-cb-orange/10 p-4 text-center">
                <p class="text-sm">
                    <Link href="/login" class="font-semibold text-cb-orange hover:underline">Войдите</Link>
                    , чтобы использовать контракты
                </p>
            </div>

            <div class="mx-auto mb-10 max-w-3xl rounded-2xl border border-cb-border bg-cb-bg-card p-6">
                <div class="mb-4 flex items-center justify-between">
                    <span class="text-sm text-cb-text-muted">
                        Выбрано: {{ selectedIds.length }}/{{ maxSkins }} (мин. {{ minSkins }})
                    </span>
                    <span v-if="totalValue > 0" class="font-bold text-cb-gold">
                        Сумма: {{ formatPrice(totalValue) }}
                    </span>
                </div>

                <div class="grid min-h-[120px] grid-cols-5 gap-3 sm:grid-cols-10">
                    <div
                        v-for="i in maxSkins"
                        :key="i"
                        class="flex aspect-square items-center justify-center rounded-lg border-2 border-dashed border-cb-border"
                    >
                        <SkinItem v-if="selectedItems[i - 1]" :skin="selectedItems[i - 1].skin" compact />
                    </div>
                </div>

                <div v-if="contractResult" class="mt-4 rounded-lg bg-cb-bg-secondary p-4 text-center">
                    <p class="text-sm text-cb-text-muted">Ожидаемый результат</p>
                    <p class="font-display mt-1 text-xl font-bold text-cb-gold">
                        {{ formatPrice(contractResult.min) }} — {{ formatPrice(contractResult.max) }}
                    </p>
                </div>

                <div v-if="resultSkin" class="mt-4 flex flex-col items-center gap-2">
                    <p class="font-semibold text-cb-green">Контракт выполнен!</p>
                    <div class="w-32">
                        <SkinItem :skin="resultSkin" compact />
                    </div>
                </div>

                <p v-if="errorMessage" class="mt-4 text-center text-sm text-cb-red">{{ errorMessage }}</p>

                <div class="mt-4 flex gap-3">
                    <button
                        class="flex flex-1 items-center justify-center gap-2 rounded-xl bg-gradient-orange py-3 font-bold text-white disabled:opacity-40"
                        :disabled="selectedIds.length < minSkins || isExecuting"
                        @click="executeContract"
                    >
                        <FileText class="h-5 w-5" />
                        {{ isExecuting ? 'Выполняем...' : 'Подписать контракт' }}
                    </button>
                    <button
                        class="rounded-xl border border-cb-border px-4 py-3 text-cb-text-muted transition-colors hover:bg-cb-bg-hover"
                        :disabled="selectedIds.length === 0"
                        @click="selectedIds = []"
                    >
                        <Trash2 class="h-5 w-5" />
                    </button>
                </div>
            </div>

            <div>
                <h2 class="mb-4 font-display text-xl font-bold">Ваш инвентарь</h2>
                <div v-if="inventory.length === 0" class="rounded-xl border border-cb-border bg-cb-bg-card p-8 text-center text-cb-text-muted">
                    Инвентарь пуст. Откройте кейс, чтобы получить скины.
                </div>
                <div v-else class="grid grid-cols-3 gap-3 sm:grid-cols-4 md:grid-cols-6 lg:grid-cols-8">
                    <button
                        v-for="item in inventory"
                        :key="item.id"
                        class="rounded-lg transition-all"
                        :class="isSelected(item) ? 'ring-2 ring-cb-orange opacity-50' : 'hover:scale-[1.02]'"
                        @click="toggleItem(item)"
                    >
                        <SkinItem :skin="item.skin" compact />
                    </button>
                </div>
            </div>
        </section>
    </CaseBattleLayout>
</template>
