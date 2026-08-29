<?php

declare(strict_types=1);

namespace App\Livewire;

use App\Models\QuestPool;
use App\Models\User;
use App\Services\ThemeService;
use Illuminate\Support\Str;
use Livewire\Attributes\Layout;
use Livewire\Component;

#[Layout('components.layouts.app')]
class GoalsComponent extends Component
{
    public bool $openGoalModal = false;
    public bool $openDepositModal = false;

    public string $name = '';
    public string $targetAmount = '';
    public string $category = 'emergency';
    public string $icon = '🎯';
    public ?int $accountId = null;

    public ?int $selectedGoalId = null;
    public ?int $depositAccountId = null;
    public string $depositAmount = '';
    public ?string $successMessage = null;

    protected array $rules = [
        'name' => 'required|string|min:3|max:100',
        'targetAmount' => 'required|numeric|min:10000',
        'category' => 'required|string|in:investment,vehicle,emergency,hobby',
        'accountId' => 'nullable|exists:accounts,id',
    ];

    public function openCreateGoalModal(): void
    {
        $this->reset(['name', 'targetAmount', 'category', 'icon', 'accountId', 'successMessage']);
        $this->category = 'emergency';

        $user = \Illuminate\Support\Facades\Auth::user() ?? User::first();
        if ($user) {
            $firstAccount = $user->accounts()->first();
            $this->accountId = $firstAccount?->id;
        }

        $this->openGoalModal = true;
    }

    public function createGoal(): void
    {
        $this->validate();

        $user = \Illuminate\Support\Facades\Auth::user() ?? User::first();

        if ($user) {
            $user->questPools()->create([
                'account_id' => $this->accountId,
                'name' => $this->name,
                'slug' => Str::slug($this->name) . '-' . Str::random(4),
                'category' => strtolower($this->category),
                'target_amount' => (float) $this->targetAmount,
                'current_amount' => 0.00,
                'allocated_ap' => 0,
                'icon' => $this->icon ?: '🎯',
            ]);

            $this->successMessage = 'Target tabungan "' . $this->name . '" berhasil dibuat!';
        }

        $this->reset(['name', 'targetAmount', 'category', 'icon', 'accountId']);
        $this->openGoalModal = false;
    }

    public function openDeposit(int $goalId): void
    {
        $this->selectedGoalId = $goalId;
        $this->depositAmount = '';
        
        $user = \Illuminate\Support\Facades\Auth::user() ?? User::first();
        if ($user) {
            $goal = $user->questPools()->find($goalId);
            $this->depositAccountId = $goal?->account_id ?? $user->accounts()->first()?->id;
        }

        $this->openDepositModal = true;
    }

    public function deposit(): void
    {
        $this->validate([
            'depositAmount' => 'required|numeric|min:1000',
            'depositAccountId' => 'nullable|exists:accounts,id',
        ]);

        $user = \Illuminate\Support\Facades\Auth::user() ?? User::first();

        if ($user && $this->selectedGoalId) {
            $goal = $user->questPools()->find($this->selectedGoalId);
            if ($goal) {
                $amount = (float) $this->depositAmount;
                $goal->increment('current_amount', $amount);

                // Potong saldo dari akun dompet asal jika dipilih
                if ($this->depositAccountId) {
                    $account = $user->accounts()->find($this->depositAccountId);
                    if ($account) {
                        $account->decrement('balance', $amount);
                        
                        // Perbarui lokasi wadah akun goal jika belum ada
                        if (!$goal->account_id) {
                            $goal->update(['account_id' => $account->id]);
                        }
                    }
                }

                $this->successMessage = 'Berhasil menambahkan tabungan Rp ' . number_format($amount) . ' ke ' . $goal->name . '!';
            }
        }

        $this->reset(['depositAmount', 'selectedGoalId', 'depositAccountId']);
        $this->openDepositModal = false;
    }

    public function render(ThemeService $themeService)
    {
        $user = \Illuminate\Support\Facades\Auth::user() ?? User::first();
        $themeMode = $themeService->getActiveMode($user);
        $labels = $themeService->getLabels($themeMode);

        if ($user && $user->questPools()->count() === 0) {
            $firstAccount = $user->accounts()->first();

            // Seed initial sample goals for user if empty
            $user->questPools()->createMany([
                [
                    'account_id' => $firstAccount?->id,
                    'name' => 'Emergency Fund',
                    'slug' => 'emergency-fund',
                    'category' => 'emergency',
                    'target_amount' => 15000000.00,
                    'current_amount' => 8500000.00,
                    'allocated_ap' => 85,
                    'icon' => '🛡️',
                ],
                [
                    'account_id' => $firstAccount?->id,
                    'name' => 'New Laptop Asset',
                    'slug' => 'new-laptop',
                    'category' => 'hobby',
                    'target_amount' => 15000000.00,
                    'current_amount' => 6000000.00,
                    'allocated_ap' => 60,
                    'icon' => '💻',
                ],
                [
                    'account_id' => $firstAccount?->id,
                    'name' => 'CB150R Maintenance & Fuel',
                    'slug' => 'cb150r-maintenance',
                    'category' => 'vehicle',
                    'target_amount' => 5000000.00,
                    'current_amount' => 3500000.00,
                    'allocated_ap' => 35,
                    'icon' => '🏍️',
                ],
                [
                    'account_id' => $firstAccount?->id,
                    'name' => 'IDX Stock Investment',
                    'slug' => 'idx-stock-investment',
                    'category' => 'investment',
                    'target_amount' => 10000000.00,
                    'current_amount' => 10000000.00,
                    'allocated_ap' => 100,
                    'icon' => '📈',
                ],
            ]);
        }

        $questPools = $user ? $user->questPools()->with('account')->get() : collect();
        $userAccounts = $user ? $user->accounts()->get() : collect();

        return view('livewire.goals-component', [
            'themeMode' => $themeMode,
            'labels' => $labels,
            'questPools' => $questPools,
            'userAccounts' => $userAccounts,
            'userApBalance' => $user?->action_points_balance ?? 0,
        ]);
    }
}
