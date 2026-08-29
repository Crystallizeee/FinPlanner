@php
    $tokens = app(\App\Services\ThemeService::class)->getThemeTokens($themeMode);
@endphp

<div class="space-y-8">
    <!-- Header -->
    <div class="glass-panel rounded-3xl p-6 sm:p-8 border border-slate-800 flex flex-col md:flex-row md:items-center justify-between gap-4">
        <div>
            <div class="flex items-center space-x-2">
                <span class="text-2xl">💱</span>
                <h1 class="font-display font-black text-2xl md:text-3xl text-white">
                    Multi-Currency & Precious Gold Metals Valuation
                </h1>
            </div>
            <p class="text-xs sm:text-sm text-slate-400 mt-1">
                Kalkulator mata uang asing & penilaian investasi Emas Antam real-time.
            </p>
        </div>
    </div>

    <!-- Main Grid: Currency Converter vs Gold Calculator -->
    <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
        <!-- Currency Converter Box -->
        <div class="p-6 sm:p-8 rounded-3xl {{ $tokens['card_bg'] }} border border-slate-800 space-y-6">
            <div class="flex items-center space-x-3">
                <span class="text-2xl">🌍</span>
                <div>
                    <h3 class="{{ $tokens['font_heading'] }} text-base text-white">Kalkulator Kurs Mata Uang Asing</h3>
                    <p class="text-xs text-slate-400">Hitung estimasi konversi valuta asing ke Rupiah.</p>
                </div>
            </div>

            <div class="space-y-4 text-xs">
                <div class="grid grid-cols-2 gap-3">
                    <div>
                        <label class="block text-slate-300 font-bold mb-1">Mata Uang</label>
                        <select wire:model.live="currencyCode" class="w-full p-3 rounded-xl bg-slate-900 border border-slate-700 text-white font-bold focus:outline-none focus:border-cyan-400">
                            @foreach($exchangeRates as $code => $r)
                                <option value="{{ $code }}">{{ $code }} (Rate: Rp {{ number_format($r, 1, ',', '.') }})</option>
                            @endforeach
                        </select>
                    </div>

                    <div>
                        <label class="block text-slate-300 font-bold mb-1">Jumlah Valas</label>
                        <input type="number" wire:model.live="amountForeign" min="0" class="w-full p-3 rounded-xl bg-slate-900 border border-slate-700 text-white font-mono focus:outline-none focus:border-cyan-400">
                    </div>
                </div>

                <div class="p-5 rounded-2xl bg-slate-950/80 border border-slate-800 text-center font-mono space-y-1">
                    <div class="text-slate-400 text-[11px]">HASIL KONVERSI KE RUPIAH:</div>
                    <div class="text-3xl font-black text-emerald-400">
                        Rp {{ number_format($convertedIdr, 0, ',', '.') }}
                    </div>
                    <div class="text-[10px] text-slate-500">1 {{ $currencyCode }} = Rp {{ number_format($rate, 1, ',', '.') }}</div>
                </div>
            </div>
        </div>

        <!-- Gold Metal Valuation Box -->
        <div class="p-6 sm:p-8 rounded-3xl bg-gradient-to-br from-amber-950/40 via-slate-900 to-slate-950 border border-amber-500/40 space-y-6 shadow-2xl">
            <div class="flex items-center space-x-3">
                <span class="text-2xl">🪙</span>
                <div>
                    <h3 class="{{ $tokens['font_heading'] }} text-base text-white">Penilaian Investasi Emas Logam Mulia</h3>
                    <p class="text-xs text-slate-400">Kalkulasi nilai pasar & keuntungan (PnL) aset emas fisik.</p>
                </div>
            </div>

            <div class="space-y-4 text-xs">
                <div class="grid grid-cols-3 gap-2">
                    <div>
                        <label class="block text-slate-300 font-bold mb-1">Berat (Gram)</label>
                        <input type="number" step="0.1" wire:model.live="goldGram" class="w-full p-3 rounded-xl bg-slate-900 border border-slate-700 text-white font-mono focus:outline-none focus:border-amber-400">
                    </div>

                    <div>
                        <label class="block text-slate-300 font-bold mb-1">Harga Beli/Gram</label>
                        <input type="number" step="10000" wire:model.live="goldBuyPricePerGram" class="w-full p-3 rounded-xl bg-slate-900 border border-slate-700 text-white font-mono focus:outline-none focus:border-amber-400">
                    </div>

                    <div>
                        <label class="block text-slate-300 font-bold mb-1">Harga Pasar/Gram</label>
                        <input type="number" step="10000" wire:model.live="goldCurrentMarketPricePerGram" class="w-full p-3 rounded-xl bg-slate-900 border border-slate-700 text-white font-mono focus:outline-none focus:border-amber-400">
                    </div>
                </div>

                <div class="p-5 rounded-2xl bg-slate-950/90 border border-amber-500/30 space-y-3 font-mono">
                    <div class="flex justify-between text-slate-300">
                        <span>Total Modal Pembelian:</span>
                        <span class="font-bold text-white">Rp {{ number_format($totalGoldCost, 0, ',', '.') }}</span>
                    </div>

                    <div class="flex justify-between text-slate-300">
                        <span>Nilai Pasar Emas Saat Ini:</span>
                        <span class="font-bold text-amber-300">Rp {{ number_format($totalGoldValue, 0, ',', '.') }}</span>
                    </div>

                    <div class="flex justify-between text-slate-300 pt-2 border-t border-slate-800">
                        <span>Unrealized PnL (Profit/Loss):</span>
                        <span class="{{ $goldPnL >= 0 ? 'text-emerald-400' : 'text-rose-400' }} font-black text-sm">
                            {{ $goldPnL >= 0 ? '+' : '' }}Rp {{ number_format($goldPnL, 0, ',', '.') }} ({{ number_format($goldPnLPct, 2) }}%)
                        </span>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
