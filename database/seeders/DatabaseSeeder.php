<?php

namespace Database\Seeders;

use App\Models\BudgetCycle;
use App\Models\ExpenseTransaction;
use App\Models\Receipt;
use App\Models\ReceiptItem;
use App\Models\User;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        // 1. Create Default RPG User
        $user = User::firstOrCreate(
            ['email' => 'warrior@financialvault.app'],
            [
                'name' => 'Financial Warrior',
                'password' => bcrypt('password123'),
                'hp_current' => 82, // 82% HP
                'action_points_balance' => 120, // 120 AP
                'current_streak' => 7,
                'longest_streak' => 14,
                'is_penalized' => false,
                'last_activity_at' => now(),
            ]
        );

        // 2. Run Category Seeder
        $this->call(ExpenseCategorySeeder::class);

        // 3. Create Active Monthly Budget Cycle
        $budgetCycle = BudgetCycle::firstOrCreate(
            [
                'user_id' => $user->id,
                'name' => now()->format('F Y') . ' Budget Vault',
            ],
            [
                'period_type' => 'monthly',
                'start_date' => now()->startOfMonth(),
                'end_date' => now()->endOfMonth(),
                'planned_budget' => 5000000.00, // Rp 5.000.000
                'spent_amount' => 900000.00,    // Rp 900.000 spent
                'hp_level' => 82,
                'status' => 'active',
            ]
        );

        // 4. Run Quest Pool Seeder
        $this->call(QuestPoolSeeder::class);

        // 5. Seed Sample OCR Receipt
        if (Receipt::count() === 0) {
            $receipt = Receipt::create([
                'user_id' => $user->id,
                'merchant_name' => 'Super Indo Grocery Store',
                'receipt_number' => 'SI-88492019',
                'ocr_raw_text' => "SUPER INDO GROCERY\nSUPER INDO MILK 1L x2 Rp 49.000\nFRESH EGGS 10S x1 Rp 31.500\nTOTAL: Rp 80.500",
                'total_amount' => 80500.00,
                'transaction_date' => now(),
                'image_path' => 'receipts/sample_superindo.jpg',
                'confidence_score' => 95.80,
            ]);

            ReceiptItem::create([
                'receipt_id' => $receipt->id,
                'item_name' => 'SUPER INDO MILK 1L',
                'quantity' => 2,
                'unit_price' => 24500.00,
                'total_price' => 49000.00,
            ]);

            ReceiptItem::create([
                'receipt_id' => $receipt->id,
                'item_name' => 'FRESH EGGS 10S',
                'quantity' => 1,
                'unit_price' => 31500.00,
                'total_price' => 31500.00,
            ]);

            ExpenseTransaction::create([
                'user_id' => $user->id,
                'budget_cycle_id' => $budgetCycle->id,
                'receipt_id' => $receipt->id,
                'source' => 'receipt_ocr',
                'merchant' => 'Super Indo Grocery Store',
                'amount' => 80500.00,
                'transaction_date' => now()->subHours(4),
                'is_verified' => true,
                'description' => 'OCR Receipt Ingestion Verified',
            ]);
        }

        // 6. Seed Sample Bank Webhook Transaction
        if (ExpenseTransaction::where('source', 'bank_webhook')->count() === 0) {
            ExpenseTransaction::create([
                'user_id' => $user->id,
                'budget_cycle_id' => $budgetCycle->id,
                'source' => 'bank_webhook',
                'merchant' => 'BCA QRIS - PERTAMINA GAS STATION',
                'amount' => 150000.00,
                'transaction_date' => now()->subDay(),
                'is_verified' => true,
                'description' => 'API Mutasi Webhook Transaction Reference #BCA-MUTASI-9021',
            ]);
        }
    }
}
