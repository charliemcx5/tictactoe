<?php

namespace App\Services;

use App\Models\Game;

class GameService
{
    private const WIN_PATTERNS = [
        [0, 1, 2], [3, 4, 5], [6, 7, 8],  // Rows
        [0, 3, 6], [1, 4, 7], [2, 5, 8],  // Columns
        [0, 4, 8], [2, 4, 6],             // Diagonals
    ];

    public function checkWinner(array $board): ?string
    {
        foreach (self::WIN_PATTERNS as $pattern) {
            [$a, $b, $c] = $pattern;
            if ($board[$a] !== '' && $board[$a] === $board[$b] && $board[$b] === $board[$c]) {
                return $board[$a];
            }
        }

        return null;
    }

    public function isDraw(array $board): bool
    {
        return $this->checkWinner($board) === null && ! in_array('', $board, true);
    }

    public function getWinningCells(array $board): array
    {
        foreach (self::WIN_PATTERNS as $pattern) {
            [$a, $b, $c] = $pattern;
            if ($board[$a] !== '' && $board[$a] === $board[$b] && $board[$b] === $board[$c]) {
                return $pattern;
            }
        }

        return [];
    }

    public function makeMove(Game $game, int $position, string $player): array
    {
        $board = $game->board;

        if ($board[$position] !== '' || $game->current_turn !== $player) {
            return ['success' => false, 'error' => 'Invalid move'];
        }

        $board[$position] = $player;
        $game->board = $board;

        $winner = $this->checkWinner($board);
        if ($winner) {
            $game->winner = $winner;
            $game->status = 'finished';
            if ($winner === 'X') {
                $game->player_x_score++;
            } else {
                $game->player_o_score++;
            }
        } elseif ($this->isDraw($board)) {
            $game->winner = 'draw';
            $game->status = 'finished';
        } else {
            $game->current_turn = $player === 'X' ? 'O' : 'X';
            $game->turn_started_at = now();
        }

        $game->save();

        return ['success' => true, 'game' => $game];
    }

    public function resetBoard(Game $game): Game
    {
        // Swap player sides
        $tempName = $game->player_x_name;
        $tempId = $game->player_x_id;
        $tempScore = $game->player_x_score;

        $game->player_x_name = $game->player_o_name;
        $game->player_x_id = $game->player_o_id;
        $game->player_x_score = $game->player_o_score;

        $game->player_o_name = $tempName;
        $game->player_o_id = $tempId;
        $game->player_o_score = $tempScore;

        // Reset board state
        $game->board = array_fill(0, 9, '');
        $game->current_turn = 'X';
        $game->status = 'playing';
        $game->winner = null;
        $game->rematch_requested_by = null;
        $game->turn_started_at = now();
        $game->save();

        return $game;
    }

    public function resetBoardSimple(Game $game): Game
    {
        // Reset board state without swapping sides (for bot games)
        $game->board = array_fill(0, 9, '');
        $game->current_turn = 'X';
        $game->status = 'playing';
        $game->winner = null;
        $game->rematch_requested_by = null;
        $game->turn_started_at = now();
        $game->save();

        return $game;
    }
}
