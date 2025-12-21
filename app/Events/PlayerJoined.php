<?php

namespace App\Events;

use App\Models\Game;
use Illuminate\Broadcasting\Channel;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Contracts\Broadcasting\ShouldBroadcast;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class PlayerJoined implements ShouldBroadcast
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    public function __construct(
        public Game $game,
        public string $playerName
    ) {}

    public function broadcastOn(): array
    {
        return [
            new Channel('game.'.$this->game->code),
        ];
    }

    public function broadcastWith(): array
    {
        return [
            'player_name' => $this->playerName,
            'game' => [
                'player_o_name' => $this->game->player_o_name,
                'status' => $this->game->status,
                'turn_started_at' => $this->game->turn_started_at?->toISOString(),
            ],
        ];
    }
}
