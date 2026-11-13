<script setup lang="ts">
import { Link, useForm } from '@inertiajs/vue3';

const form = useForm({
    name: '',
    email: '',
    password: '',
    password_confirmation: '',
});

function submit() {
    form.post('/register', {
        onFinish: () => form.reset('password', 'password_confirmation'),
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
                <h1 class="font-display mt-4 text-2xl font-bold">Регистрация</h1>
            </div>

            <form class="rounded-2xl border border-cb-border bg-cb-bg-secondary p-6" @submit.prevent="submit">
                <div class="mb-4">
                    <label class="mb-1.5 block text-sm text-cb-text-muted">Имя</label>
                    <input
                        v-model="form.name"
                        type="text"
                        required
                        class="w-full rounded-xl border border-cb-border bg-cb-bg-card px-4 py-2.5 outline-none focus:border-cb-orange"
                    />
                    <p v-if="form.errors.name" class="mt-1 text-xs text-cb-red">{{ form.errors.name }}</p>
                </div>

                <div class="mb-4">
                    <label class="mb-1.5 block text-sm text-cb-text-muted">Email</label>
                    <input
                        v-model="form.email"
                        type="email"
                        required
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

                <div class="mb-6">
                    <label class="mb-1.5 block text-sm text-cb-text-muted">Подтверждение пароля</label>
                    <input
                        v-model="form.password_confirmation"
                        type="password"
                        required
                        class="w-full rounded-xl border border-cb-border bg-cb-bg-card px-4 py-2.5 outline-none focus:border-cb-orange"
                    />
                </div>

                <button
                    type="submit"
                    class="w-full rounded-xl bg-gradient-orange py-3 font-semibold text-white transition-opacity hover:opacity-90 disabled:opacity-50"
                    :disabled="form.processing"
                >
                    {{ form.processing ? 'Регистрация...' : 'Зарегистрироваться' }}
                </button>

                <p class="mt-4 text-center text-sm text-cb-text-muted">
                    Уже есть аккаунт?
                    <Link href="/login" class="text-cb-orange hover:underline">Войти</Link>
                </p>
            </form>
        </div>
    </div>
</template>
