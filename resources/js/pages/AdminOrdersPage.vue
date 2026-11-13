<template>
    <div class="container ludo-page">
        <h1 class="ludo-page-title">Заявки на вывод</h1>
        <p class="ludo-page-sub">Обработка выводов скинов · одобрение или отмена</p>

        <div class="d-flex flex-wrap gap-2 mb-3">
            <button
                v-for="filter in filters"
                :key="filter.value"
                class="ludo-filter-tab"
                :class="{ active: statusFilter === filter.value }"
                @click="statusFilter = filter.value"
            >
                {{ filter.label }}
            </button>
        </div>

        <div class="ludo-table-wrap">
            <div class="table-responsive">
                <table class="table ludo-table table-striped table-hover align-middle">
                    <thead>
                        <tr>
                            <th>ID</th>
                            <th>Пользователь</th>
                            <th>Скин</th>
                            <th>Цена</th>
                            <th>Статус</th>
                            <th>Дата</th>
                            <th>Действия</th>
                        </tr>
                    </thead>
                    <tbody>
                        <tr v-if="!filteredOrders.length">
                            <td colspan="7" class="text-center text-secondary py-4">Нет заявок</td>
                        </tr>
                        <tr v-for="order in filteredOrders" :key="order.id">
                            <td>#{{ order.id }}</td>
                            <td>
                                <div class="fw-semibold">{{ order.userName }}</div>
                                <div class="small text-secondary">{{ order.userEmail }}</div>
                            </td>
                            <td>{{ order.weapon }} | {{ order.skinName }}</td>
                            <td class="ludo-case-price">{{ order.skinPrice }} ₽</td>
                            <td>
                                <span class="badge rounded-pill" :class="statusClass(order.status)">
                                    {{ order.statusLabel }}
                                </span>
                            </td>
                            <td class="small text-secondary">{{ formatDate(order.createdAt) }}</td>
                            <td>
                                <div v-if="canManage(order)" class="d-flex flex-wrap gap-1">
                                    <button
                                        v-if="order.status === 'pending'"
                                        class="btn btn-sm btn-ludo-outline"
                                        @click="setStatus(order.id, 'processing')"
                                    >
                                        В работу
                                    </button>
                                    <button
                                        class="btn btn-sm btn-ludo-primary"
                                        @click="setStatus(order.id, 'completed')"
                                    >
                                        Выполнить
                                    </button>
                                    <button
                                        class="btn btn-sm btn-outline-danger"
                                        @click="setStatus(order.id, 'cancelled')"
                                    >
                                        Отменить
                                    </button>
                                </div>
                                <span v-else class="small text-secondary">—</span>
                            </td>
                        </tr>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</template>

<script>
export default {
    name: 'AdminOrdersPage',
    props: ['datasend', 'datasendJson'],
    data() {
        return {
            orders: [],
            statusFilter: 'all',
            filters: [
                { value: 'all', label: 'Все' },
                { value: 'pending', label: 'Ожидают' },
                { value: 'processing', label: 'В работе' },
                { value: 'completed', label: 'Выполнены' },
                { value: 'cancelled', label: 'Отменены' },
            ],
        };
    },
    computed: {
        filteredOrders() {
            if (this.statusFilter === 'all') {
                return this.orders;
            }
            return this.orders.filter((order) => order.status === this.statusFilter);
        },
    },
    mounted() {
        this.loadOrders();
    },
    methods: {
        loadOrders() {
            this.datasend('admin/withdrawals').then((result) => {
                if (result) {
                    this.orders = result;
                }
            });
        },
        canManage(order) {
            return order.status === 'pending' || order.status === 'processing';
        },
        statusClass(status) {
            return {
                pending: 'bg-warning text-dark',
                processing: 'bg-info text-dark',
                completed: 'bg-success',
                cancelled: 'bg-secondary',
            }[status] || 'bg-secondary';
        },
        formatDate(value) {
            if (!value) {
                return '—';
            }
            return new Date(value).toLocaleString('ru-RU');
        },
        setStatus(id, status) {
            const labels = {
                processing: 'взять в работу',
                completed: 'отметить выполненной',
                cancelled: 'отменить (скин вернётся пользователю)',
            };
            if (!confirm('Подтвердить: ' + labels[status] + '?')) {
                return;
            }
            this.datasendJson('admin/withdrawals/' + id + '/status', 'POST', { status: status }).then((result) => {
                if (result && result.message) {
                    alert(result.message);
                    return;
                }
                if (result) {
                    this.loadOrders();
                }
            });
        },
    },
};
</script>
