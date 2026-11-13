<template>
    <div class="container-fluid px-lg-4 ludo-page">
        <div class="d-flex flex-wrap justify-content-between align-items-center gap-2 mb-4">
            <div>
                <h1 class="ludo-page-title mb-1">Управление пользователями</h1>
                <p class="ludo-page-sub mb-0">Назначение ролей администраторов и менеджеров, мониторинг игроков</p>
            </div>
            <span class="ludo-stat-pill">Всего аккаунтов: <strong>{{ users.length }}</strong></span>
        </div>

        <div v-if="stats" class="row g-3 mb-4">
            <div class="col-6 col-md-3">
                <div class="ludo-stat-card text-center h-100">
                    <div class="small text-secondary mb-1">Пользователей</div>
                    <strong class="fs-4">{{ stats.users }}</strong>
                </div>
            </div>
            <div class="col-6 col-md-3">
                <div class="ludo-stat-card text-center h-100">
                    <div class="small text-secondary mb-1">Скинов в пуле</div>
                    <strong class="fs-4 text-info">{{ stats.skins }}</strong>
                </div>
            </div>
            <div class="col-6 col-md-3">
                <div class="ludo-stat-card text-center h-100">
                    <div class="small text-secondary mb-1">Кейсов на сайте</div>
                    <strong class="fs-4 text-warning">{{ stats.cases }}</strong>
                </div>
            </div>
            <div class="col-6 col-md-3">
                <div class="ludo-stat-card text-center h-100">
                    <div class="small text-secondary mb-1">Открытий сегодня</div>
                    <strong class="fs-4 text-success">{{ stats.opensToday }}</strong>
                </div>
            </div>
        </div>
        <div class="row g-2 align-items-center mb-3">
            <div class="col-md-5 col-lg-4">
                <input v-model.trim="searchQuery" type="text" class="form-control form-control-sm ludo-form-control" placeholder="Поиск по имени, email или ID..." />
            </div>
            <div class="col-md-7 col-lg-8">
                <div class="ludo-filter-tabs ludo-filter-tabs-sm mb-0 justify-content-md-end">
                    <button class="ludo-filter-tab" :class="{ active: roleFilter === 'all' }" @click="roleFilter = 'all'">Все ({{ users.length }})</button>
                    <button class="ludo-filter-tab" :class="{ active: roleFilter === 'admin' }" @click="roleFilter = 'admin'">Админы</button>
                    <button class="ludo-filter-tab" :class="{ active: roleFilter === 'manager' }" @click="roleFilter = 'manager'">Менеджеры</button>
                    <button class="ludo-filter-tab" :class="{ active: roleFilter === 'user' }" @click="roleFilter = 'user'">Игроки</button>
                </div>
            </div>
        </div>
        <div class="ludo-table-wrap">
            <div class="table-responsive">
                <table class="table ludo-table table-striped table-hover align-middle mb-0">
                    <thead>
                        <tr>
                            <th>ID</th>
                            <th>Пользователь</th>
                            <th>Email</th>
                            <th>Баланс</th>
                            <th>Роль</th>
                            <th>Действия</th>
                        </tr>
                    </thead>
                    <tbody>
                        <tr v-if="!filteredUsers.length">
                            <td colspan="6" class="text-center text-secondary py-4">Пользователи не найдены</td>
                        </tr>
                        <tr v-for="u in filteredUsers" :key="u.id">
                            <td class="text-secondary">#{{ u.id }}</td>
                            <td>
                                <div class="d-flex align-items-center gap-2" style="cursor: pointer;" @click="changePage && changePage('PlayerPage', u.id)">
                                    <UserAvatar :avatar="u.avatar" :name="u.name" :initials="u.initials" size="sm" />
                                    <span class="fw-semibold">{{ u.name }}</span>
                                </div>
                            </td>
                            <td class="small text-secondary">{{ u.email }}</td>
                            <td class="ludo-case-price">{{ u.balance }} ₽</td>
                            <td>
                                <span class="badge rounded-pill" :class="roleBadgeClass(u.role)">
                                    {{ role[u.role] || u.roleLabel || u.role }}
                                </span>
                            </td>
                            <td>
                                <template v-if="u.role !== 'super_admin'">
                                    <select class="form-select form-select-sm d-inline-block w-auto ludo-form-control" :value="u.role" @change="updateRole(u.id, $event.target.value)">
                                        <option value="user">Пользователь</option>
                                        <option value="manager">Менеджер</option>
                                        <option value="admin">Админ</option>
                                    </select>
                                    <button class="btn btn-sm btn-outline-danger ms-2" title="Удалить" @click="removeUser(u.id)">×</button>
                                </template>
                                <span v-else class="text-secondary small">Защищён</span>
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
    props: ['datasend', 'datasendJson', 'changePage'],
    data() {
        return {
            users: [],
            stats: null,
            searchQuery: '',
            roleFilter: 'all',
            role: {
                super_admin: 'Главный админ',
                admin: 'Админ',
                manager: 'Менеджер',
                user: 'Пользователь',
            },
        };
    },
    computed: {
        filteredUsers() {
            const q = this.searchQuery.toLowerCase();
            return this.users.filter((u) => {
                if (this.roleFilter === 'admin' && u.role !== 'admin' && u.role !== 'super_admin') return false;
                if (this.roleFilter !== 'all' && this.roleFilter !== 'admin' && u.role !== this.roleFilter) return false;
                if (q && !u.name.toLowerCase().includes(q) && !u.email.toLowerCase().includes(q) && !String(u.id).includes(q)) {
                    return false;
                }
                return true;
            });
        },
    },
    mounted() {
        this.getUsers();
        this.getStats();
    },
    methods: {
        roleBadgeClass(role) {
            if (role === 'super_admin') return 'bg-warning text-dark';
            if (role === 'admin') return 'bg-danger';
            if (role === 'manager') return 'bg-info text-dark';
            return 'bg-secondary';
        },
        getStats() {
            this.datasend('admin/stats').then((result) => {
                if (result) this.stats = result;
            });
        },
        getUsers() {
            this.datasend('admin/users').then((result) => {
                if (result) this.users = result;
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
