<script setup lang="ts">
import TicTacToeLogo from '@/components/TicTacToeLogo.vue';
import { Link, usePage } from '@inertiajs/vue3';
import { LogOut } from 'lucide-vue-next';

const page = usePage();
const user = page.props.auth?.user as { name: string } | null;
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

                <!-- Sign out -->
                <Link
                    v-if="user"
                    href="/logout"
                    method="post"
                    as="button"
                    class="text-md flex items-center gap-1.5 text-muted-foreground hover:text-foreground"
                >
                    <LogOut class="size-4" />
                    <span>Sign out</span>
                </Link>
            </div>
        </header>

        <!-- Main Content -->
        <main class="flex-1">
            <slot />
        </main>
    </div>
</template>
