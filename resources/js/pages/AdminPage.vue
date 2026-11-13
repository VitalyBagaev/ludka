<template>
    <div class="container ludo-page">
        <h1 class="ludo-page-title">Управление пользователями</h1>
        <p class="ludo-page-sub">Только главный админ может назначать админов и менеджеров</p>
        <div class="ludo-table-wrap">
            <div class="table-responsive">
                <table class="table ludo-table table-striped table-hover align-middle">
                    <thead>
                        <tr>
                            <th>ID</th>
                            <th>Имя</th>
                            <th>Email</th>
                            <th>Баланс</th>
                            <th>Роль</th>
                            <th>Действия</th>
                        </tr>
                    </thead>
                    <tbody>
                        <tr v-for="u in users" :key="u.id">
                            <td>{{ u.id }}</td>
                            <td>
                                <div class="d-flex align-items-center gap-2">
                                    <UserAvatar :avatar="u.avatar" :name="u.name" :initials="u.initials" size="sm" />
                                    <span>{{ u.name }}</span>
                                </div>
                            </td>
                            <td>{{ u.email }}</td>
                            <td class="ludo-case-price">{{ u.balance }} ₽</td>
                            <td>
                                <span class="badge rounded-pill" :class="roleBadgeClass(u.role)">
                                    {{ role[u.role] || u.roleLabel || u.role }}
                                </span>
                            </td>
                            <td>
                                <template v-if="u.role !== 'super_admin'">
                                    <select
                                        class="form-select form-select-sm d-inline-block w-auto ludo-form-control"
                                        :value="u.role"
                                        @change="updateRole(u.id, $event.target.value)"
                                    >
                                        <option value="user">Пользователь</option>
                                        <option value="manager">Менеджер</option>
                                        <option value="admin">Админ</option>
                                    </select>
                                    <button class="btn btn-sm btn-outline-danger ms-2" @click="removeUser(u.id)">×</button>
                                </template>
                                <span v-else class="text-muted small">Защищён</span>
                            </td>
                        </tr>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</template>

<script>
import UserAvatar from '../components/UserAvatar.vue';

export default {
    name: 'AdminPage',
    components: { UserAvatar },
    props: ['datasend', 'datasendJson'],
    data() {
        return {
            users: [],
            role: {
                super_admin: 'Главный админ',
                admin: 'Админ',
                manager: 'Менеджер',
                user: 'Пользователь',
            },
        };
    },
    mounted() {
        this.getUsers();
    },
    methods: {
        roleBadgeClass(role) {
            if (role === 'super_admin') return 'bg-warning text-dark';
            if (role === 'admin') return 'bg-danger';
            if (role === 'manager') return 'bg-info text-dark';
            return 'bg-secondary';
        },
        getUsers() {
            this.datasend('admin/users').then((result) => {
                if (result) {
                    this.users = result;
                }
            });
        },
        updateRole(id, role) {
            this.datasendJson('admin/users/' + id + '/role', 'POST', { role }).then((result) => {
                if (result?.errors) {
                    alert(Object.values(result.errors).flat().join('\n'));
                }
                this.getUsers();
            });
        },
        removeUser(id) {
            if (!confirm('Удалить пользователя?')) return;
            this.datasend('admin/users/' + id, 'DELETE').then(() => this.getUsers());
        },
    },
};
</script>
