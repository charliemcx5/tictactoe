<?php

namespace Database\Factories;

use App\Models\Game;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Game>
 */
class GameFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'code' => Game::generateCode(),
            'board' => array_fill(0, 9, ''),
            'mode' => 'bot',
            'timer_setting' => 'off',
            'player_x_id' => null,
            'player_x_name' => fake()->name(),
            'player_x_score' => 0,
            'player_o_id' => null,
            'player_o_name' => 'Bot',
            'player_o_score' => 0,
            'current_turn' => 'X',
            'status' => 'playing',
            'winner' => null,
            'turn_started_at' => now(),
        ];
    }
}

