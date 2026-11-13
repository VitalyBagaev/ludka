<template>
    <footer class="ludo-footer">
        <div class="container text-center">
            <button type="button" class="ludo-footer-egg" title="" @click="changePage('SecretCasePage')">
                <span class="ludo-footer-egg-ludo">ЛУДО</span><span class="ludo-footer-egg-mania">МАНИЯ</span>
            </button>
            <div class="small text-secondary">© 2026 — CS2 кейсы, битвы и апгрейд · 18+</div>
        </div>
    </footer>
</template>

<script>
export default {
    name: 'FooterComponent',
    props: ['changePage'],
};
</script>
