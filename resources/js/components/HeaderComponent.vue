<template>
    <nav class="navbar navbar-expand-lg navbar-dark ludo-navbar sticky-top">
        <div class="container-fluid px-lg-4">
            <a class="ludo-brand" href="#" @click.prevent="changePage('HomePage')">
                <span>ЛУДО</span><span>МАНИЯ</span>
            </a>
            <button class="navbar-toggler border-secondary" type="button" data-bs-toggle="collapse" data-bs-target="#mainNav">
                <span class="navbar-toggler-icon"></span>
            </button>
            <div class="collapse navbar-collapse" id="mainNav">
                <ul class="navbar-nav me-auto ms-lg-3 gap-lg-1">
                    <li class="nav-item"><a class="nav-link ludo-nav-link" href="#" @click.prevent="changePage('HomePage')">Кейсы</a></li>
                    <li class="nav-item"><a class="nav-link ludo-nav-link" href="#" @click.prevent="changePage('BattlesPage')">Битвы</a></li>
                    <li class="nav-item"><a class="nav-link ludo-nav-link" href="#" @click.prevent="changePage('UpgradePage')">Апгрейд</a></li>
                    <li class="nav-item"><a class="nav-link ludo-nav-link" href="#" @click.prevent="changePage('ContractsPage')">Контракты</a></li>
                </ul>
                <ul class="navbar-nav align-items-lg-center gap-lg-2">
                    <li class="nav-item d-none d-lg-block">
                        <span class="ludo-online-badge"><span class="ludo-live-dot"></span> {{ onlineCount }} онлайн</span>
                    </li>
                    <template v-if="user">
                        <li class="nav-item d-flex align-items-center gap-2">
                            <a href="#" class="ludo-balance text-decoration-none" @click.prevent="changePage('UserPage')">{{ userInfo.balance }} ₽</a>
                            <button class="btn btn-ludo-deposit" title="Пополнить" @click="openDeposit">+</button>
                        </li>
                        <li class="nav-item">
                            <a href="#" class="ludo-nav-user" @click.prevent="changePage('UserPage')">
                                <UserAvatar :avatar="userInfo.avatar" :name="userInfo.name" :initials="userInfo.initials" size="sm" />
                                <span class="ludo-nav-username d-none d-lg-inline">{{ userInfo.name }}</span>
                            </a>
                        </li>
                        <template v-if="safePermissions.manageRoles">
                            <li class="nav-item">
                                <a class="nav-link ludo-nav-link" href="#" @click.prevent="changePage('AdminPage')">Пользователи</a>
                            </li>
                        </template>
                        <template v-if="safePermissions.manageSkins">
                            <li class="nav-item">
                                <a class="nav-link ludo-nav-link" href="#" @click.prevent="changePage('AdminSkinsPage')">Скины</a>
                            </li>
                        </template>
                        <template v-if="safePermissions.manageCases">
                            <li class="nav-item">
                                <a class="nav-link ludo-nav-link" href="#" @click.prevent="changePage('AdminCasesPage')">Кейсы (админ)</a>
                            </li>
                        </template>
                        <template v-if="safePermissions.manageWithdrawals">
                            <li class="nav-item">
                                <a class="nav-link ludo-nav-link" href="#" @click.prevent="changePage('AdminOrdersPage')">Выводы</a>
                            </li>
                        </template>
                        <template v-if="safePermissions.manageFeatured && !safePermissions.manageCases">
                            <li class="nav-item">
                                <a class="nav-link ludo-nav-link" href="#" @click.prevent="changePage('ManagerPage')">Менеджер</a>
                            </li>
                        </template>
                        <li class="nav-item"><a class="nav-link ludo-nav-link" href="#" @click.prevent="logoutUser()">Выйти</a></li>
                    </template>
                    <template v-else>
                        <li class="nav-item"><a class="nav-link ludo-nav-link" href="#" @click.prevent="changePage('AuthPage')">Вход</a></li>
                        <li class="nav-item"><a class="btn btn-ludo-primary btn-sm" href="#" @click.prevent="changePage('RegisterPage')">Регистрация</a></li>
                    </template>
                </ul>
            </div>
        </div>
    </nav>
</template>

<script>
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
    props: ['user', 'userInfo', 'permissions', 'logoutUser', 'changePage', 'openDeposit', 'onlineCount'],
    computed: {
        safePermissions() {
            return { ...emptyPermissions, ...(this.permissions || {}) };
        },
    },
};
</script>
