<script setup lang="ts">
import AdminLayout from '@/layouts/AdminLayout.vue';
import { formatPrice } from '@/lib/game';
import { router } from '@inertiajs/vue3';
import { Trash2 } from 'lucide-vue-next';

interface AdminUser {
    id: number;
    name: string;
    email: string;
    balance: number;
    role: string;
    roleLabel: string;
    createdAt?: string;
}

defineProps<{
    users: AdminUser[];
    roles: { value: string; label: string }[];
}>();

const roleBadgeClass: Record<string, string> = {
    admin: 'bg-cb-red/20 text-cb-red',
    manager: 'bg-cb-gold/20 text-cb-gold',
    user: 'bg-cb-border text-cb-text-muted',
};

function updateRole(userId: number, role: string) {
    router.patch(`/admin/users/${userId}/role`, { role }, { preserveScroll: true });
}

function deleteUser(userId: number, name: string) {
    if (confirm(`Удалить пользователя ${name}?`)) {
        router.delete(`/admin/users/${userId}`, { preserveScroll: true });
    }
}
</script>

<template>
    <AdminLayout title="Пользователи">
        <p class="mb-4 text-sm text-cb-text-muted">
            Сортировка: сначала администраторы, затем менеджеры, затем пользователи
        </p>

        <div class="overflow-hidden rounded-xl border border-cb-border">
            <table class="w-full text-sm">
                <thead class="bg-cb-bg-secondary text-left text-cb-text-muted">
                    <tr>
                        <th class="px-4 py-3 font-medium">ID</th>
                        <th class="px-4 py-3 font-medium">Имя</th>
                        <th class="px-4 py-3 font-medium">Email</th>
                        <th class="px-4 py-3 font-medium">Баланс</th>
                        <th class="px-4 py-3 font-medium">Роль</th>
                        <th class="px-4 py-3 font-medium">Действия</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-cb-border bg-cb-bg-card">
                    <tr v-for="user in users" :key="user.id" class="hover:bg-cb-bg-hover">
                        <td class="px-4 py-3">{{ user.id }}</td>
                        <td class="px-4 py-3 font-medium">{{ user.name }}</td>
                        <td class="px-4 py-3 text-cb-text-muted">{{ user.email }}</td>
                        <td class="px-4 py-3 text-cb-gold">{{ formatPrice(user.balance) }}</td>
                        <td class="px-4 py-3">
                            <span
                                class="rounded-full px-2.5 py-0.5 text-xs font-semibold"
                                :class="roleBadgeClass[user.role]"
                            >
                                {{ user.roleLabel }}
                            </span>
                        </td>
                        <td class="px-4 py-3">
                            <div class="flex items-center gap-2">
                                <select
                                    :value="user.role"
                                    class="rounded-lg border border-cb-border bg-cb-bg-secondary px-2 py-1 text-xs"
                                    @change="updateRole(user.id, ($event.target as HTMLSelectElement).value)"
                                >
                                    <option v-for="role in roles" :key="role.value" :value="role.value">
                                        {{ role.label }}
                                    </option>
                                </select>
                                <button
                                    class="rounded-lg p-1.5 text-cb-red hover:bg-cb-red/10"
                                    title="Удалить"
                                    @click="deleteUser(user.id, user.name)"
                                >
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
