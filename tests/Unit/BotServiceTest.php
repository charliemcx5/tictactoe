<?php

use App\Services\BotService;

test('bot takes winning move when available', function () {
    $service = new BotService();

    // Bot (O) can win with move at position 2
    $board = ['O', 'O', '', 'X', '', 'X', '', '', ''];
    $move = $service->getMove($board);

    expect($move)->toBe(2);
});

test('bot blocks opponent winning move', function () {
    $service = new BotService();

    // Opponent (X) can win, bot should block at position 2
    $board = ['X', 'X', '', 'O', '', '', '', '', ''];
    $move = $service->getMove($board);

    expect($move)->toBe(2);
});

test('bot takes center when available and no immediate threat', function () {
    $service = new BotService();

    $board = ['X', '', '', '', '', '', '', '', ''];
    $move = $service->getMove($board);

    expect($move)->toBe(4); // Center position
});

test('bot takes corner when center is taken and no immediate threat', function () {
    $service = new BotService();

    $board = ['X', '', '', '', 'O', '', '', '', ''];
    $move = $service->getMove($board);

    // Should take one of the corners: 0, 2, 6, or 8
    expect([0, 2, 6, 8])->toContain($move);
});

test('bot makes random move when no strategy applies', function () {
    $service = new BotService();

    $board = ['X', 'O', 'X', 'O', 'X', '', '', '', ''];
    $move = $service->getMove($board);

    // Should be one of the available positions: 5, 6, 7, 8
    expect([5, 6, 7, 8])->toContain($move);
});

test('bot prioritizes win over block', function () {
    $service = new BotService();

    // Bot can win AND opponent can win - bot should win
    $board = ['O', 'O', '', 'X', 'X', '', '', '', ''];
    $move = $service->getMove($board);

    // Bot should win (position 2), not block opponent
    expect($move)->toBe(2);
});

test('bot blocks multiple potential wins correctly', function () {
    $service = new BotService();

    // Opponent has two potential wins, bot can only block one
    // But the test ensures bot blocks when it can
    $board = ['X', 'X', '', '', '', '', '', '', ''];
    $move = $service->getMove($board);

    expect($move)->toBe(2); // Blocks the horizontal win
});

test('bot handles empty board by taking center', function () {
    $service = new BotService();

    $board = array_fill(0, 9, '');
    $move = $service->getMove($board);

    expect($move)->toBe(4); // Center
});

test('bot makes valid move when only one option', function () {
    $service = new BotService();

    $board = ['X', 'O', 'X', 'O', 'X', 'O', 'O', 'X', ''];
    $move = $service->getMove($board);

    expect($move)->toBe(8); // Only available position
});

test('bot does not place on occupied cell', function () {
    $service = new BotService();

    // All corners and center are taken, bot should pick an edge
    $board = ['X', '', 'O', '', 'X', '', 'O', '', 'X'];
    $move = $service->getMove($board);

    // Should pick one of the edges: 1, 3, 5, 7
    expect([1, 3, 5, 7])->toContain($move);
});

