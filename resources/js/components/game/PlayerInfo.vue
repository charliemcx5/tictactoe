<script setup lang="ts">
import { cn } from '@/lib/utils';
import type { PlayerSymbol } from '@/types/game';

interface Props {
    name: string;
    symbol: PlayerSymbol;
    score: number;
    isCurrentTurn?: boolean;
    isYou?: boolean;
}

withDefaults(defineProps<Props>(), {
    isCurrentTurn: false,
    isYou: false,
});
</script>

<template>
    <div class="flex flex-col items-center gap-1">
        <div
            :class="
                cn('text-4xl font-bold', symbol === 'X' ? 'text-yellow-500' : 'text-foreground')
            "
        >
            {{ symbol }}
        </div>
        <div class="flex items-center gap-1">
            <span class="font-medium">{{ name }}</span>
            <span v-if="isYou" class="text-muted-foreground">(you)</span>
        </div>
        <div class="text-sm font-mono text-muted-foreground">Score: {{ score }}</div>
    </div>
</template>
