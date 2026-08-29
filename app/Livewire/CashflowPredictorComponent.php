<?php

declare(strict_types=1);

namespace App\Livewire;

use App\Models\User;
use App\Services\ThemeService;
use Livewire\Attributes\Layout;
use Livewire\Component;

#[Layout('components.layouts.app')]
class CashflowPredictorComponent extends Component
{
    public ?User $user = null;

    // What-if simulator spending adjustment (+/- percentage)
    public int $spendingAdjustmentPct = 0;

    public function mount(): void
    {
        $this->user = \Illuminate\Support\Facades\Auth::user() ?? User::first();
    }

    public function render(ThemeService $themeService)
    {
        $themeMode = $themeService->getActiveMode($this->user);
        $labels = $themeService->getLabels($themeMode);

        $liquidBalance = $this->user ? (float) $this->user->accounts()->sum('balance') : 0;

        // Calculate historical burn rate over last 30 days
        $thirtyDaysAgo = now()->subDays(30);
        $totalSpent30Days = $this->user ? (float) $this->user->expenseTransactions()
            ->where('transaction_date', '>=', $thirtyDaysAgo)
            ->sum('amount') : 0;

        $dailyBurnRate = $totalSpent30Days > 0 ? ($totalSpent30Days / 30) : 50000;

        // Days remaining in current month
        $daysInMonth = (int) now()->daysInMonth;
        $currentDay = (int) now()->format('j');
        $daysRemaining = max(1, $daysInMonth - $currentDay);

        // Upcoming unpaid bills this month
        $todayDay = $currentDay;
        $upcomingBills = $this->user ? (float) $this->user->recurringExpenses()
            ->where('is_active', true)
            ->where('due_day', '>', $todayDay)
            ->where(function ($q) {
                $q->whereNull('last_paid_at')
                  ->orWhere('last_paid_at', '<', now()->startOfMonth());
            })
            ->sum('amount') : 0;

        // Apply What-If slider factor
        $adjustedBurnRate = $dailyBurnRate * (1 + ($this->spendingAdjustmentPct / 100));

        // Total Projected Expenses till end of month
        $projectedDailyExpenses = $adjustedBurnRate * $daysRemaining;
        $projectedTotalOutflow = $projectedDailyExpenses + $upcomingBills;

        // Projected End of Month Saldo
        $projectedEndBalance = $liquidBalance - $projectedTotalOutflow;

        // Risk Level Status
        if ($projectedEndBalance < 0) {
            $riskStatus = 'CRITICAL DEFICIT';
            $riskBadgeColor = 'rose';
        } elseif ($projectedEndBalance < ($liquidBalance * 0.2)) {
            $riskStatus = 'LOW RESERVE WARNING';
            $riskBadgeColor = 'amber';
        } else {
            $riskStatus = 'HEALTHY SURPLUS';
            $riskBadgeColor = 'emerald';
        }

        return view('livewire.cashflow-predictor-component', [
            'liquidBalance' => $liquidBalance,
            'dailyBurnRate' => $dailyBurnRate,
            'adjustedBurnRate' => $adjustedBurnRate,
            'daysRemaining' => $daysRemaining,
            'upcomingBills' => $upcomingBills,
            'projectedDailyExpenses' => $projectedDailyExpenses,
            'projectedTotalOutflow' => $projectedTotalOutflow,
            'projectedEndBalance' => $projectedEndBalance,
            'riskStatus' => $riskStatus,
            'riskBadgeColor' => $riskBadgeColor,
            'themeMode' => $themeMode,
            'labels' => $labels,
        ]);
    }
}
