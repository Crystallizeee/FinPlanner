<?php

declare(strict_types=1);

namespace App\Livewire;

use App\Models\CategoryBudget;
use App\Models\User;
use App\Services\ThemeService;
use Livewire\Attributes\Layout;
use Livewire\Component;

#[Layout('components.layouts.app')]
class CategoryBudgetsComponent extends Component
{
    public string $category = 'food';
    public string $amount_limit = '';
    public string $successMessage = '';
    public string $errorMessage = '';

    protected array $rules = [
        'category' => 'required|string|max:50',
        'amount_limit' => 'required|numeric|min:1000',
    ];

    public function setBudget(): void
    {
        $this->validate();
        /** @var User $user */
        $user = \Illuminate\Support\Facades\Auth::user();

        if (!$user) {
            return;
        }

        CategoryBudget::updateOrCreate(
            ['user_id' => $user->id, 'category' => strtolower($this->category)],
            ['amount_limit' => (float) $this->amount_limit]
        );

        $this->successMessage = "Batas anggaran untuk kategori " . strtoupper($this->category) . " berhasil diperbarui!";
        $this->amount_limit = '';
    }

    public function deleteBudget(int $id): void
    {
        /** @var User $user */
        $user = \Illuminate\Support\Facades\Auth::user();
        if ($user) {
            $user->categoryBudgets()->where('id', $id)->delete();
            $this->successMessage = "Batas anggaran kategori berhasil dihapus.";
        }
    }

    public function render(ThemeService $themeService)
    {
        $user = \Illuminate\Support\Facades\Auth::user() ?? User::first();
        $themeMode = $themeService->getActiveMode($user);
        $labels = $themeService->getLabels($themeMode);

        $budgets = $user ? $user->categoryBudgets()->get() : collect();
        $recentTransactions = $user ? $user->expenseTransactions()->where('transaction_date', '>=', now()->startOfMonth())->get() : collect();

        $budgetStats = $budgets->map(function ($b) use ($recentTransactions) {
            $spent = (float) $recentTransactions->where('category', strtolower($b->category))->sum('amount');
            $pct = $b->amount_limit > 0 ? round(($spent / $b->amount_limit) * 100, 1) : 0;
            return [
                'id' => $b->id,
                'category' => $b->category,
                'limit' => (float) $b->amount_limit,
                'spent' => $spent,
                'percentage' => $pct,
                'is_exceeded' => $spent > $b->amount_limit,
                'is_warning' => $pct >= 80 && $pct <= 100,
            ];
        });

        return view('livewire.category-budgets-component', [
            'budgetStats' => $budgetStats,
            'themeMode' => $themeMode,
            'labels' => $labels,
        ]);
    }
}
