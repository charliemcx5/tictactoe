<?php

namespace App\Events;

use App\Models\GameMessage;
use Illuminate\Broadcasting\Channel;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Contracts\Broadcasting\ShouldBroadcast;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class ChatMessageSent implements ShouldBroadcast
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    public function __construct(
        public GameMessage $message
    ) {}

    public function broadcastOn(): array
    {
        return [
            new Channel('game.'.$this->message->game->code),
        ];
    }

    public function broadcastWith(): array
    {
        return [
            'message' => [
                'id' => $this->message->id,
                'player_name' => $this->message->player_name,
                'player_symbol' => $this->message->player_symbol,
                'content' => $this->message->content,
                'created_at' => $this->message->created_at->toISOString(),
                'is_system' => $this->message->is_system,
            ],
        ];
    }
}
