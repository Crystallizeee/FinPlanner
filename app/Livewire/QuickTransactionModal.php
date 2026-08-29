<?php

declare(strict_types=1);

namespace App\Livewire;

use App\Models\ExpenseTransaction;
use App\Models\User;
use App\Services\ThemeService;
use Livewire\Attributes\On;
use Livewire\Component;

class QuickTransactionModal extends Component
{
    public bool $openAddModal = false;
    public string $type = 'expense'; // expense, income, transfer
    public string $amount = '';
    public string $merchant = '';
    public string $category = 'Makanan & Minuman';
    public string $account = 'Bank BCA Gaji';
    public string $sourceAccount = 'Bank BCA Gaji';
    public string $targetAccount = 'GoPay / GoTo';
    public string $note = '';
    public ?string $successMessage = null;

    protected $listeners = [
        'openQuickTransactionModal' => 'open',
    ];

    public function open(): void
    {
        $this->reset(['amount', 'merchant', 'note', 'successMessage']);
        $this->type = 'expense';
        $this->openAddModal = true;
    }

    public function close(): void
    {
        $this->openAddModal = false;
    }

    public function saveTransaction(): void
    {
        if ($this->type === 'transfer') {
            $this->validate([
                'amount' => 'required|numeric|min:100',
                'sourceAccount' => 'required|string',
                'targetAccount' => 'required|string|different:sourceAccount',
            ], [
                'targetAccount.different' => 'Akun tujuan harus berbeda dari akun asal!',
            ]);

            $this->merchant = "Transfer: {$this->sourceAccount} ➔ {$this->targetAccount}";
            $this->category = 'Pindah Saldo';
            $this->account = $this->sourceAccount;
        } else {
            $this->validate([
                'amount' => 'required|numeric|min:100',
                'merchant' => 'required|string|min:2|max:100',
            ]);
        }

        $user = \Illuminate\Support\Facades\Auth::user() ?? User::first();

        if ($user) {
            $cycle = \App\Models\BudgetCycle::where('user_id', $user->id)->first() ?? \App\Models\BudgetCycle::create([
                'user_id' => $user->id,
                'name' => 'Current Cycle',
                'period_type' => 'monthly',
                'start_date' => now()->startOfMonth(),
                'end_date' => now()->endOfMonth(),
                'planned_budget' => 5000000.00,
                'spent_amount' => 0.00,
                'hp_level' => 100,
                'status' => 'active',
            ]);

            // Handle DB Accounts balance updates
            $sourceAcc = \App\Models\Account::where('user_id', $user->id)->where('name', $this->sourceAccount)->first();
            $targetAcc = \App\Models\Account::where('user_id', $user->id)->where('name', $this->targetAccount)->first();
            $mainAcc = \App\Models\Account::where('user_id', $user->id)->where('name', $this->account)->first() ?? $sourceAcc;

            if ($this->type === 'transfer') {
                $transferAmount = (float) $this->amount;
                if ($sourceAcc) {
                    $sourceAcc->decrement('balance', $transferAmount);
                }
                if ($targetAcc) {
                    $targetAcc->increment('balance', $transferAmount);
                }
            } elseif ($this->type === 'expense' && $mainAcc) {
                $mainAcc->decrement('balance', (float) $this->amount);
            } elseif ($this->type === 'income' && $mainAcc) {
                $mainAcc->increment('balance', (float) $this->amount);
            }

            // Record ExpenseTransaction linked to DB Account
            ExpenseTransaction::create([
                'user_id' => $user->id,
                'budget_cycle_id' => $cycle->id,
                'account_id' => $mainAcc?->id,
                'merchant' => $this->merchant,
                'amount' => (float) $this->amount,
                'description' => $this->note ?: ($this->type === 'transfer' ? 'Pindah saldo antar akun' : 'Transaksi Manual (' . $this->category . ')'),
                'transaction_date' => now(),
            ]);

            // Award XP / AP bonus
            $user->increment('action_points_balance', 10);
            $user->update(['last_activity_at' => now()]);
        }

        $msg = $this->type === 'transfer'
            ? 'Pindah saldo Rp ' . number_format((float) $this->amount) . ' dari ' . $this->sourceAccount . ' ke ' . $this->targetAccount . ' berhasil! (+10 XP)'
            : 'Transaksi "' . $this->merchant . '" (Rp ' . number_format((float) $this->amount) . ') berhasil dicatat! (+10 XP)';

        session()->flash('global_success', $msg);

        $this->reset(['amount', 'merchant', 'note']);
        $this->openAddModal = false;

        $this->dispatch('transactionRecorded');
        $this->dispatch('accountUpdated');
    }

    private function getDefaultAccounts(): array
    {
        return [
            ['name' => 'Bank BCA Gaji', 'type' => 'bank', 'balance' => 14500000, 'icon' => '🏦', 'account_number' => '8839-XXXX-201', 'updated_at' => now()->format('d M Y, H:i')],
            ['name' => 'Bank Mandiri Savings', 'type' => 'bank', 'balance' => 8200000, 'icon' => '🏦', 'account_number' => '1370-XXXX-882', 'updated_at' => now()->format('d M Y, H:i')],
            ['name' => 'GoPay / GoTo', 'type' => 'ewallet', 'balance' => 450000, 'icon' => '📱', 'account_number' => '0812-XXXX-990', 'updated_at' => now()->format('d M Y, H:i')],
            ['name' => 'OVO Wallet', 'type' => 'ewallet', 'balance' => 250000, 'icon' => '📱', 'account_number' => '0812-XXXX-990', 'updated_at' => now()->format('d M Y, H:i')],
            ['name' => 'Cash / Dompet Fisik', 'type' => 'cash', 'balance' => 650000, 'icon' => '💵', 'account_number' => 'Fisik Saku', 'updated_at' => now()->format('d M Y, H:i')],
            ['name' => 'Bibit Mutual Funds', 'type' => 'investment', 'balance' => 12000000, 'icon' => '📈', 'account_number' => 'Bibit Portfolio', 'updated_at' => now()->format('d M Y, H:i')],
        ];
    }

    public function render(ThemeService $themeService)
    {
        $user = \Illuminate\Support\Facades\Auth::user() ?? User::first();
        $themeMode = $themeService->getActiveMode($user);
        $labels = $themeService->getLabels($themeMode);

        $accounts = $user ? $user->accounts : collect();

        return view('livewire.quick-transaction-modal', [
            'themeMode' => $themeMode,
            'labels' => $labels,
            'userAccounts' => $accounts,
        ]);
    }
}
