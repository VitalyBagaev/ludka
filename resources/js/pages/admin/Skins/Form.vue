<script setup lang="ts">
import AdminLayout from '@/layouts/AdminLayout.vue';
import type { Skin } from '@/types';
import { Link, useForm } from '@inertiajs/vue3';
import { computed } from 'vue';

const props = defineProps<{
    skin: Skin | null;
    rarities: { value: string; label: string }[];
}>();

const isEdit = computed(() => !!props.skin);

const form = useForm({
    name: props.skin?.name ?? '',
    weapon: props.skin?.weapon ?? '',
    rarity: props.skin?.rarity ?? 'consumer',
    price: props.skin?.price ?? 100,
    image: props.skin?.image ?? '',
});

function submit() {
    if (isEdit.value && props.skin) {
        form.put(`/admin/skins/${props.skin.id}`);
    } else {
        form.post('/admin/skins');
    }
}
</script>

<template>
    <AdminLayout :title="isEdit ? 'Редактировать скин' : 'Добавить скин'">
        <form class="mx-auto max-w-lg space-y-4" @submit.prevent="submit">
            <div>
                <label class="mb-1 block text-sm text-cb-text-muted">Название скина</label>
                <input v-model="form.name" required class="w-full rounded-xl border border-cb-border bg-cb-bg-card px-4 py-2.5" />
                <p v-if="form.errors.name" class="mt-1 text-xs text-cb-red">{{ form.errors.name }}</p>
            </div>
            <div>
                <label class="mb-1 block text-sm text-cb-text-muted">Оружие</label>
                <input v-model="form.weapon" required class="w-full rounded-xl border border-cb-border bg-cb-bg-card px-4 py-2.5" />
            </div>
            <div>
                <label class="mb-1 block text-sm text-cb-text-muted">Редкость</label>
                <select v-model="form.rarity" class="w-full rounded-xl border border-cb-border bg-cb-bg-card px-4 py-2.5">
                    <option v-for="r in rarities" :key="r.value" :value="r.value">{{ r.label }}</option>
                </select>
            </div>
            <div>
                <label class="mb-1 block text-sm text-cb-text-muted">Цена (₽)</label>
                <input v-model.number="form.price" type="number" min="1" required class="w-full rounded-xl border border-cb-border bg-cb-bg-card px-4 py-2.5" />
            </div>
            <div>
                <label class="mb-1 block text-sm text-cb-text-muted">URL изображения</label>
                <input v-model="form.image" type="url" required class="w-full rounded-xl border border-cb-border bg-cb-bg-card px-4 py-2.5" />
            </div>
            <div v-if="form.image" class="flex justify-center rounded-xl border border-cb-border bg-cb-bg-card p-4">
                <img :src="form.image" alt="Preview" class="h-24 object-contain" />
            </div>
            <div class="flex gap-3 pt-2">
                <button type="submit" class="rounded-xl bg-gradient-orange px-6 py-2.5 font-semibold text-white" :disabled="form.processing">
                    {{ isEdit ? 'Сохранить' : 'Добавить' }}
                </button>
                <Link href="/admin/skins" class="rounded-xl border border-cb-border px-6 py-2.5 text-cb-text-muted hover:bg-cb-bg-hover">
                    Отмена
                </Link>
            </div>
        </form>
    </AdminLayout>
</template>
