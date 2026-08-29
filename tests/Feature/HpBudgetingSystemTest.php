<?php

namespace Tests\Feature;

use App\Models\BudgetCycle;
use App\Models\ExpenseTransaction;
use App\Models\User;
use App\Services\HpBudgetingService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class HpBudgetingSystemTest extends TestCase
{
    use RefreshDatabase;

    public function test_hp_drops_below_20_percent_when_expenses_exceed_planned_threshold(): void
    {
        $user = User::create([
            'name' => 'HP Test User',
            'email' => 'hp@example.com',
            'password' => bcrypt('password'),
            'hp_current' => 100,
        ]);

        $budgetCycle = BudgetCycle::create([
            'user_id' => $user->id,
            'name' => 'Test Cycle HP',
            'period_type' => 'monthly',
            'start_date' => now()->startOfMonth(),
            'end_date' => now()->endOfMonth(),
            'planned_budget' => 1000000.00,
            'spent_amount' => 0.00,
            'hp_level' => 100,
        ]);

        // Spend Rp 850.000 out of Rp 1.000.000 (Remaining = 15% HP)
        ExpenseTransaction::create([
            'user_id' => $user->id,
            'budget_cycle_id' => $budgetCycle->id,
            'source' => 'receipt_ocr',
            'merchant' => 'Super Indo',
            'amount' => 850000.00,
            'transaction_date' => now(),
            'is_verified' => true,
        ]);

        /** @var HpBudgetingService $hpService */
        $hpService = app(HpBudgetingService::class);
        $hpService->recalculateCycleHp($budgetCycle);

        $user->refresh();
        $budgetCycle->refresh();

        $this->assertEquals(15, $user->hp_current);
        $this->assertEquals('critical', $budgetCycle->status);
        $this->assertTrue($user->hasCriticalHp());
    }
}
