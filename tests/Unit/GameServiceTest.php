<?php

use App\Models\Game;
use App\Services\GameService;

test('check winner detects horizontal win', function () {
    $service = new GameService();

    // Top row
    $board = ['X', 'X', 'X', '', '', '', '', '', ''];
    expect($service->checkWinner($board))->toBe('X');

    // Middle row
    $board = ['', '', '', 'O', 'O', 'O', '', '', ''];
    expect($service->checkWinner($board))->toBe('O');

    // Bottom row
    $board = ['', '', '', '', '', '', 'X', 'X', 'X'];
    expect($service->checkWinner($board))->toBe('X');
});

test('check winner detects vertical win', function () {
    $service = new GameService();

    // Left column
    $board = ['X', '', '', 'X', '', '', 'X', '', ''];
    expect($service->checkWinner($board))->toBe('X');

    // Middle column
    $board = ['', 'O', '', '', 'O', '', '', 'O', ''];
    expect($service->checkWinner($board))->toBe('O');

    // Right column
    $board = ['', '', 'X', '', '', 'X', '', '', 'X'];
    expect($service->checkWinner($board))->toBe('X');
});

test('check winner detects diagonal win', function () {
    $service = new GameService();

    // Top-left to bottom-right
    $board = ['X', '', '', '', 'X', '', '', '', 'X'];
    expect($service->checkWinner($board))->toBe('X');

    // Top-right to bottom-left
    $board = ['', '', 'O', '', 'O', '', 'O', '', ''];
    expect($service->checkWinner($board))->toBe('O');
});

test('check winner returns null when no winner', function () {
    $service = new GameService();

    $board = ['X', 'O', 'X', 'O', '', 'X', 'O', 'X', 'O'];
    expect($service->checkWinner($board))->toBeNull();

    $board = array_fill(0, 9, '');
    expect($service->checkWinner($board))->toBeNull();
});

test('is draw returns true when board full and no winner', function () {
    $service = new GameService();

    $board = ['X', 'O', 'X', 'O', 'O', 'X', 'O', 'X', 'O'];
    expect($service->isDraw($board))->toBeTrue();
});

test('is draw returns false when board has empty cells', function () {
    $service = new GameService();

    $board = ['X', 'O', 'X', 'O', '', 'X', 'O', 'X', 'O'];
    expect($service->isDraw($board))->toBeFalse();

    $board = array_fill(0, 9, '');
    expect($service->isDraw($board))->toBeFalse();
});

test('is draw returns false when there is a winner', function () {
    $service = new GameService();

    $board = ['X', 'X', 'X', 'O', 'O', '', '', '', ''];
    expect($service->isDraw($board))->toBeFalse();
});

test('get winning cells returns correct pattern for horizontal win', function () {
    $service = new GameService();

    $board = ['X', 'X', 'X', '', '', '', '', '', ''];
    $cells = $service->getWinningCells($board);
    expect($cells)->toBe([0, 1, 2]);

    $board = ['', '', '', 'O', 'O', 'O', '', '', ''];
    $cells = $service->getWinningCells($board);
    expect($cells)->toBe([3, 4, 5]);
});

test('get winning cells returns correct pattern for vertical win', function () {
    $service = new GameService();

    $board = ['X', '', '', 'X', '', '', 'X', '', ''];
    $cells = $service->getWinningCells($board);
    expect($cells)->toBe([0, 3, 6]);
});

test('get winning cells returns correct pattern for diagonal win', function () {
    $service = new GameService();

    $board = ['X', '', '', '', 'X', '', '', '', 'X'];
    $cells = $service->getWinningCells($board);
    expect($cells)->toBe([0, 4, 8]);

    $board = ['', '', 'O', '', 'O', '', 'O', '', ''];
    $cells = $service->getWinningCells($board);
    expect($cells)->toBe([2, 4, 6]);
});

test('get winning cells returns empty array when no winner', function () {
    $service = new GameService();

    $board = array_fill(0, 9, '');
    $cells = $service->getWinningCells($board);
    expect($cells)->toBe([]);

    $board = ['X', 'O', 'X', 'O', '', 'X', 'O', 'X', 'O'];
    $cells = $service->getWinningCells($board);
    expect($cells)->toBe([]);
});

test('make move updates board correctly', function () {
    $service = new GameService();
    $game = Game::factory()->create([
        'board' => array_fill(0, 9, ''),
        'current_turn' => 'X',
        'status' => 'playing',
    ]);

    $result = $service->makeMove($game, 0, 'X');

    expect($result['success'])->toBeTrue();
    $game->refresh();
    expect($game->board[0])->toBe('X');
});

test('make move changes turn after valid move', function () {
    $service = new GameService();
    $game = Game::factory()->create([
        'board' => array_fill(0, 9, ''),
        'current_turn' => 'X',
        'status' => 'playing',
    ]);

    $service->makeMove($game, 0, 'X');

    $game->refresh();
    expect($game->current_turn)->toBe('O');
});

test('make move detects win and updates game status', function () {
    $service = new GameService();
    $game = Game::factory()->create([
        'board' => ['X', 'X', '', '', '', '', '', '', ''],
        'current_turn' => 'X',
        'status' => 'playing',
    ]);

    $service->makeMove($game, 2, 'X');

    $game->refresh();
    expect($game->status)->toBe('finished');
    expect($game->winner)->toBe('X');
    expect($game->player_x_score)->toBe(1);
});

test('make move detects draw and updates game status', function () {
    $service = new GameService();
    $game = Game::factory()->create([
        'board' => ['X', 'O', 'X', 'O', 'O', 'X', '', 'X', 'O'],
        'current_turn' => 'X',
        'status' => 'playing',
    ]);

    $service->makeMove($game, 6, 'X');

    $game->refresh();
    expect($game->status)->toBe('finished');
    expect($game->winner)->toBe('draw');
});

test('make move rejects invalid move on occupied cell', function () {
    $service = new GameService();
    $game = Game::factory()->create([
        'board' => ['X', '', '', '', '', '', '', '', ''],
        'current_turn' => 'X',
        'status' => 'playing',
    ]);

    $result = $service->makeMove($game, 0, 'X');

    expect($result['success'])->toBeFalse();
    expect($result['error'])->toBe('Invalid move');
});

test('make move rejects move when not player turn', function () {
    $service = new GameService();
    $game = Game::factory()->create([
        'board' => array_fill(0, 9, ''),
        'current_turn' => 'O',
        'status' => 'playing',
    ]);

    $result = $service->makeMove($game, 0, 'X');

    expect($result['success'])->toBeFalse();
    expect($result['error'])->toBe('Invalid move');
});

test('reset board clears board and resets game state', function () {
    $service = new GameService();
    $game = Game::factory()->create([
        'board' => ['X', 'O', 'X', 'O', 'X', 'O', 'O', 'X', 'O'],
        'current_turn' => 'O',
        'status' => 'finished',
        'winner' => 'draw',
        'player_x_score' => 1,
        'player_o_score' => 1,
    ]);

    $result = $service->resetBoard($game);

    expect($result->board)->toBe(array_fill(0, 9, ''));
    expect($result->current_turn)->toBe('X');
    expect($result->status)->toBe('playing');
    expect($result->winner)->toBeNull();
    expect($result->player_x_score)->toBe(1); // Scores are preserved
    expect($result->player_o_score)->toBe(1);
});

