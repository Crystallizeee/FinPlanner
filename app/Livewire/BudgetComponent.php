<?php

declare(strict_types=1);

namespace App\Livewire;

use App\Models\User;
use App\Services\ThemeService;
use Livewire\Attributes\Layout;
use Livewire\Component;

#[Layout('components.layouts.app')]
class BudgetComponent extends Component
{
    public bool $showCreateModal = false;
    public string $newCategoryName = '';
    public string $newCategoryLimit = '';
    public string $newCategoryIcon = '📁';
    public ?string $successMessage = null;

    protected array $rules = [
        'newCategoryName' => 'required|string|min:3|max:50',
        'newCategoryLimit' => 'required|numeric|min:10000',
    ];

    public function openModal(): void
    {
        $this->reset(['newCategoryName', 'newCategoryLimit', 'newCategoryIcon', 'successMessage']);
        $this->showCreateModal = true;
    }

    public function closeModal(): void
    {
        $this->showCreateModal = false;
    }

    public function createBudget(): void
    {
        $this->validate();

        $customCategories = session('custom_budget_categories', []);
        $customCategories[] = [
            'name' => $this->newCategoryName,
            'spent' => 0,
            'limit' => (int) $this->newCategoryLimit,
            'icon' => $this->newCategoryIcon ?: '📁',
        ];

        session(['custom_budget_categories' => $customCategories]);

        $this->successMessage = 'Alokasi budget "' . $this->newCategoryName . '" berhasil ditambahkan!';
        $this->reset(['newCategoryName', 'newCategoryLimit', 'newCategoryIcon']);
        $this->showCreateModal = false;
    }

    public function render(ThemeService $themeService)
    {
        $user = \Illuminate\Support\Facades\Auth::user() ?? User::first();
        $themeMode = $themeService->getActiveMode($user);
        $labels = $themeService->getLabels($themeMode);

        $defaultCategories = [
            [
                'name' => 'Food & Groceries',
                'spent' => 1200000,
                'limit' => 1500000,
                'icon' => '🍲',
            ],
            [
                'name' => 'Transportation & Fuel',
                'spent' => 700000,
                'limit' => 800000,
                'icon' => '🚗',
            ],
            [
                'name' => 'Entertainment & Hobbies',
                'spent' => 900000,
                'limit' => 700000,
                'icon' => '🎮',
            ],
            [
                'name' => 'Utilities & Internet',
                'spent' => 450000,
                'limit' => 500000,
                'icon' => '⚡',
            ],
            [
                'name' => 'Health & Personal Care',
                'spent' => 500000,
                'limit' => 1500000,
                'icon' => '💊',
            ],
        ];

        $customCategories = session('custom_budget_categories', []);
        $categories = array_merge($defaultCategories, $customCategories);

        return view('livewire.budget-component', [
            'themeMode' => $themeMode,
            'labels' => $labels,
            'categories' => $categories,
        ]);
    }
}
