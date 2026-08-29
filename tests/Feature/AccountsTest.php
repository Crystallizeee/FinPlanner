<?php

namespace Tests\Feature;

use App\Livewire\AccountsComponent;
use App\Models\Account;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use Tests\TestCase;

class AccountsTest extends TestCase
{
    use RefreshDatabase;

    public function test_accounts_page_renders_successfully(): void
    {
        $user = User::factory()->create();

        $response = $this->actingAs($user)->get(route('accounts'));
        $response->assertStatus(200);
    }

    public function test_user_can_create_new_financial_account(): void
    {
        $user = User::factory()->create();

        Livewire::actingAs($user)
            ->test(AccountsComponent::class)
            ->call('openCreateModal')
            ->set('name', 'BCA Tabungan Bisnis')
            ->set('type', 'bank')
            ->set('balance', 25000000)
            ->set('accountNumber', '5520-112-990')
            ->call('createAccount')
            ->assertHasNoErrors()
            ->assertSet('showCreateModal', false);

        $this->assertDatabaseHas('accounts', [
            'user_id' => $user->id,
            'name' => 'BCA Tabungan Bisnis',
            'balance' => 25000000,
        ]);
    }

    public function test_user_can_update_account_balance(): void
    {
        $user = User::factory()->create();
        $account = Account::create([
            'user_id' => $user->id,
            'name' => 'BCA Utama',
            'type' => 'bank',
            'balance' => 10000000,
            'account_number' => '123456',
        ]);

        Livewire::actingAs($user)
            ->test(AccountsComponent::class)
            ->call('openEditModal', $account->id)
            ->set('balance', 18000000)
            ->call('updateAccount')
            ->assertHasNoErrors()
            ->assertSet('showEditModal', false);

        $this->assertDatabaseHas('accounts', [
            'id' => $account->id,
            'balance' => 18000000,
        ]);
    }
}
