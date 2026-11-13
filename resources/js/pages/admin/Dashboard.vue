<script setup lang="ts">
import AdminLayout from '@/layouts/AdminLayout.vue';

defineProps<{
    stats: {
        users: number;
        admins: number;
        managers: number;
        skins: number;
        cases: number;
        opensToday: number;
    };
}>();
</script>

<template>
    <AdminLayout title="Админ-панель">
        <div class="grid gap-4 sm:grid-cols-2 lg:grid-cols-3">
            <div class="rounded-xl border border-cb-border bg-cb-bg-card p-5">
                <p class="text-sm text-cb-text-muted">Пользователей</p>
                <p class="font-display mt-1 text-3xl font-bold">{{ stats.users }}</p>
            </div>
            <div class="rounded-xl border border-cb-border bg-cb-bg-card p-5">
                <p class="text-sm text-cb-text-muted">Админов / Менеджеров</p>
                <p class="font-display mt-1 text-3xl font-bold">{{ stats.admins }} / {{ stats.managers }}</p>
            </div>
            <div class="rounded-xl border border-cb-border bg-cb-bg-card p-5">
                <p class="text-sm text-cb-text-muted">Скинов в пуле</p>
                <p class="font-display mt-1 text-3xl font-bold">{{ stats.skins }}</p>
            </div>
            <div class="rounded-xl border border-cb-border bg-cb-bg-card p-5">
                <p class="text-sm text-cb-text-muted">Кейсов</p>
                <p class="font-display mt-1 text-3xl font-bold">{{ stats.cases }}</p>
            </div>
            <div class="rounded-xl border border-cb-border bg-cb-bg-card p-5">
                <p class="text-sm text-cb-text-muted">Открытий сегодня</p>
                <p class="font-display mt-1 text-3xl font-bold text-cb-green">{{ stats.opensToday }}</p>
            </div>
        </div>
    </AdminLayout>
</template>
