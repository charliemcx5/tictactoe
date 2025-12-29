<script setup lang="ts">
import { Button } from '@/components/ui/button';
import {
    Dialog,
    DialogContent,
    DialogDescription,
    DialogFooter,
    DialogHeader,
    DialogTitle,
} from '@/components/ui/dialog';
import { Input } from '@/components/ui/input';
import { Label } from '@/components/ui/label';
import GameLayout from '@/layouts/GameLayout.vue';
import type { GameMode, TimerSetting } from '@/types/game';
import { Head, router, usePage } from '@inertiajs/vue3';
import { ArrowDownToLine, Plus } from 'lucide-vue-next';
import { ref } from 'vue';

const page = usePage();
const user = page.props.auth?.user as { name: string } | null;

const mode = ref<GameMode>('online');
const timerSetting = ref<TimerSetting>('off');
const playerName = ref(user?.name ?? 'Guest');
const joinCode = ref('');
const isCreating = ref(false);
const isJoining = ref(false);
const errors = ref<{ player_name?: string; join?: string }>({});

// Name modal state
const showNameModal = ref(false);
const pendingAction = ref<'create' | 'join' | null>(null);
const nameInput = ref('');
const nameError = ref('');

function handleModeUpdate(value: GameMode) {
    mode.value = value;
    
    // Auto-create game when switching to bot mode
    if (value === 'bot') {
        handleCreateGame();
    }
}

function handleCreateGame() {
    // If user is logged in or bot mode, create game directly
    if (user?.name || mode.value === 'bot') {
        createGame();
    } else {
        // Show name modal for online games only
        pendingAction.value = 'create';
        nameInput.value = '';
        nameError.value = '';
        showNameModal.value = true;
    }
}

function handleJoinGame() {
    // Validate join code first
    if (joinCode.value.length !== 6) {
        errors.value.join = 'Please enter a valid 6-character code';
        return;
    }

    // If user is logged in, join game directly
    if (user?.name) {
        joinGame();
    } else {
        // Show name modal
        pendingAction.value = 'join';
        nameInput.value = '';
        nameError.value = '';
        showNameModal.value = true;
    }
}

function confirmName() {
    if (!nameInput.value.trim()) {
        nameError.value = 'Please enter your name';
        return;
    }

    playerName.value = nameInput.value.trim();
    showNameModal.value = false;

    if (pendingAction.value === 'create') {
        createGame();
    } else if (pendingAction.value === 'join') {
        joinGame();
    }

    pendingAction.value = null;
}

function createGame() {
    if (mode.value === 'bot' && !playerName.value.trim()) {
        playerName.value = 'Guest';
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
        @update:mode="handleModeUpdate"
        @update:timer-setting="timerSetting = $event as TimerSetting"
    >
        <div class="flex flex-1 items-center justify-center px-4 py-12">
            <div class="w-full max-w-4xl space-y-8">
                <!-- Welcome Message -->
                <div class="text-center">
                    <h1 class="text-4xl font-bold tracking-tight">
                        Welcome to tictactoe
                    </h1>
                    <p class="mt-3 text-lg text-muted-foreground">
                        Play against the computer or challenge your friends!
                    </p>
                </div>

                <!-- Cards Container -->
                <div class="flex flex-col gap-6 md:flex-row">
                    <!-- Create Game Card -->
                    <div
                        class="h-fit group flex-1 rounded-2xl border border-border bg-card p-6 pb-12 transition-all duration-300 hover:border-yellow-500/50"
                    >
                        <div class="mb-6 flex items-center gap-3">
                            <div
                                class="flex h-10 w-10 items-center justify-center rounded-xl bg-yellow-500/10 transition-transform group-hover:scale-110"
                            >
                                <Plus class="h-5 w-5 text-yellow-500" />
                            </div>
                            <div>
                                <h2 class="text-xl font-semibold">
                                    Create Game
                                </h2>
                                <p class="text-sm text-muted-foreground">
                                    Start a new match
                                </p>
                            </div>
                        </div>

                        <Button
                            class="w-full cursor-pointer bg-black/80 text-white transition-all hover:bg-black/90 hover:shadow-lg dark:bg-yellow-500/20"
                            :disabled="isCreating"
                            @click="handleCreateGame"
                        >
                            {{ isCreating ? 'Creating...' : 'Create Game' }}
                        </Button>
                    </div>

                    <!-- Join Game Card (Online mode only) -->
                    <div
                        v-if="mode === 'online'"
                        class="group flex-1 rounded-2xl border border-border bg-card p-6 pb-12 transition-all duration-300 hover:border-blue-500/50"
                    >
                        <div class="mb-6 flex items-center gap-3">
                            <div
                                class="flex h-10 w-10 items-center justify-center rounded-xl bg-blue-500/10 transition-transform group-hover:scale-110"
                            >
                                <ArrowDownToLine
                                    class="h-5 w-5 text-blue-500"
                                />
                            </div>
                            <div>
                                <h2 class="text-xl font-semibold">Join Game</h2>
                                <p class="text-sm text-muted-foreground">
                                    Enter a game code
                                </p>
                            </div>
                        </div>

                        <div class="space-y-4">
                            <div class="space-y-2">
                                <Label for="joinCode">Game Code</Label>
                                <Input
                                    id="joinCode"
                                    v-model="joinCode"
                                    placeholder="ABCDEF"
                                    maxlength="6"
                                    class="text-center font-mono text-lg tracking-widest uppercase"
                                    :class="{
                                        'border-destructive': errors.join,
                                    }"
                                    @keyup.enter="joinGame"
                                />
                                <p
                                    v-if="errors.join"
                                    class="text-sm text-destructive"
                                >
                                    {{ errors.join }}
                                </p>
                            </div>

                            <Button
                                class="w-full cursor-pointer bg-black/80 text-white transition-all hover:bg-black/90 hover:shadow-lg dark:bg-blue-500/20"
                                :disabled="isJoining"
                                @click="handleJoinGame"
                            >
                                {{ isJoining ? 'Joining...' : 'Join Game' }}
                            </Button>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Name Input Modal -->
        <Dialog :open="showNameModal" @update:open="showNameModal = $event">
            <DialogContent>
                <DialogHeader>
                    <DialogTitle>Enter Your Name</DialogTitle>
                    <DialogDescription>
                        Please enter your name to
                        {{ pendingAction === 'create' ? 'create' : 'join' }} the
                        game.
                    </DialogDescription>
                </DialogHeader>
                <div class="space-y-2 py-4">
                    <Label for="nameInput">Your Name</Label>
                    <Input
                        id="nameInput"
                        v-model="nameInput"
                        placeholder="Enter your name"
                        :class="{ 'border-destructive': nameError }"
                        @keyup.enter="confirmName"
                    />
                    <p v-if="nameError" class="text-sm text-destructive">
                        {{ nameError }}
                    </p>
                </div>
                <DialogFooter>
                    <Button variant="outline" @click="showNameModal = false">
                        Cancel
                    </Button>
                    <Button @click="confirmName">Continue</Button>
                </DialogFooter>
            </DialogContent>
        </Dialog>
    </GameLayout>
</template>
