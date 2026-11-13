<script setup lang="ts">
import AppHeader from '@/components/AppHeader.vue';
import LiveFeed from '@/components/LiveFeed.vue';
import type { LiveDrop } from '@/types';

defineProps<{
    liveDrops?: LiveDrop[];
}>();
</script>

<template>
    <div class="flex min-h-screen flex-col bg-cb-bg">
        <AppHeader />
        <div class="flex flex-1">
            <LiveFeed :drops="liveDrops" />
            <main class="flex-1 overflow-x-hidden">
                <slot />
            </main>
        </div>
        <footer class="border-t border-cb-border bg-cb-bg-secondary py-6">
            <div class="mx-auto max-w-[1600px] px-4 lg:px-6">
                <div class="flex flex-col items-center justify-between gap-4 sm:flex-row">
                    <div class="flex items-center gap-2">
                        <div class="flex h-7 w-7 items-center justify-center rounded bg-gradient-orange">
                            <span class="font-display text-xs font-bold text-white">CB</span>
                        </div>
                        <span class="font-display text-sm font-bold">
                            <span class="text-gradient-orange">CASE</span><span class="text-white">BATTLE</span>
                        </span>
                    </div>
                    <p class="text-xs text-cb-text-muted">
                        © 2026 CaseBattle. Все права защищены. 18+
                    </p>
                    <div class="flex gap-4 text-xs text-cb-text-muted">
                        <a href="#" class="hover:text-cb-text">Правила</a>
                        <a href="#" class="hover:text-cb-text">Поддержка</a>
                        <a href="#" class="hover:text-cb-text">FAQ</a>
                    </div>
                </div>
            </div>
        </footer>
    </div>
</template>
