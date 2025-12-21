<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Game extends Model
{
    use HasFactory;

    protected $fillable = [
        'code',
        'board',
        'mode',
        'timer_setting',
        'player_x_id',
        'player_x_name',
        'player_x_score',
        'player_o_id',
        'player_o_name',
        'player_o_score',
        'current_turn',
        'status',
        'winner',
        'turn_started_at',
    ];

    protected function casts(): array
    {
        return [
            'board' => 'array',
            'turn_started_at' => 'datetime',
        ];
    }

    public function playerX(): BelongsTo
    {
        return $this->belongsTo(User::class, 'player_x_id');
    }

    public function playerO(): BelongsTo
    {
        return $this->belongsTo(User::class, 'player_o_id');
    }

    public function messages(): HasMany
    {
        return $this->hasMany(GameMessage::class);
    }

    public static function generateCode(): string
    {
        do {
            $code = strtoupper(substr(str_shuffle('ABCDEFGHIJKLMNOPQRSTUVWXYZ'), 0, 6));
        } while (static::where('code', $code)->exists());

        return $code;
    }
}
