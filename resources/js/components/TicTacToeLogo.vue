<script setup lang="ts">
import type { HTMLAttributes } from 'vue';

defineOptions({
    inheritAttrs: false,
});

interface Props {
    class?: HTMLAttributes['class'];
}

defineProps<Props>();
</script>

<template>
    <svg
        xmlns="http://www.w3.org/2000/svg"
        viewBox="0 0 24 24"
        :class="$props.class"
        v-bind="$attrs"
    >
        <!-- 3x3 grid of rounded squares -->
        <rect x="1" y="1" width="6" height="6" rx="1" fill="currentColor" />
        <rect x="9" y="1" width="6" height="6" rx="1" fill="currentColor" />
        <rect x="17" y="1" width="6" height="6" rx="1" fill="currentColor" />
        <rect x="1" y="9" width="6" height="6" rx="1" fill="currentColor" />
        <rect x="9" y="9" width="6" height="6" rx="1" fill="currentColor" />
        <rect x="17" y="9" width="6" height="6" rx="1" fill="currentColor" />
        <rect x="1" y="17" width="6" height="6" rx="1" fill="currentColor" />
        <rect x="9" y="17" width="6" height="6" rx="1" fill="currentColor" />
        <rect x="17" y="17" width="6" height="6" rx="1" fill="currentColor" />
    </svg>
</template>
