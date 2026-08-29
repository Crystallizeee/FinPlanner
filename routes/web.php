<?php

use App\Http\Middleware\EnforceFinancialDiscipline;
use App\Livewire\AccountsComponent;
use App\Livewire\AchievementsComponent;
use App\Livewire\ActionPointsAllocator;
use App\Livewire\AnalyticsComponent;
use App\Livewire\Auth\LoginComponent;
use App\Livewire\Auth\RegisterComponent;
use App\Livewire\BudgetComponent;
use App\Livewire\ChallengesComponent;
use App\Livewire\DashboardComponent;
use App\Livewire\FinancialJourneyComponent;
use App\Livewire\GoalsComponent;
use App\Livewire\PortfolioComponent;
use App\Livewire\ProfileComponent;
use App\Livewire\SettingsComponent;
use App\Livewire\TransactionsComponent;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Route;

// Guest authentication routes
Route::middleware(['guest'])->group(function () {
    Route::get('/login', LoginComponent::class)->name('login');
    Route::get('/register', RegisterComponent::class)->name('register');
});

// Authenticated application routes
Route::middleware(['auth'])->group(function () {
    Route::get('/', DashboardComponent::class)->name('dashboard');
    Route::get('/transactions', TransactionsComponent::class)->name('transactions');
    Route::get('/accounts', AccountsComponent::class)->name('accounts');
    Route::get('/budget', BudgetComponent::class)->name('budget');
    Route::get('/goals', GoalsComponent::class)->name('goals');
    Route::get('/challenges', ChallengesComponent::class)->name('challenges');
    Route::get('/achievements', AchievementsComponent::class)->name('achievements');
    Route::get('/journey', FinancialJourneyComponent::class)->name('journey');
    Route::get('/profile', ProfileComponent::class)->name('profile');
    Route::get('/settings', SettingsComponent::class)->name('settings');

    // Legacy routes alias
    Route::get('/allocator', ActionPointsAllocator::class)->name('allocator');

    Route::middleware([EnforceFinancialDiscipline::class])->group(function () {
        Route::get('/analytics', AnalyticsComponent::class)->name('analytics');
        Route::get('/portfolio', PortfolioComponent::class)->name('portfolio');
    });

    // Logout Route
    Route::post('/logout', function () {
        Auth::logout();
        request()->session()->invalidate();
        request()->session()->regenerateToken();
        return redirect()->route('login');
    })->name('logout');
});
