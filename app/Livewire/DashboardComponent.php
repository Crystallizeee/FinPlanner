<?php

declare(strict_types=1);

namespace App\Livewire;

use App\Models\BudgetCycle;
use App\Models\ExpenseTransaction;
use App\Models\Receipt;
use App\Models\User;
use App\Services\HpBudgetingService;
use App\Services\OcrReceiptProcessorService;
use App\Services\StreakPenaltyService;
use Livewire\Attributes\Layout;
use Livewire\Attributes\On;
use Livewire\Component;
use Livewire\WithFileUploads;

#[Layout('components.layouts.app')]
class DashboardComponent extends Component
{
    use WithFileUploads;

    public ?User $user = null;

    public ?BudgetCycle $activeCycle = null;

    #[On('transactionRecorded')]
    public function refreshDashboard(): void
    {
        // Triggers re-render to update budget cycle progress and XP balance
    }

    /** @var mixed */
    public $receiptImage;

    public bool $isProcessingOcr = false;

    public ?string $ocrSuccessMessage = null;

    public ?string $ocrErrorMessage = null;

    public ?int $selectedReceiptId = null;

    public bool $showReceiptModal = false;

    public ?Receipt $activeReceiptModal = null;

    public bool $isEditingReceipt = false;

    public ?string $editMerchantName = null;

    public ?float $editTotalAmount = null;

    public array $editItems = [];

    public function mount(): void
    {
        $this->loadUserData();
    }

    public function loadUserData(): void
    {
        $this->user = \Illuminate\Support\Facades\Auth::user() ?? User::first();

        if (! $this->user) {
            $this->user = User::create([
                'name' => 'Financial Warrior',
                'email' => 'warrior@financialplanner.id',
                'password' => bcrypt('secret123'),
                'hp_current' => 100,
                'action_points_balance' => 85,
                'current_streak' => 12,
                'longest_streak' => 30,
                'is_penalized' => false,
                'last_activity_at' => now(),
            ]);

            // Create initial active budget cycle (Rp 5.000.000)
            $this->activeCycle = BudgetCycle::create([
                'user_id' => $this->user->id,
                'name' => 'August 2026 Financial Discipline Cycle',
                'period_type' => 'monthly',
                'start_date' => now()->startOfMonth(),
                'end_date' => now()->endOfMonth(),
                'planned_budget' => 5000000.00,
                'spent_amount' => 1250000.00,
                'hp_level' => 75,
                'status' => 'active',
            ]);

            // Seed initial sample quest pools
            $this->user->questPools()->createMany([
                [
                    'name' => 'IDX Stock Averaging Down (BBCA/TLKM)',
                    'slug' => 'idx-stock-averaging-down',
                    'category' => 'investment',
                    'target_amount' => 10000000.00,
                    'allocated_ap' => 45,
                    'current_amount' => 450000.00,
                    'icon' => '📈',
                ],
                [
                    'name' => 'CB150R Maintenance & Oil Service',
                    'slug' => 'cb150r-maintenance',
                    'category' => 'vehicle',
                    'target_amount' => 1500000.00,
                    'allocated_ap' => 20,
                    'current_amount' => 200000.00,
                    'icon' => '🏍️',
                ],
                [
                    'name' => 'Emergency Cash Vault',
                    'slug' => 'emergency-cash-vault',
                    'category' => 'emergency',
                    'target_amount' => 15000000.00,
                    'allocated_ap' => 20,
                    'current_amount' => 200000.00,
                    'icon' => '🛡️',
                ],
            ]);
        } else {
            $this->activeCycle = $this->user->getActiveBudgetCycle();
        }
    }

    /**
     * Upload receipt image and trigger OCR anti-cheat processor.
     */
    public function uploadReceipt(OcrReceiptProcessorService $ocrService): void
    {
        $this->validate([
            'receiptImage' => 'required|image|max:10240', // Max 10MB
        ]);

        $this->isProcessingOcr = true;
        $this->ocrErrorMessage = null;
        $this->ocrSuccessMessage = null;

        try {
            /** @var \Illuminate\Http\UploadedFile $file */
            $file = $this->receiptImage;
            $receipt = $ocrService->processReceipt($this->user, $file, $this->activeCycle);

            $this->ocrSuccessMessage = "Receipt berhasil diproses! Rp " . number_format((float) $receipt->total_amount, 0, ',', '.') . " dari {$receipt->merchant_name} — " . count($receipt->items) . " item ditemukan. (+10 XP)";
            $this->receiptImage = null;
            $this->loadUserData();

            // Auto-open review modal so user can inspect extracted items
            $this->activeReceiptModal = $receipt->load('items');
            $this->showReceiptModal = true;
            $this->isEditingReceipt = false;

            $this->dispatch('transactionRecorded');
        } catch (\Throwable $e) {
            $this->ocrErrorMessage = "OCR Gagal: {$e->getMessage()}";
        } finally {
            $this->isProcessingOcr = false;
        }
    }

    public function viewReceiptDetails(int $receiptId): void
    {
        $this->activeReceiptModal = Receipt::with('items')->find($receiptId);
        $this->showReceiptModal = true;
        $this->isEditingReceipt = false;
    }

    public function startEditingReceipt(): void
    {
        if ($this->activeReceiptModal) {
            $this->editMerchantName = $this->activeReceiptModal->merchant_name;
            $this->editTotalAmount = (float) $this->activeReceiptModal->total_amount;
            $this->editItems = $this->activeReceiptModal->items->map(fn ($item) => [
                'id' => $item->id,
                'name' => $item->item_name,
                'qty' => (float) $item->quantity,
                'unit_price' => (float) $item->unit_price,
                'total_price' => (float) $item->total_price,
            ])->toArray();

            if (empty($this->editItems)) {
                $this->addItemRow();
            }

            $this->isEditingReceipt = true;
        }
    }

    public function addItemRow(): void
    {
        $this->editItems[] = [
            'id' => null,
            'name' => '',
            'qty' => 1.0,
            'unit_price' => 0.0,
            'total_price' => 0.0,
        ];
    }

    public function removeItemRow(int $index): void
    {
        unset($this->editItems[$index]);
        $this->editItems = array_values($this->editItems);
        $this->recalculateItemsTotal();
    }

    public function updatedEditItems($value, $key): void
    {
        $parts = explode('.', (string) $key);
        if (count($parts) === 2 && in_array($parts[1], ['qty', 'unit_price'])) {
            $index = (int) $parts[0];
            if (isset($this->editItems[$index])) {
                $qty = (float) ($this->editItems[$index]['qty'] ?? 0);
                $unitPrice = (float) ($this->editItems[$index]['unit_price'] ?? 0);
                $this->editItems[$index]['total_price'] = round($qty * $unitPrice, 2);
                $this->recalculateItemsTotal();
            }
        } elseif (count($parts) === 2 && $parts[1] === 'total_price') {
            $this->recalculateItemsTotal();
        }
    }

    public function recalculateItemsTotal(): void
    {
        if (! empty($this->editItems)) {
            $sum = (float) array_sum(array_column($this->editItems, 'total_price'));
            if ($sum > 0) {
                $this->editTotalAmount = $sum;
            }
        }
    }

    public function cancelEditingReceipt(): void
    {
        $this->isEditingReceipt = false;
    }

    public function saveReceiptCorrection(HpBudgetingService $hpService): void
    {
        if (! $this->activeReceiptModal) {
            return;
        }

        $this->validate([
            'editMerchantName' => 'required|string|max:255',
            'editTotalAmount' => 'required|numeric|min:1',
            'editItems' => 'nullable|array',
            'editItems.*.name' => 'required|string|max:255',
            'editItems.*.qty' => 'required|numeric|min:0.01',
            'editItems.*.unit_price' => 'required|numeric|min:0',
            'editItems.*.total_price' => 'required|numeric|min:0',
        ]);

        $this->activeReceiptModal->update([
            'merchant_name' => $this->editMerchantName,
            'total_amount' => $this->editTotalAmount,
            'ocr_status' => 'manually_corrected',
        ]);

        // Sync itemized breakdown
        $this->activeReceiptModal->items()->delete();
        foreach ($this->editItems as $item) {
            if (! empty(trim($item['name']))) {
                $this->activeReceiptModal->items()->create([
                    'item_name' => trim($item['name']),
                    'quantity' => $item['qty'],
                    'unit_price' => $item['unit_price'],
                    'total_price' => $item['total_price'],
                ]);
            }
        }

        // Sync corresponding expense transaction
        ExpenseTransaction::where('receipt_id', $this->activeReceiptModal->id)->update([
            'merchant' => $this->editMerchantName,
            'amount' => $this->editTotalAmount,
            'description' => "Verified OCR Receipt - {$this->editMerchantName} (Manually Corrected)",
        ]);

        if ($this->activeCycle) {
            $hpService->recalculateCycleHp($this->activeCycle);
        }

        $this->activeReceiptModal->refresh();
        $this->activeReceiptModal->load('items');

        $this->ocrSuccessMessage = "✅ Koreksi berhasil disimpan! Total diperbarui menjadi Rp " . number_format((float) $this->editTotalAmount, 0, ',', '.') . " — {$this->editMerchantName}.";

        // Return to read-only view (don't close modal) so user can review updated items
        $this->isEditingReceipt = false;
        $this->editItems = [];

        $this->dispatch('transactionRecorded');
        $this->loadUserData();
    }

    public function closeReceiptModal(): void
    {
        $this->showReceiptModal = false;
        $this->activeReceiptModal = null;
        $this->isEditingReceipt = false;
        $this->editItems = [];
    }

    public function render(\App\Services\ThemeService $themeService)
    {
        $hpPercentage = $this->user->hp_current;
        $isCriticalMode = $hpPercentage < 20;
        $themeMode = $themeService->getActiveMode($this->user);
        $labels = $themeService->getLabels($themeMode);

        // Dynamic Greeting
        $hour = (int) now()->format('H');
        if ($hour < 12) {
            $greetingTime = 'Good morning';
        } elseif ($hour < 18) {
            $greetingTime = 'Good afternoon';
        } else {
            $greetingTime = 'Good evening';
        }

        // Dynamic KPI Metrics
        $totalPlannedBudget = $this->activeCycle ? (float) $this->activeCycle->planned_budget : 5000000.0;
        $totalSpent = $this->activeCycle ? (float) $this->activeCycle->spent_amount : 0.0;
        $remainingBudget = max(0, $totalPlannedBudget - $totalSpent);
        $totalSavings = (float) $this->user->questPools()->sum('current_amount');
        $accountsTotal = (float) $this->user->accounts()->sum('balance');
        $totalBalance = $accountsTotal > 0 ? $accountsTotal : ($remainingBudget + $totalSavings);
        $savingsRate = $totalPlannedBudget > 0 ? min(100, round(($totalSavings / $totalPlannedBudget) * 100, 1)) : 0.0;
        $netCashFlow = $totalPlannedBudget - $totalSpent;

        // Dynamic Health Factor Breakdown
        $savingRatioPercent = min(100, round(($totalSavings / max(1, $totalPlannedBudget)) * 100));
        $budgetDisciplinePercent = (int) max(0, min(100, round(100 - ($totalSpent / max(1, $totalPlannedBudget) * 100))));
        $spendingControlPercent = (int) max(0, min(100, round(100 - ($totalSpent / max(1, $totalPlannedBudget) * 80))));
        $emergencyFundPercent = min(100, round(($totalSavings / 5000000) * 100));
        $debtManagementPercent = 90;

        // Dynamic Gamification Rank & Level
        $userLevel = (int) floor($this->user->action_points_balance / 50) + 1;
        $apBalance = $this->user->action_points_balance;
        $xpCurrentLevel = $apBalance % 50;
        $xpPercentage = min(100, round(($xpCurrentLevel / 50) * 100));

        if ($userLevel <= 3) {
            $rankTitle = 'Money Novice';
        } elseif ($userLevel <= 7) {
            $rankTitle = 'Budget Apprentice';
        } elseif ($userLevel <= 12) {
            $rankTitle = 'Money Strategist';
        } else {
            $rankTitle = 'Financial Mastermind';
        }

        $recentTransactions = ExpenseTransaction::with('receipt')
            ->where('user_id', $this->user->id)
            ->latest('transaction_date')
            ->take(8)
            ->get();

        return view('livewire.dashboard-component', [
            'hpPercentage' => $hpPercentage,
            'isCriticalMode' => $isCriticalMode,
            'recentTransactions' => $recentTransactions,
            'themeMode' => $themeMode,
            'labels' => $labels,

            // Dynamic Metrics
            'greetingTime' => $greetingTime,
            'userName' => $this->user->name,
            'activeCycleName' => $this->activeCycle ? $this->activeCycle->name : 'Active Cycle',
            'totalBalance' => $totalBalance,
            'totalPlannedBudget' => $totalPlannedBudget,
            'totalSpent' => $totalSpent,
            'remainingBudget' => $remainingBudget,
            'totalSavings' => $totalSavings,
            'savingsRate' => $savingsRate,
            'netCashFlow' => $netCashFlow,

            // Dynamic Health Breakdown
            'savingRatioPercent' => $savingRatioPercent,
            'budgetDisciplinePercent' => $budgetDisciplinePercent,
            'spendingControlPercent' => $spendingControlPercent,
            'emergencyFundPercent' => $emergencyFundPercent,
            'debtManagementPercent' => $debtManagementPercent,

            // Dynamic Gamification
            'userLevel' => $userLevel,
            'apBalance' => $apBalance,
            'xpCurrentLevel' => $xpCurrentLevel,
            'xpPercentage' => $xpPercentage,
            'rankTitle' => $rankTitle,
        ]);
    }
}
