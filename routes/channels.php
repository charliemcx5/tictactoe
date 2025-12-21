<?php

use Illuminate\Support\Facades\Broadcast;

Broadcast::channel('App.Models.User.{id}', function ($user, $id) {
    return (int) $user->id === (int) $id;
});

// Allow public access to game channels (code acts as "password")
Broadcast::channel('game.{code}', function ($user, string $code) {
    // Anyone can listen to public game channels
    // The game code itself acts as authorization
    return true;
});
