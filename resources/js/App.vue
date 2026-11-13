<template>
    <div class="d-flex flex-column min-vh-100">
        <HeaderComponent
            :user="user"
            :userInfo="userInfo"
            :permissions="permissions"
            :logoutUser="logoutUser"
            :changePage="changePage"
            :openDeposit="openDeposit"
            :onlineCount="onlineCount"
        />

        <LiveTicker v-if="liveDrops.length" :drops="liveDrops" :changePage="changePage" />

        <div class="d-flex flex-grow-1">
            <main class="flex-grow-1 min-w-0">
                <HomePage
                    v-if="page == 'HomePage'"
                    :changePage="changePage"
                    :datasend="datasend"
                    :user="user"
                    :userInfo="userInfo"
                    :getUser="getUser"
                    :caseImg="caseImg"
                    :imgError="imgError"
                />
                <CasePage
                    v-if="page == 'CasePage'"
                    :changePage="changePage"
                    :datasend="datasend"
                    :pageId="pageId"
                    :user="user"
                    :userInfo="userInfo"
                    :getUser="getUser"
                    :onLiveDrop="prependLiveDrop"
                    :rarityClass="rarityClass"
                    :rarityLabel="rarityLabel"
                    :skinImg="skinImg"
                    :caseImg="caseImg"
                    :imgError="imgError"
                />
                <BattlesPage
                    v-if="page == 'BattlesPage'"
                    :datasend="datasend"
                    :user="user"
                    :changePage="changePage"
                />
                <UpgradePage
                    v-if="page == 'UpgradePage'"
                    :datasend="datasend"
                    :datasendJson="datasendJson"
                    :user="user"
                    :userInfo="userInfo"
                    :getUser="getUser"
                    :changePage="changePage"
                    :skinImg="skinImg"
                    :imgError="imgError"
                    :rarityClass="rarityClass"
                    :rarityLabel="rarityLabel"
                />
                <ContractsPage
                    v-if="page == 'ContractsPage'"
                    :datasend="datasend"
                    :datasendJson="datasendJson"
                    :user="user"
                    :userInfo="userInfo"
                    :skinImg="skinImg"
                    :imgError="imgError"
                    :rarityClass="rarityClass"
                    :rarityLabel="rarityLabel"
                />
                <UserPage
                    v-if="page == 'UserPage' && user"
                    :changePage="changePage"
                    :datasend="datasend"
                    :datasendJson="datasendJson"
                    :user="user"
                    :getUser="getUser"
                    :openDeposit="openDeposit"
                    :skinImg="skinImg"
                    :imgError="imgError"
                    :sourceLabel="sourceLabel"
                    :txType="txType"
                />
                <PlayerPage
                    v-if="page == 'PlayerPage'"
                    :changePage="changePage"
                    :datasend="datasend"
                    :pageId="pageId"
                    :skinImg="skinImg"
                    :imgError="imgError"
                />
                <SecretCasePage
                    v-if="page == 'SecretCasePage'"
                    :changePage="changePage"
                    :datasend="datasend"
                    :user="user"
                    :getUser="getUser"
                    :onLiveDrop="prependLiveDrop"
                    :skinImg="skinImg"
                    :caseImg="caseImg"
                    :imgError="imgError"
                    :rarityClass="rarityClass"
                />
                <AuthPage v-if="page == 'AuthPage'" :datasend="datasend" :changeToken="changeToken" :changePage="changePage" />
                <RegisterPage v-if="page == 'RegisterPage'" :datasend="datasend" :changeToken="changeToken" :changePage="changePage" />
                <AdminPage v-if="page == 'AdminPage' && permissions.manageRoles" :datasend="datasend" :datasendJson="datasendJson" />
                <AdminSkinsPage
                    v-if="page == 'AdminSkinsPage' && permissions.manageSkins"
                    :datasend="datasend"
                    :datasendJson="datasendJson"
                    :skinImg="skinImg"
                    :imgError="imgError"
                />
                <AdminCasesPage
                    v-if="page == 'AdminCasesPage' && permissions.manageCases"
                    :datasend="datasend"
                    :datasendJson="datasendJson"
                    :caseImg="caseImg"
                    :imgError="imgError"
                />
                <AdminOrdersPage
                    v-if="page == 'AdminOrdersPage' && permissions.manageWithdrawals"
                    :datasend="datasend"
                    :datasendJson="datasendJson"
                />
                <ManagerPage
                    v-if="page == 'ManagerPage' && permissions.manageFeatured && !permissions.manageCases"
                    :datasend="datasend"
                    :skinImg="skinImg"
                    :imgError="imgError"
                />
            </main>

            <LiveFeedSidebar
                v-if="showLiveFeed()"
                :drops="liveDrops"
                :skinImg="skinImg"
                :imgError="imgError"
                :changePage="changePage"
            />
        </div>

        <FooterComponent :changePage="changePage" />

        <DepositModal
            :show="depositOpen"
            :balance="userInfo.balance || 0"
            :datasend="datasend"
            :promos="promoInfo"
            @close="closeDeposit"
            @done="onDepositDone"
        />
    </div>
</template>

<script>
import { caseImg, imgError, rarityClass, rarityLabel, skinImg } from './helpers/images.js';
import DepositModal from './components/DepositModal.vue';
import FooterComponent from './components/FooterComponent.vue';
import HeaderComponent from './components/HeaderComponent.vue';
import LiveFeedSidebar from './components/LiveFeedSidebar.vue';
import LiveTicker from './components/LiveTicker.vue';
import AdminCasesPage from './pages/AdminCasesPage.vue';
import AdminOrdersPage from './pages/AdminOrdersPage.vue';
import AdminPage from './pages/AdminPage.vue';
import AdminSkinsPage from './pages/AdminSkinsPage.vue';
import AuthPage from './pages/AuthPage.vue';
import BattlesPage from './pages/BattlesPage.vue';
import CasePage from './pages/CasePage.vue';
import ContractsPage from './pages/ContractsPage.vue';
import HomePage from './pages/HomePage.vue';
import ManagerPage from './pages/ManagerPage.vue';
import PlayerPage from './pages/PlayerPage.vue';
import RegisterPage from './pages/RegisterPage.vue';
import SecretCasePage from './pages/SecretCasePage.vue';
import UpgradePage from './pages/UpgradePage.vue';
import UserPage from './pages/UserPage.vue';

export default {
    name: 'App',
    components: {
        HeaderComponent,
        FooterComponent,
        LiveFeedSidebar,
        LiveTicker,
        DepositModal,
        HomePage,
        CasePage,
        BattlesPage,
        UpgradePage,
        ContractsPage,
        UserPage,
        PlayerPage,
        SecretCasePage,
        AuthPage,
        RegisterPage,
        AdminPage,
        AdminSkinsPage,
        AdminCasesPage,
        AdminOrdersPage,
        ManagerPage,
    },
    data() {
        return {
            page: localStorage.getItem('page') || 'HomePage',
            pageId: localStorage.getItem('pageId') || null,
            API: '/api/',
            user: false,
            permissions: {
                manageRoles: false,
                deleteUsers: false,
                viewUsers: false,
                manageSkins: false,
                manageCases: false,
                manageWithdrawals: false,
                manageFeatured: false,
                viewLiveDrops: false,
                viewStats: false,
            },
            userInfo: {},
            liveDrops: [],
            depositOpen: false,
            promoInfo: null,
            onlineCount: 1247,
            rarityClass,
            rarityLabel,
            sourceLabel: {
                case_open: 'Из кейса',
                upgrade: 'Апгрейд',
                contract: 'Контракт',
            },
            txType: {
                deposit: 'Пополнение',
                withdraw: 'Вывод скинов',
                case_open: 'Открытие кейса',
                sell: 'Продажа скина',
                promo: 'Промокод',
                upgrade_boost: 'Буст апгрейда',
                contract_boost: 'Буст контракта',
            },
            liveTimer: null,
            onlineTimer: null,
            promoTimer: null,
        };
    },
    mounted() {
        if (localStorage.getItem('token')) {
            this.getUser();
        }
        this.getLiveDrops();
        this.getPromos();
        this.liveTimer = setInterval(() => this.getLiveDrops(), 10000);
        this.promoTimer = setInterval(() => this.getPromos(), 60000);
        this.onlineTimer = setInterval(() => {
            this.onlineCount = 1200 + Math.floor(Math.random() * 150);
        }, 30000);
        this.setupEcho();
    },
    unmounted() {
        clearInterval(this.liveTimer);
        clearInterval(this.onlineTimer);
        clearInterval(this.promoTimer);
    },
    methods: {
        skinImg,
        caseImg,
        imgError,
        showLiveFeed() {
            const hidden = ['AuthPage', 'RegisterPage', 'AdminPage', 'AdminSkinsPage', 'AdminCasesPage', 'AdminOrdersPage', 'ManagerPage'];
            return !hidden.includes(this.page);
        },
        openDeposit() {
            this.depositOpen = true;
        },
        closeDeposit() {
            this.depositOpen = false;
        },
        onDepositDone() {
            this.getUser();
            this.getPromos();
        },
        getPromos() {
            this.datasend('promo').then((result) => {
                if (result) {
                    this.promoInfo = result;
                }
            });
        },
        setupEcho() {
            if (!window.Echo) {
                return;
            }
            window.Echo.channel('live-drops').listen('.LiveDropCreated', (e) => {
                if (e && e.drop) {
                    this.prependLiveDrop(e.drop);
                }
            });
        },
        prependLiveDrop(drop) {
            this.liveDrops = [drop, ...this.liveDrops.filter((d) => d.id !== drop.id)].slice(0, 30);
        },
        getLiveDrops() {
            this.datasend('live-drops').then((result) => {
                if (result) {
                    this.liveDrops = result;
                }
            });
        },
        getUser() {
            this.datasend('user').then((response) => {
                if (!response) {
                    return;
                }
                this.user = true;
                this.userInfo = response;
                this.permissions = {
                    manageRoles: false,
                    deleteUsers: false,
                    viewUsers: false,
                    manageSkins: false,
                    manageCases: false,
                    manageWithdrawals: false,
                    manageFeatured: false,
                    viewLiveDrops: false,
                    viewStats: false,
                    ...(response.permissions || {}),
                };
            });
        },
        changePage(page, pageId = null) {
            this.page = page;
            this.pageId = pageId;
            localStorage.setItem('page', page);
            localStorage.setItem('pageId', pageId);
        },
        logoutUser() {
            if (localStorage.getItem('token')) {
                this.datasend('logout', 'POST');
            }
            localStorage.removeItem('token');
            this.user = false;
            this.permissions = {
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
            this.userInfo = {};
            this.changePage('HomePage');
        },
        changeToken(token) {
            localStorage.setItem('token', token);
            this.user = true;
            this.getUser();
        },
        async datasend(route, method = 'GET', formdata = null) {
            const myHeaders = new Headers();
            myHeaders.append('Accept', 'application/json');
            if (localStorage.getItem('token')) {
                myHeaders.append('Authorization', 'Bearer ' + localStorage.getItem('token'));
            }
            const requestOptions = {
                method: method,
                headers: myHeaders,
                redirect: 'follow',
            };
            if (method != 'GET') {
                requestOptions.body = formdata;
            }
            const response = await fetch(this.API + route, requestOptions);
            if (response.status == 401) {
                this.logoutUser();
                return;
            }
            if (response.status == 204) {
                return {};
            }
            return response.json();
        },
        async datasendJson(route, method = 'POST', data = null) {
            const myHeaders = new Headers();
            myHeaders.append('Accept', 'application/json');
            myHeaders.append('Content-Type', 'application/json');
            if (localStorage.getItem('token')) {
                myHeaders.append('Authorization', 'Bearer ' + localStorage.getItem('token'));
            }
            const response = await fetch(this.API + route, {
                method: method,
                headers: myHeaders,
                body: JSON.stringify(data),
            });
            if (response.status == 401) {
                this.logoutUser();
                return;
            }
            return response.json();
        },
    },
};
</script>
