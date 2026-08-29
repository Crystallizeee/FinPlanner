<?php

declare(strict_types=1);

namespace App\Livewire;

use App\Models\BudgetCycle;
use App\Models\QuestPool;
use App\Models\User;
use App\Services\ActionPointService;
use Livewire\Attributes\Layout;
use Livewire\Component;

#[Layout('components.layouts.app')]
class ActionPointsAllocator extends Component
{
    public ?User $user = null;

    public ?BudgetCycle $activeCycle = null;

    /** @var array<int, int> */
    public array $apAllocations = [];

    public ?string $feedbackMessage = null;

    public ?string $errorMessage = null;

    public function mount(): void
    {
        $this->loadData();
    }

    public function loadData(): void
    {
        $this->user = \Illuminate\Support\Facades\Auth::user() ?? User::first();
        if ($this->user) {
            $this->activeCycle = $this->user->getActiveBudgetCycle();
            $questPools = $this->user->questPools;
            foreach ($questPools as $pool) {
                $this->apAllocations[$pool->id] = 0;
            }
        }
    }

    /**
     * Trigger end-of-cycle surplus evaluation to convert unspent budget into AP.
     */
    public function evaluateCycleSurplus(ActionPointService $apService): void
    {
        if (! $this->activeCycle) {
            $this->errorMessage = 'No active budget cycle found.';
            return;
        }

        $convertedAp = $apService->evaluateCycleSurplus($this->activeCycle);

        if ($convertedAp > 0) {
            $this->feedbackMessage = "Cycle surplus evaluated! Converted Rp " . number_format((float)$this->activeCycle->surplus_amount, 0, ',', '.') . " unspent budget into +{$convertedAp} Action Points!";
        } else {
            $this->errorMessage = 'No unspent budget surplus available in current cycle.';
        }

        $this->loadData();
    }

    /**
     * Submit allocations of Action Points from client to quest pools.
     *
     * @param array<int, int> $allocations
     */
    public function submitAllocations(array $allocations, ActionPointService $apService): void
    {
        $this->feedbackMessage = null;
        $this->errorMessage = null;

        $totalApToSpend = array_sum($allocations);

        if ($totalApToSpend <= 0) {
            $this->errorMessage = 'Please allocate at least 1 Action Point before submitting.';
            return;
        }

        if ($this->user->action_points_balance < $totalApToSpend) {
            $this->errorMessage = "Insufficient AP balance! You have {$this->user->action_points_balance} AP, but tried to allocate {$totalApToSpend} AP.";
            return;
        }

        try {
            $allocatedCount = 0;
            foreach ($allocations as $poolId => $apAmount) {
                $apAmount = (int) $apAmount;
                if ($apAmount > 0) {
                    $pool = QuestPool::where('user_id', $this->user->id)->find($poolId);
                    if ($pool) {
                        $apService->allocateApToQuestPool($this->user, $pool, $apAmount, $this->activeCycle);
                        $allocatedCount += $apAmount;
                    }
                }
            }

            $this->feedbackMessage = "Success! Allocated {$allocatedCount} Action Points across your real-world financial quest pools.";
            $this->loadData();
        } catch (\Throwable $e) {
            $this->errorMessage = "Allocation error: {$e->getMessage()}";
        }
    }

    public function render(\App\Services\ThemeService $themeService)
    {
        $questPools = $this->user ? $this->user->questPools()->get() : collect();
        $themeMode = $themeService->getActiveMode($this->user);
        $labels = $themeService->getLabels($themeMode);

        return view('livewire.action-points-allocator', [
            'questPools' => $questPools,
            'themeMode' => $themeMode,
            'labels' => $labels,
        ]);
    }
}
