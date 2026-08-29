<?php

namespace Tests\Feature;

use App\Models\BudgetCycle;
use App\Models\QuestPool;
use App\Models\User;
use App\Services\ActionPointService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ActionPointsAllocationTest extends TestCase
{
    use RefreshDatabase;

    public function test_surplus_budget_converts_to_action_points_and_allocates_to_quest_pool(): void
    {
        $user = User::create([
            'name' => 'AP User',
            'email' => 'ap@example.com',
            'password' => bcrypt('password'),
            'action_points_balance' => 0,
        ]);

        $budgetCycle = BudgetCycle::create([
            'user_id' => $user->id,
            'name' => 'Ended Cycle',
            'period_type' => 'monthly',
            'start_date' => now()->subMonth(),
            'end_date' => now()->subDay(),
            'planned_budget' => 5000000.00,
            'spent_amount' => 3500000.00, // Surplus = 1.500.000 (150 AP)
            'status' => 'completed',
        ]);

        $questPool = QuestPool::create([
            'user_id' => $user->id,
            'name' => 'IDX Stock Averaging Down',
            'slug' => 'idx-stock-averaging-down',
            'category' => 'investment',
            'target_amount' => 10000000.00,
            'current_amount' => 0.00,
            'allocated_ap' => 0,
        ]);

        /** @var ActionPointService $apService */
        $apService = app(ActionPointService::class);
        $earnedAp = $apService->evaluateCycleSurplus($budgetCycle);

        $user->refresh();
        $this->assertEquals(150, $earnedAp);
        $this->assertEquals(150, $user->action_points_balance);

        $allocation = $apService->allocateApToQuestPool($user, $questPool, 50, $budgetCycle);

        $this->assertNotNull($allocation);
        $user->refresh();
        $questPool->refresh();

        $this->assertEquals(100, $user->action_points_balance);
        $this->assertEquals(50, $questPool->allocated_ap);
        $this->assertEquals(500000.00, $questPool->current_amount);
    }
}
