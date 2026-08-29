<?php

declare(strict_types=1);

namespace App\Livewire;

use App\Models\User;
use App\Services\ThemeService;
use Livewire\Component;

class ThemeSwitcher extends Component
{
    public string $currentMode = ThemeService::THEME_FINTECH;

    public function mount(ThemeService $themeService): void
    {
        $user = \Illuminate\Support\Facades\Auth::user() ?? User::first();
        $this->currentMode = $themeService->getActiveMode($user);
    }

    public function cycleTheme(ThemeService $themeService): void
    {
        $user = \Illuminate\Support\Facades\Auth::user() ?? User::first();

        $order = [
            ThemeService::THEME_FINTECH,
            ThemeService::THEME_CYBER,
            ThemeService::THEME_GAMEFUL,
            ThemeService::THEME_WEALTH,
        ];

        $currentIndex = array_search($this->currentMode, $order, true);
        $nextIndex = ($currentIndex === false || $currentIndex === count($order) - 1) ? 0 : $currentIndex + 1;
        $nextMode = $order[$nextIndex];

        $themeService->setMode($nextMode, $user);
        $this->currentMode = $nextMode;

        $this->redirect(request()->header('Referer', route('dashboard')), navigate: false);
    }

    public function render()
    {
        return view('livewire.theme-switcher');
    }
}
