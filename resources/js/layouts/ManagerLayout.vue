<script setup lang="ts">
import { Link, usePage } from '@inertiajs/vue3';
import { computed } from 'vue';

defineProps<{
    title: string;
}>();

const page = usePage();
const flash = computed(() => page.props.flash as { success?: string; error?: string });
</script>

<template>
    <div class="flex min-h-screen bg-cb-bg">
        <aside class="hidden w-64 shrink-0 flex-col border-r border-cb-border bg-cb-bg-secondary lg:flex">
            <div class="border-b border-cb-border p-5">
                <Link href="/" class="flex items-center gap-2">
                    <div class="flex h-8 w-8 items-center justify-center rounded-lg bg-gradient-orange">
                        <span class="font-display text-sm font-bold text-white">CB</span>
                    </div>
                    <span class="font-display text-sm font-bold">
                        <span class="text-cb-gold">MANAGER</span>
                    </span>
                </Link>
            </div>
            <nav class="flex-1 space-y-1 p-4">
                <Link
                    href="/manager"
                    class="block rounded-lg px-3 py-2 text-sm transition-colors"
                    :class="page.url === '/manager' ? 'bg-cb-orange/10 text-cb-orange' : 'text-cb-text-muted hover:bg-cb-bg-hover'"
                >
                    Дашборд
                </Link>
                <Link
                    href="/manager/cases"
                    class="block rounded-lg px-3 py-2 text-sm transition-colors"
                    :class="page.url.startsWith('/manager/cases') ? 'bg-cb-orange/10 text-cb-orange' : 'text-cb-text-muted hover:bg-cb-bg-hover'"
                >
                    Управление кейсами
                </Link>
                <Link
                    href="/manager/live-drops"
                    class="block rounded-lg px-3 py-2 text-sm transition-colors"
                    :class="page.url.startsWith('/manager/live-drops') ? 'bg-cb-orange/10 text-cb-orange' : 'text-cb-text-muted hover:bg-cb-bg-hover'"
                >
                    Live Drop
                </Link>
                <Link
                    href="/manager/users"
                    class="block rounded-lg px-3 py-2 text-sm transition-colors"
                    :class="page.url.startsWith('/manager/users') ? 'bg-cb-orange/10 text-cb-orange' : 'text-cb-text-muted hover:bg-cb-bg-hover'"
                >
                    Пользователи
                </Link>
            </nav>
            <div class="border-t border-cb-border p-4">
                <Link href="/" class="text-sm text-cb-text-muted hover:text-cb-text">← На сайт</Link>
            </div>
        </aside>

        <div class="flex flex-1 flex-col">
            <header class="border-b border-cb-border bg-cb-bg-secondary px-6 py-4">
                <h1 class="font-display text-xl font-bold">{{ title }}</h1>
            </header>

            <div v-if="flash?.success" class="mx-6 mt-4 rounded-lg bg-cb-green/10 px-4 py-3 text-sm text-cb-green">
                {{ flash.success }}
            </div>
            <div v-if="flash?.error" class="mx-6 mt-4 rounded-lg bg-cb-red/10 px-4 py-3 text-sm text-cb-red">
                {{ flash.error }}
            </div>

            <main class="flex-1 p-6">
                <slot />
            </main>
        </div>
    </div>
</template>
