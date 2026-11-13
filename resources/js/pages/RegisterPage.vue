<template>
    <div class="container ludo-page">
        <div class="row justify-content-center">
            <div class="col-md-5">
                <div class="ludo-auth-card">
                    <div class="ludo-auth-header">
                        <h1>Регистрация</h1>
                    </div>
                    <div class="p-4">
                        <RegisterComponent :datasend="datasend" :changeToken="changeToken" :changePage="changePage" />
                        <p class="text-center mt-4 mb-0 small text-secondary">
                            Уже есть аккаунт?
                            <a href="#" class="text-warning" @click.prevent="changePage('AuthPage')">Войти</a>
                        </p>
                    </div>
                </div>
            </div>
        </div>
    </div>
</template>

<script>
import RegisterComponent from '../components/RegisterComponent.vue';
export default {
    name: 'RegisterPage',
    components: { RegisterComponent },
    props: ['datasend', 'changeToken', 'changePage'],
};
</script>
