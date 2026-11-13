<template>
    <div class="ludo-skin-item" :class="[rarityClass[skin.rarity], compact ? 'ludo-skin-item-compact' : '']">
        <img :src="skinImg(skin.image)" :alt="skin.name" @error="imgError($event, 'skin')" />
        <div v-if="!compact" class="ludo-skin-item-info">
            <div class="small text-secondary">{{ skin.weapon }}</div>
            <div class="small fw-semibold text-truncate">{{ skin.name }}</div>
            <div class="ludo-rarity-badge">{{ rarityLabel[skin.rarity] }}</div>
            <div class="ludo-case-price small">{{ skin.price }} ₽</div>
        </div>
    </div>
</template>

<script>
import { rarityClass, rarityLabel } from '../helpers/images-meta.js';

export default {
    name: 'SkinItem',
    props: ['skin', 'skinImg', 'imgError', 'compact'],
    data() {
        return {
            rarityClass: rarityClass,
            rarityLabel: rarityLabel,
        };
    },
};
</script>
