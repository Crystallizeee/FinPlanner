<?php

declare(strict_types=1);

namespace App\Livewire;

use App\Models\Account;
use App\Models\ExpenseTransaction;
use App\Models\RecurringExpense;
use App\Models\User;
use App\Services\ThemeService;
use Livewire\Attributes\Layout;
use Livewire\Component;

#[Layout('components.layouts.app')]
class SubscriptionsComponent extends Component
{
    public string $title = '';
    public string $amount = '';
    public string $category = 'bills';
    public ?int $account_id = null;
    public int $due_day = 1;
    public string $successMessage = '';

    protected array $rules = [
        'title' => 'required|string|max:100',
        'amount' => 'required|numeric|min:1000',
        'due_day' => 'required|integer|between:1,31',
    ];

    public function addSubscription(): void
    {
        $this->validate();
        /** @var User $user */
        $user = \Illuminate\Support\Facades\Auth::user();

        if (!$user) {
            return;
        }

        $user->recurringExpenses()->create([
            'account_id' => $this->account_id,
            'title' => $this->title,
            'amount' => (float) $this->amount,
            'category' => $this->category,
            'due_day' => $this->due_day,
            'billing_cycle' => 'monthly',
            'is_active' => true,
        ]);

        $this->successMessage = "Tagihan rutin '{$this->title}' berhasil ditambahkan!";
        $this->reset(['title', 'amount', 'account_id']);
    }

    public function payBill(int $id): void
    {
        /** @var User $user */
        $user = \Illuminate\Support\Facades\Auth::user();
        if (!$user) {
            return;
        }

        /** @var RecurringExpense|null $bill */
        $bill = $user->recurringExpenses()->find($id);
        if (!$bill) {
            return;
        }

        $account = $bill->account ?? $user->accounts()->first();
        if ($account) {
            $account->deductBalance((float) $bill->amount);
        }

        $activeCycle = $user->getActiveBudgetCycle() ?? $user->budgetCycles()->first() ?? $user->budgetCycles()->create([
            'name' => 'Siklus Anggaran Utama',
            'period_type' => 'monthly',
            'start_date' => now()->startOfMonth(),
            'end_date' => now()->endOfMonth(),
            'planned_budget' => 5000000,
            'spent_amount' => 0,
            'hp_level' => 100,
            'status' => 'active',
        ]);

        ExpenseTransaction::create([
            'user_id' => $user->id,
            'budget_cycle_id' => $activeCycle->id,
            'account_id' => $account?->id,
            'source' => 'manual',
            'merchant' => $bill->title,
            'amount' => $bill->amount,
            'category' => $bill->category,
            'description' => "Pembayaran Tagihan Rutin: {$bill->title}",
            'transaction_date' => now(),
            'is_verified' => true,
        ]);

        $bill->update(['last_paid_at' => now()]);

        $this->successMessage = "Tagihan '{$bill->title}' (Rp " . number_format((float)$bill->amount, 0, ',', '.') . ") telah dibayar & memotong saldo rekening!";
    }

    public function deleteBill(int $id): void
    {
        /** @var User $user */
        $user = \Illuminate\Support\Facades\Auth::user();
        if ($user) {
            $user->recurringExpenses()->where('id', $id)->delete();
            $this->successMessage = "Tagihan rutin berhasil dihapus.";
        }
    }

    public function render(ThemeService $themeService)
    {
        $user = \Illuminate\Support\Facades\Auth::user() ?? User::first();
        $themeMode = $themeService->getActiveMode($user);
        $labels = $themeService->getLabels($themeMode);

        $subscriptions = $user ? $user->recurringExpenses()->with('account')->get() : collect();
        $accounts = $user ? $user->accounts()->get() : collect();

        return view('livewire.subscriptions-component', [
            'subscriptions' => $subscriptions,
            'accounts' => $accounts,
            'themeMode' => $themeMode,
            'labels' => $labels,
        ]);
    }
}
