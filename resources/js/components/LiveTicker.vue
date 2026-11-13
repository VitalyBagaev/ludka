<template>
    <div v-if="drops.length" class="ludo-ticker-wrap">
        <span class="ludo-ticker-label"><span class="ludo-live-dot"></span> LIVE</span>
        <div class="ludo-ticker-track">
            <div class="ludo-ticker-content">
                <button
                    v-for="(drop, i) in doubled"
                    :key="i"
                    type="button"
                    class="ludo-ticker-item"
                    @click="goToPlayer(drop)"
                >
                    <UserAvatar
                        :avatar="drop.avatar"
                        :name="drop.user"
                        :initials="drop.initials"
                        size="xs"
                    />
                    <strong>{{ drop.user }}</strong> выбил {{ drop.skin?.weapon }} | {{ drop.skin?.name }}
                    <span class="text-warning">({{ drop.skin?.price }} ₽)</span>
                    <span class="mx-3 opacity-25">•</span>
                </button>
            </div>
        </div>
    </div>
</template>

<script>
import UserAvatar from './UserAvatar.vue';

export default {
    name: 'LiveTicker',
    components: { UserAvatar },
    props: ['drops', 'changePage'],
    computed: {
        doubled() {
            return [...this.drops, ...this.drops];
        },
    },
    methods: {
        goToPlayer(drop) {
            if (!drop || !drop.userId) {
                return;
            }
            this.changePage('PlayerPage', drop.userId);
        },
    },
};
</script>
