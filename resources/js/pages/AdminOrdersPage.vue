<template>
    <div class="container-fluid px-lg-4 ludo-page">
        <div class="d-flex flex-wrap justify-content-between align-items-center gap-2 mb-4">
            <div>
                <h1 class="ludo-page-title mb-1">Заявки на вывод скинов</h1>
                <p class="ludo-page-sub mb-0">Обработка выводов игроков · одобрение или возврат в инвентарь</p>
            </div>
            <button type="button" class="btn btn-ludo-outline btn-sm" @click="loadOrders">↻ Обновить</button>
        </div>

        <div class="row g-3 mb-4">
            <div class="col-6 col-md-3">
                <div class="ludo-stat-card text-center h-100">
                    <div class="small text-secondary mb-1">Ожидают</div>
                    <strong class="fs-4 text-warning">{{ countByStatus('pending') }}</strong>
                </div>
            </div>
            <div class="col-6 col-md-3">
                <div class="ludo-stat-card text-center h-100">
                    <div class="small text-secondary mb-1">В работе</div>
                    <strong class="fs-4 text-info">{{ countByStatus('processing') }}</strong>
                </div>
            </div>
            <div class="col-6 col-md-3">
                <div class="ludo-stat-card text-center h-100">
                    <div class="small text-secondary mb-1">Выполнено</div>
                    <strong class="fs-4 text-success">{{ countByStatus('completed') }}</strong>
                </div>
            </div>
            <div class="col-6 col-md-3">
                <div class="ludo-stat-card text-center h-100">
                    <div class="small text-secondary mb-1">Сумма активных</div>
                    <strong class="fs-4 text-warning">{{ pendingSum }} ₽</strong>
                </div>
            </div>
        </div>

        <div class="row g-2 align-items-center mb-3">
            <div class="col-md-5 col-lg-4">
                <input v-model.trim="searchQuery" type="text" class="form-control form-control-sm ludo-form-control" placeholder="Поиск по игроку, скину или #ID..." />
            </div>
            <div class="col-md-7 col-lg-8">
                <div class="ludo-filter-tabs ludo-filter-tabs-sm mb-0 justify-content-md-end">
                    <button v-for="filter in filters" :key="filter.value" class="ludo-filter-tab" :class="{ active: statusFilter === filter.value }" @click="statusFilter = filter.value">
                        {{ filter.label }}
                    </button>
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
                            <th>Скин</th>
                            <th>Цена</th>
                            <th>Статус</th>
                            <th>Дата</th>
                            <th class="text-end">Действия</th>
                        </tr>
                    </thead>
                    <tbody>
                        <tr v-if="!filteredOrders.length">
                            <td colspan="7" class="text-center text-secondary py-4">Заявок не найдено</td>
                        </tr>
                        <tr v-for="order in filteredOrders" :key="order.id">
                            <td class="text-secondary">#{{ order.id }}</td>
                            <td>
                                <div class="fw-semibold">{{ order.userName }}</div>
                                <div class="small text-secondary">{{ order.userEmail }}</div>
                            </td>
                            <td class="fw-semibold">{{ order.weapon }} | {{ order.skinName }}</td>
                            <td class="ludo-case-price">{{ order.skinPrice }} ₽</td>
                            <td>
                                <span class="badge rounded-pill" :class="statusClass(order.status)">
                                    {{ order.statusLabel }}
                                </span>
                            </td>
                            <td class="small text-secondary">{{ formatDate(order.createdAt) }}</td>
                            <td class="text-end">
                                <div v-if="canManage(order)" class="d-flex flex-wrap justify-content-end gap-1">
                                    <button v-if="order.status === 'pending'" class="btn btn-sm btn-ludo-outline" @click="setStatus(order.id, 'processing')">
                                        В работу
                                    </button>
                                    <button class="btn btn-sm btn-ludo-primary" @click="setStatus(order.id, 'completed')">
                                        Выполнить
                                    </button>
                                    <button class="btn btn-sm btn-outline-danger" @click="setStatus(order.id, 'cancelled')">
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
            searchQuery: '',
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
        pendingSum() {
            return this.orders
                .filter((o) => o.status === 'pending' || o.status === 'processing')
                .reduce((sum, o) => sum + (Number(o.skinPrice) || 0), 0);
        },
        filteredOrders() {
            const q = this.searchQuery.toLowerCase();
            return this.orders.filter((order) => {
                if (this.statusFilter !== 'all' && order.status !== this.statusFilter) return false;
                if (q) {
                    const matchUser = (order.userName || '').toLowerCase().includes(q) || (order.userEmail || '').toLowerCase().includes(q);
                    const matchSkin = (order.weapon || '').toLowerCase().includes(q) || (order.skinName || '').toLowerCase().includes(q);
                    const matchId = String(order.id).includes(q);
                    if (!matchUser && !matchSkin && !matchId) return false;
                }
                return true;
            });
        },
    },
    mounted() {
        this.loadOrders();
    },
    methods: {
        countByStatus(st) {
            return this.orders.filter((o) => o.status === st).length;
        },
        loadOrders() {
            this.datasend('admin/withdrawals').then((result) => {
                if (result) this.orders = result;
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
            if (!value) return '—';
            return new Date(value).toLocaleString('ru-RU');
        },
        setStatus(id, status) {
            const labels = {
                processing: 'взять в работу',
                completed: 'отметить выполненной',
                cancelled: 'отменить (скин вернётся пользователю)',
            };
            if (!confirm('Подтвердить: ' + labels[status] + '?')) return;
            this.datasendJson('admin/withdrawals/' + id + '/status', 'POST', { status }).then((result) => {
                if (result && result.message) { alert(result.message); return; }
                if (result) this.loadOrders();
            });
        },
    },
};
</script>
