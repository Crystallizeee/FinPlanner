<?php

namespace Database\Seeders;

use App\Models\ExpenseCategory;
use Illuminate\Database\Seeder;
use Illuminate\Support\Str;

class ExpenseCategorySeeder extends Seeder
{
    public function run(): void
    {
        $categories = [
            ['name' => 'Supermarket & Groceries', 'icon' => '🛒'],
            ['name' => 'Vehicle & Fuel (CB150R)', 'icon' => '🏍️'],
            ['name' => 'Bills & Utilities', 'icon' => '⚡'],
            ['name' => 'Health & Medical', 'icon' => '💊'],
            ['name' => 'Investment & Stocks', 'icon' => '📈'],
        ];

        foreach ($categories as $cat) {
            ExpenseCategory::firstOrCreate(
                ['code' => Str::slug($cat['name'])],
                [
                    'name' => $cat['name'],
                    'icon' => $cat['icon'],
                    'budget_allocation' => 1000000.00,
                ]
            );
        }
    }
}
