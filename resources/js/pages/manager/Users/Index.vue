<script setup lang="ts">
import ManagerLayout from '@/layouts/ManagerLayout.vue';
import { formatPrice } from '@/lib/game';

interface UserRow {
    id: number;
    name: string;
    email: string;
    balance: number;
    role: string;
    roleLabel: string;
}

defineProps<{
    users: UserRow[];
}>();

const roleBadgeClass: Record<string, string> = {
    admin: 'bg-cb-red/20 text-cb-red',
    manager: 'bg-cb-gold/20 text-cb-gold',
    user: 'bg-cb-border text-cb-text-muted',
};
</script>

<template>
    <ManagerLayout title="Пользователи (просмотр)">
        <p class="mb-4 text-sm text-cb-text-muted">Только просмотр. Назначение ролей доступно администратору.</p>

        <div class="overflow-hidden rounded-xl border border-cb-border">
            <table class="w-full text-sm">
                <thead class="bg-cb-bg-secondary text-left text-cb-text-muted">
                    <tr>
                        <th class="px-4 py-3">ID</th>
                        <th class="px-4 py-3">Имя</th>
                        <th class="px-4 py-3">Email</th>
                        <th class="px-4 py-3">Баланс</th>
                        <th class="px-4 py-3">Роль</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-cb-border bg-cb-bg-card">
                    <tr v-for="user in users" :key="user.id">
                        <td class="px-4 py-3">{{ user.id }}</td>
                        <td class="px-4 py-3 font-medium">{{ user.name }}</td>
                        <td class="px-4 py-3 text-cb-text-muted">{{ user.email }}</td>
                        <td class="px-4 py-3 text-cb-gold">{{ formatPrice(user.balance) }}</td>
                        <td class="px-4 py-3">
                            <span class="rounded-full px-2.5 py-0.5 text-xs font-semibold" :class="roleBadgeClass[user.role]">
                                {{ user.roleLabel }}
                            </span>
                        </td>
                    </tr>
                </tbody>
            </table>
        </div>
    </ManagerLayout>
</template>
