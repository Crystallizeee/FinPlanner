<?php

declare(strict_types=1);

namespace App\Livewire\Auth;

use App\Models\User;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Validate;
use Livewire\Component;

#[Layout('components.layouts.guest')]
class RegisterComponent extends Component
{
    #[Validate('required|string|min:2|max:100')]
    public string $name = '';

    #[Validate('required|string|email|max:255|unique:users,email')]
    public string $email = '';

    #[Validate('required|string|min:8')]
    public string $password = '';

    #[Validate('required|string|same:password')]
    public string $passwordConfirmation = '';

    public function register(): mixed
    {
        $this->validate();

        $user = User::create([
            'name' => $this->name,
            'email' => $this->email,
            'password' => Hash::make($this->password),
            'hp_current' => 100,
            'action_points_balance' => 100,
            'current_streak' => 1,
            'longest_streak' => 1,
            'theme_mode' => 'cyberpunk',
        ]);

        // Seed initial default wallets for new user
        $user->accounts()->createMany([
            [
                'name' => 'Bank BCA Utama',
                'type' => 'bank',
                'balance' => 5000000.00,
                'icon' => '🏦',
                'account_number' => '8839-XXXX-001',
            ],
            [
                'name' => 'GoPay / E-Wallet',
                'type' => 'ewallet',
                'balance' => 250000.00,
                'icon' => '📱',
                'account_number' => '0812-XXXX-000',
            ],
            [
                'name' => 'Cash / Dompet Fisik',
                'type' => 'cash',
                'balance' => 300000.00,
                'icon' => '💵',
                'account_number' => 'Fisik Saku',
            ],
        ]);

        Auth::login($user);
        session()->regenerate();

        session()->flash('global_success', 'Selamat datang! Akun keuangan Anda berhasil dibuat.');

        return redirect()->route('dashboard');
    }

    public function render()
    {
        return view('livewire.auth.register-component');
    }
}
