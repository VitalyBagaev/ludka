<script setup lang="ts">
import SkinItem from '@/components/SkinItem.vue';
import CaseBattleLayout from '@/layouts/CaseBattleLayout.vue';
import { apiPost } from '@/lib/api';
import { formatPrice } from '@/lib/game';
import type { InventoryItem, LiveDrop, Skin } from '@/types';
import { Link, router, usePage } from '@inertiajs/vue3';
import { ArrowRight, TrendingUp } from 'lucide-vue-next';
import { computed, ref } from 'vue';

const props = defineProps<{
    liveDrops: LiveDrop[];
    inventory: InventoryItem[];
}>();

const page = usePage();
const isLoggedIn = computed(() => !!(page.props.auth as { user?: unknown })?.user);

const selectedItem = ref<InventoryItem | null>(null);
const targetPrice = ref(5000);
const upgradeChance = ref(15);
const isUpgrading = ref(false);
const upgradeResult = ref<'win' | 'lose' | null>(null);
const wonSkin = ref<Skin | null>(null);
const errorMessage = ref('');

const selectedSkin = computed(() => selectedItem.value?.skin ?? null);

function selectItem(item: InventoryItem) {
    selectedItem.value = item;
    targetPrice.value = Math.round(item.skin.price * 3);
    updateChance();
}

function updateChance() {
    if (!selectedSkin.value) return;
    upgradeChance.value = Math.min(75, Math.max(5, Math.round((selectedSkin.value.price / targetPrice.value) * 100)));
}

async function startUpgrade() {
    if (!selectedItem.value || isUpgrading.value) return;

    if (!isLoggedIn.value) {
        router.visit('/login');
        return;
    }

    isUpgrading.value = true;
    upgradeResult.value = null;
    wonSkin.value = null;
    errorMessage.value = '';

    try {
        const data = await apiPost<{ success: boolean; skin?: Skin }>('/upgrade', {
            inventory_item_id: selectedItem.value.id,
            target_price: targetPrice.value,
        });

        upgradeResult.value = data.success ? 'win' : 'lose';
        if (data.success && data.skin) {
            wonSkin.value = data.skin;
        }
        selectedItem.value = null;
        router.reload({ only: ['inventory'] });
    } catch (error) {
        errorMessage.value = error instanceof Error ? error.message : 'Ошибка апгрейда';
    } finally {
        isUpgrading.value = false;
    }
}
</script>

<template>
    <CaseBattleLayout :live-drops="liveDrops">
        <section class="mx-auto max-w-[1200px] px-4 py-8 lg:px-6">
            <div class="mb-8 text-center">
                <h1 class="font-display flex items-center justify-center gap-3 text-3xl font-bold">
                    <TrendingUp class="h-8 w-8 text-cb-orange" />
                    Апгрейд
                </h1>
                <p class="mt-2 text-cb-text-muted">Обменяй свой скин на более дорогой — испытай удачу</p>
            </div>

            <div v-if="!isLoggedIn" class="mb-8 rounded-xl border border-cb-orange/30 bg-cb-orange/10 p-4 text-center">
                <p class="text-sm">
                    <Link href="/login" class="font-semibold text-cb-orange hover:underline">Войдите</Link>
                    , чтобы использовать апгрейд
                </p>
            </div>

            <div class="mx-auto mb-10 max-w-2xl">
                <div class="relative rounded-2xl border border-cb-border bg-cb-bg-card p-8">
                    <div class="flex items-center justify-center gap-8">
                        <div class="flex flex-col items-center gap-3">
                            <p class="text-xs font-semibold uppercase text-cb-text-muted">Ваш скин</p>
                            <div
                                class="flex h-36 w-36 items-center justify-center rounded-xl border-2 border-dashed transition-colors"
                                :class="selectedSkin ? 'border-cb-orange/50 bg-cb-orange/5' : 'border-cb-border'"
                            >
                                <SkinItem v-if="selectedSkin" :skin="selectedSkin" compact />
                                <span v-else class="text-sm text-cb-text-muted">Выберите скин</span>
                            </div>
                            <p v-if="selectedSkin" class="font-bold text-cb-gold">{{ formatPrice(selectedSkin.price) }}</p>
                        </div>

                        <ArrowRight class="h-8 w-8 shrink-0 text-cb-orange" />

                        <div class="flex flex-col items-center gap-3">
                            <p class="text-xs font-semibold uppercase text-cb-text-muted">Цель</p>
                            <div class="flex h-36 w-36 flex-col items-center justify-center rounded-xl border border-cb-border bg-cb-bg-secondary">
                                <span class="font-display text-3xl font-bold text-cb-gold">{{ formatPrice(targetPrice) }}</span>
                                <input
                                    v-model.number="targetPrice"
                                    type="range"
                                    :min="selectedSkin ? selectedSkin.price * 1.5 : 100"
                                    :max="50000"
                                    :step="100"
                                    class="mt-4 w-28 accent-cb-orange"
                                    :disabled="!selectedSkin"
                                    @input="updateChance"
                                />
                            </div>
                        </div>
                    </div>

                    <div class="mt-8 flex flex-col items-center">
                        <div class="relative flex h-32 w-32 items-center justify-center">
                            <svg class="absolute inset-0 -rotate-90" viewBox="0 0 100 100">
                                <circle cx="50" cy="50" r="45" fill="none" stroke="#2a2a3a" stroke-width="6" />
                                <circle
                                    cx="50"
                                    cy="50"
                                    r="45"
                                    fill="none"
                                    stroke="#ff6b00"
                                    stroke-width="6"
                                    stroke-linecap="round"
                                    :stroke-dasharray="`${upgradeChance * 2.83} 283`"
                                />
                            </svg>
                            <span class="font-display text-3xl font-bold">{{ upgradeChance }}%</span>
                        </div>
                        <p class="mt-2 text-sm text-cb-text-muted">Шанс успеха</p>
                    </div>

                    <div v-if="upgradeResult" class="mt-4 text-center">
                        <p class="text-xl font-bold" :class="upgradeResult === 'win' ? 'text-cb-green' : 'text-cb-red'">
                            {{ upgradeResult === 'win' ? 'Успех! Скин получен!' : 'Не повезло, скин потерян' }}
                        </p>
                        <div v-if="wonSkin" class="mx-auto mt-3 w-32">
                            <SkinItem :skin="wonSkin" compact />
                        </div>
                    </div>

                    <p v-if="errorMessage" class="mt-4 text-center text-sm text-cb-red">{{ errorMessage }}</p>

                    <button
                        class="mt-6 w-full rounded-xl bg-gradient-orange py-3.5 text-lg font-bold text-white transition-opacity disabled:opacity-40"
                        :disabled="!selectedItem || isUpgrading"
                        @click="startUpgrade"
                    >
                        {{ isUpgrading ? 'Апгрейд...' : 'Апгрейд' }}
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
                        :class="selectedItem?.id === item.id ? 'ring-2 ring-cb-orange' : ''"
                        @click="selectItem(item)"
                    >
                        <SkinItem :skin="item.skin" compact />
                    </button>
                </div>
            </div>
        </section>
    </CaseBattleLayout>
</template>
