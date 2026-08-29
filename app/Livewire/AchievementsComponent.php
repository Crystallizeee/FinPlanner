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

        $achievements = [
            [
                'title' => 'FIRST STEP',
                'description' => 'Record your first transaction.',
                'icon' => '🚀',
                'category' => 'Getting Started',
                'is_unlocked' => true,
                'progress' => '1 / 1',
                'reward' => '+100 XP',
            ],
            [
                'title' => 'BUDGET GUARDIAN',
                'description' => 'Stay within budget for 1 month.',
                'icon' => '🛡️',
                'category' => 'Budget',
                'is_unlocked' => true,
                'progress' => '1 / 1 month',
                'reward' => '+500 XP',
            ],
            [
                'title' => 'BUDGET MASTER',
                'description' => 'Stay within budget for 3 consecutive months.',
                'icon' => '👑',
                'category' => 'Budget',
                'is_unlocked' => false,
                'progress' => '2 / 3 months',
                'reward' => '+2,500 XP',
            ],
            [
                'title' => 'SAVER',
                'description' => 'Save your first Rp1.000.000.',
                'icon' => '💰',
                'category' => 'Saving',
                'is_unlocked' => true,
                'progress' => 'Rp1.000.000',
                'reward' => '+300 XP',
            ],
            [
                'title' => 'SUPER SAVER',
                'description' => 'Save Rp10.000.000.',
                'icon' => '🏦',
                'category' => 'Saving',
                'is_unlocked' => false,
                'progress' => 'Rp2.75M / Rp10M',
                'reward' => '+1,500 XP',
            ],
            [
                'title' => 'GOAL CRUSHER',
                'description' => 'Complete your first financial goal.',
                'icon' => '🎯',
                'category' => 'Goals',
                'is_unlocked' => true,
                'progress' => '1 Goal',
                'reward' => '+1,000 XP',
            ],
            [
                'title' => 'CONSISTENCY',
                'description' => 'Maintain a 30-day streak.',
                'icon' => '🔥',
                'category' => 'Consistency',
                'is_unlocked' => false,
                'progress' => '14 / 30 days',
                'reward' => '+1,000 XP',
            ],
            [
                'title' => 'DATA ANALYST',
                'description' => 'Review financial analytics 10 times.',
                'icon' => '📈',
                'category' => 'Financial Intelligence',
                'is_unlocked' => true,
                'progress' => '10 / 10',
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
