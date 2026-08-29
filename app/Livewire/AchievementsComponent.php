<?php

declare(strict_types=1);

namespace App\Livewire;

use App\Models\User;
use App\Services\ThemeService;
use Livewire\Attributes\Layout;
use Livewire\Component;

#[Layout('components.layouts.app')]
class AchievementsComponent extends Component
{
    public string $selectedCategory = 'all';

    public function render(ThemeService $themeService)
    {
        $user = \Illuminate\Support\Facades\Auth::user() ?? User::first();
        $themeMode = $themeService->getActiveMode($user);
        $labels = $themeService->getLabels($themeMode);

        $totalTransactions = $user ? $user->expenseTransactions()->count() : 0;
        $totalSavings = $user ? (float) $user->accounts()->sum('balance') : 0;
        $completedGoals = $user ? $user->questPools()->get()->filter(fn($p) => $p->current_amount >= $p->target_amount)->count() : 0;
        $ocrScans = $user ? $user->expenseTransactions()->where('source', 'receipt_ocr')->count() : 0;
        $streakDays = $user ? $user->current_streak : 0;
        $completedCycles = $user ? $user->budgetCycles()->where('status', 'completed')->count() : 0;

        $achievements = [
            [
                'title' => 'FIRST STEP',
                'description' => 'Record your first transaction.',
                'icon' => '🚀',
                'category' => 'Getting Started',
                'is_unlocked' => $totalTransactions >= 1,
                'progress' => min(1, $totalTransactions) . ' / 1',
                'reward' => '+100 XP',
            ],
            [
                'title' => 'BUDGET GUARDIAN',
                'description' => 'Stay within budget for 1 month.',
                'icon' => '🛡️',
                'category' => 'Budget',
                'is_unlocked' => $completedCycles >= 1,
                'progress' => min(1, $completedCycles) . ' / 1 month',
                'reward' => '+500 XP',
            ],
            [
                'title' => 'BUDGET MASTER',
                'description' => 'Stay within budget for 3 consecutive months.',
                'icon' => '👑',
                'category' => 'Budget',
                'is_unlocked' => $completedCycles >= 3,
                'progress' => min(3, $completedCycles) . ' / 3 months',
                'reward' => '+2,500 XP',
            ],
            [
                'title' => 'SAVER',
                'description' => 'Save your first Rp1.000.000.',
                'icon' => '💰',
                'category' => 'Saving',
                'is_unlocked' => $totalSavings >= 1000000,
                'progress' => 'Rp ' . number_format(min(1000000, $totalSavings), 0, ',', '.') . ' / Rp 1M',
                'reward' => '+300 XP',
            ],
            [
                'title' => 'SUPER SAVER',
                'description' => 'Save Rp10.000.000.',
                'icon' => '🏦',
                'category' => 'Saving',
                'is_unlocked' => $totalSavings >= 10000000,
                'progress' => 'Rp ' . number_format(min(10000000, $totalSavings), 0, ',', '.') . ' / Rp 10M',
                'reward' => '+1,500 XP',
            ],
            [
                'title' => 'GOAL CRUSHER',
                'description' => 'Complete your first financial goal.',
                'icon' => '🎯',
                'category' => 'Goals',
                'is_unlocked' => $completedGoals >= 1,
                'progress' => $completedGoals . ' Goal(s)',
                'reward' => '+1,000 XP',
            ],
            [
                'title' => 'CONSISTENCY',
                'description' => 'Maintain a 30-day streak.',
                'icon' => '🔥',
                'category' => 'Consistency',
                'is_unlocked' => $streakDays >= 30,
                'progress' => min(30, $streakDays) . ' / 30 days',
                'reward' => '+1,000 XP',
            ],
            [
                'title' => 'DATA ANALYST',
                'description' => 'Review receipt scans or OCR transactions.',
                'icon' => '📈',
                'category' => 'Financial Intelligence',
                'is_unlocked' => $ocrScans >= 1,
                'progress' => $ocrScans . ' Scan(s)',
                'reward' => '+250 XP',
            ],
        ];

        return view('livewire.achievements-component', [
            'themeMode' => $themeMode,
            'labels' => $labels,
            'achievements' => $achievements,
        ]);
    }
}
