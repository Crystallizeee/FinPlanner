<?php

declare(strict_types=1);

namespace App\Livewire;

use App\Models\User;
use App\Services\ThemeService;
use Livewire\Attributes\Layout;
use Livewire\Component;

#[Layout('components.layouts.app')]
class FinancialHealthComponent extends Component
{
    public ?User $user = null;

    // Interactive Stress Test Simulation Params
    public int $simulatedMonths = 3;
    public float $simulatedEmergencyCost = 0;

    public function mount(): void
    {
        $this->user = \Illuminate\Support\Facades\Auth::user() ?? User::first();
    }

    public function render(ThemeService $themeService)
    {
        $themeMode = $themeService->getActiveMode($this->user);
        $labels = $themeService->getLabels($themeMode);

        $accounts = $this->user ? $this->user->accounts()->get() : collect();
        $transactions = $this->user ? $this->user->expenseTransactions()->get() : collect();
        $debts = $this->user ? $this->user->debts()->get() : collect();
        $categoryBudgets = $this->user ? $this->user->categoryBudgets()->get() : collect();

        $liquidBalance = (float) $accounts->sum('balance');
        $totalDebt = (float) $debts->sum('remaining_amount');
        $monthlyDebtPayment = (float) $debts->sum('minimum_monthly_payment');

        // Estimate monthly expenses (last 30 days spent)
        $monthlyExpenses = (float) $transactions->where('transaction_date', '>=', now()->subDays(30))->sum('amount');
        if ($monthlyExpenses <= 0) {
            $monthlyExpenses = 3000000; // default baseline for calculation if no history
        }

        // 1. Emergency Fund Months Covered
        $monthsCovered = $monthlyExpenses > 0 ? round($liquidBalance / $monthlyExpenses, 1) : 0;
        $emergencyFundScore = (int) min(30, round(($monthsCovered / 6) * 30));

        // 2. Savings Rate Score
        $questPools = $this->user ? $this->user->questPools()->get() : collect();
        $totalSavings = (float) $questPools->sum('current_amount');
        $savingsScore = (int) min(30, round(($totalSavings / (max(1, $monthlyExpenses * 2))) * 30));

        // 3. Debt Ratio Score (Debt Service Ratio)
        $estimatedIncome = $monthlyExpenses * 1.5;
        $debtRatioPct = $estimatedIncome > 0 ? ($monthlyDebtPayment / $estimatedIncome) * 100 : 0;
        $debtScore = $debtRatioPct <= 30 ? 20 : max(0, (int) round(20 - ($debtRatioPct - 30)));

        // 4. Budget Compliance Score
        $exceededBudgetsCount = 0;
        foreach ($categoryBudgets as $cb) {
            $spent = (float) $transactions->where('category', $cb->category)->sum('amount');
            if ($spent > $cb->amount_limit) {
                $exceededBudgetsCount++;
            }
        }
        $budgetScore = count($categoryBudgets) > 0 
            ? max(0, 20 - ($exceededBudgetsCount * 10)) 
            : 20;

        $healthScore = min(100, $emergencyFundScore + $savingsScore + $debtScore + $budgetScore);

        // Health Category Grade
        $grade = 'A+ (Optimal)';
        if ($healthScore < 50) {
            $grade = 'C (Kritis / Perlu Perbaikan)';
        } elseif ($healthScore < 75) {
            $grade = 'B (Cukup Sehat)';
        } elseif ($healthScore < 90) {
            $grade = 'A (Sangat Sehat)';
        }

        // Stress test simulation output
        $simulatedTotalCost = ($monthlyExpenses * $this->simulatedMonths) + $this->simulatedEmergencyCost;
        $stressTestSurplus = $liquidBalance - $simulatedTotalCost;
        $isStressTestPassed = $stressTestSurplus >= 0;

        return view('livewire.financial-health-component', [
            'liquidBalance' => $liquidBalance,
            'totalDebt' => $totalDebt,
            'monthlyExpenses' => $monthlyExpenses,
            'monthsCovered' => $monthsCovered,
            'healthScore' => $healthScore,
            'grade' => $grade,
            'emergencyFundScore' => $emergencyFundScore,
            'savingsScore' => $savingsScore,
            'debtScore' => $debtScore,
            'budgetScore' => $budgetScore,
            'simulatedTotalCost' => $simulatedTotalCost,
            'stressTestSurplus' => $stressTestSurplus,
            'isStressTestPassed' => $isStressTestPassed,
            'themeMode' => $themeMode,
            'labels' => $labels,
        ]);
    }
}
