<script setup lang="ts">
import { Button } from '@/components/ui/button';
import { Input } from '@/components/ui/input';
import { cn } from '@/lib/utils';
import type { ChatMessage, PlayerSymbol } from '@/types/game';
import { ChevronUp } from 'lucide-vue-next';
import { nextTick, ref, watch } from 'vue';

interface Props {
    messages: ChatMessage[];
    playerSymbol: PlayerSymbol;
    isOpen?: boolean;
    unreadCount?: number;
}

const props = withDefaults(defineProps<Props>(), {
    isOpen: true,
    unreadCount: 0,
});

const emit = defineEmits<{
    (e: 'send', content: string): void;
    (e: 'toggle'): void;
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
            messagesContainer.value.scrollTop =
                messagesContainer.value.scrollHeight;
        }
    },
);
</script>

<template>
    <div
        class="flex flex-col overflow-hidden rounded-t-lg border border-border bg-card shadow-lg transition-all duration-300 ease-in-out"
        :class="isOpen ? 'h-[400px]' : 'h-12'"
    >
        <!-- Header -->
        <div
            class="flex shrink-0 cursor-pointer items-center justify-between border-b border-border px-4 py-3"
            @click="emit('toggle')"
        >
            <div class="flex items-center gap-2">
                <span class="font-medium">Room Chat</span>
                <!-- Unread indicator -->
                <span
                    v-if="!isOpen && unreadCount > 0"
                    class="size-2 rounded-full bg-red-500"
                ></span>
            </div>
            <button
                class="rounded p-1 text-muted-foreground transition-colors hover:bg-muted hover:text-foreground"
                @click.stop="emit('toggle')"
            >
                <ChevronUp
                    class="size-4 transition-transform duration-300"
                    :class="{ 'rotate-180': isOpen }"
                />
            </button>
        </div>

        <!-- Messages -->
        <div
            ref="messagesContainer"
            class="flex-1 space-y-2 overflow-y-auto p-4 transition-opacity duration-200"
            :class="isOpen ? 'opacity-100' : 'opacity-0'"
        >
            <template v-if="messages.length === 0">
                <p class="text-center text-sm text-muted-foreground">
                    No messages yet
                </p>
            </template>
            <template v-for="message in messages" :key="message.id">
                <!-- System messages -->
                <div
                    v-if="'is_system' in message && message.is_system"
                    class="text-center text-xs text-muted-foreground italic"
                >
                    {{ message.content }}
                </div>
                <!-- Regular messages -->
                <div
                    v-else
                    :class="
                        cn(
                            'text-sm',
                            message.player_symbol === playerSymbol &&
                                'text-right',
                        )
                    "
                >
                    <span class="text-muted-foreground"
                        >{{ message.player_name }}:
                    </span>
                    <span>{{ message.content }}</span>
                </div>
            </template>
        </div>

        <!-- Input -->
        <div
            class="flex shrink-0 gap-2 border-t border-border p-3 transition-opacity duration-200"
            :class="isOpen ? 'opacity-100' : 'opacity-0'"
        >
            <Input
                v-model="newMessage"
                placeholder="Type a message..."
                class="flex-1"
                :disabled="!isOpen"
                @keyup.enter="sendMessage"
            />
            <Button variant="secondary" :disabled="!isOpen" @click="sendMessage"
                >Send</Button
            >
        </div>
    </div>
</template>
