<?php

namespace Tests\Feature;

use App\Livewire\AccountsComponent;
use App\Livewire\Auth\LoginComponent;
use App\Livewire\Auth\RegisterComponent;
use App\Models\Account;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Auth;

use Livewire\Livewire;
use Tests\TestCase;

class AuthAndMultiTenancyTest extends TestCase
{
    use RefreshDatabase;

    public function test_guest_is_redirected_to_login(): void
    {
        $response = $this->get(route('dashboard'));
        $response->assertRedirect(route('login'));
    }

    public function test_user_can_register(): void
    {
        Livewire::test(RegisterComponent::class)
            ->set('name', 'Budi Santoso')
            ->set('email', 'budi@example.com')
            ->set('password', 'password123')
            ->set('passwordConfirmation', 'password123')
            ->call('register')
            ->assertHasNoErrors()
            ->assertRedirect(route('dashboard'));

        $this->assertDatabaseHas('users', [
            'email' => 'budi@example.com',
            'name' => 'Budi Santoso',
        ]);

        $this->assertTrue(Auth::check());
    }

    public function test_user_can_login(): void
    {
        $user = User::factory()->create([
            'email' => 'siti@example.com',
            'password' => bcrypt('secret123'),
        ]);

        Livewire::test(LoginComponent::class)
            ->set('email', 'siti@example.com')
            ->set('password', 'secret123')
            ->call('login')
            ->assertHasNoErrors()
            ->assertRedirect(route('dashboard'));

        $this->assertTrue(Auth::check());
        $this->assertEquals($user->id, Auth::id());
    }

    public function test_user_cannot_see_or_modify_other_user_account(): void
    {
        $userA = User::factory()->create();
        $userB = User::factory()->create();

        $accountA = Account::create([
            'user_id' => $userA->id,
            'name' => 'User A Account',
            'type' => 'bank',
            'balance' => 5000000,
        ]);

        $accountB = Account::create([
            'user_id' => $userB->id,
            'name' => 'User B Account',
            'type' => 'bank',
            'balance' => 10000000,
        ]);

        // User A tests
        Livewire::actingAs($userA)
            ->test(AccountsComponent::class)
            ->assertSee('User A Account')
            ->assertDontSee('User B Account');

        // User A attempts to edit User B's account
        Livewire::actingAs($userA)
            ->test(AccountsComponent::class)
            ->call('openEditModal', $accountB->id)
            ->set('balance', 99999999)
            ->call('updateAccount');

        // Account B's balance must remain untouched (User A cannot modify it)
        $this->assertEquals(10000000, $accountB->fresh()->balance);

        // User A attempts to delete User B's account
        Livewire::actingAs($userA)
            ->test(AccountsComponent::class)
            ->call('deleteAccount', $accountB->id);

        // Account B must still exist in DB
        $this->assertDatabaseHas('accounts', [
            'id' => $accountB->id,
        ]);
    }

    public function test_user_can_create_and_deposit_to_goals(): void
    {
        $user = User::factory()->create();
        $account = Account::create([
            'user_id' => $user->id,
            'name' => 'BCA Savings',
            'type' => 'bank',
            'balance' => 50000000,
        ]);

        Livewire::actingAs($user)
            ->test(\App\Livewire\GoalsComponent::class)
            ->call('openCreateGoalModal')
            ->set('name', 'Beli Rumah Impian')
            ->set('targetAmount', 500000000)
            ->set('category', 'investment')
            ->set('accountId', $account->id)
            ->set('icon', '🏡')
            ->call('createGoal')
            ->assertHasNoErrors();

        $goal = $user->questPools()->where('name', 'Beli Rumah Impian')->first();
        $this->assertNotNull($goal);
        $this->assertEquals(500000000, $goal->target_amount);
        $this->assertEquals($account->id, $goal->account_id);

        Livewire::actingAs($user)
            ->test(\App\Livewire\GoalsComponent::class)
            ->call('openDeposit', $goal->id)
            ->set('depositAccountId', $account->id)
            ->set('depositAmount', 10000000)
            ->call('deposit')
            ->assertHasNoErrors();

        $this->assertEquals(10000000, $goal->fresh()->current_amount);
        $this->assertEquals(40000000, $account->fresh()->balance);
    }

    public function test_allocator_renders_successfully(): void
    {
        $user = User::factory()->create();

        Livewire::actingAs($user)
            ->test(\App\Livewire\ActionPointsAllocator::class)
            ->assertStatus(200)
            ->assertSee('Action Points');
    }
}
