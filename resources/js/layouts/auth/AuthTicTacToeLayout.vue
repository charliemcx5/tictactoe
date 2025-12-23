<script setup lang="ts">
import TicTacToeLogo from '@/components/TicTacToeLogo.vue';
import { Card } from '@/components/ui/card';
import { useAppearance } from '@/composables/useAppearance';
import { Link } from '@inertiajs/vue3';
import { Github, Monitor, Moon, Sun } from 'lucide-vue-next';

defineProps<{
    title?: string;
    description?: string;
}>();

const { appearance, updateAppearance } = useAppearance();

const toggleDarkMode = () => {
    updateAppearance(appearance.value === 'dark' ? 'light' : 'dark');
};
</script>

<template>
    <div class="flex min-h-screen flex-col bg-background text-foreground">
        <!-- Header -->
        <header class="">
            <div class="flex items-center justify-between px-6 py-3">
                <!-- Logo -->
                <Link href="/" class="flex items-center gap-2">
                    <TicTacToeLogo class="size-6 text-yellow-500" />
                    <span class="text-lg font-semibold">tictactoe</span>
                </Link>
            </div>
        </header>

        <!-- Main Content -->
        <main class="flex flex-1 items-center justify-center p-6">
            <Card>
                <div class="w-full max-w-md p-6">
                    <div class="mb-6 flex flex-col items-center gap-4">
                        <div class="flex items-center gap-2">
                            <TicTacToeLogo class="size-8 text-yellow-500" />
                            <span class="text-xl font-medium">tictactoe</span>
                        </div>
                        <div
                            v-if="title || description"
                            class="space-y-2 text-center"
                        >
                            <h1 v-if="title" class="text-xl font-medium">
                                {{ title }}
                            </h1>
                            <p
                                v-if="description"
                                class="text-sm text-muted-foreground"
                            >
                                {{ description }}
                            </p>
                        </div>
                    </div>
                    <slot />
                </div>
            </Card>
        </main>

        <!-- Footer -->
        <footer class="py-4">
            <div class="flex items-center justify-between px-6">
                <div class="flex items-center gap-4">
                    <button
                        class="flex cursor-pointer items-center gap-1.5 text-sm text-muted-foreground hover:text-foreground"
                        @click="toggleDarkMode"
                    >
                        <component
                            :is="
                                appearance === 'dark'
                                    ? Moon
                                    : appearance === 'light'
                                      ? Sun
                                      : Monitor
                            "
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
