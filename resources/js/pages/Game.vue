<script setup lang="ts">
import GameBoard from '@/components/game/GameBoard.vue';
import GameChat from '@/components/game/GameChat.vue';
import PlayerInfo from '@/components/game/PlayerInfo.vue';
import { Button } from '@/components/ui/button';
import {
    Dialog,
    DialogContent,
    DialogDescription,
    DialogFooter,
    DialogHeader,
    DialogTitle,
} from '@/components/ui/dialog';
import { useTimer } from '@/composables/useTimer';
import GameLayout from '@/layouts/GameLayout.vue';
import type {
    ChatMessage,
    Game,
    GamePageProps,
    PlayerSymbol,
} from '@/types/game';
import { Head, router } from '@inertiajs/vue3';
import { useEchoPublic } from '@laravel/echo-vue';
import { computed, onMounted, onUnmounted, ref, watch } from 'vue';

const props = defineProps<GamePageProps>();

const game = ref<Game>(props.game);
const playerSymbol = ref<PlayerSymbol | null>(props.playerSymbol);
const messages = ref<ChatMessage[]>(props.messages);
const isLoading = ref(false);

// Chat widget state
const isChatOpen = ref(false);
const unreadCount = ref(0);

// Confirmation state
const showConfirmLeave = ref(false);
const pendingVisit = ref<any>(null);
const skipConfirmation = ref(false);

const { remainingTime, startTimer, stopTimer } = useTimer();

// Computed properties
const isMyTurn = computed(() => game.value.current_turn === playerSymbol.value);
const isWaiting = computed(() => game.value.status === 'waiting');
const isPlaying = computed(() => game.value.status === 'playing');
const isFinished = computed(() => game.value.status === 'finished');

const myName = computed(() =>
    playerSymbol.value === 'X'
        ? game.value.player_x_name
        : game.value.player_o_name,
);
const opponentName = computed(() =>
    playerSymbol.value === 'X'
        ? game.value.player_o_name
        : game.value.player_x_name,
);
const myScore = computed(() =>
    playerSymbol.value === 'X'
        ? game.value.player_x_score
        : game.value.player_o_score,
);
const opponentScore = computed(() =>
    playerSymbol.value === 'X'
        ? game.value.player_o_score
        : game.value.player_x_score,
);

const showTimer = computed(
    () => game.value.timer_setting !== 'off' && isPlaying.value,
);

// Timer can only be changed by X player between rounds (not during playing)
const canChangeTimer = computed(
    () => playerSymbol.value === 'X' && !isPlaying.value,
);

const resultMessage = computed(() => {
    if (!isFinished.value) return '';
    if (game.value.winner === 'draw') return "It's a draw!";
    if (game.value.winner === playerSymbol.value) return 'You won!';
    return 'You lost!';
});

const rematchRequestedByMe = computed(() => {
    const rematchBy = (game.value as any).rematch_requested_by as
        | PlayerSymbol
        | null
        | undefined;
    return rematchBy === playerSymbol.value;
});

const rematchRequestedByOpponent = computed(() => {
    const rematchBy = (game.value as any).rematch_requested_by as
        | PlayerSymbol
        | null
        | undefined;
    return !!rematchBy && rematchBy !== playerSymbol.value;
});

const canRequestRematch = computed(() => {
    const rematchBy = (game.value as any).rematch_requested_by as
        | PlayerSymbol
        | null
        | undefined;
    return isFinished.value && !rematchBy;
});

const canAcceptRematch = computed(() => {
    return isFinished.value && rematchRequestedByOpponent.value;
});

// Setup real-time listeners using useEchoPublic for guest support
const { leave: leaveChannel } = useEchoPublic<{
    game?: Partial<Game>;
    message?: ChatMessage;
    player_name?: string;
}>(
    `game.${props.game.code}`,
    [
        '.App\\Events\\GameUpdated',
        '.App\\Events\\PlayerJoined',
        '.App\\Events\\ChatMessageSent',
    ],
    (payload) => {
        // Handle PlayerJoined event
        if (payload.player_name && payload.game) {
            game.value = {
                ...game.value,
                ...payload.game,
                player_o_name: payload.player_name,
            };
            startTimerIfNeeded();
        }
        // Handle GameUpdated event
        else if (payload.game) {
            // If game was reset (status changed from finished to playing), reload page to get fresh session
            const wasFinished = game.value.status === 'finished';
            const isNowPlaying = payload.game.status === 'playing';

            if (wasFinished && isNowPlaying && game.value.mode === 'online') {
                // Reload page to get fresh session-based props
                router.reload();
                return;
            }

            // Fully replace game state with broadcast data
            game.value = { ...game.value, ...payload.game };
            startTimerIfNeeded();
        }
        // Handle ChatMessageSent event
        if (payload.message) {
            if (!messages.value.some((m) => m.id === payload.message!.id)) {
                messages.value.push(payload.message);
                // Increment unread count if chat is closed
                if (!isChatOpen.value) {
                    unreadCount.value++;
                }
            }
        }
    },
);

// Navigation interception
const cleanupListener = router.on('before', (event) => {
    if (skipConfirmation.value) return;

    // Only confirm if game is active (playing or waiting) and online
    if (
        game.value.mode === 'online' &&
        (game.value.status === 'playing' || game.value.status === 'waiting')
    ) {
        const url = event.detail.visit.url.toString();
        const code = game.value.code;

        // Define safe paths that are in-game actions and shouldn't trigger confirmation
        const safePaths = [
            `/game/${code}/move`,
            `/game/${code}/chat`,
            `/game/${code}/timer`,
            `/game/${code}/request-rematch`,
            `/game/${code}/accept-rematch`,
        ];

        // Also allow reloads of the current page
        const isCurrentPage = url.includes(window.location.pathname);
        const isSafeAction = safePaths.some((path) => url.includes(path));

        if (!isSafeAction && !isCurrentPage) {
            event.preventDefault();
            pendingVisit.value = event.detail.visit;
            showConfirmLeave.value = true;
        }
    }
});

onMounted(() => {
    startTimerIfNeeded();
});

onUnmounted(() => {
    stopTimer();
    leaveChannel();
    cleanupListener();
});

function confirmLeave() {
    if (pendingVisit.value) {
        skipConfirmation.value = true;
        showConfirmLeave.value = false;
        router.visit(pendingVisit.value.url, pendingVisit.value);
    }
}

function cancelLeave() {
    showConfirmLeave.value = false;
    pendingVisit.value = null;
}

function startTimerIfNeeded() {
    if (game.value.timer_setting !== 'off' && isPlaying.value) {
        const timerSeconds = parseInt(game.value.timer_setting);
        if (game.value.turn_started_at) {
            const elapsed = Math.floor(
                (Date.now() - new Date(game.value.turn_started_at).getTime()) /
                    1000,
            );
            const remaining = Math.max(0, timerSeconds - elapsed);
            if (remaining > 0) {
                startTimer(remaining, handleTimeout);
            } else if (isMyTurn.value) {
                handleTimeout();
            }
        } else {
            startTimer(timerSeconds, handleTimeout);
        }
    } else {
        stopTimer();
    }
}

function handleTimeout() {
    if (isMyTurn.value && isPlaying.value) {
        forfeit();
    }
}

watch(isPlaying, (playing) => {
    if (playing) {
        startTimerIfNeeded();
    } else {
        stopTimer();
    }
});

watch(
    () => game.value.current_turn,
    () => {
        // Reset timer when turn changes
        if (isPlaying.value) {
            startTimerIfNeeded();
        }
    },
);

// Watch for prop changes from page navigation/reload (e.g., after playAgain redirect)
watch(
    () => props.game,
    (newGame) => {
        game.value = newGame;
        startTimerIfNeeded();
    },
    { deep: true },
);

watch(
    () => props.playerSymbol,
    (newSymbol) => {
        playerSymbol.value = newSymbol;
    },
);

watch(
    () => props.messages,
    (newMessages) => {
        messages.value = newMessages;
    },
    { deep: true },
);

// Reset unread count when chat is opened
watch(isChatOpen, (open) => {
    if (open) {
        unreadCount.value = 0;
    }
});

// Actions
function makeMove(position: number) {
    if (!isMyTurn.value || !isPlaying.value || isLoading.value) return;
    if (game.value.board[position] !== '') return;

    isLoading.value = true;

    // Optimistic update
    const newBoard = [...game.value.board];
    newBoard[position] = playerSymbol.value!;
    game.value.board = newBoard;

    router.post(
        `/game/${game.value.code}/move`,
        { position },
        {
            preserveScroll: true,
            onSuccess: (page) => {
                // Update from server response
                const pageProps = page.props as unknown as GamePageProps;
                game.value = pageProps.game;
                startTimerIfNeeded();
            },
            onError: () => {
                // Revert optimistic update
                router.reload();
            },
            onFinish: () => {
                isLoading.value = false;
            },
        },
    );
}

function requestRematch() {
    router.post(
        `/game/${game.value.code}/request-rematch`,
        {},
        {
            preserveScroll: true,
        },
    );
}

function acceptRematch() {
    router.post(
        `/game/${game.value.code}/accept-rematch`,
        {},
        {
            preserveScroll: true,
        },
    );
}

function forfeit() {
    router.post(`/game/${game.value.code}/forfeit`);
}

function sendMessage(content: string) {
    router.post(
        `/game/${game.value.code}/chat`,
        { content },
        { preserveScroll: true },
    );
}

function goHome() {
    // For online games that are playing or finished, notify other player
    if (game.value.mode === 'online' && (isPlaying.value || isFinished.value)) {
        router.post(`/game/${game.value.code}/leave`);
    } else {
        router.visit('/');
    }
}

function updateTimer(timerSetting: string) {
    if (!canChangeTimer.value) return;
    router.post(
        `/game/${game.value.code}/timer`,
        { timer_setting: timerSetting },
        {
            preserveScroll: true,
            onSuccess: (page) => {
                const pageProps = page.props as unknown as GamePageProps;
                game.value = pageProps.game;
            },
        },
    );
}

function handleModeUpdate(newMode: 'online' | 'bot') {
    if (newMode === game.value.mode) return;

    // Trigger navigation to create new game
    // This will be caught by router.on('before')
    router.post('/game', {
        player_name: myName.value ?? 'Player',
        mode: newMode,
        timer_setting: 'off',
    });
}
</script>

<template>
    <Head :title="`Game ${game.code}`" />

    <GameLayout
        :show-mode-selector="true"
        :show-timer-selector="true"
        :mode="game.mode"
        :timer-setting="game.timer_setting"
        :timer-disabled="!canChangeTimer"
        @update:timer-setting="updateTimer"
        @update:mode="handleModeUpdate"
    >
        <div class="mt-8 w-full px-6 py-8">
            <!-- Main Game Area - Centered -->
            <div class="mx-auto max-w-lg space-y-6">
                <!-- Players Info -->
                <div class="flex items-center justify-center gap-8">
                    <PlayerInfo
                        :name="myName ?? 'You'"
                        :symbol="playerSymbol ?? 'X'"
                        :score="myScore"
                        :is-current-turn="isMyTurn"
                        :is-you="true"
                    />
                    <span class="text-xl text-muted-foreground">vs</span>
                    <PlayerInfo
                        :name="
                            opponentName ??
                            (isWaiting ? 'Waiting...' : 'Opponent')
                        "
                        :symbol="playerSymbol === 'X' ? 'O' : 'X'"
                        :score="opponentScore"
                        :is-current-turn="!isMyTurn && isPlaying"
                    />
                </div>

                <!-- Game Board -->
                <div class="flex justify-center">
                    <GameBoard
                        :board="game.board"
                        :disabled="!isMyTurn || !isPlaying"
                        :winning-cells="game.winning_cells ?? []"
                        @cell-click="makeMove"
                    />
                </div>

                <!-- Timer -->
                <div v-if="showTimer" class="text-center">
                    <div
                        :class="[
                            'font-mono text-3xl font-bold',
                            remainingTime <= 5 &&
                                'animate-pulse text-destructive',
                            isMyTurn && remainingTime > 5 && 'text-yellow-500',
                        ]"
                    >
                        0:{{ remainingTime.toString().padStart(2, '0') }}
                    </div>
                </div>

                <!-- Status Messages -->
                <div class="space-y-2 text-center">
                    <template v-if="isWaiting">
                        <p class="text-lg text-muted-foreground">
                            Waiting for opponent...
                        </p>
                        <p
                            class="font-mono text-2xl font-bold tracking-widest text-yellow-500"
                        >
                            {{ game.code }}
                        </p>
                        <p class="text-sm text-muted-foreground">
                            Share this code with your friend
                        </p>
                    </template>

                    <template v-else-if="isPlaying">
                        <p class="text-lg">
                            {{ isMyTurn ? 'Your turn' : "Opponent's turn" }}
                        </p>
                    </template>

                    <template v-else-if="isFinished">
                        <p class="text-2xl font-bold">{{ resultMessage }}</p>
                    </template>
                </div>

                <!-- Actions -->
                <div class="flex justify-center gap-4">
                    <template v-if="isFinished">
                        <!-- Bot games: simple play again -->
                        <Button
                            v-if="game.mode === 'bot'"
                            @click="requestRematch"
                        >
                            Play Again
                        </Button>

                        <!-- Online games: rematch request/accept flow -->
                        <template v-else-if="game.mode === 'online'">
                            <Button
                                v-if="canRequestRematch"
                                @click="requestRematch"
                            >
                                Request Rematch
                            </Button>
                            <Button
                                v-if="rematchRequestedByMe"
                                variant="outline"
                                disabled
                            >
                                Rematch Requested...
                            </Button>
                            <Button
                                v-if="canAcceptRematch"
                                @click="acceptRematch"
                            >
                                Accept Rematch
                            </Button>
                        </template>

                        <Button variant="outline" @click="goHome">
                            Back to Home
                        </Button>
                    </template>
                    <Button
                        v-if="isPlaying && game.mode === 'online'"
                        variant="destructive"
                        @click="forfeit"
                    >
                        Forfeit
                    </Button>
                    <Button v-if="isWaiting" variant="outline" @click="goHome">
                        Cancel
                    </Button>
                </div>
            </div>
        </div>

        <!-- Floating Chat Widget (Online mode only) -->
        <div
            v-if="game.mode === 'online'"
            class="fixed right-24 bottom-0 z-40 w-80"
        >
            <GameChat
                :messages="messages"
                :player-symbol="playerSymbol ?? 'X'"
                :is-open="isChatOpen"
                :unread-count="unreadCount"
                @send="sendMessage"
                @toggle="isChatOpen = !isChatOpen"
            />
        </div>

        <Dialog
            :open="showConfirmLeave"
            @update:open="showConfirmLeave = $event"
        >
            <DialogContent>
                <DialogHeader>
                    <DialogTitle>Leave Game?</DialogTitle>
                    <DialogDescription>
                        Are you sure you want to leave? You will forfeit the
                        current game.
                    </DialogDescription>
                </DialogHeader>
                <DialogFooter>
                    <Button variant="outline" @click="cancelLeave"
                        >Cancel</Button
                    >
                    <Button variant="destructive" @click="confirmLeave"
                        >Leave Game</Button
                    >
                </DialogFooter>
            </DialogContent>
        </Dialog>
    </GameLayout>
</template>
