<script setup lang="ts">
import DepositModal from '@/components/DepositModal.vue';
import { formatPrice } from '@/lib/game';
import { Link, router, usePage } from '@inertiajs/vue3';
import { Coins, LogIn, LogOut, Menu, Shield, Users, X } from 'lucide-vue-next';
import { computed, ref } from 'vue';

const page = usePage();
const mobileMenuOpen = ref(false);
const depositOpen = ref(false);

interface AuthUser {
    id: number;
    name: string;
    email: string;
    balance: number;
    role: string;
    roleLabel: string;
}

const user = computed(() => (page.props.auth as { user?: AuthUser })?.user ?? null);
const isLoggedIn = computed(() => !!user.value);
const isAdmin = computed(() => user.value?.role === 'admin');
const isManager = computed(() => user.value?.role === 'manager' || isAdmin.value);

const navItems = [
    { label: 'Кейсы', href: '/', route: 'home' },
    { label: 'Битвы', href: '/battles', route: 'battles' },
    { label: 'Апгрейд', href: '/upgrade', route: 'upgrade' },
    { label: 'Контракты', href: '/contracts', route: 'contracts' },
];

const currentRoute = computed(() => {
    const url = page.url;
    if (url.startsWith('/battles')) return 'battles';
    if (url.startsWith('/upgrade')) return 'upgrade';
    if (url.startsWith('/contracts')) return 'contracts';
    if (url.startsWith('/case')) return 'home';
    return 'home';
});

function logout() {
    router.post('/logout');
}
</script>

<template>
    <header class="sticky top-0 z-50 border-b border-cb-border bg-cb-bg-secondary/95 backdrop-blur-md">
        <div class="mx-auto flex h-16 max-w-[1600px] items-center justify-between px-4 lg:px-6">
            <Link href="/" class="flex items-center gap-2">
                <div class="flex h-9 w-9 items-center justify-center rounded-lg bg-gradient-orange">
                    <span class="font-display text-lg font-bold text-white">CB</span>
                </div>
                <span class="font-display hidden text-xl font-bold tracking-wide sm:block">
                    <span class="text-gradient-orange">CASE</span><span class="text-white">BATTLE</span>
                </span>
            </Link>

            <nav class="hidden items-center gap-1 md:flex">
                <Link
                    v-for="item in navItems"
                    :key="item.route"
                    :href="item.href"
                    class="rounded-lg px-4 py-2 text-sm font-medium transition-colors"
                    :class="
                        currentRoute === item.route
                            ? 'bg-cb-orange/10 text-cb-orange'
                            : 'text-cb-text-muted hover:bg-cb-bg-hover hover:text-cb-text'
                    "
                >
                    {{ item.label }}
                </Link>
            </nav>

            <div class="flex items-center gap-3">
                <div class="hidden items-center gap-1.5 text-cb-text-muted sm:flex">
                    <Users class="h-4 w-4 text-cb-green" />
                    <span class="text-sm"><span class="font-medium text-cb-green">1,247</span> онлайн</span>
                </div>

                <template v-if="isLoggedIn && user">
                    <Link
                        v-if="isAdmin"
                        href="/admin"
                        class="hidden items-center gap-1.5 rounded-lg border border-cb-red/30 bg-cb-red/10 px-3 py-1.5 text-xs font-semibold text-cb-red sm:flex"
                    >
                        <Shield class="h-3.5 w-3.5" />
                        Админ
                    </Link>
                    <Link
                        v-if="isManager"
                        href="/manager"
                        class="hidden items-center gap-1.5 rounded-lg border border-cb-gold/30 bg-cb-gold/10 px-3 py-1.5 text-xs font-semibold text-cb-gold sm:flex"
                    >
                        <Shield class="h-3.5 w-3.5" />
                        Менеджер
                    </Link>
                    <div class="flex items-center gap-2 rounded-lg border border-cb-border bg-cb-bg-card px-3 py-1.5">
                        <Coins class="h-4 w-4 text-cb-gold" />
                        <span class="text-sm font-semibold text-cb-gold">{{ formatPrice(user.balance) }}</span>
                    </div>
                    <button
                        class="hidden rounded-lg bg-gradient-orange px-4 py-2 text-sm font-semibold text-white transition-opacity hover:opacity-90 sm:block"
                        @click="depositOpen = true"
                    >
                        Пополнить
                    </button>
                    <button
                        class="hidden rounded-lg border border-cb-border px-3 py-2 text-sm text-cb-text-muted transition-colors hover:bg-cb-bg-hover sm:block"
                        title="Выйти"
                        @click="logout"
                    >
                        <LogOut class="h-4 w-4" />
                    </button>
                </template>
                <template v-else>
                    <Link
                        href="/login"
                        class="hidden items-center gap-2 rounded-lg bg-gradient-orange px-4 py-2 text-sm font-semibold text-white transition-opacity hover:opacity-90 sm:flex"
                    >
                        <LogIn class="h-4 w-4" />
                        Войти
                    </Link>
                </template>

                <button
                    class="rounded-lg p-2 text-cb-text-muted hover:bg-cb-bg-hover md:hidden"
                    @click="mobileMenuOpen = !mobileMenuOpen"
                >
                    <X v-if="mobileMenuOpen" class="h-5 w-5" />
                    <Menu v-else class="h-5 w-5" />
                </button>
            </div>
        </div>

        <div v-if="mobileMenuOpen" class="border-t border-cb-border bg-cb-bg-secondary px-4 py-3 md:hidden">
            <nav class="flex flex-col gap-1">
                <Link
                    v-for="item in navItems"
                    :key="item.route"
                    :href="item.href"
                    class="rounded-lg px-4 py-2.5 text-sm font-medium transition-colors"
                    :class="
                        currentRoute === item.route
                            ? 'bg-cb-orange/10 text-cb-orange'
                            : 'text-cb-text-muted hover:bg-cb-bg-hover'
                    "
                    @click="mobileMenuOpen = false"
                >
                    {{ item.label }}
                </Link>
            </nav>
            <button
                v-if="isLoggedIn"
                class="mt-2 flex w-full items-center justify-center gap-2 rounded-lg bg-gradient-orange px-4 py-2.5 text-sm font-semibold text-white"
                @click="depositOpen = true; mobileMenuOpen = false"
            >
                Пополнить баланс
            </button>
            <Link
                v-else
                href="/login"
                class="mt-2 flex w-full items-center justify-center gap-2 rounded-lg bg-gradient-orange px-4 py-2.5 text-sm font-semibold text-white"
            >
                <LogIn class="h-4 w-4" />
                Войти
            </Link>
        </div>
    </header>

    <DepositModal :open="depositOpen" @close="depositOpen = false" />
</template>
