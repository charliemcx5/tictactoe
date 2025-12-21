<script setup lang="ts">
import { cn } from '@/lib/utils';

interface Props {
    board: string[];
    disabled?: boolean;
    winningCells?: number[];
}

withDefaults(defineProps<Props>(), {
    disabled: false,
    winningCells: () => [],
});

const emit = defineEmits<{
    (e: 'cellClick', position: number): void;
}>();
</script>

<template>
    <div class="grid aspect-square w-full max-w-[320px] grid-cols-3 gap-2">
        <button
            v-for="(cell, index) in board"
            :key="index"
            :disabled="disabled || cell !== ''"
            :class="
                cn(
                    'flex aspect-square items-center justify-center rounded-lg bg-muted',
                    'text-5xl font-bold transition-all',
                    'hover:bg-muted/80 disabled:cursor-not-allowed',
                    cell === 'X' && 'text-yellow-500',
                    cell === 'O' && 'text-foreground',
                    winningCells.includes(index) && 'bg-yellow-500/20 ring-2 ring-yellow-500',
                )
            "
            @click="emit('cellClick', index)"
        >
            {{ cell }}
        </button>
    </div>
</template>
