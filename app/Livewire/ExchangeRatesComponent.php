<?php

declare(strict_types=1);

namespace App\Livewire;

use App\Models\User;
use App\Services\ThemeService;
use Livewire\Attributes\Layout;
use Livewire\Component;

#[Layout('components.layouts.app')]
class ExchangeRatesComponent extends Component
{
    public ?User $user = null;

    // Currency Calculator State
    public float $amountForeign = 100;
    public string $currencyCode = 'USD';

    // Gold Calculator State
    public float $goldGram = 10;
    public float $goldBuyPricePerGram = 1350000;
    public float $goldCurrentMarketPricePerGram = 1450000;

    protected array $exchangeRates = [
        'USD' => 15850,
        'EUR' => 17200,
        'SGD' => 11800,
        'JPY' => 105.5,
        'AUD' => 10400,
    ];

    public function mount(): void
    {
        $this->user = \Illuminate\Support\Facades\Auth::user() ?? User::first();
    }

    public function render(ThemeService $themeService)
    {
        $themeMode = $themeService->getActiveMode($this->user);
        $labels = $themeService->getLabels($themeMode);

        $rate = $this->exchangeRates[$this->currencyCode] ?? 15850;
        $convertedIdr = $this->amountForeign * $rate;

        // Gold calculations
        $totalGoldCost = $this->goldGram * $this->goldBuyPricePerGram;
        $totalGoldValue = $this->goldGram * $this->goldCurrentMarketPricePerGram;
        $goldPnL = $totalGoldValue - $totalGoldCost;
        $goldPnLPct = $totalGoldCost > 0 ? ($goldPnL / $totalGoldCost) * 100 : 0;

        return view('livewire.exchange-rates-component', [
            'exchangeRates' => $this->exchangeRates,
            'rate' => $rate,
            'convertedIdr' => $convertedIdr,
            'totalGoldCost' => $totalGoldCost,
            'totalGoldValue' => $totalGoldValue,
            'goldPnL' => $goldPnL,
            'goldPnLPct' => $goldPnLPct,
            'themeMode' => $themeMode,
            'labels' => $labels,
        ]);
    }
}
