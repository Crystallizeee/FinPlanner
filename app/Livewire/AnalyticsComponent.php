<?php

declare(strict_types=1);

namespace App\Livewire;

use App\Models\ExpenseTransaction;
use App\Models\User;
use Livewire\Attributes\Layout;
use Livewire\Component;
use Symfony\Component\HttpFoundation\StreamedResponse;

#[Layout('components.layouts.app')]
class AnalyticsComponent extends Component
{
    public ?User $user = null;

    public function mount(): void
    {
        $this->user = \Illuminate\Support\Facades\Auth::user() ?? User::first();
    }

    public function exportCsv(): StreamedResponse
    {
        /** @var User $user */
        $user = \Illuminate\Support\Facades\Auth::user() ?? User::first();
        $transactions = $user ? $user->expenseTransactions()->latest('transaction_date')->get() : collect();

        $headers = [
            'Content-Type' => 'text/csv',
            'Content-Disposition' => 'attachment; filename="financial_report_' . date('Y-m-d') . '.csv"',
        ];

        $callback = function () use ($transactions) {
            $file = fopen('php://output', 'w');
            fputcsv($file, ['ID', 'Date', 'Merchant', 'Category', 'Amount (Rp)', 'Source', 'Description']);

            foreach ($transactions as $tx) {
                fputcsv($file, [
                    $tx->id,
                    $tx->transaction_date ? $tx->transaction_date->format('Y-m-d H:i:s') : '',
                    $tx->merchant,
                    $tx->category,
                    $tx->amount,
                    $tx->source,
                    $tx->description,
                ]);
            }

            fclose($file);
        };

        return response()->stream($callback, 200, $headers);
    }

    public function render(\App\Services\ThemeService $themeService)
    {
        $themeMode = $themeService->getActiveMode($this->user);
        $labels = $themeService->getLabels($themeMode);

        if ($this->user && $this->user->is_penalized) {
            return view('discipline-locked', [
                'user' => $this->user,
                'reason' => 'Streak Penalized: Advanced Analytics are locked until you upload today\'s receipt or sync transactions!',
                'themeMode' => $themeMode,
                'labels' => $labels,
            ]);
        }

        $transactions = $this->user ? $this->user->expenseTransactions()->with('receipt')->latest('transaction_date')->get() : collect();

        $totalSpent = (float) $transactions->sum('amount');
        $receiptCount = $transactions->where('source', 'receipt_ocr')->count();
        $bankSyncCount = $transactions->where('source', 'bank_webhook')->count();

        $categoryBreakdown = $transactions->groupBy('category')->map(function ($group) use ($totalSpent) {
            $sum = (float) $group->sum('amount');
            $pct = $totalSpent > 0 ? (int) round(($sum / $totalSpent) * 100) : 0;
            return [
                'sum' => $sum,
                'pct' => $pct,
                'count' => $group->count(),
            ];
        });

        // Monthly trends (Last 6 months)
        $monthlyTrends = [];
        for ($i = 5; $i >= 0; $i--) {
            $monthDate = now()->subMonths($i);
            $monthLabel = $monthDate->format('M Y');
            $monthSpent = (float) $transactions->filter(function ($tx) use ($monthDate) {
                return $tx->transaction_date && $tx->transaction_date->format('Y-m') === $monthDate->format('Y-m');
            })->sum('amount');

            $monthlyTrends[] = [
                'month' => $monthLabel,
                'spent' => $monthSpent,
            ];
        }

        return view('livewire.analytics-component', [
            'transactions' => $transactions,
            'totalSpent' => $totalSpent,
            'receiptCount' => $receiptCount,
            'bankSyncCount' => $bankSyncCount,
            'categoryBreakdown' => $categoryBreakdown,
            'monthlyTrends' => $monthlyTrends,
            'themeMode' => $themeMode,
            'labels' => $labels,
        ]);
    }
}
