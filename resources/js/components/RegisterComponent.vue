<template>
    <form @submit.prevent="registerUser">
        <div class="mb-3">
            <label class="form-label">Имя</label>
            <input v-model="name" type="text" class="form-control ludo-form-control" placeholder="Никнейм" />
            <div v-if="errors.name" class="ludo-alert ludo-alert-danger mt-2 mb-0 py-2 small">{{ errors.name.join('. ') }}</div>
        </div>
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
        <button type="submit" class="btn btn-ludo-primary w-100">Создать аккаунт</button>
    </form>
</template>

<script>
export default {
    name: 'RegisterComponent',
    props: ['datasend', 'changeToken', 'changePage'],
    data() {
        return { name: null, email: null, password: null, errors: {} };
    },
    methods: {
        registerUser() {
            this.errors = {};
            let formdata = new FormData();
            if (this.name) formdata.append('name', this.name);
            if (this.email) formdata.append('email', this.email);
            if (this.password) formdata.append('password', this.password);
            this.datasend('register', 'POST', formdata).then((result) => {
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
