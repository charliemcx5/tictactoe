<?php

use App\Http\Controllers\ChatController;
use App\Http\Controllers\GameController;
use Illuminate\Support\Facades\Route;
use Inertia\Inertia;

Route::get('/', function () {
    return Inertia::render('Welcome');
})->name('home');

// Game routes
Route::post('/game', [GameController::class, 'create'])->name('game.create');
Route::get('/game/{code}', [GameController::class, 'show'])->name('game.show');
Route::post('/game/{code}/join', [GameController::class, 'join'])->name('game.join');
Route::post('/game/{code}/move', [GameController::class, 'move'])->name('game.move');
Route::post('/game/{code}/request-rematch', [GameController::class, 'requestRematch'])->name('game.requestRematch');
Route::post('/game/{code}/accept-rematch', [GameController::class, 'acceptRematch'])->name('game.acceptRematch');
Route::post('/game/{code}/forfeit', [GameController::class, 'forfeit'])->name('game.forfeit');
Route::post('/game/{code}/timer', [GameController::class, 'updateTimer'])->name('game.updateTimer');
Route::post('/game/{code}/leave', [GameController::class, 'leave'])->name('game.leave');
Route::post('/game/{code}/chat', [ChatController::class, 'store'])->name('game.chat');

require __DIR__.'/settings.php';
