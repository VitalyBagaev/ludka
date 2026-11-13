<template>
    <div class="container ludo-page">
        <div class="row justify-content-center">
            <div class="col-md-5">
                <div class="ludo-auth-card">
                    <div class="ludo-auth-header">
                        <h1>Вход в Лудоманию</h1>
                    </div>
                    <div class="p-4">
                        <LoginComponent :datasend="datasend" :changeToken="changeToken" :changePage="changePage" />
                        <p class="text-center mt-4 mb-0 small text-secondary">
                            Нет аккаунта?
                            <a href="#" class="text-warning" @click.prevent="changePage('RegisterPage')">Регистрация</a>
                        </p>
                    </div>
                </div>
            </div>
        </div>
    </div>
</template>

<script>
import LoginComponent from '../components/LoginComponent.vue';
export default {
    name: 'AuthPage',
    components: { LoginComponent },
    props: ['datasend', 'changeToken', 'changePage'],
};
</script>
