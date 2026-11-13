<template>
    <form @submit.prevent="authUser">
        <div class="mb-3">
            <label class="form-label">Email</label>
            <input v-model="email" type="text" class="form-control ludo-form-control" placeholder="you@mail.ru" />
            <div v-if="errors.email" class="ludo-alert ludo-alert-danger mt-2 mb-0 py-2 small">{{ errors.email.join('. ') }}</div>
        </div>
        <div class="mb-4">
            <label class="form-label">Пароль</label>
            <input v-model="password" type="password" class="form-control ludo-form-control" placeholder="••••••••" />
            <div v-if="errors.password" class="ludo-alert ludo-alert-danger mt-2 mb-0 py-2 small">{{ errors.password.join('. ') }}</div>
        </div>
        <button type="submit" class="btn btn-ludo-primary w-100">Войти</button>
    </form>
</template>

<script>
export default {
    name: 'LoginComponent',
    props: ['datasend', 'changeToken', 'changePage'],
    data() {
        return { email: null, password: null, errors: {} };
    },
    methods: {
        authUser() {
            this.errors = {};
            let formdata = new FormData();
            if (this.email) formdata.append('email', this.email);
            if (this.password) formdata.append('password', this.password);
            this.datasend('login', 'POST', formdata).then((result) => {
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
