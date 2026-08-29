<?php

declare(strict_types=1);

namespace App\Livewire;

use App\Models\Wishlist;
use App\Models\User;
use App\Services\ThemeService;
use Livewire\Attributes\Layout;
use Livewire\Component;

#[Layout('components.layouts.app')]
class WishlistComponent extends Component
{
    public ?User $user = null;

    public string $item_name = '';
    public string $price = '';
    public string $category = 'lifestyle';
    public int $cooling_off_days = 30;
    public ?int $selectedAccountId = null;
    public string $successMessage = '';

    protected array $rules = [
        'item_name' => 'required|string|max:100',
        'price' => 'required|numeric|min:1000',
        'cooling_off_days' => 'required|integer|between:1,90',
    ];

    public function mount(): void
    {
        $this->user = \Illuminate\Support\Facades\Auth::user() ?? User::first();
        if ($this->user) {
            $firstAccount = $this->user->accounts()->first();
            $this->selectedAccountId = $firstAccount?->id;
        }
    }

    public function addWishlist(): void
    {
        $this->validate();
        /** @var User $user */
        $user = \Illuminate\Support\Facades\Auth::user();
        if (!$user) {
            return;
        }

        $unlockDate = now()->addDays($this->cooling_off_days);

        $user->wishlists()->create([
            'item_name' => $this->item_name,
            'price' => (float) $this->price,
            'category' => $this->category,
            'cooling_off_days' => $this->cooling_off_days,
            'unlock_at' => $unlockDate,
            'is_purchased' => false,
        ]);

        $this->successMessage = "Item '{$this->item_name}' dimasukkan ke Cooling-off Matrix! Kunci akan terbuka pada " . $unlockDate->format('d M Y') . ".";
        $this->reset(['item_name', 'price']);
    }

    public function purchaseItem(int $id): void
    {
        /** @var User $user */
        $user = \Illuminate\Support\Facades\Auth::user();
        if (!$user) {
            return;
        }

        /** @var Wishlist|null $item */
        $item = $user->wishlists()->find($id);
        if (!$item || !$item->isUnlocked() || $item->is_purchased) {
            return;
        }

        $account = $user->accounts()->find($this->selectedAccountId) ?? $user->accounts()->first();
        if ($account) {
            $account->deductBalance((float) $item->price);
        }

        $item->update(['is_purchased' => true]);

        $this->successMessage = "Selamat! Pembelian '{$item->item_name}' seharga Rp " . number_format((float)$item->price, 0, ',', '.') . " berhasil dicatat!";
    }

    public function deleteItem(int $id): void
    {
        /** @var User $user */
        $user = \Illuminate\Support\Facades\Auth::user();
        if ($user) {
            $user->wishlists()->where('id', $id)->delete();
            $this->successMessage = "Item wishlist berhasil dihapus.";
        }
    }

    public function render(ThemeService $themeService)
    {
        $themeMode = $themeService->getActiveMode($this->user);
        $labels = $themeService->getLabels($themeMode);

        $items = $this->user ? $this->user->wishlists()->orderBy('unlock_at', 'asc')->get() : collect();
        $accounts = $this->user ? $this->user->accounts()->get() : collect();

        return view('livewire.wishlist-component', [
            'items' => $items,
            'accounts' => $accounts,
            'themeMode' => $themeMode,
            'labels' => $labels,
        ]);
    }
}
