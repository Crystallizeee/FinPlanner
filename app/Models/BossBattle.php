<?php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class BossBattle extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'boss_name',
        'max_hp',
        'current_hp',
        'reward_xp',
        'status',
        'month_year',
    ];

    protected function casts(): array
    {
        return [
            'max_hp' => 'integer',
            'current_hp' => 'integer',
            'reward_xp' => 'integer',
        ];
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function damage(int $amount): void
    {
        $newHp = max(0, $this->current_hp - $amount);
        $this->current_hp = $newHp;

        if ($newHp <= 0) {
            $this->status = 'defeated';
        }

        $this->save();
    }

    public function getHpPercentage(): int
    {
        if ($this->max_hp <= 0) {
            return 0;
        }

        return (int) round(($this->current_hp / $this->max_hp) * 100);
    }
}
