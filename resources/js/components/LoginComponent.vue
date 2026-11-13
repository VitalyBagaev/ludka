<template>
    <form @submit.prevent="authUser">
        <div class="mb-3">
            <label class="form-label small text-secondary">Email</label>
            <input v-model.trim="email" type="text" class="form-control ludo-form-control" placeholder="you@mail.ru"  />
            <div v-if="errors.email" class="ludo-alert ludo-alert-danger mt-2 mb-0 py-2 small">{{ errors.email.join('. ') }}</div>
        </div>
        <div class="mb-4">
            <label class="form-label small text-secondary">Пароль</label>
            <div class="input-group">
                <input v-model="password" :type="showPassword ? 'text' : 'password'" class="form-control ludo-form-control" placeholder="••••••••" required />
                <button type="button" class="btn btn-ludo-outline" @click="showPassword = !showPassword">
                    {{ showPassword ? '🙈' : '👁' }}
                </button>
            </div>
            <div v-if="errors.password" class="ludo-alert ludo-alert-danger mt-2 mb-0 py-2 small">{{ errors.password.join('. ') }}</div>
        </div>
        <button type="submit" class="btn btn-ludo-primary w-100 py-2 fw-semibold" :disabled="loading">
            {{ loading ? 'Входим в аккаунт...' : 'Войти' }}
        </button>
    </form>
</template>

<script>
export default {
    name: 'LoginComponent',
    props: ['datasend', 'changeToken', 'changePage'],
    data() {
        return {
            email: '',
            password: '',
            showPassword: false,
            loading: false,
            errors: {},
        };
    },
    methods: {
        authUser() {
            if (this.loading) return;
            this.errors = {};
            this.loading = true;
            const formdata = new FormData();
            if (this.email) formdata.append('email', this.email);
            if (this.password) formdata.append('password', this.password);
            this.datasend('login', 'POST', formdata).then((result) => {
                this.loading = false;
                if (!result) return;
                if (result.errors) this.errors = result.errors;
                if (result.token) {
                    this.changeToken(result.token);
                    this.changePage('HomePage');
                }
            });
        },
    },
};
</script>
