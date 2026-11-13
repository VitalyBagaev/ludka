<script setup lang="ts">
import ManagerLayout from '@/layouts/ManagerLayout.vue';
import { formatPrice, rarityColors } from '@/lib/game';
import type { Skin } from '@/types';
import { Link, useForm } from '@inertiajs/vue3';
import { Plus, Trash2 } from 'lucide-vue-next';

interface CaseSkin extends Skin {
    dropWeight: number;
}

interface CaseFormData {
    id: number;
    name: string;
    price: number;
    image: string;
    category: string;
    featured: boolean;
    skins: CaseSkin[];
}

const props = defineProps<{
    caseItem: CaseFormData;
    allSkins: Skin[];
    categories: { value: string; label: string }[];
}>();

const form = useForm({
    name: props.caseItem.name,
    price: props.caseItem.price,
    image: props.caseItem.image,
    category: props.caseItem.category,
    featured: props.caseItem.featured,
    skins: props.caseItem.skins.map((s) => ({
        skin_id: s.id,
        drop_weight: s.dropWeight,
    })),
});

function addSkin(skinId: number) {
    if (form.skins.some((s) => s.skin_id === skinId)) return;
    form.skins.push({ skin_id: skinId, drop_weight: 100 });
}

function removeSkin(index: number) {
    form.skins.splice(index, 1);
}

function getSkin(skinId: number) {
    return props.allSkins.find((s) => s.id === skinId);
}

function submit() {
    form.put(`/manager/cases/${props.caseItem.id}`);
}
</script>

<template>
    <ManagerLayout title="Редактировать кейс">
        <form class="grid gap-6 lg:grid-cols-2" @submit.prevent="submit">
            <div class="space-y-4 rounded-xl border border-cb-border bg-cb-bg-card p-5">
                <div>
                    <label class="mb-1 block text-sm text-cb-text-muted">Название</label>
                    <input v-model="form.name" required class="w-full rounded-xl border border-cb-border bg-cb-bg-secondary px-4 py-2.5" />
                </div>
                <div>
                    <label class="mb-1 block text-sm text-cb-text-muted">Цена (₽)</label>
                    <input v-model.number="form.price" type="number" min="1" required class="w-full rounded-xl border border-cb-border bg-cb-bg-secondary px-4 py-2.5" />
                </div>
                <div>
                    <label class="mb-1 block text-sm text-cb-text-muted">URL изображения</label>
                    <input v-model="form.image" type="url" required class="w-full rounded-xl border border-cb-border bg-cb-bg-secondary px-4 py-2.5" />
                </div>
                <div>
                    <label class="mb-1 block text-sm text-cb-text-muted">Категория</label>
                    <select v-model="form.category" class="w-full rounded-xl border border-cb-border bg-cb-bg-secondary px-4 py-2.5">
                        <option v-for="cat in categories" :key="cat.value" :value="cat.value">{{ cat.label }}</option>
                    </select>
                </div>
                <label class="flex items-center gap-2 text-sm">
                    <input v-model="form.featured" type="checkbox" class="accent-cb-orange" />
                    Hot
                </label>
            </div>

            <div class="space-y-4 rounded-xl border border-cb-border bg-cb-bg-card p-5">
                <h2 class="font-semibold">Скины в кейсе</h2>
                <div v-for="(entry, index) in form.skins" :key="entry.skin_id" class="flex items-center gap-3 rounded-lg border border-cb-border p-3">
                    <img v-if="getSkin(entry.skin_id)" :src="getSkin(entry.skin_id)!.image" class="h-10 w-10" />
                    <div class="flex-1 truncate text-sm">{{ getSkin(entry.skin_id)?.name }}</div>
                    <input v-model.number="entry.drop_weight" type="number" min="1" class="w-16 rounded border border-cb-border px-2 py-1 text-xs" />
                    <button type="button" @click="removeSkin(index)"><Trash2 class="h-4 w-4 text-cb-red" /></button>
                </div>
                <div class="max-h-40 overflow-y-auto">
                    <button
                        v-for="skin in allSkins"
                        :key="skin.id"
                        type="button"
                        class="flex w-full items-center gap-2 rounded px-2 py-1 text-sm hover:bg-cb-bg-hover disabled:opacity-40"
                        :disabled="form.skins.some((s) => s.skin_id === skin.id)"
                        @click="addSkin(skin.id)"
                    >
                        <Plus class="h-3 w-3" /> {{ skin.weapon }} | {{ skin.name }}
                    </button>
                </div>
            </div>

            <div class="flex gap-3 lg:col-span-2">
                <button type="submit" class="rounded-xl bg-gradient-orange px-6 py-2.5 font-semibold text-white">Сохранить</button>
                <Link href="/manager/cases" class="rounded-xl border border-cb-border px-6 py-2.5 text-cb-text-muted">Назад</Link>
            </div>
        </form>
    </ManagerLayout>
</template>
