<?php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ApAllocation extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'quest_pool_id',
        'budget_cycle_id',
        'ap_spent',
        'converted_amount',
        'notes',
    ];

    protected function casts(): array
    {
        return [
            'ap_spent' => 'integer',
            'converted_amount' => 'decimal:2',
        ];
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function questPool(): BelongsTo
    {
        return $this->belongsTo(QuestPool::class);
    }

    public function budgetCycle(): BelongsTo
    {
        return $this->belongsTo(BudgetCycle::class);
    }
}
