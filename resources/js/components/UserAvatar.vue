<template>
    <div class="ludo-user-avatar" :class="'ludo-user-avatar-' + size" :title="name || ''">
        <img v-if="avatar" :src="avatar" :alt="name" />
        <span v-else class="ludo-user-avatar-initials">{{ displayInitials }}</span>
    </div>
</template>

<script>
export default {
    name: 'UserAvatar',
    props: {
        avatar: { type: String, default: null },
        name: { type: String, default: '' },
        initials: { type: String, default: '' },
        size: { type: String, default: 'md' },
    },
    computed: {
        displayInitials() {
            if (this.initials) {
                return this.initials;
            }
            if (!this.name) {
                return '??';
            }
            const parts = this.name.trim().split(/[_\s]+/);
            if (parts.length >= 2) {
                return (parts[0][0] + parts[1][0]).toUpperCase();
            }
            return this.name.substring(0, 2).toUpperCase();
        },
    },
};
</script>
