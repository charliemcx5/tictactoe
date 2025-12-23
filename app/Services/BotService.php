<?php

namespace App\Services;

class BotService
{
    private const WIN_PATTERNS = [
        [0, 1, 2], [3, 4, 5], [6, 7, 8],
        [0, 3, 6], [1, 4, 7], [2, 5, 8],
        [0, 4, 8], [2, 4, 6],
    ];

    public function getMove(array $board, string $botSymbol = 'O'): int
    {
        $opponentSymbol = $botSymbol === 'X' ? 'O' : 'X';

        // 1. Check for winning move
        $winMove = $this->findWinningMove($board, $botSymbol);
        if ($winMove !== null) {
            return $winMove;
        }

        // 2. Block opponent's winning move
        $blockMove = $this->findWinningMove($board, $opponentSymbol);
        if ($blockMove !== null) {
            return $blockMove;
        }

        // 3. Take center if available
        if ($board[4] === '') {
            return 4;
        }

        // 4. Take a corner if available
        $corners = [0, 2, 6, 8];
        $availableCorners = array_filter($corners, fn ($i) => $board[$i] === '');
        if (count($availableCorners) > 0) {
            return $availableCorners[array_rand($availableCorners)];
        }

        // 5. Random available move
        $available = array_keys(array_filter($board, fn ($cell) => $cell === ''));

        return $available[array_rand($available)];
    }

    private function findWinningMove(array $board, string $player): ?int
    {
        foreach (self::WIN_PATTERNS as $pattern) {
            $values = [$board[$pattern[0]], $board[$pattern[1]], $board[$pattern[2]]];
            $playerCount = count(array_filter($values, fn ($v) => $v === $player));
            $emptyIndex = array_search('', $values, true);

            if ($playerCount === 2 && $emptyIndex !== false) {
                return $pattern[$emptyIndex];
            }
        }

        return null;
    }
}
