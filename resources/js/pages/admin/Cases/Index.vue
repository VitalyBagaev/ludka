<script setup lang="ts">
import AdminLayout from '@/layouts/AdminLayout.vue';
import { formatPrice } from '@/lib/game';
import { Link, router } from '@inertiajs/vue3';
import { Pencil, Plus, Trash2 } from 'lucide-vue-next';

interface CaseRow {
    id: number;
    name: string;
    price: number;
    category: string;
    featured: boolean;
    image: string;
    skinsCount: number;
}

defineProps<{
    cases: CaseRow[];
}>();

function deleteCase(id: number, name: string) {
    if (confirm(`Удалить кейс ${name}?`)) {
        router.delete(`/admin/cases/${id}`);
    }
}
</script>

<template>
    <AdminLayout title="Кейсы">
        <div class="mb-4 flex justify-end">
            <Link href="/admin/cases/create" class="flex items-center gap-2 rounded-lg bg-gradient-orange px-4 py-2 text-sm font-semibold text-white">
                <Plus class="h-4 w-4" />
                Создать кейс
            </Link>
        </div>

        <div class="overflow-hidden rounded-xl border border-cb-border">
            <table class="w-full text-sm">
                <thead class="bg-cb-bg-secondary text-left text-cb-text-muted">
                    <tr>
                        <th class="px-4 py-3">Кейс</th>
                        <th class="px-4 py-3">Цена</th>
                        <th class="px-4 py-3">Категория</th>
                        <th class="px-4 py-3">Скинов</th>
                        <th class="px-4 py-3">Hot</th>
                        <th class="px-4 py-3">Действия</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-cb-border bg-cb-bg-card">
                    <tr v-for="item in cases" :key="item.id" class="hover:bg-cb-bg-hover">
                        <td class="px-4 py-3">
                            <div class="flex items-center gap-3">
                                <img :src="item.image" class="h-10 w-10 object-contain" />
                                <span class="font-medium">{{ item.name }}</span>
                            </div>
                        </td>
                        <td class="px-4 py-3 text-cb-gold">{{ formatPrice(item.price) }}</td>
                        <td class="px-4 py-3 capitalize">{{ item.category }}</td>
                        <td class="px-4 py-3">{{ item.skinsCount }}</td>
                        <td class="px-4 py-3">
                            <span v-if="item.featured" class="text-cb-orange">✓</span>
                            <span v-else class="text-cb-text-muted">—</span>
                        </td>
                        <td class="px-4 py-3">
                            <div class="flex gap-1">
                                <Link :href="`/admin/cases/${item.id}/edit`" class="rounded-lg p-1.5 hover:bg-cb-bg-hover">
                                    <Pencil class="h-4 w-4" />
                                </Link>
                                <button class="rounded-lg p-1.5 text-cb-red hover:bg-cb-red/10" @click="deleteCase(item.id, item.name)">
                                    <Trash2 class="h-4 w-4" />
                                </button>
                            </div>
                        </td>
                    </tr>
                </tbody>
            </table>
        </div>
    </AdminLayout>
</template>
