<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('games', function (Blueprint $table) {
            $table->id();
            $table->string('code', 6)->unique();
            $table->json('board');
            $table->enum('mode', ['bot', 'online']);
            $table->enum('timer_setting', ['off', '3', '10', '30'])->default('off');

            // Player X (creator/host)
            $table->foreignId('player_x_id')->nullable()->constrained('users')->nullOnDelete();
            $table->string('player_x_name');
            $table->unsignedInteger('player_x_score')->default(0);

            // Player O (joiner or bot)
            $table->foreignId('player_o_id')->nullable()->constrained('users')->nullOnDelete();
            $table->string('player_o_name')->nullable();
            $table->unsignedInteger('player_o_score')->default(0);

            $table->enum('current_turn', ['X', 'O'])->default('X');
            $table->enum('status', ['waiting', 'playing', 'finished'])->default('waiting');
            $table->enum('winner', ['X', 'O', 'draw'])->nullable();
            $table->timestamp('turn_started_at')->nullable();

            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('games');
    }
};
