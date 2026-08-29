<?php

declare(strict_types=1);

namespace App\Livewire;

use App\Models\User;
use App\Services\ThemeService;
use Livewire\Attributes\Layout;
use Livewire\Component;

#[Layout('components.layouts.app')]
class ChallengesComponent extends Component
{
    public string $feedbackMessage = '';

    public function acceptChallenge(int $xpReward): void
    {
        /** @var User $user */
        $user = \Illuminate\Support\Facades\Auth::user();
        if ($user) {
            $user->addXp($xpReward);
            $this->feedbackMessage = "Tantangan berhasil diterima & diselesaikan! (+{$xpReward} XP diklaim)";
        }
    }

    public function render(ThemeService $themeService)
    {
        $user = \Illuminate\Support\Facades\Auth::user() ?? User::first();
        $themeMode = $themeService->getActiveMode($user);
        $labels = $themeService->getLabels($themeMode);

        $activeCycle = $user ? $user->getActiveBudgetCycle() : null;
        $recentFoodSpent = $user ? (float) $user->expenseTransactions()->where('category', 'food')->where('transaction_date', '>=', now()->subDays(7))->sum('amount') : 0;
        $totalSpentWeek = $user ? (float) $user->expenseTransactions()->where('transaction_date', '>=', now()->subDays(7))->sum('amount') : 0;

        return view('livewire.challenges-component', [
            'user' => $user,
            'activeCycle' => $activeCycle,
            'recentFoodSpent' => $recentFoodSpent,
            'totalSpentWeek' => $totalSpentWeek,
            'themeMode' => $themeMode,
            'labels' => $labels,
        ]);
    }
}
