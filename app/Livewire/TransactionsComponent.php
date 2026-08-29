<?php

declare(strict_types=1);

namespace App\Livewire;

use App\Models\ExpenseTransaction;
use App\Models\User;
use App\Services\ThemeService;
use Livewire\Attributes\Layout;
use Livewire\Attributes\On;
use Livewire\Component;

#[Layout('components.layouts.app')]
class TransactionsComponent extends Component
{
    public string $search = '';
    public string $filterType = 'all'; // all, income, expense
    public string $filterCategory = 'all';

    #[On('transactionRecorded')]
    public function refreshTransactions(): void
    {
        // Triggers component re-render on new transaction
    }

    public function render(ThemeService $themeService)
    {
        $user = \Illuminate\Support\Facades\Auth::user() ?? User::first();
        $themeMode = $themeService->getActiveMode($user);
        $labels = $themeService->getLabels($themeMode);

        $query = ExpenseTransaction::where('user_id', $user?->id);

        if (! empty($this->search)) {
            $query->where('merchant', 'like', '%' . $this->search . '%');
        }

        if ($this->filterType === 'income') {
            // Filter for income transactions if any exist, or return empty if ExpenseTransaction is expenses only
            $query->where('id', '<', 0);
        }

        $transactions = $query->latest('transaction_date')->get();

        return view('livewire.transactions-component', [
            'transactions' => $transactions,
            'themeMode' => $themeMode,
            'labels' => $labels,
        ]);
    }
}
