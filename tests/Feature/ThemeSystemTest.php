<?php

declare(strict_types=1);

namespace Tests\Feature;

use App\Livewire\ThemeSwitcher;
use App\Models\User;
use App\Services\ThemeService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use Tests\TestCase;

class ThemeSystemTest extends TestCase
{
    use RefreshDatabase;

    public function test_default_theme_is_fintech(): void
    {
        $user = User::factory()->create(['theme_mode' => 'fintech']);
        $themeService = app(ThemeService::class);

        $activeMode = $themeService->getActiveMode($user);
        $this->assertEquals('fintech', $activeMode);

        $labels = $themeService->getLabels('fintech');
        $this->assertEquals('FINTECH MINIMAL', $labels['brand_name']);
        $this->assertArrayHasKey('xp_name', $labels);
    }

    public function test_can_switch_theme_mode_via_theme_service(): void
    {
        $user = User::factory()->create(['theme_mode' => 'fintech']);
        $themeService = app(ThemeService::class);

        $themeService->setMode('cyber', $user);

        $user->refresh();
        $this->assertEquals('cyber', $user->theme_mode);

        $labels = $themeService->getLabels('cyber');
        $this->assertEquals('CYBER FINANCE', $labels['brand_name']);
    }

    public function test_theme_switcher_component_cycles_themes(): void
    {
        $user = User::factory()->create(['theme_mode' => 'fintech']);

        Livewire::test(ThemeSwitcher::class)
            ->assertSet('currentMode', 'fintech')
            ->call('cycleTheme')
            ->assertSet('currentMode', 'cyber');

        $user->refresh();
        $this->assertEquals('cyber', $user->theme_mode);
    }
}
