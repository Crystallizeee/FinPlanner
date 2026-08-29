<?php

declare(strict_types=1);

namespace App\Services;

use App\Models\BudgetCycle;
use App\Models\User;
use Illuminate\Support\Facades\DB;

class HpBudgetingService
{
    /**
     * Recalculates the HP level and spent total for a given budget cycle inside a database transaction.
     */
    public function recalculateCycleHp(BudgetCycle $budgetCycle): BudgetCycle
    {
        return DB::transaction(function () use ($budgetCycle) {
            $totalSpent = $budgetCycle->expenseTransactions()
                ->where('is_verified', true)
                ->sum('amount');

            $budgetCycle->spent_amount = (float) $totalSpent;
            $hpPercentage = $budgetCycle->calculateHpPercentage();

            $budgetCycle->hp_level = $hpPercentage;

            if ($hpPercentage < 20) {
                $budgetCycle->status = 'critical';
            } elseif ($budgetCycle->status === 'critical' && $hpPercentage >= 20) {
                $budgetCycle->status = 'active';
            }

            $budgetCycle->save();

            // Sync user's global HP level
            $user = $budgetCycle->user;
            if ($user) {
                $user->hp_current = $hpPercentage;
                $user->save();
            }

            return $budgetCycle;
        });
    }

    /**
     * Check if user is in critical HP mode (< 20%).
     */
    public function isCriticalHpMode(User $user): bool
    {
        return $user->hp_current < 20;
    }
}
