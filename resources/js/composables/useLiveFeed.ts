import type { LiveDrop } from '@/types';
import { echo } from '@/echo';
import { onMounted, onUnmounted, ref, watch, type Ref } from 'vue';

export function useLiveFeed(source: LiveDrop[] | Ref<LiveDrop[]>) {
    const sourceRef = Array.isArray(source) ? ref(source) : source;
    const drops = ref<LiveDrop[]>([...sourceRef.value]);

    watch(
        sourceRef,
        (newDrops) => {
            drops.value = [...newDrops];
        },
        { deep: true },
    );

    function prependDrop(drop: LiveDrop) {
        drops.value = [drop, ...drops.value.filter((d) => d.id !== drop.id)].slice(0, 50);
    }

    onMounted(() => {
        if (!echo) {
            return;
        }

        echo.channel('live-drops').listen('.LiveDropCreated', (payload: { drop: LiveDrop }) => {
            prependDrop(payload.drop);
        });
    });

    onUnmounted(() => {
        echo?.leave('live-drops');
    });

    return { drops, prependDrop };
}
