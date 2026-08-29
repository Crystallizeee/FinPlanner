<?php

declare(strict_types=1);

namespace App\Livewire;

use App\Models\Debt;
use App\Models\User;
use App\Services\ThemeService;
use Livewire\Attributes\Layout;
use Livewire\Component;

#[Layout('components.layouts.app')]
class DebtPlannerComponent extends Component
{
    public ?User $user = null;

    // Debt Form state
    public string $name = '';
    public string $lender = '';
    public string $total_amount = '';
    public string $remaining_amount = '';
    public string $interest_rate = '0';
    public string $minimum_monthly_payment = '';
    public int $due_day = 1;
    public string $strategy = 'snowball'; // 'snowball' or 'avalanche'
    public string $successMessage = '';

    protected array $rules = [
        'name' => 'required|string|max:100',
        'remaining_amount' => 'required|numeric|min:0',
        'minimum_monthly_payment' => 'required|numeric|min:0',
        'due_day' => 'required|integer|between:1,31',
    ];

    public function mount(): void
    {
        $this->user = \Illuminate\Support\Facades\Auth::user() ?? User::first();
    }

    public function addDebt(): void
    {
        $this->validate();
        /** @var User $user */
        $user = \Illuminate\Support\Facades\Auth::user();

        if (!$user) {
            return;
        }

        $totalAmt = $this->total_amount !== '' ? (float) $this->total_amount : (float) $this->remaining_amount;

        $user->debts()->create([
            'name' => $this->name,
            'lender' => $this->lender,
            'total_amount' => $totalAmt,
            'remaining_amount' => (float) $this->remaining_amount,
            'interest_rate' => (float) $this->interest_rate,
            'minimum_monthly_payment' => (float) $this->minimum_monthly_payment,
            'due_day' => $this->due_day,
        ]);

        $this->successMessage = "Data utang/cicilan '{$this->name}' berhasil ditambahkan!";
        $this->reset(['name', 'lender', 'total_amount', 'remaining_amount', 'interest_rate', 'minimum_monthly_payment']);
    }

    public function makePayment(int $id, float $amount): void
    {
        /** @var User $user */
        $user = \Illuminate\Support\Facades\Auth::user();
        if (!$user) {
            return;
        }

        /** @var Debt|null $debt */
        $debt = $user->debts()->find($id);
        if ($debt) {
            $newRemaining = max(0, (float)$debt->remaining_amount - $amount);
            $debt->update(['remaining_amount' => $newRemaining]);
            $this->successMessage = "Pembayaran cicilan Rp " . number_format($amount, 0, ',', '.') . " untuk '{$debt->name}' telah dicatat!";
        }
    }

    public function deleteDebt(int $id): void
    {
        /** @var User $user */
        $user = \Illuminate\Support\Facades\Auth::user();
        if ($user) {
            $user->debts()->where('id', $id)->delete();
            $this->successMessage = "Data cicilan berhasil dihapus.";
        }
    }

    public function render(ThemeService $themeService)
    {
        $themeMode = $themeService->getActiveMode($this->user);
        $labels = $themeService->getLabels($themeMode);

        $debts = $this->user ? $this->user->debts()->get() : collect();

        $totalOriginalDebt = (float) $debts->sum('total_amount');
        $totalRemainingDebt = (float) $debts->sum('remaining_amount');
        $totalMonthlyPayment = (float) $debts->sum('minimum_monthly_payment');

        // Debt Snowball sorting (Lowest remaining balance first)
        $snowballOrder = $debts->sortBy('remaining_amount')->values();

        // Debt Avalanche sorting (Highest interest rate first)
        $avalancheOrder = $debts->sortByDesc('interest_rate')->values();

        $activeOrder = $this->strategy === 'avalanche' ? $avalancheOrder : $snowballOrder;

        return view('livewire.debt-planner-component', [
            'debts' => $activeOrder,
            'totalOriginalDebt' => $totalOriginalDebt,
            'totalRemainingDebt' => $totalRemainingDebt,
            'totalMonthlyPayment' => $totalMonthlyPayment,
            'themeMode' => $themeMode,
            'labels' => $labels,
        ]);
    }
}
