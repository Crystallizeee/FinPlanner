<?php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class BudgetCycle extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'name',
        'period_type',
        'start_date',
        'end_date',
        'planned_budget',
        'spent_amount',
        'hp_level',
        'status',
        'surplus_amount',
        'surplus_converted_ap',
        'is_evaluated',
    ];

    protected function casts(): array
    {
        return [
            'start_date' => 'date',
            'end_date' => 'date',
            'planned_budget' => 'decimal:2',
            'spent_amount' => 'decimal:2',
            'hp_level' => 'integer',
            'surplus_amount' => 'decimal:2',
            'surplus_converted_ap' => 'integer',
            'is_evaluated' => 'boolean',
        ];
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function expenseTransactions(): HasMany
    {
        return $this->hasMany(ExpenseTransaction::class);
    }

    public function apAllocations(): HasMany
    {
        return $this->hasMany(ApAllocation::class);
    }

    /**
     * Calculate current HP percentage based on expenses vs planned budget.
     * HP = 100% when spent = 0, decreases to 0% when spent >= planned_budget.
     */
    public function calculateHpPercentage(): int
    {
        if ($this->planned_budget <= 0) {
            return 100;
        }

        $remainingRatio = ($this->planned_budget - $this->spent_amount) / $this->planned_budget;
        $hp = (int) round($remainingRatio * 100);

        return max(0, min(100, $hp));
    }
}
