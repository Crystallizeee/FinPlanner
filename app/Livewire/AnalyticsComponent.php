<?php

declare(strict_types=1);

namespace App\Livewire;

use App\Models\ExpenseTransaction;
use App\Models\User;
use Livewire\Attributes\Layout;
use Livewire\Component;

#[Layout('components.layouts.app')]
class AnalyticsComponent extends Component
{
    public ?User $user = null;

    public function mount(): void
    {
        $this->user = \Illuminate\Support\Facades\Auth::user() ?? User::first();
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

        $totalSpent = $transactions->sum('amount');
        $receiptCount = $transactions->where('source', 'receipt_ocr')->count();
        $bankSyncCount = $transactions->where('source', 'bank_webhook')->count();

        return view('livewire.analytics-component', [
            'transactions' => $transactions,
            'totalSpent' => $totalSpent,
            'receiptCount' => $receiptCount,
            'bankSyncCount' => $bankSyncCount,
            'themeMode' => $themeMode,
            'labels' => $labels,
        ]);
    }
}
