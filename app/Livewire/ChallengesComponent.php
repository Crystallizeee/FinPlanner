<?php

declare(strict_types=1);

namespace App\Livewire;

use App\Models\BossBattle;
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

            // Also attack Boss Raid!
            $boss = $this->getOrCreateBoss($user);
            if ($boss && $boss->status === 'active') {
                $boss->damage(150); // 150 DMG per challenge
            }

            $this->feedbackMessage = "Tantangan berhasil diselesaikan! (+{$xpReward} XP diklaim & Deal 150 DMG ke Boss Raid!)";
        }
    }

    public function attackBoss(): void
    {
        /** @var User $user */
        $user = \Illuminate\Support\Facades\Auth::user();
        if (!$user) {
            return;
        }

        $boss = $this->getOrCreateBoss($user);
        if ($boss && $boss->status === 'active') {
            $boss->damage(200);
            if ($boss->status === 'defeated') {
                $user->addXp($boss->reward_xp);
                $this->feedbackMessage = "🎉 VICTORY! Boss {$boss->boss_name} BERHASIL DIKALAHKAN! (+{$boss->reward_xp} XP BONUS DIKLAIM!)";
            } else {
                $this->feedbackMessage = "⚔️ Serangan berhasil! Menimbulkan 200 DMG pada {$boss->boss_name}! (Sisa HP: {$boss->current_hp}/{$boss->max_hp})";
            }
        }
    }

    protected function getOrCreateBoss(User $user): BossBattle
    {
        $monthYear = now()->format('Y-m');
        /** @var BossBattle|null $boss */
        $boss = $user->bossBattles()->where('month_year', $monthYear)->first();

        if (!$boss) {
            $boss = $user->bossBattles()->create([
                'boss_name' => 'Impulse Spending Dragon',
                'max_hp' => 1000,
                'current_hp' => 1000,
                'reward_xp' => 1500,
                'status' => 'active',
                'month_year' => $monthYear,
            ]);
        }

        return $boss;
    }

    public function render(ThemeService $themeService)
    {
        $user = \Illuminate\Support\Facades\Auth::user() ?? User::first();
        $themeMode = $themeService->getActiveMode($user);
        $labels = $themeService->getLabels($themeMode);

        $activeCycle = $user ? $user->getActiveBudgetCycle() : null;
        $recentFoodSpent = $user ? (float) $user->expenseTransactions()->where('category', 'food')->where('transaction_date', '>=', now()->subDays(7))->sum('amount') : 0;
        $totalSpentWeek = $user ? (float) $user->expenseTransactions()->where('transaction_date', '>=', now()->subDays(7))->sum('amount') : 0;

        $activeBoss = $user ? $this->getOrCreateBoss($user) : null;

        return view('livewire.challenges-component', [
            'user' => $user,
            'activeCycle' => $activeCycle,
            'recentFoodSpent' => $recentFoodSpent,
            'totalSpentWeek' => $totalSpentWeek,
            'activeBoss' => $activeBoss,
            'themeMode' => $themeMode,
            'labels' => $labels,
        ]);
    }
}
