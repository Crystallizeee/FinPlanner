<?php

declare(strict_types=1);

namespace App\Livewire;

use App\Models\QuestPool;
use App\Models\User;
use Livewire\Attributes\Layout;
use Livewire\Component;

#[Layout('components.layouts.app')]
class PortfolioComponent extends Component
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
                'reason' => 'Streak Penalized: Real-World Quest Portfolio is locked until you upload today\'s receipt or sync transactions!',
                'themeMode' => $themeMode,
                'labels' => $labels,
            ]);
        }

        $questPools = $this->user ? $this->user->questPools()->with('apAllocations')->get() : collect();
        $totalPortfolioValue = $questPools->sum('current_amount');
        $totalApAllocated = $questPools->sum('allocated_ap');

        return view('livewire.portfolio-component', [
            'questPools' => $questPools,
            'totalPortfolioValue' => $totalPortfolioValue,
            'totalApAllocated' => $totalApAllocated,
            'themeMode' => $themeMode,
            'labels' => $labels,
        ]);
    }
}
