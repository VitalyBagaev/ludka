<script setup lang="ts">
import ManagerLayout from '@/layouts/ManagerLayout.vue';
import { formatPrice } from '@/lib/game';
import { Link, router } from '@inertiajs/vue3';
import { Pencil, Star } from 'lucide-vue-next';

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

function toggleFeatured(id: number) {
    router.patch(`/manager/cases/${id}/featured`, {}, { preserveScroll: true });
}
</script>

<template>
    <ManagerLayout title="Управление кейсами">
        <p class="mb-4 text-sm text-cb-text-muted">
            Менеджер может редактировать кейсы, управлять Hot-метками и составом скинов из пула
        </p>

        <div class="overflow-hidden rounded-xl border border-cb-border">
            <table class="w-full text-sm">
                <thead class="bg-cb-bg-secondary text-left text-cb-text-muted">
                    <tr>
                        <th class="px-4 py-3">Кейс</th>
                        <th class="px-4 py-3">Цена</th>
                        <th class="px-4 py-3">Скинов</th>
                        <th class="px-4 py-3">Hot</th>
                        <th class="px-4 py-3">Действия</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-cb-border bg-cb-bg-card">
                    <tr v-for="item in cases" :key="item.id">
                        <td class="px-4 py-3">
                            <div class="flex items-center gap-3">
                                <img :src="item.image" class="h-10 w-10 object-contain" />
                                <span class="font-medium">{{ item.name }}</span>
                            </div>
                        </td>
                        <td class="px-4 py-3 text-cb-gold">{{ formatPrice(item.price) }}</td>
                        <td class="px-4 py-3">{{ item.skinsCount }}</td>
                        <td class="px-4 py-3">
                            <button
                                class="rounded-lg p-1.5 transition-colors"
                                :class="item.featured ? 'text-cb-orange' : 'text-cb-text-muted hover:text-cb-orange'"
                                @click="toggleFeatured(item.id)"
                            >
                                <Star class="h-4 w-4" :fill="item.featured ? 'currentColor' : 'none'" />
                            </button>
                        </td>
                        <td class="px-4 py-3">
                            <Link :href="`/manager/cases/${item.id}/edit`" class="inline-flex rounded-lg p-1.5 hover:bg-cb-bg-hover">
                                <Pencil class="h-4 w-4" />
                            </Link>
                        </td>
                    </tr>
                </tbody>
            </table>
        </div>
    </ManagerLayout>
</template>
