<?php

declare(strict_types=1);

namespace App\Livewire;

use App\Models\Account;
use App\Models\User;
use App\Services\ThemeService;
use Illuminate\Support\Facades\DB;
use Livewire\Attributes\Layout;
use Livewire\Attributes\On;
use Livewire\Component;

#[Layout('components.layouts.app')]
class AccountsComponent extends Component
{
    public bool $showCreateModal = false;

    public bool $showEditModal = false;

    public bool $showHistoryModal = false;

    public ?int $editingAccountId = null;

    public ?int $selectedAccountId = null;

    public string $name = '';

    public string $type = 'bank';

    public string $balance = '';

    public string $icon = '🏦';

    public string $accountNumber = '';

    public ?string $successMessage = null;

    public ?string $errorMessage = null;

    #[On('accountUpdated')]
    #[On('transactionRecorded')]
    public function refreshAccounts(): void
    {
        // Triggers livewire re-render
    }

    protected array $rules = [
        'name' => 'required|string|min:2|max:50',
        'balance' => 'required|numeric|min:0',
        'type' => 'required|string',
    ];

    public function mount(): void
    {
        $this->ensureInitialAccountsExist();
    }

    private function getUser(): User
    {
        $user = \Illuminate\Support\Facades\Auth::user() ?? User::first();
        if (! $user) {
            $user = User::create([
                'name' => 'Financial Warrior',
                'email' => 'warrior@financialplanner.id',
                'password' => bcrypt('secret123'),
                'hp_current' => 100,
                'action_points_balance' => 85,
            ]);
        }

        return $user;
    }

    private function ensureInitialAccountsExist(): void
    {
        $user = $this->getUser();
        if ($user->accounts()->count() === 0) {
            $user->accounts()->createMany([
                [
                    'name' => 'Bank BCA Gaji',
                    'type' => 'bank',
                    'balance' => 14500000.00,
                    'icon' => '🏦',
                    'account_number' => '8839-XXXX-201',
                ],
                [
                    'name' => 'Bank Mandiri Savings',
                    'type' => 'bank',
                    'balance' => 8200000.00,
                    'icon' => '🏦',
                    'account_number' => '1370-XXXX-882',
                ],
                [
                    'name' => 'GoPay / GoTo',
                    'type' => 'ewallet',
                    'balance' => 450000.00,
                    'icon' => '📱',
                    'account_number' => '0812-XXXX-990',
                ],
                [
                    'name' => 'OVO Wallet',
                    'type' => 'ewallet',
                    'balance' => 250000.00,
                    'icon' => '📱',
                    'account_number' => '0812-XXXX-990',
                ],
                [
                    'name' => 'Cash / Dompet Fisik',
                    'type' => 'cash',
                    'balance' => 650000.00,
                    'icon' => '💵',
                    'account_number' => 'Fisik Saku',
                ],
                [
                    'name' => 'Bibit Mutual Funds',
                    'type' => 'investment',
                    'balance' => 12000000.00,
                    'icon' => '📈',
                    'account_number' => 'Bibit Portfolio',
                ],
            ]);
        }
    }

    public function openCreateModal(): void
    {
        $this->reset(['name', 'balance', 'accountNumber', 'editingAccountId', 'successMessage', 'errorMessage']);
        $this->type = 'bank';
        $this->icon = '🏦';
        $this->showCreateModal = true;
    }

    public function closeModal(): void
    {
        $this->showCreateModal = false;
        $this->showEditModal = false;
        $this->showHistoryModal = false;
    }

    public function createAccount(): void
    {
        $this->validate();
        $user = $this->getUser();

        $user->accounts()->create([
            'name' => $this->name,
            'type' => $this->type,
            'balance' => (float) $this->balance,
            'icon' => $this->getIconForType($this->type, $this->icon),
            'account_number' => $this->accountNumber ?: '-',
        ]);

        $this->successMessage = 'Akun / Wallet "' . $this->name . '" berhasil ditambahkan!';
        $this->reset(['name', 'balance', 'accountNumber']);
        $this->showCreateModal = false;
    }

    public function openEditModal(int $accountId): void
    {
        $account = $this->getUser()->accounts()->find($accountId);

        if ($account) {
            $this->editingAccountId = $account->id;
            $this->name = $account->name;
            $this->type = $account->type;
            $this->balance = (string) $account->balance;
            $this->icon = $account->icon;
            $this->accountNumber = $account->account_number ?? '';
            $this->showEditModal = true;
        } else {
            $this->errorMessage = 'Akun tidak ditemukan atau bukan milik Anda.';
        }
    }

    public function updateAccount(): void
    {
        $this->validate();

        if ($this->editingAccountId) {
            $account = $this->getUser()->accounts()->find($this->editingAccountId);
            if ($account) {
                $account->update([
                    'name' => $this->name,
                    'type' => $this->type,
                    'balance' => (float) $this->balance,
                    'icon' => $this->getIconForType($this->type, $this->icon),
                    'account_number' => $this->accountNumber ?: '-',
                ]);

                $this->successMessage = 'Saldo & detail akun "' . $this->name . '" berhasil diperbarui!';
                $this->showEditModal = false;
            }
        }
    }

    public function viewTransactionHistory(int $accountId): void
    {
        $account = $this->getUser()->accounts()->find($accountId);
        if ($account) {
            $this->selectedAccountId = $accountId;
            $this->showHistoryModal = true;
        }
    }

    public function deleteAccount(int $accountId): void
    {
        $account = $this->getUser()->accounts()->find($accountId);
        if ($account) {
            $name = $account->name;
            $account->delete();
            $this->successMessage = 'Akun "' . $name . '" berhasil dihapus.';
        }
    }

    private function getIconForType(string $type, string $fallback): string
    {
        return match ($type) {
            'bank' => '🏦',
            'ewallet' => '📱',
            'cash' => '💵',
            'credit' => '💳',
            'investment' => '📈',
            'crypto' => '🪙',
            default => $fallback ?: '👛',
        };
    }

    public function render(ThemeService $themeService)
    {
        $user = $this->getUser();
        $themeMode = $themeService->getActiveMode($user);
        $labels = $themeService->getLabels($themeMode);

        $accounts = $user->accounts()->withCount('expenseTransactions')->get();
        $totalBalance = (float) $accounts->sum('balance');

        $selectedAccount = $this->selectedAccountId ? Account::with(['expenseTransactions' => function ($q) {
            $q->with(['expenseCategory', 'receipt'])->latest('transaction_date');
        }])->find($this->selectedAccountId) : null;

        return view('livewire.accounts-component', [
            'themeMode' => $themeMode,
            'labels' => $labels,
            'accounts' => $accounts,
            'totalBalance' => $totalBalance,
            'selectedAccount' => $selectedAccount,
        ]);
    }
}
