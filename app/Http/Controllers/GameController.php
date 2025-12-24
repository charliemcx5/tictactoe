<?php

namespace App\Http\Controllers;

use App\Events\ChatMessageSent;
use App\Events\GameUpdated;
use App\Events\PlayerJoined;
use App\Events\PlayerLeft;
use App\Models\Game;
use App\Models\GameMessage;
use App\Services\BotService;
use App\Services\GameService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class GameController extends Controller
{
    public function __construct(
        private GameService $gameService,
        private BotService $botService
    ) {}

    public function create(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'player_name' => 'required|string|max:50',
            'mode' => 'required|in:bot,online',
            'timer_setting' => 'required|in:off,5,10,30',
        ]);

        $game = Game::create([
            'code' => Game::generateCode(),
            'board' => array_fill(0, 9, ''),
            'mode' => $validated['mode'],
            'timer_setting' => $validated['timer_setting'],
            'player_x_id' => $request->user()?->id,
            'player_x_name' => $request->user()?->name ?? $validated['player_name'],
            'player_o_name' => $validated['mode'] === 'bot' ? 'Bot' : null,
            'current_turn' => 'X',
            'status' => $validated['mode'] === 'bot' ? 'playing' : 'waiting',
            'turn_started_at' => $validated['mode'] === 'bot' ? now() : null,
        ]);

        // Store player info in session for guests
        session(['game_player' => [
            'game_id' => $game->id,
            'symbol' => 'X',
            'name' => $game->player_x_name,
        ]]);

        return redirect()->route('game.show', $game->code);
    }

    public function show(string $code): Response
    {
        $game = Game::where('code', $code)->firstOrFail();
        $sessionPlayer = session('game_player');
        $winningCells = $this->gameService->getWinningCells($game->board);

        // Fix session mismatch after rematch (sides swapped)
        // If player's name matches a position but their session symbol doesn't match, update session
        if ($sessionPlayer && $sessionPlayer['game_id'] === $game->id && $game->mode === 'online') {
            $playerName = $sessionPlayer['name'];
            $currentSymbol = $sessionPlayer['symbol'];
            
            // Check if name matches X position but symbol is O, or vice versa
            if ($playerName === $game->player_x_name && $currentSymbol !== 'X') {
                session(['game_player' => [
                    'game_id' => $game->id,
                    'symbol' => 'X',
                    'name' => $playerName,
                ]]);
                $sessionPlayer['symbol'] = 'X';
            } elseif ($playerName === $game->player_o_name && $currentSymbol !== 'O') {
                session(['game_player' => [
                    'game_id' => $game->id,
                    'symbol' => 'O',
                    'name' => $playerName,
                ]]);
                $sessionPlayer['symbol'] = 'O';
            }
        }

        return Inertia::render('Game', [
            'game' => [
                'id' => $game->id,
                'code' => $game->code,
                'board' => $game->board,
                'mode' => $game->mode,
                'timer_setting' => $game->timer_setting,
                'player_x_name' => $game->player_x_name,
                'player_x_score' => $game->player_x_score,
                'player_o_name' => $game->player_o_name,
                'player_o_score' => $game->player_o_score,
                'current_turn' => $game->current_turn,
                'status' => $game->status,
                'winner' => $game->winner,
                'turn_started_at' => $game->turn_started_at?->toISOString(),
                'winning_cells' => $winningCells,
                'rematch_requested_by' => $game->rematch_requested_by,
            ],
            'playerSymbol' => $sessionPlayer && $sessionPlayer['game_id'] === $game->id
                ? $sessionPlayer['symbol']
                : null,
            'messages' => $game->messages()
                ->orderBy('created_at', 'asc')
                ->get()
                ->map(fn ($m) => [
                    'id' => $m->id,
                    'player_name' => $m->player_name,
                    'player_symbol' => $m->player_symbol,
                    'content' => $m->content,
                    'created_at' => $m->created_at->toISOString(),
                    'is_system' => $m->is_system,
                ]),
        ]);
    }

    public function join(Request $request, string $code): RedirectResponse
    {
        $validated = $request->validate([
            'player_name' => 'required|string|max:50',
        ]);

        $game = Game::where('code', $code)
            ->where('status', 'waiting')
            ->where('mode', 'online')
            ->firstOrFail();

        $game->update([
            'player_o_id' => $request->user()?->id,
            'player_o_name' => $request->user()?->name ?? $validated['player_name'],
            'status' => 'playing',
            'turn_started_at' => now(),
        ]);

        session(['game_player' => [
            'game_id' => $game->id,
            'symbol' => 'O',
            'name' => $game->player_o_name,
        ]]);

        broadcast(new PlayerJoined($game, $game->player_o_name))->toOthers();

        // Create system message for player joined
        $message = GameMessage::create([
            'game_id' => $game->id,
            'player_name' => 'System',
            'player_symbol' => null,
            'content' => "{$game->player_o_name} joined the game",
            'is_system' => true,
        ]);
        broadcast(new ChatMessageSent($message))->toOthers();

        return redirect()->route('game.show', $code);
    }

    public function move(Request $request, string $code): RedirectResponse
    {
        $validated = $request->validate([
            'position' => 'required|integer|min:0|max:8',
        ]);

        $game = Game::where('code', $code)->firstOrFail();
        $sessionPlayer = session('game_player');

        if (! $sessionPlayer || $sessionPlayer['game_id'] !== $game->id) {
            abort(403);
        }

        $result = $this->gameService->makeMove(
            $game,
            $validated['position'],
            $sessionPlayer['symbol']
        );

        if (! $result['success']) {
            return back()->withErrors(['move' => $result['error']]);
        }

        broadcast(new GameUpdated($game))->toOthers();

        // Bot response - check if it's the bot's turn (bot could be X or O)
        if ($game->mode === 'bot' && $game->status === 'playing') {
            $botSymbol = $game->player_x_name === 'Bot' ? 'X' : 'O';
            if ($game->current_turn === $botSymbol) {
                $botMove = $this->botService->getMove($game->board, $botSymbol);
                $this->gameService->makeMove($game, $botMove, $botSymbol);
            }
        }

        return back();
    }

    public function requestRematch(string $code): RedirectResponse
    {
        $game = Game::where('code', $code)->firstOrFail();
        $sessionPlayer = session('game_player');

        if (! $sessionPlayer || $sessionPlayer['game_id'] !== $game->id) {
            abort(403);
        }

        // Only allow rematch request when game is finished
        if ($game->status !== 'finished') {
            return redirect()->route('game.show', $code);
        }

        // Bot games: reset immediately without request/accept flow
        if ($game->mode === 'bot') {
            $this->gameService->resetBoardSimple($game);
            broadcast(new GameUpdated($game->fresh()))->toOthers();

            // Bot makes first move if bot is X
            if ($game->current_turn === 'X' && $game->player_x_name === 'Bot') {
                $botMove = $this->botService->getMove($game->board, 'X');
                $this->gameService->makeMove($game, $botMove, 'X');
            }

            // Force full page reload to get fresh session-based props
            return redirect()->route('game.show', $code);
        }

        // Online games: set rematch request
        $game->update([
            'rematch_requested_by' => $sessionPlayer['symbol'],
        ]);

        broadcast(new GameUpdated($game->fresh()))->toOthers();

        return back();
    }

    public function acceptRematch(string $code): RedirectResponse
    {
        $game = Game::where('code', $code)->firstOrFail();
        $sessionPlayer = session('game_player');

        if (! $sessionPlayer || $sessionPlayer['game_id'] !== $game->id) {
            abort(403);
        }

        // Only allow accept when game is finished and other player requested
        if ($game->status !== 'finished' || ! $game->rematch_requested_by) {
            return redirect()->route('game.show', $code);
        }

        // Can't accept your own request
        if ($game->rematch_requested_by === $sessionPlayer['symbol']) {
            return redirect()->route('game.show', $code);
        }

        // Perform the reset with side swapping
        $this->gameService->resetBoard($game);

        // Update both players' sessions - swap their symbols
        // For the accepting player
        $newSymbol = $sessionPlayer['symbol'] === 'X' ? 'O' : 'X';
        session(['game_player' => [
            'game_id' => $game->id,
            'symbol' => $newSymbol,
            'name' => $sessionPlayer['name'],
        ]]);

        // Broadcast the reset - other player will reload to get updated session
        broadcast(new GameUpdated($game->fresh()))->toOthers();

        // Force full page reload to get fresh session-based props
        return redirect()->route('game.show', $code);
    }

    public function forfeit(string $code): RedirectResponse
    {
        $game = Game::where('code', $code)->firstOrFail();
        $sessionPlayer = session('game_player');

        if (! $sessionPlayer || $sessionPlayer['game_id'] !== $game->id) {
            abort(403);
        }

        // The player who forfeits loses
        $winner = $sessionPlayer['symbol'] === 'X' ? 'O' : 'X';

        $game->update([
            'status' => 'finished',
            'winner' => $winner,
        ]);

        // Update score for winner
        if ($winner === 'X') {
            $game->increment('player_x_score');
        } else {
            $game->increment('player_o_score');
        }

        broadcast(new GameUpdated($game->fresh()))->toOthers();

        return redirect()->route('home');
    }

    public function updateTimer(Request $request, string $code): RedirectResponse
    {
        $validated = $request->validate([
            'timer_setting' => 'required|in:off,5,10,30',
        ]);

        $game = Game::where('code', $code)->firstOrFail();
        $sessionPlayer = session('game_player');

        if (! $sessionPlayer || $sessionPlayer['game_id'] !== $game->id) {
            abort(403);
        }

        // Only X player can change timer, and only between rounds
        if ($sessionPlayer['symbol'] !== 'X' || $game->status === 'playing') {
            abort(403);
        }

        $game->update([
            'timer_setting' => $validated['timer_setting'],
        ]);

        broadcast(new GameUpdated($game->fresh()))->toOthers();

        return back();
    }

    public function leave(string $code): RedirectResponse
    {
        $game = Game::where('code', $code)->firstOrFail();
        $sessionPlayer = session('game_player');

        if (! $sessionPlayer || $sessionPlayer['game_id'] !== $game->id) {
            abort(403);
        }

        // Clear session first
        session()->forget('game_player');

        // Bot games: delete immediately
        if ($game->mode === 'bot') {
            $game->delete();

            return redirect()->route('home');
        }

        // Online games: notify other player and delete
        broadcast(new PlayerLeft($game->code, $sessionPlayer['name']))->toOthers();
        $game->delete();

        return redirect()->route('home');
    }
}
