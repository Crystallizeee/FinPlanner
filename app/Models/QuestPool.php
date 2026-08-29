<?php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class QuestPool extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'account_id',
        'name',
        'slug',
        'category',
        'target_amount',
        'allocated_ap',
        'current_amount',
        'icon',
    ];

    protected function casts(): array
    {
        return [
            'target_amount' => 'decimal:2',
            'allocated_ap' => 'integer',
            'current_amount' => 'decimal:2',
        ];
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function account(): BelongsTo
    {
        return $this->belongsTo(Account::class);
    }

    public function apAllocations(): HasMany
    {
        return $this->hasMany(ApAllocation::class);
    }

    /**
     * Get completion percentage of this quest pool.
     */
    public function getProgressPercentage(): int
    {
        if ($this->target_amount <= 0) {
            return 100;
        }

        $ratio = ($this->current_amount / $this->target_amount) * 100;

        return (int) min(100, round($ratio));
    }

    /**
     * Get display emoji icon, mapping legacy icon strings if present.
     */
    public function getDisplayIconAttribute(): string
    {
        $map = [
            'shield-check' => '🛡️',
            'shield' => '🛡️',
            'wrench' => '🏍️',
            'chart-bar' => '📈',
            'trending-up' => '📈',
            'banknotes' => '💵',
            'car' => '🚗',
            'home' => '🏡',
            'laptop' => '💻',
            'plane' => '✈️',
        ];

        return $map[$this->icon] ?? ($this->icon ?: '🎯');
    }
}
