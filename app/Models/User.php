<?php

declare(strict_types=1);

namespace App\Models;

use Database\Factories\UserFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;

class User extends Authenticatable
{
    /** @use HasFactory<UserFactory> */
    use HasFactory, Notifiable;

    /**
     * The attributes that are mass assignable.
     *
     * @var list<string>
     */
    protected $fillable = [
        'name',
        'email',
        'password',
        'hp_current',
        'action_points_balance',
        'current_streak',
        'longest_streak',
        'is_penalized',
        'theme_mode',
        'last_activity_at',
    ];

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var list<string>
     */
    protected $hidden = [
        'password',
        'remember_token',
    ];

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
            'hp_current' => 'integer',
            'action_points_balance' => 'integer',
            'current_streak' => 'integer',
            'longest_streak' => 'integer',
            'is_penalized' => 'boolean',
            'last_activity_at' => 'datetime',
        ];
    }

    /**
     * User's budget cycles.
     */
    public function budgetCycles(): HasMany
    {
        return $this->hasMany(BudgetCycle::class);
    }

    /**
     * Get the current active budget cycle.
     * Includes 'critical' status cycles — these are still active, just low on HP.
     */
    public function getActiveBudgetCycle(): ?BudgetCycle
    {
        return $this->budgetCycles()
            ->whereIn('status', ['active', 'critical'])
            ->latest('id')
            ->first();
    }

    /**
     * User's uploaded receipts.
     */
    public function receipts(): HasMany
    {
        return $this->hasMany(Receipt::class);
    }

    /**
     * User's financial accounts / wallets.
     */
    public function accounts(): HasMany
    {
        return $this->hasMany(Account::class);
    }

    /**
     * User's expense transactions.
     */
    public function expenseTransactions(): HasMany
    {
        return $this->hasMany(ExpenseTransaction::class);
    }

    /**
     * User's quest pools (e.g. IDX stock averaging down, bike maintenance).
     */
    public function questPools(): HasMany
    {
        return $this->hasMany(QuestPool::class);
    }

    /**
     * User's action point allocations.
     */
    public function apAllocations(): HasMany
    {
        return $this->hasMany(ApAllocation::class);
    }

    /**
     * User's category budgets.
     */
    public function categoryBudgets(): HasMany
    {
        return $this->hasMany(CategoryBudget::class);
    }

    /**
     * User's recurring expenses/subscriptions.
     */
    public function recurringExpenses(): HasMany
    {
        return $this->hasMany(RecurringExpense::class);
    }

    /**
     * User's investment assets.
     */
    public function investmentAssets(): HasMany
    {
        return $this->hasMany(InvestmentAsset::class);
    }

    /**
     * Check if user is currently in critical HP status (< 20%).
     */
    public function hasCriticalHp(): bool
    {
        return $this->hp_current < 20;
    }
}
