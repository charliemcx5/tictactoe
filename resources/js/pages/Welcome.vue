<script setup lang="ts">
import { ref } from 'vue';
import { Head, router, usePage } from '@inertiajs/vue3';
import GameLayout from '@/layouts/GameLayout.vue';
import { Button } from '@/components/ui/button';
import { Input } from '@/components/ui/input';
import { Label } from '@/components/ui/label';
import type { GameMode, TimerSetting } from '@/types/game';

const page = usePage();
const user = page.props.auth?.user as { name: string } | null;

const mode = ref<GameMode>('online');
const timerSetting = ref<TimerSetting>('off');
const playerName = ref(user?.name ?? '');
const joinCode = ref('');
const isCreating = ref(false);
const isJoining = ref(false);
const errors = ref<{ player_name?: string; join?: string }>({});

function createGame() {
    if (!playerName.value.trim()) {
        errors.value.player_name = 'Please enter your name';
        return;
    }

    isCreating.value = true;
    errors.value = {};

    router.post(
        '/game',
        {
            player_name: playerName.value,
            mode: mode.value,
            timer_setting: timerSetting.value,
        },
        {
            onFinish: () => {
                isCreating.value = false;
            },
            onError: (err) => {
                errors.value = err as typeof errors.value;
            },
        },
    );
}

function joinGame() {
    if (!playerName.value.trim()) {
        errors.value.player_name = 'Please enter your name';
        return;
    }
    if (joinCode.value.length !== 6) {
        errors.value.join = 'Please enter a valid 6-character code';
        return;
    }

    isJoining.value = true;
    errors.value = {};

    router.post(
        `/game/${joinCode.value.toUpperCase()}/join`,
        {
            player_name: playerName.value,
        },
        {
            onFinish: () => {
                isJoining.value = false;
            },
            onError: () => {
                errors.value.join = 'Game not found or already started';
            },
        },
    );
}
</script>

<template>
    <Head title="Welcome" />

    <GameLayout
        :mode="mode"
        :timer-setting="timerSetting"
        @update:mode="mode = $event"
        @update:timer-setting="timerSetting = $event"
    >
        <div class="flex flex-1 items-center justify-center px-4 py-12">
            <div class="w-full max-w-md space-y-8">
                <!-- Welcome Message -->
                <div class="text-center">
                    <h1 class="text-3xl font-bold">Welcome to tictactoe</h1>
                    <p class="mt-2 text-muted-foreground">
                        Play against the computer or challenge your friends!
                    </p>
                </div>

                <!-- Main Form -->
                <div class="space-y-6">
                    <!-- Player Name -->
                    <div class="space-y-2">
                        <Label for="playerName">Your Name</Label>
                        <Input
                            id="playerName"
                            v-model="playerName"
                            placeholder="Enter your name"
                            :disabled="!!user"
                            :class="{ 'border-destructive': errors.player_name }"
                        />
                        <p v-if="errors.player_name" class="text-sm text-destructive">
                            {{ errors.player_name }}
                        </p>
                    </div>

                    <!-- Create Game Button -->
                    <Button
                        class="w-full bg-yellow-500 text-black hover:bg-yellow-600"
                        :disabled="isCreating"
                        @click="createGame"
                    >
                        {{ isCreating ? 'Creating...' : 'Create Game' }}
                    </Button>

                    <!-- Join with Code (Online mode only) -->
                    <template v-if="mode === 'online'">
                        <div class="relative flex items-center">
                            <div class="flex-1 border-t border-border"></div>
                            <span class="px-4 text-sm text-muted-foreground">or</span>
                            <div class="flex-1 border-t border-border"></div>
                        </div>

                        <div class="space-y-2">
                            <Label for="joinCode">Join with Code</Label>
                            <div class="flex gap-2">
                                <Input
                                    id="joinCode"
                                    v-model="joinCode"
                                    placeholder="ABCDEF"
                                    maxlength="6"
                                    class="flex-1 uppercase"
                                    :class="{ 'border-destructive': errors.join }"
                                    @keyup.enter="joinGame"
                                />
                                <Button
                                    variant="secondary"
                                    :disabled="isJoining"
                                    @click="joinGame"
                                >
                                    {{ isJoining ? 'Joining...' : 'Join' }}
                                </Button>
                            </div>
                            <p v-if="errors.join" class="text-sm text-destructive">
                                {{ errors.join }}
                            </p>
                        </div>
                    </template>
                </div>
            </div>
        </div>
    </GameLayout>
</template>
