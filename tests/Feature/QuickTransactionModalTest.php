<?php

namespace Tests\Feature;

use App\Livewire\QuickTransactionModal;
use App\Models\Account;
use App\Models\ExpenseTransaction;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use Tests\TestCase;

class QuickTransactionModalTest extends TestCase
{
    use RefreshDatabase;

    public function test_user_can_record_transaction_and_gain_xp(): void
    {
        $user = User::factory()->create([
            'action_points_balance' => 100,
        ]);

        Livewire::actingAs($user)
            ->test(QuickTransactionModal::class)
            ->call('open')
            ->set('amount', 75000)
            ->set('merchant', 'Starbucks Coffee')
            ->set('category', 'Makanan & Minuman')
            ->set('account', 'Bank BCA Gaji')
            ->call('saveTransaction')
            ->assertHasNoErrors()
            ->assertSet('openAddModal', false);

        $this->assertDatabaseHas('expense_transactions', [
            'user_id' => $user->id,
            'merchant' => 'Starbucks Coffee',
            'amount' => 75000,
        ]);

        $this->assertEquals(110, $user->fresh()->action_points_balance);
    }

    public function test_user_can_transfer_funds_between_accounts(): void
    {
        $user = User::factory()->create([
            'action_points_balance' => 100,
        ]);

        $bca = Account::create([
            'user_id' => $user->id,
            'name' => 'Bank BCA Gaji',
            'type' => 'bank',
            'balance' => 14500000,
        ]);

        $gopay = Account::create([
            'user_id' => $user->id,
            'name' => 'GoPay / GoTo',
            'type' => 'ewallet',
            'balance' => 450000,
        ]);

        Livewire::actingAs($user)
            ->test(QuickTransactionModal::class)
            ->call('open')
            ->set('type', 'transfer')
            ->set('amount', 100000)
            ->set('sourceAccount', 'Bank BCA Gaji')
            ->set('targetAccount', 'GoPay / GoTo')
            ->call('saveTransaction')
            ->assertHasNoErrors()
            ->assertSet('openAddModal', false);

        $this->assertDatabaseHas('expense_transactions', [
            'user_id' => $user->id,
            'merchant' => 'Transfer: Bank BCA Gaji ➔ GoPay / GoTo',
            'amount' => 100000,
        ]);

        $this->assertEquals(14400000, $bca->fresh()->balance);
        $this->assertEquals(550000, $gopay->fresh()->balance);
    }
}
