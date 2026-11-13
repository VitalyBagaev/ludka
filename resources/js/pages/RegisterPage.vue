<template>
    <div class="container ludo-page d-flex align-items-center" style="min-height: calc(100vh - 180px);">
        <div class="row justify-content-center w-100">
            <div class="col-md-6 col-lg-5">
                <div class="ludo-auth-card ludo-auth-glow">
                    <div class="ludo-auth-header text-center">
                        <div class="mb-2" style="font-size: 28px;">🎁</div>
                        <h1 class="mb-1">Регистрация</h1>
                        <p class="small text-secondary mb-0">Создай аккаунт и получи доступ к бесплатному кейсу каждые 30 минут</p>
                    </div>
                    <div class="p-4">
                        <RegisterComponent :datasend="datasend" :changeToken="changeToken" :changePage="changePage" />
                        <div class="mt-4 pt-3 border-top border-secondary-subtle text-center">
                            <p class="mb-0 small text-secondary">
                                Уже есть аккаунт?
                                <a href="#" class="text-warning fw-semibold text-decoration-none" @click.prevent="changePage('AuthPage')">
                                    Войти →
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
import RegisterComponent from '../components/RegisterComponent.vue';

export default {
    name: 'RegisterPage',
    components: { RegisterComponent },
    props: ['datasend', 'changeToken', 'changePage'],
};
</script>

<style scoped>
.ludo-auth-glow {
    border: 1px solid rgba(240, 180, 41, 0.25);
    box-shadow: 0 16px 48px rgba(0, 0, 0, 0.45);
}
</style>
