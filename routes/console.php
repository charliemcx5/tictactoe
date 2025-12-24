<?php

use App\Models\Game;
use Illuminate\Foundation\Inspiring;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Schedule;

Artisan::command('inspire', function () {
    $this->comment(Inspiring::quote());
})->purpose('Display an inspiring quote');

Artisan::command('games:cleanup', function () {
    $deleted = Game::where('created_at', '<', now()->subMinutes(60))->delete();
    $this->info("Deleted {$deleted} old games.");
})->purpose('Delete games older than 60 minutes');

// Schedule the cleanup to run every minute
Schedule::command('games:cleanup')->everyMinute();
