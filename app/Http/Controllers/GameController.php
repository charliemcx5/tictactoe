<?php

namespace App\Http\Controllers;

use App\Events\GameUpdated;
use App\Events\PlayerJoined;
use App\Models\Game;
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
            'timer_setting' => 'required|in:off,3,10,30',
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

        // Bot response
        if ($game->mode === 'bot' && $game->status === 'playing' && $game->current_turn === 'O') {
            $botMove = $this->botService->getMove($game->board);
            $this->gameService->makeMove($game, $botMove, 'O');
            broadcast(new GameUpdated($game->fresh()))->toOthers();
        }

        return back();
    }

    public function playAgain(string $code): RedirectResponse
    {
        $game = Game::where('code', $code)->firstOrFail();
        $sessionPlayer = session('game_player');

        if (! $sessionPlayer || $sessionPlayer['game_id'] !== $game->id) {
            abort(403);
        }

        $this->gameService->resetBoard($game);
        broadcast(new GameUpdated($game))->toOthers();

        return back();
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
}
