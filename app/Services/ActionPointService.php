<?php

declare(strict_types=1);

namespace App\Services;

use App\Models\ApAllocation;
use App\Models\BudgetCycle;
use App\Models\QuestPool;
use App\Models\User;
use Illuminate\Support\Facades\DB;

class ActionPointService
{
    /**
     * Evaluate surplus for a completed or active budget cycle and convert to Action Points (AP).
     */
    public function evaluateCycleSurplus(BudgetCycle $budgetCycle): int
    {
        return DB::transaction(function () use ($budgetCycle) {
            $user = $budgetCycle->user;

            $surplus = max(0.00, (float) ($budgetCycle->planned_budget - $budgetCycle->spent_amount));
            // Conversion rate: 1 AP = Rp 10.000 surplus
            $convertedAp = (int) floor($surplus / 10000);

            $budgetCycle->surplus_amount = $surplus;
            $budgetCycle->surplus_converted_ap = $convertedAp;
            $budgetCycle->is_evaluated = true;
            $budgetCycle->save();

            if ($convertedAp > 0 && $user) {
                $user->action_points_balance += $convertedAp;
                $user->save();
            }

            return $convertedAp;
        });
    }

    /**
     * Allocate Action Points to a Quest Pool.
     *
     * @throws \InvalidArgumentException
     */
    public function allocateApToQuestPool(
        User $user,
        QuestPool $questPool,
        int $apAmount,
        ?BudgetCycle $budgetCycle = null,
        ?string $notes = null
    ): ApAllocation {
        if ($apAmount <= 0) {
            throw new \InvalidArgumentException('Allocation AP must be greater than zero.');
        }

        if ($user->action_points_balance < $apAmount) {
            throw new \InvalidArgumentException("Insufficient Action Points. Balance: {$user->action_points_balance} AP, Required: {$apAmount} AP.");
        }

        $budgetCycle ??= $user->getActiveBudgetCycle();
        if (! $budgetCycle) {
            throw new \RuntimeException('No active budget cycle found.');
        }

        return DB::transaction(function () use ($user, $questPool, $apAmount, $budgetCycle, $notes) {
            // Conversion: 1 AP = Rp 10.000 real-world fund allocation value
            $financialValue = $apAmount * 10000.00;

            // Deduct user AP
            $user->action_points_balance -= $apAmount;
            $user->save();

            // Update Quest Pool
            $questPool->allocated_ap += $apAmount;
            $questPool->current_amount += $financialValue;
            $questPool->save();

            // Record AP Allocation
            return ApAllocation::create([
                'user_id' => $user->id,
                'quest_pool_id' => $questPool->id,
                'budget_cycle_id' => $budgetCycle->id,
                'ap_spent' => $apAmount,
                'converted_amount' => $financialValue,
                'notes' => $notes ?? "Allocated {$apAmount} AP to {$questPool->name}",
            ]);
        });
    }
}
