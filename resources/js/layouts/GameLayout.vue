<script setup lang="ts">
import { Link, usePage } from '@inertiajs/vue3';
import {
    Bot,
    Github,
    Monitor,
    Moon,
    Sun,
    User,
} from 'lucide-vue-next';
import TicTacToeLogo from '@/components/TicTacToeLogo.vue';
import { useAppearance } from '@/composables/useAppearance';

const page = usePage();
const { appearance, toggleDarkMode } = useAppearance();

// Props for the layout
withDefaults(
    defineProps<{
        showModeSelector?: boolean;
        timerSetting?: string;
        mode?: 'online' | 'bot';
    }>(),
    {
        showModeSelector: true,
        timerSetting: 'off',
        mode: 'bot',
    },
);

const emit = defineEmits<{
    (e: 'update:timerSetting', value: string): void;
    (e: 'update:mode', value: 'online' | 'bot'): void;
}>();

const timerOptions = ['off', '5', '10', '30'];
</script>

<template>
    <div class="flex min-h-screen flex-col bg-background text-foreground">
        <!-- Header -->
        <header class="border-b border-border">
            <div class="container mx-auto flex items-center justify-between px-4 py-3">
                <!-- Logo -->
                <Link href="/" class="flex items-center gap-2">
                    <TicTacToeLogo class="size-6 text-yellow-500" />
                    <span class="text-lg font-semibold">tictactoe</span>
                </Link>

                <!-- Mode & Timer Selector -->
                <div v-if="showModeSelector" class="flex items-center gap-6">
                    <!-- Timer -->
                    <div class="flex items-center gap-2">
                        <span class="text-sm text-muted-foreground">TIMER</span>
                        <div class="flex rounded-lg bg-muted p-1">
                            <button
                                v-for="t in timerOptions"
                                :key="t"
                                :class="[
                                    'rounded-md px-3 py-1 text-sm font-medium transition-colors',
                                    timerSetting === t
                                        ? 'bg-background text-foreground shadow-sm'
                                        : 'text-muted-foreground hover:text-foreground',
                                ]"
                                @click="emit('update:timerSetting', t)"
                            >
                                {{ t === 'off' ? 'Off' : `${t}s` }}
                            </button>
                        </div>
                    </div>

                    <!-- Mode -->
                    <div class="flex items-center gap-2">
                        <span class="text-sm text-muted-foreground">MODE</span>
                        <div class="flex rounded-lg bg-muted p-1">
                            <button
                                :class="[
                                    'flex items-center gap-1.5 rounded-md px-3 py-1 text-sm font-medium transition-colors',
                                    mode === 'online'
                                        ? 'bg-background text-foreground shadow-sm'
                                        : 'text-muted-foreground hover:text-foreground',
                                ]"
                                @click="emit('update:mode', 'online')"
                            >
                                <Monitor class="size-4" />
                                Online
                            </button>
                            <button
                                :class="[
                                    'flex items-center gap-1.5 rounded-md px-3 py-1 text-sm font-medium transition-colors',
                                    mode === 'bot'
                                        ? 'bg-background text-foreground shadow-sm'
                                        : 'text-muted-foreground hover:text-foreground',
                                ]"
                                @click="emit('update:mode', 'bot')"
                            >
                                <Bot class="size-4" />
                                Bot
                            </button>
                        </div>
                    </div>
                </div>

                <!-- Auth -->
                <div class="flex items-center gap-2">
                    <User class="size-4 text-muted-foreground" />
                    <Link
                        v-if="!page.props.auth?.user"
                        href="/login"
                        class="text-sm hover:underline"
                    >
                        Log in
                    </Link>
                    <template v-else>
                        <span class="text-sm">{{ page.props.auth.user.name }}</span>
                    </template>
                </div>
            </div>
        </header>

        <!-- Main Content -->
        <main class="flex-1">
            <slot />
        </main>

        <!-- Footer -->
        <footer class="border-t border-border py-4">
            <div class="container mx-auto flex items-center justify-between px-4">
                <div class="flex items-center gap-4">
                    <button
                        class="flex items-center gap-1.5 text-sm text-muted-foreground hover:text-foreground"
                        @click="toggleDarkMode"
                    >
                        <Moon v-if="appearance === 'light'" class="size-4" />
                        <Sun v-else class="size-4" />
                        <span>{{ appearance === 'dark' ? 'Light' : 'Dark' }}</span>
                    </button>
                    <a
                        href="https://github.com"
                        target="_blank"
                        rel="noopener noreferrer"
                        class="flex items-center gap-1.5 text-sm text-muted-foreground hover:text-foreground"
                    >
                        <Github class="size-4" />
                        <span>Github</span>
                    </a>
                </div>
                <span class="text-sm text-muted-foreground">v1.0.0</span>
            </div>
        </footer>
    </div>
</template>
