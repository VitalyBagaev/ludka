<template>
    <header class="navbar navbar-expand-lg navbar-dark ludo-navbar sticky-top">
        <div class="container-fluid px-lg-4">
            <a class="ludo-brand" href="#/" @click.prevent="changePage('HomePage')">
                <span>ЛУДО</span><span>МАНИЯ</span>
            </a>
            <button class="navbar-toggler border-secondary" type="button" data-bs-toggle="collapse" data-bs-target="#mainNav">
                <span class="navbar-toggler-icon"></span>
            </button>
            <div class="collapse navbar-collapse" id="mainNav">
                <ul class="navbar-nav me-auto ms-lg-3 gap-lg-1">
                    <li class="nav-item">
                        <a class="nav-link ludo-nav-link" :class="{ 'text-warning fw-semibold': isNavActive('HomePage', 'CasePage') }" href="#/" @click.prevent="changePage('HomePage')">Кейсы</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link ludo-nav-link" :class="{ 'text-warning fw-semibold': isNavActive('BattlesPage') }" href="#/battles" @click.prevent="changePage('BattlesPage')">Битвы</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link ludo-nav-link" :class="{ 'text-warning fw-semibold': isNavActive('UpgradePage') }" href="#/upgrade" @click.prevent="changePage('UpgradePage')">Апгрейд</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link ludo-nav-link" :class="{ 'text-warning fw-semibold': isNavActive('ContractsPage') }" href="#/contracts" @click.prevent="changePage('ContractsPage')">Контракты</a>
                    </li>
                    <li class="nav-item">
    <a class="nav-link ludo-nav-link" :class="{ 'text-warning fw-semibold': isNavActive('LeaderboardPage') }" href="#/top" @click.prevent="changePage('LeaderboardPage')">🏆 Топ</a>
</li>
                </ul>

                <ul class="navbar-nav align-items-lg-center gap-2">
                    <li class="nav-item">
                        <button type="button" class="btn btn-sm btn-ludo-outline" :title="muted ? 'Включить звук' : 'Выключить звук'" @click="toggleSound">
                            {{ muted ? '🔇' : '🔊' }}
                        </button>
                    </li>
                    <li class="nav-item d-none d-xl-block">
                        <span class="ludo-online-badge"><span class="ludo-live-dot"></span> {{ onlineCount }} онлайн</span>
                    </li>

                    <template v-if="user">
                        <li class="nav-item d-flex align-items-center gap-2">
                            <a href="#/user" class="ludo-balance text-decoration-none" @click.prevent="changePage('UserPage')">{{ userInfo.balance }} ₽</a>
                            <button type="button" class="btn btn-ludo-deposit" title="Пополнить баланс" @click="openDeposit">+</button>
                        </li>
                        <li class="nav-item">
                            <a href="#/user" class="ludo-nav-user" @click.prevent="changePage('UserPage')">
                                <UserAvatar :avatar="userInfo.avatar" :name="userInfo.name" :initials="userInfo.initials" size="sm" />
                                <span class="ludo-nav-username d-none d-lg-inline">{{ userInfo.name }}</span>
                            </a>
                        </li>

                        <li v-if="hasStaffMenu" class="nav-item dropdown">
                            <button class="btn btn-sm btn-ludo-outline dropdown-toggle" type="button" data-bs-toggle="dropdown" aria-expanded="false">
                                ⚙️ Управление
                            </button>
                            <ul class="dropdown-menu dropdown-menu-dark dropdown-menu-end">
                                <li v-if="safePermissions.manageRoles">
                                    <a class="dropdown-item" href="#" @click.prevent="changePage('AdminPage')">👥 Пользователи</a>
                                </li>
                                <li v-if="safePermissions.manageSkins">
                                    <a class="dropdown-item" href="#" @click.prevent="changePage('AdminSkinsPage')">🔫 Пул скинов</a>
                                </li>
                                <li v-if="safePermissions.manageCases">
                                    <a class="dropdown-item" href="#" @click.prevent="changePage('AdminCasesPage')">📦 Кейсы (админ)</a>
                                </li>
                                <li v-if="safePermissions.manageWithdrawals">
                                    <a class="dropdown-item" href="#" @click.prevent="changePage('AdminOrdersPage')">📤 Заявки на вывод</a>
                                </li>
                                <li v-if="safePermissions.manageFeatured && !safePermissions.manageCases">
                                    <a class="dropdown-item" href="#" @click.prevent="changePage('ManagerPage')">📊 Панель менеджера</a>
                                </li>
                            </ul>
                        </li>

                        <li class="nav-item">
                            <a class="nav-link ludo-nav-link text-danger-emphasis" href="#" @click.prevent="logoutUser()">Выйти</a>
                        </li>
                    </template>

                    <template v-else>
                        <li class="nav-item"><a class="nav-link ludo-nav-link" href="#/login" @click.prevent="changePage('AuthPage')">Вход</a></li>
                        <li class="nav-item"><a class="btn btn-ludo-primary btn-sm" href="#/register" @click.prevent="changePage('RegisterPage')">Регистрация</a></li>
                    </template>
                </ul>
            </div>
        </div>
    </header>
</template>
<script>
import { sounds } from '../helpers/sounds.js';
import UserAvatar from './UserAvatar.vue';

const emptyPermissions = {
    manageRoles: false,
    deleteUsers: false,
    viewUsers: false,
    manageSkins: false,
    manageCases: false,
    manageWithdrawals: false,
    manageFeatured: false,
    viewLiveDrops: false,
    viewStats: false,
};

export default {
    name: 'HeaderComponent',
    components: { UserAvatar },
    props: ['user', 'userInfo', 'permissions', 'logoutUser', 'changePage', 'openDeposit', 'onlineCount', 'currentPage'],
    data() {
        return {
            muted: sounds.isMuted(),
        };
    },
    computed: {
        safePermissions() {
            return { ...emptyPermissions, ...(this.permissions || {}) };
        },
        hasStaffMenu() {
            const p = this.safePermissions;
            return p.manageRoles || p.manageSkins || p.manageCases || p.manageWithdrawals || p.manageFeatured;
        },
    },
    methods: {
        isNavActive(...pages) {
            return pages.includes(this.currentPage);
        },
        toggleSound() {
            this.muted = sounds.toggleMute();
            if (!this.muted) {
                sounds.click();
            }
        },
    },
};
</script>
