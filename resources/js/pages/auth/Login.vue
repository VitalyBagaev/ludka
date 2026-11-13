<script setup lang="ts">
import { Link, useForm } from '@inertiajs/vue3';

defineProps<{
    canResetPassword?: boolean;
    canRegister?: boolean;
    status?: string;
}>();

const form = useForm({
    email: '',
    password: '',
    remember: false,
});

function submit() {
    form.post('/login', {
        onFinish: () => form.reset('password'),
    });
}
</script>

<template>
    <div class="flex min-h-screen items-center justify-center bg-cb-bg px-4">
        <div class="w-full max-w-md">
            <div class="mb-8 text-center">
                <Link href="/" class="inline-flex items-center gap-2">
                    <div class="flex h-10 w-10 items-center justify-center rounded-lg bg-gradient-orange">
                        <span class="font-display text-lg font-bold text-white">CB</span>
                    </div>
                </Link>
                <h1 class="font-display mt-4 text-2xl font-bold">Вход в CaseBattle</h1>
                <div class="mt-3 space-y-1 text-xs text-cb-text-muted">
                    <p>Админ: admin@casebattle.test / password</p>
                    <p>Менеджер: manager@casebattle.test / password</p>
                    <p>Игрок: test@example.com / password</p>
                </div>
            </div>

            <div v-if="status" class="mb-4 rounded-lg bg-cb-green/10 p-3 text-center text-sm text-cb-green">
                {{ status }}
            </div>

            <form class="rounded-2xl border border-cb-border bg-cb-bg-secondary p-6" @submit.prevent="submit">
                <div class="mb-4">
                    <label class="mb-1.5 block text-sm text-cb-text-muted">Email</label>
                    <input
                        v-model="form.email"
                        type="email"
                        required
                        autofocus
                        class="w-full rounded-xl border border-cb-border bg-cb-bg-card px-4 py-2.5 outline-none focus:border-cb-orange"
                    />
                    <p v-if="form.errors.email" class="mt-1 text-xs text-cb-red">{{ form.errors.email }}</p>
                </div>

                <div class="mb-4">
                    <label class="mb-1.5 block text-sm text-cb-text-muted">Пароль</label>
                    <input
                        v-model="form.password"
                        type="password"
                        required
                        class="w-full rounded-xl border border-cb-border bg-cb-bg-card px-4 py-2.5 outline-none focus:border-cb-orange"
                    />
                    <p v-if="form.errors.password" class="mt-1 text-xs text-cb-red">{{ form.errors.password }}</p>
                </div>

                <label class="mb-6 flex items-center gap-2 text-sm text-cb-text-muted">
                    <input v-model="form.remember" type="checkbox" class="accent-cb-orange" />
                    Запомнить меня
                </label>

                <button
                    type="submit"
                    class="w-full rounded-xl bg-gradient-orange py-3 font-semibold text-white transition-opacity hover:opacity-90 disabled:opacity-50"
                    :disabled="form.processing"
                >
                    {{ form.processing ? 'Вход...' : 'Войти' }}
                </button>

                <p v-if="canRegister" class="mt-4 text-center text-sm text-cb-text-muted">
                    Нет аккаунта?
                    <Link href="/register" class="text-cb-orange hover:underline">Регистрация</Link>
                </p>
            </form>
        </div>
    </div>
</template>
