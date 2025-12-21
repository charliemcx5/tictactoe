<?php

namespace App\Http\Controllers;

use App\Events\ChatMessageSent;
use App\Models\Game;
use App\Models\GameMessage;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;

class ChatController extends Controller
{
    public function store(Request $request, string $code): RedirectResponse
    {
        $validated = $request->validate([
            'content' => 'required|string|max:500',
        ]);

        $game = Game::where('code', $code)->firstOrFail();
        $sessionPlayer = session('game_player');

        if (! $sessionPlayer || $sessionPlayer['game_id'] !== $game->id) {
            abort(403);
        }

        $message = GameMessage::create([
            'game_id' => $game->id,
            'user_id' => $request->user()?->id,
            'player_name' => $sessionPlayer['name'],
            'player_symbol' => $sessionPlayer['symbol'],
            'content' => $validated['content'],
        ]);

        broadcast(new ChatMessageSent($message))->toOthers();

        return back();
    }
}
