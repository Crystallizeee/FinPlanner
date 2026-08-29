<?php

declare(strict_types=1);

namespace App\Livewire;

use App\Models\User;
use App\Services\ThemeService;
use Livewire\Attributes\Layout;
use Livewire\Component;

#[Layout('components.layouts.app')]
class SettingsComponent extends Component
{
    public bool $enableXp = true;
    public bool $enableChallenges = true;
    public bool $enableStreaks = true;
    public bool $enableNotifications = true;
    public bool $enableAnimations = true;

    public function selectTheme(string $themeId, ThemeService $themeService): void
    {
        $user = \Illuminate\Support\Facades\Auth::user() ?? User::first();
        $themeService->setMode($themeId, $user);
        $this->dispatch('themeChanged');
        $this->redirect(request()->header('Referer') ?? route('settings'));
    }

    public function render(ThemeService $themeService)
    {
        $user = \Illuminate\Support\Facades\Auth::user() ?? User::first();
        $currentTheme = $themeService->getActiveMode($user);
        $availableThemes = $themeService->getAvailableThemes();
        $labels = $themeService->getLabels($currentTheme);

        return view('livewire.settings-component', [
            'currentTheme' => $currentTheme,
            'availableThemes' => $availableThemes,
            'labels' => $labels,
        ]);
    }
}
