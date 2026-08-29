<?php

namespace Tests\Feature;

use App\Models\BudgetCycle;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class BankMutasiWebhookTest extends TestCase
{
    use RefreshDatabase;

    public function test_api_mutasi_webhook_auto_deducts_budget_for_debit_transactions(): void
    {
        $user = User::create([
            'name' => 'Bank User',
            'email' => 'bank@example.com',
            'password' => bcrypt('password'),
            'hp_current' => 100,
        ]);

        $budgetCycle = BudgetCycle::create([
            'user_id' => $user->id,
            'name' => 'Test Cycle',
            'period_type' => 'monthly',
            'start_date' => now()->startOfMonth(),
            'end_date' => now()->endOfMonth(),
            'planned_budget' => 2000000.00,
            'spent_amount' => 0.00,
            'hp_level' => 100,
        ]);

        $payload = [
            'user_id' => $user->id,
            'bank' => 'BCA',
            'reference_id' => 'MUTASI-TRX-10029',
            'amount' => 250000.00,
            'type' => 'db',
            'description' => 'QRIS SUPERINDO PAHLAWAN',
            'transaction_date' => now()->toDateTimeString(),
        ];

        $response = $this->postJson('/api/v1/webhooks/api-mutasi', $payload, [
            'X-Api-Mutasi-Signature' => 'financial_planner_secret_token',
        ]);

        $response->assertStatus(200);
        $response->assertJson(['success' => true]);

        $this->assertDatabaseHas('expense_transactions', [
            'user_id' => $user->id,
            'source' => 'bank_webhook',
            'amount' => 250000.00,
        ]);

        $budgetCycle->refresh();
        $this->assertEquals(250000.00, $budgetCycle->spent_amount);
    }
}
