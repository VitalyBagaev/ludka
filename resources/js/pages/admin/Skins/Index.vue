<script setup lang="ts">
import SkinItem from '@/components/SkinItem.vue';
import AdminLayout from '@/layouts/AdminLayout.vue';
import { formatPrice } from '@/lib/game';
import type { Skin } from '@/types';
import { Link, router } from '@inertiajs/vue3';
import { Pencil, Plus, Trash2 } from 'lucide-vue-next';

defineProps<{
    skins: Skin[];
}>();

function deleteSkin(id: number, name: string) {
    if (confirm(`Удалить скин ${name}?`)) {
        router.delete(`/admin/skins/${id}`);
    }
}
</script>

<template>
    <AdminLayout title="Пул скинов">
        <div class="mb-4 flex justify-end">
            <Link
                href="/admin/skins/create"
                class="flex items-center gap-2 rounded-lg bg-gradient-orange px-4 py-2 text-sm font-semibold text-white"
            >
                <Plus class="h-4 w-4" />
                Добавить скин
            </Link>
        </div>

        <div class="grid gap-4 sm:grid-cols-2 lg:grid-cols-4">
            <div
                v-for="skin in skins"
                :key="skin.id"
                class="rounded-xl border border-cb-border bg-cb-bg-card p-4"
            >
                <SkinItem :skin="skin" />
                <div class="mt-3 flex items-center justify-between border-t border-cb-border pt-3">
                    <span class="text-xs font-bold text-cb-gold">{{ formatPrice(skin.price) }}</span>
                    <div class="flex gap-1">
                        <Link
                            :href="`/admin/skins/${skin.id}/edit`"
                            class="rounded-lg p-1.5 text-cb-text-muted hover:bg-cb-bg-hover"
                        >
                            <Pencil class="h-4 w-4" />
                        </Link>
                        <button
                            class="rounded-lg p-1.5 text-cb-red hover:bg-cb-red/10"
                            @click="deleteSkin(skin.id, skin.name)"
                        >
                            <Trash2 class="h-4 w-4" />
                        </button>
                    </div>
                </div>
            </div>
        </div>
    </AdminLayout>
</template>
