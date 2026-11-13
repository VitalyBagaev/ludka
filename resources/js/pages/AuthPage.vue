<template>
    <div class="container ludo-page d-flex align-items-center" style="min-height: calc(100vh - 180px);">
        <div class="row justify-content-center w-100">
            <div class="col-md-6 col-lg-5">
                <div class="ludo-auth-card ludo-auth-glow">
                    <div class="ludo-auth-header text-center">
                        <div class="mb-2" style="font-size: 28px;">🎯</div>
                        <h1 class="mb-1">Вход в <em>Лудоманию</em></h1>
                        <p class="small text-secondary mb-0">Открывай кейсы, участвуй в битвах и забирай скины CS2</p>
                    </div>
                    <div class="p-4">
                        <LoginComponent :datasend="datasend" :changeToken="changeToken" :changePage="changePage" />
                        <div class="mt-4 pt-3 border-top border-secondary-subtle text-center">
                            <p class="mb-0 small text-secondary">
                                Ещё нет аккаунта?
                                <a href="#" class="text-warning fw-semibold text-decoration-none" @click.prevent="changePage('RegisterPage')">
                                    Зарегистрироваться →
                                </a>
                            </p>
                        </div>
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

<style scoped>
.ludo-auth-glow {
    border: 1px solid rgba(240, 180, 41, 0.25);
    box-shadow: 0 16px 48px rgba(0, 0, 0, 0.45);
}
</style>
