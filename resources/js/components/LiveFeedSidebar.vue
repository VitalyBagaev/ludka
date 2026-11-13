<template>
    <aside class="ludo-live-sidebar d-none d-xl-flex">
        <div class="ludo-section-label px-3 pt-3 pb-2">
            <span class="ludo-live-dot"></span> Live Drop
        </div>
        <div class="ludo-live-sidebar-scroll">
            <TransitionGroup name="ludo-drop">
                <div v-for="drop in drops" :key="drop.id" class="ludo-drop-item mx-2 mb-2">
                    <button type="button" class="ludo-drop-user-btn" @click="goToPlayer(drop)">
                        <UserAvatar
                            :avatar="drop.avatar"
                            :name="drop.user"
                            :initials="drop.initials"
                            size="md"
                        />
                    </button>
                    <button
                        v-if="drop.skin"
                        type="button"
                        class="ludo-drop-skin-btn"
                        :title="drop.skin.weapon + ' | ' + drop.skin.name"
                        @click="goToPlayer(drop)"
                    >
                        <img
                            class="ludo-drop-skin-thumb"
                            :src="skinImg(drop.skin.image)"
                            :alt="drop.skin.name"
                            @error="imgError($event, 'skin')"
                        />
                    </button>
                    <button type="button" class="ludo-drop-info-btn flex-grow-1 min-w-0" @click="goToPlayer(drop)">
                        <div class="ludo-drop-user text-truncate">{{ drop.user }}</div>
                        <div class="ludo-drop-case text-truncate">{{ drop.caseName }}</div>
                        <div class="ludo-drop-skin">{{ drop.skin ? drop.skin.price : 0 }} ₽</div>
                    </button>
                </div>
            </TransitionGroup>
            <div v-if="!drops.length" class="text-secondary small text-center py-4 px-3">Нет дропов</div>
        </div>
    </aside>
</template>

<script>
import UserAvatar from './UserAvatar.vue';

export default {
    name: 'LiveFeedSidebar',
    components: { UserAvatar },
    props: ['drops', 'skinImg', 'imgError', 'changePage'],
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
