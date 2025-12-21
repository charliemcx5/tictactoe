<?php

use App\Models\Game;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

test('guest can create game', function () {
    $response = $this->post(route('game.create'), [
        'player_name' => 'Test Player',
        'mode' => 'bot',
        'timer_setting' => 'off',
    ]);

    $response->assertRedirect();
    $this->assertDatabaseHas('games', [
        'player_x_name' => 'Test Player',
        'mode' => 'bot',
        'status' => 'playing',
    ]);
    $this->assertNotNull(session('game_player'));
});

test('guest can create online game', function () {
    $response = $this->post(route('game.create'), [
        'player_name' => 'Host Player',
        'mode' => 'online',
        'timer_setting' => '10',
    ]);

    $response->assertRedirect();
    $this->assertDatabaseHas('games', [
        'player_x_name' => 'Host Player',
        'mode' => 'online',
        'status' => 'waiting',
        'timer_setting' => '10',
    ]);
});

test('authenticated user can create game', function () {
    $user = User::factory()->create();

    $response = $this->actingAs($user)->post(route('game.create'), [
        'player_name' => 'User Name',
        'mode' => 'bot',
        'timer_setting' => 'off',
    ]);

    $response->assertRedirect();
    $this->assertDatabaseHas('games', [
        'player_x_id' => $user->id,
        'player_x_name' => $user->name,
    ]);
});

test('guest can view game', function () {
    $game = Game::factory()->create([
        'player_x_name' => 'Player X',
        'mode' => 'bot',
        'status' => 'playing',
    ]);

    session(['game_player' => [
        'game_id' => $game->id,
        'symbol' => 'X',
        'name' => 'Player X',
    ]]);

    $response = $this->get(route('game.show', $game->code));

    $response->assertStatus(200);
    $response->assertInertia(fn ($page) => $page
        ->component('Game')
        ->has('game')
        ->where('game.code', $game->code)
    );
});

test('guest can join online game', function () {
    $game = Game::factory()->create([
        'mode' => 'online',
        'status' => 'waiting',
        'player_x_name' => 'Host',
    ]);

    $response = $this->post(route('game.join', $game->code), [
        'player_name' => 'Joiner',
    ]);

    $response->assertRedirect(route('game.show', $game->code));
    $this->assertDatabaseHas('games', [
        'id' => $game->id,
        'player_o_name' => 'Joiner',
        'status' => 'playing',
    ]);
    $this->assertEquals('O', session('game_player')['symbol']);
});

test('cannot join non-existent game', function () {
    $response = $this->post(route('game.join', 'INVALID'), [
        'player_name' => 'Player',
    ]);

    $response->assertNotFound();
});

test('cannot join already started game', function () {
    $game = Game::factory()->create([
        'mode' => 'online',
        'status' => 'playing',
    ]);

    $response = $this->post(route('game.join', $game->code), [
        'player_name' => 'Player',
    ]);

    $response->assertNotFound();
});

test('player can make valid move', function () {
    $game = Game::factory()->create([
        'board' => array_fill(0, 9, ''),
        'current_turn' => 'X',
        'status' => 'playing',
        'mode' => 'online',
    ]);

    session(['game_player' => [
        'game_id' => $game->id,
        'symbol' => 'X',
        'name' => 'Player',
    ]]);

    $response = $this->post(route('game.move', $game->code), [
        'position' => 0,
    ]);

    $response->assertRedirect();
    $game->refresh();
    $this->assertEquals('X', $game->board[0]);
    $this->assertEquals('O', $game->current_turn);
});

test('player cannot make move when not their turn', function () {
    $game = Game::factory()->create([
        'board' => array_fill(0, 9, ''),
        'current_turn' => 'O',
        'status' => 'playing',
    ]);

    session(['game_player' => [
        'game_id' => $game->id,
        'symbol' => 'X',
        'name' => 'Player',
    ]]);

    $response = $this->post(route('game.move', $game->code), [
        'position' => 0,
    ]);

    $response->assertRedirect();
    $response->assertSessionHasErrors('move');
});

test('player cannot make move on occupied cell', function () {
    $board = array_fill(0, 9, '');
    $board[0] = 'X';
    $game = Game::factory()->create([
        'board' => $board,
        'current_turn' => 'X',
        'status' => 'playing',
    ]);

    session(['game_player' => [
        'game_id' => $game->id,
        'symbol' => 'X',
        'name' => 'Player',
    ]]);

    $response = $this->post(route('game.move', $game->code), [
        'position' => 0,
    ]);

    $response->assertRedirect();
    $response->assertSessionHasErrors('move');
});

test('win detection works for horizontal win', function () {
    $game = Game::factory()->create([
        'board' => ['X', 'X', '', '', '', '', '', '', ''],
        'current_turn' => 'X',
        'status' => 'playing',
        'mode' => 'bot',
    ]);

    session(['game_player' => [
        'game_id' => $game->id,
        'symbol' => 'X',
        'name' => 'Player',
    ]]);

    $this->post(route('game.move', $game->code), [
        'position' => 2,
    ]);

    $game->refresh();
    $this->assertEquals('finished', $game->status);
    $this->assertEquals('X', $game->winner);
    $this->assertEquals(1, $game->player_x_score);
});

test('win detection works for vertical win', function () {
    $game = Game::factory()->create([
        'board' => ['O', '', '', 'O', '', '', '', '', ''],
        'current_turn' => 'O',
        'status' => 'playing',
        'mode' => 'bot',
    ]);

    session(['game_player' => [
        'game_id' => $game->id,
        'symbol' => 'O',
        'name' => 'Player',
    ]]);

    $this->post(route('game.move', $game->code), [
        'position' => 6,
    ]);

    $game->refresh();
    $this->assertEquals('finished', $game->status);
    $this->assertEquals('O', $game->winner);
});

test('win detection works for diagonal win', function () {
    $game = Game::factory()->create([
        'board' => ['X', '', '', '', 'X', '', '', '', ''],
        'current_turn' => 'X',
        'status' => 'playing',
        'mode' => 'bot',
    ]);

    session(['game_player' => [
        'game_id' => $game->id,
        'symbol' => 'X',
        'name' => 'Player',
    ]]);

    $this->post(route('game.move', $game->code), [
        'position' => 8,
    ]);

    $game->refresh();
    $this->assertEquals('finished', $game->status);
    $this->assertEquals('X', $game->winner);
});

test('draw detection works', function () {
    $game = Game::factory()->create([
        'board' => ['X', 'O', 'X', 'O', 'O', 'X', '', 'X', 'O'],
        'current_turn' => 'X',
        'status' => 'playing',
        'mode' => 'bot',
    ]);

    session(['game_player' => [
        'game_id' => $game->id,
        'symbol' => 'X',
        'name' => 'Player',
    ]]);

    $this->post(route('game.move', $game->code), [
        'position' => 6,
    ]);

    $game->refresh();
    $this->assertEquals('finished', $game->status);
    $this->assertEquals('draw', $game->winner);
});

test('play again resets board', function () {
    $game = Game::factory()->create([
        'board' => ['X', 'O', 'X', 'O', 'X', 'O', 'O', 'X', 'O'],
        'current_turn' => 'O',
        'status' => 'finished',
        'winner' => 'draw',
    ]);

    session(['game_player' => [
        'game_id' => $game->id,
        'symbol' => 'X',
        'name' => 'Player',
    ]]);

    $response = $this->post(route('game.playAgain', $game->code));

    $response->assertRedirect();
    $game->refresh();
    $this->assertEquals(array_fill(0, 9, ''), $game->board);
    $this->assertEquals('X', $game->current_turn);
    $this->assertEquals('playing', $game->status);
    $this->assertNull($game->winner);
});

test('forfeit ends game', function () {
    $game = Game::factory()->create([
        'status' => 'playing',
        'mode' => 'online',
    ]);

    session(['game_player' => [
        'game_id' => $game->id,
        'symbol' => 'X',
        'name' => 'Player',
    ]]);

    $response = $this->post(route('game.forfeit', $game->code));

    $response->assertRedirect(route('home'));
    $game->refresh();
    $this->assertEquals('finished', $game->status);
    $this->assertEquals('O', $game->winner);
    $this->assertEquals(1, $game->player_o_score);
});

test('guest can send chat message', function () {
    $game = Game::factory()->create([
        'mode' => 'online',
        'status' => 'playing',
    ]);

    session(['game_player' => [
        'game_id' => $game->id,
        'symbol' => 'X',
        'name' => 'Player',
    ]]);

    $response = $this->post(route('game.chat', $game->code), [
        'content' => 'Hello!',
    ]);

    $response->assertRedirect();
    $this->assertDatabaseHas('game_messages', [
        'game_id' => $game->id,
        'player_name' => 'Player',
        'player_symbol' => 'X',
        'content' => 'Hello!',
    ]);
});

test('cannot make move without session', function () {
    $game = Game::factory()->create([
        'status' => 'playing',
    ]);

    $response = $this->post(route('game.move', $game->code), [
        'position' => 0,
    ]);

    $response->assertStatus(403);
});

