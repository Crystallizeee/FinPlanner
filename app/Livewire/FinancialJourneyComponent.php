<?php

declare(strict_types=1);

namespace App\Livewire;

use App\Models\User;
use App\Services\ThemeService;
use Livewire\Attributes\Layout;
use Livewire\Component;

#[Layout('components.layouts.app')]
class FinancialJourneyComponent extends Component
{
    public function render(ThemeService $themeService)
    {
        $user = \Illuminate\Support\Facades\Auth::user() ?? User::first();
        $themeMode = $themeService->getActiveMode($user);
        $labels = $themeService->getLabels($themeMode);

        $userLevel = $user ? $user->level : 1;

        $stages = [
            ['level' => 1, 'title' => 'Financial Rookie', 'icon' => '🌱', 'desc' => 'Mulai mencatat transaksi & membangun kebiasaan awal.'],
            ['level' => 5, 'title' => 'Budget Explorer', 'icon' => '🧭', 'desc' => 'Memahami pola pengeluaran dan membuat budget bulanan.'],
            ['level' => 10, 'title' => 'Money Strategist', 'icon' => '⚔️', 'desc' => 'Menjaga budget 3 bulan berturut-turut & mengoptimalkan tabungan.'],
            ['level' => 20, 'title' => 'Financial Planner', 'icon' => '📜', 'desc' => 'Merencanakan dana darurat 6 bulan & portofolio investasi.'],
            ['level' => 25, 'title' => 'Goal Crusher', 'icon' => '🎯', 'desc' => 'Menyelesaikan 5 financial goals utama.'],
            ['level' => 30, 'title' => 'Wealth Builder', 'icon' => '🏛️', 'desc' => 'Pertumbuhan kekayaan bersih berkesinambungan.'],
            ['level' => 50, 'title' => 'Financial Master', 'icon' => '👑', 'desc' => 'Kebebasan finansial penuh dan otomasi aset.'],
            ['level' => 75, 'title' => 'Wealth Architect', 'icon' => '⚜️', 'desc' => 'Arsitek portofolio dan manajemen risiko tingkat lanjut.'],
            ['level' => 100, 'title' => 'Financial Legend', 'icon' => '🌌', 'desc' => 'Puncak penguasaan finansial dan warisan abadi.'],
        ];

        foreach ($stages as &$stage) {
            $stage['is_current'] = ($userLevel >= $stage['level']);
        }

        return view('livewire.financial-journey-component', [
            'themeMode' => $themeMode,
            'labels' => $labels,
            'stages' => $stages,
        ]);
    }
}
