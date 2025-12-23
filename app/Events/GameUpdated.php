<?php

namespace App\Events;

use App\Models\Game;
use App\Services\GameService;
use Illuminate\Broadcasting\Channel;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Contracts\Broadcasting\ShouldBroadcast;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class GameUpdated implements ShouldBroadcast
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    public function __construct(
        public Game $game
    ) {}

    public function broadcastOn(): array
    {
        return [
            new Channel('game.'.$this->game->code),
        ];
    }

    public function broadcastWith(): array
    {
        $gameService = app(GameService::class);
        $winningCells = $gameService->getWinningCells($this->game->board);

        return [
            'game' => [
                'id' => $this->game->id,
                'code' => $this->game->code,
                'board' => $this->game->board,
                'mode' => $this->game->mode,
                'timer_setting' => $this->game->timer_setting,
                'player_x_name' => $this->game->player_x_name,
                'player_x_score' => $this->game->player_x_score,
                'player_o_name' => $this->game->player_o_name,
                'player_o_score' => $this->game->player_o_score,
                'current_turn' => $this->game->current_turn,
                'status' => $this->game->status,
                'winner' => $this->game->winner,
                'turn_started_at' => $this->game->turn_started_at?->toISOString(),
                'winning_cells' => $winningCells,
                'rematch_requested_by' => $this->game->rematch_requested_by,
            ],
        ];
    }
}
