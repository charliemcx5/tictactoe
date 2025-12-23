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
const { appearance, updateAppearance } = useAppearance();

const toggleDarkMode = () => {
    updateAppearance(appearance.value === 'dark' ? 'light' : 'dark');
};

// Props for the layout
withDefaults(
    defineProps<{
        showModeSelector?: boolean;
        showTimerSelector?: boolean;
        timerSetting?: string;
        mode?: 'online' | 'bot';
        timerDisabled?: boolean;
    }>(),
    {
        showModeSelector: true,
        showTimerSelector: true,
        timerSetting: 'off',
        mode: 'bot',
        timerDisabled: false,
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
        <header class="">
            <div class="flex items-center justify-between px-6 py-3">
                <!-- Logo -->
                <Link href="/" class="flex items-center gap-2">
                    <TicTacToeLogo class="size-6 text-yellow-500" />
                    <span class="text-lg font-medium">tictactoe</span>
                </Link>

                <!-- Mode & Timer Selector -->
                <div v-if="showModeSelector || showTimerSelector" class="flex items-center gap-6">
                    <!-- Timer -->
                    <div v-if="showTimerSelector" class="flex items-center gap-2">
                        <span class="text-sm text-muted-foreground">TIMER</span>
                        <div class="flex rounded-lg bg-muted p-1">
                            <button
                                v-for="t in timerOptions"
                                :key="t"
                                :disabled="timerDisabled"
                                :class="[
                                    'rounded-md px-3 py-1 text-sm font-medium transition-colors',
                                    timerSetting === t
                                        ? 'bg-background text-foreground shadow-sm'
                                        : 'text-muted-foreground hover:text-foreground',
                                    timerDisabled && 'cursor-not-allowed opacity-50',
                                ]"
                                @click="!timerDisabled && emit('update:timerSetting', t)"
                            >
                                {{ t === 'off' ? 'Off' : `${t}s` }}
                            </button>
                        </div>
                    </div>

                    <!-- Mode -->
                    <div v-if="showModeSelector" class="flex items-center gap-2">
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
                    <Link
                        v-if="!page.props.auth?.user"
                        href="/login"
                        class="flex items-center gap-2 text-muted-foreground transition-colors hover:text-foreground"
                    >
                        <User class="size-4" />
                        <span class="text-md">Log in</span>
                    </Link>
                    <Link
                        v-else
                        href="/settings/profile"
                        class="flex items-center gap-2 text-muted-foreground transition-colors hover:text-foreground"
                    >
                        <User class="size-4" />
                        <span class="text-md">{{ page.props.auth.user.name }}</span>
                    </Link>
                </div>
            </div>
        </header>

        <!-- Main Content -->
        <main class="flex-1 flex" :class="{ 'justify-center': $page.url === '/' }">
            <slot />
        </main>

        <!-- Footer -->
        <footer class="py-4">
            <div class="flex items-center justify-between px-6">
                <div class="flex items-center gap-4">
                    <button
                        class="flex items-center cursor-pointer gap-1.5 text-sm text-muted-foreground hover:text-foreground"
                        @click="toggleDarkMode"
                    >
                        <component
                            :is="appearance === 'dark' ? Moon : appearance === 'light' ? Sun : Monitor"
                            class="size-4"
                        />
                        <span class="capitalize">{{ appearance }}</span>
                    </button>
                    <a
                        href="https://github.com/charliemcx5/tictactoe"
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
