<script setup lang="ts">
import { computed, onMounted, onUnmounted, ref, watch } from 'vue';
import { Head, router } from '@inertiajs/vue3';
import { useEchoPublic } from '@laravel/echo-vue';
import GameLayout from '@/layouts/GameLayout.vue';
import GameBoard from '@/components/game/GameBoard.vue';
import PlayerInfo from '@/components/game/PlayerInfo.vue';
import GameChat from '@/components/game/GameChat.vue';
import { Button } from '@/components/ui/button';
import { useTimer } from '@/composables/useTimer';
import type { Game, ChatMessage, PlayerSymbol, GamePageProps } from '@/types/game';

const props = defineProps<GamePageProps>();

const game = ref<Game>(props.game);
const playerSymbol = ref<PlayerSymbol | null>(props.playerSymbol);
const messages = ref<ChatMessage[]>(props.messages);
const isLoading = ref(false);

const { remainingTime, startTimer, stopTimer } = useTimer();

// Computed properties
const isMyTurn = computed(() => game.value.current_turn === playerSymbol.value);
const isWaiting = computed(() => game.value.status === 'waiting');
const isPlaying = computed(() => game.value.status === 'playing');
const isFinished = computed(() => game.value.status === 'finished');

const myName = computed(() =>
    playerSymbol.value === 'X' ? game.value.player_x_name : game.value.player_o_name,
);
const opponentName = computed(() =>
    playerSymbol.value === 'X' ? game.value.player_o_name : game.value.player_x_name,
);
const myScore = computed(() =>
    playerSymbol.value === 'X' ? game.value.player_x_score : game.value.player_o_score,
);
const opponentScore = computed(() =>
    playerSymbol.value === 'X' ? game.value.player_o_score : game.value.player_x_score,
);

const showTimer = computed(
    () => game.value.timer_setting !== 'off' && isPlaying.value,
);

const resultMessage = computed(() => {
    if (!isFinished.value) return '';
    if (game.value.winner === 'draw') return "It's a draw!";
    if (game.value.winner === playerSymbol.value) return 'You won!';
    return 'You lost!';
});

// Setup real-time listeners using useEchoPublic for guest support
const { leave: leaveChannel } = useEchoPublic<{ game?: Partial<Game>; message?: ChatMessage; player_name?: string }>(
    `game.${props.game.code}`,
    ['.App\\Events\\GameUpdated', '.App\\Events\\PlayerJoined', '.App\\Events\\ChatMessageSent'],
    (payload) => {
        // Handle PlayerJoined event
        if (payload.player_name && payload.game) {
            game.value.player_o_name = payload.player_name;
            Object.assign(game.value, payload.game);
            startTimerIfNeeded();
        }
        // Handle GameUpdated event
        else if (payload.game) {
            Object.assign(game.value, payload.game);
            startTimerIfNeeded();
        }
        // Handle ChatMessageSent event
        if (payload.message) {
            messages.value.push(payload.message);
        }
    },
);

onMounted(() => {
    startTimerIfNeeded();
});

onUnmounted(() => {
    stopTimer();
    leaveChannel();
});

function startTimerIfNeeded() {
    if (game.value.timer_setting !== 'off' && isPlaying.value) {
        const timerSeconds = parseInt(game.value.timer_setting);
        if (game.value.turn_started_at) {
            const elapsed = Math.floor(
                (Date.now() - new Date(game.value.turn_started_at).getTime()) / 1000,
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

watch(() => game.value.current_turn, () => {
    // Reset timer when turn changes
    if (isPlaying.value) {
        startTimerIfNeeded();
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

function playAgain() {
    router.post(`/game/${game.value.code}/play-again`, {}, { preserveScroll: true });
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
    router.visit('/');
}
</script>

<template>
    <Head :title="`Game ${game.code}`" />

    <GameLayout :show-mode-selector="false">
        <div class="container mx-auto px-4 py-8">
            <div class="flex flex-col gap-8 lg:flex-row">
                <!-- Main Game Area -->
                <div class="flex-1">
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
                                :name="opponentName ?? (isWaiting ? 'Waiting...' : 'Opponent')"
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
                                    'text-3xl font-mono font-bold',
                                    remainingTime <= 5 && 'text-destructive animate-pulse',
                                    isMyTurn && remainingTime > 5 && 'text-yellow-500',
                                ]"
                            >
                                0:{{ remainingTime.toString().padStart(2, '0') }}
                            </div>
                        </div>

                        <!-- Status Messages -->
                        <div class="space-y-2 text-center">
                            <template v-if="isWaiting">
                                <p class="text-lg text-muted-foreground">Waiting for opponent...</p>
                                <p class="text-2xl font-mono font-bold tracking-widest text-yellow-500">
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
                            <Button v-if="isFinished" @click="playAgain"> Play Again </Button>
                            <Button v-if="isFinished" variant="outline" @click="goHome">
                                Back to Home
                            </Button>
                            <Button
                                v-if="isPlaying && game.mode === 'online'"
                                variant="destructive"
                                @click="forfeit"
                            >
                                Forfeit
                            </Button>
                            <Button
                                v-if="isWaiting"
                                variant="outline"
                                @click="goHome"
                            >
                                Cancel
                            </Button>
                        </div>
                    </div>
                </div>

                <!-- Chat Panel (Online mode only) -->
                <div v-if="game.mode === 'online'" class="h-[400px] w-full lg:w-80">
                    <GameChat
                        :messages="messages"
                        :player-symbol="playerSymbol ?? 'X'"
                        @send="sendMessage"
                    />
                </div>
            </div>
        </div>
    </GameLayout>
</template>
