<?php

declare(strict_types=1);

namespace App\Livewire;

use App\Models\InvestmentAsset;
use App\Models\QuestPool;
use App\Models\User;
use Livewire\Attributes\Layout;
use Livewire\Component;

#[Layout('components.layouts.app')]
class PortfolioComponent extends Component
{
    public ?User $user = null;

    // Investment Asset Form State
    public string $asset_name = '';
    public string $asset_type = 'Saham';
    public string $quantity = '1';
    public string $purchase_price = '';
    public string $current_price = '';
    public string $notes = '';
    public string $successMessage = '';

    protected array $rules = [
        'asset_name' => 'required|string|max:100',
        'asset_type' => 'required|string',
        'quantity' => 'required|numeric|min:0.0001',
        'purchase_price' => 'required|numeric|min:0',
        'current_price' => 'required|numeric|min:0',
    ];

    public function mount(): void
    {
        $this->user = \Illuminate\Support\Facades\Auth::user() ?? User::first();
    }

    public function addAsset(): void
    {
        $this->validate();
        /** @var User $user */
        $user = \Illuminate\Support\Facades\Auth::user();

        if (!$user) {
            return;
        }

        $user->investmentAssets()->create([
            'asset_name' => $this->asset_name,
            'asset_type' => $this->asset_type,
            'quantity' => (float) $this->quantity,
            'purchase_price' => (float) $this->purchase_price,
            'current_price' => (float) $this->current_price,
            'notes' => $this->notes,
        ]);

        $this->successMessage = "Aset investasi '{$this->asset_name}' berhasil ditambahkan ke portofolio!";
        $this->reset(['asset_name', 'quantity', 'purchase_price', 'current_price', 'notes']);
    }

    public function deleteAsset(int $id): void
    {
        /** @var User $user */
        $user = \Illuminate\Support\Facades\Auth::user();
        if ($user) {
            $user->investmentAssets()->where('id', $id)->delete();
            $this->successMessage = "Aset investasi berhasil dihapus.";
        }
    }

    public function render(\App\Services\ThemeService $themeService)
    {
        $themeMode = $themeService->getActiveMode($this->user);
        $labels = $themeService->getLabels($themeMode);

        if ($this->user && $this->user->is_penalized) {
            return view('discipline-locked', [
                'user' => $this->user,
                'reason' => 'Streak Penalized: Real-World Quest Portfolio is locked until you upload today\'s receipt or sync transactions!',
                'themeMode' => $themeMode,
                'labels' => $labels,
            ]);
        }

        $questPools = $this->user ? $this->user->questPools()->with('apAllocations')->get() : collect();
        $accounts = $this->user ? $this->user->accounts()->get() : collect();
        $investmentAssets = $this->user ? $this->user->investmentAssets()->get() : collect();

        $liquidBalance = (float) $accounts->sum('balance');
        $goalsBalance = (float) $questPools->sum('current_amount');
        $investmentsValue = (float) $investmentAssets->sum(fn($a) => $a->total_value);
        
        $totalNetWorth = $liquidBalance + $goalsBalance + $investmentsValue;
        $totalApAllocated = $questPools->sum('allocated_ap');

        return view('livewire.portfolio-component', [
            'questPools' => $questPools,
            'investmentAssets' => $investmentAssets,
            'liquidBalance' => $liquidBalance,
            'goalsBalance' => $goalsBalance,
            'investmentsValue' => $investmentsValue,
            'totalNetWorth' => $totalNetWorth,
            'totalApAllocated' => $totalApAllocated,
            'themeMode' => $themeMode,
            'labels' => $labels,
        ]);
    }
}
