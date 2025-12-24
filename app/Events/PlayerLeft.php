<?php

namespace App\Events;

use Illuminate\Broadcasting\Channel;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Contracts\Broadcasting\ShouldBroadcast;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class PlayerLeft implements ShouldBroadcast
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    public function __construct(
        public string $gameCode,
        public string $playerName
    ) {}

    public function broadcastOn(): array
    {
        return [
            new Channel('game.'.$this->gameCode),
        ];
    }

    public function broadcastWith(): array
    {
        return [
            'player_name' => $this->playerName,
        ];
    }
}
