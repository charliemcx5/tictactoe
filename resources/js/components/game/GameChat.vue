<script setup lang="ts">
import { nextTick, ref, watch } from 'vue';
import { Button } from '@/components/ui/button';
import { Input } from '@/components/ui/input';
import { cn } from '@/lib/utils';
import type { ChatMessage, PlayerSymbol } from '@/types/game';

interface Props {
    messages: ChatMessage[];
    playerSymbol: PlayerSymbol;
}

const props = defineProps<Props>();

const emit = defineEmits<{
    (e: 'send', content: string): void;
}>();

const newMessage = ref('');
const messagesContainer = ref<HTMLElement | null>(null);

function sendMessage() {
    if (newMessage.value.trim()) {
        emit('send', newMessage.value.trim());
        newMessage.value = '';
    }
}

watch(
    () => props.messages.length,
    async () => {
        await nextTick();
        if (messagesContainer.value) {
            messagesContainer.value.scrollTop = messagesContainer.value.scrollHeight;
        }
    },
);
</script>

<template>
    <div class="flex h-full flex-col rounded-lg border border-border bg-card">
        <!-- Header -->
        <div class="flex items-center gap-2 border-b border-border px-4 py-3">
            <span class="font-medium">Room Chat</span>
            <span class="size-2 rounded-full bg-green-500"></span>
        </div>

        <!-- Messages -->
        <div ref="messagesContainer" class="flex-1 space-y-2 overflow-y-auto p-4">
            <template v-if="messages.length === 0">
                <p class="text-center text-sm text-muted-foreground">No messages yet</p>
            </template>
            <div
                v-for="message in messages"
                :key="message.id"
                :class="cn('text-sm', message.player_symbol === playerSymbol && 'text-right')"
            >
                <span class="text-muted-foreground">{{ message.player_name }}: </span>
                <span>{{ message.content }}</span>
            </div>
        </div>

        <!-- Input -->
        <div class="flex gap-2 border-t border-border p-3">
            <Input
                v-model="newMessage"
                placeholder="Type a message..."
                class="flex-1"
                @keyup.enter="sendMessage"
            />
            <Button variant="secondary" @click="sendMessage">Send</Button>
        </div>
    </div>
</template>
