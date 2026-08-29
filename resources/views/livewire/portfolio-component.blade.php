@php
    $tokens = app(\App\Services\ThemeService::class)->getThemeTokens($themeMode);
@endphp

<div class="space-y-8">
    <!-- Header -->
    <div class="glass-panel rounded-3xl p-6 sm:p-8 border border-slate-800 flex flex-col md:flex-row md:items-center justify-between gap-4">
        <div>
            <div class="flex items-center space-x-2">
                <span class="text-2xl">{{ $labels['icon_portfolio'] ?? '🏛️' }}</span>
                <h1 class="font-display font-black text-2xl md:text-3xl text-white">
                    Net Worth & Investment Assets Portfolio
                </h1>
            </div>
            <p class="text-xs sm:text-sm text-slate-400 mt-1">
                Kekayaan bersih gabungan: Rekening Kas + Dana Goal Tabungan + Portofolio Investasi (Saham, Reksa Dana, Emas).
            </p>
        </div>

        <div class="px-6 py-4 rounded-2xl bg-gradient-to-r from-emerald-500/20 via-cyan-500/20 to-teal-500/20 border border-emerald-500/30 text-emerald-300 text-xs font-bold font-mono text-right">
            <div class="text-[10px] text-slate-400 uppercase">TOTAL NET WORTH</div>
            <div class="text-xl font-black text-white">Rp {{ number_format((float)$totalNetWorth, 0, ',', '.') }}</div>
        </div>
    </div>

    @if ($successMessage)
        <div class="p-4 rounded-2xl bg-emerald-950/80 border border-emerald-500/50 text-emerald-300 text-xs font-semibold">
            {{ $successMessage }}
        </div>
    @endif

    <!-- Breakdown Summary Cards -->
    <div class="grid grid-cols-1 md:grid-cols-3 gap-4 font-mono text-xs">
        <div class="p-5 rounded-3xl bg-slate-900/90 border border-slate-800 space-y-1">
            <div class="text-slate-400">💵 Saldo Rekening Likuid</div>
            <div class="text-lg font-bold text-cyan-400">Rp {{ number_format((float)$liquidBalance, 0, ',', '.') }}</div>
        </div>
        <div class="p-5 rounded-3xl bg-slate-900/90 border border-slate-800 space-y-1">
            <div class="text-slate-400">🎯 Savings Goals (Quest Pools)</div>
            <div class="text-lg font-bold text-emerald-400">Rp {{ number_format((float)$goalsBalance, 0, ',', '.') }}</div>
        </div>
        <div class="p-5 rounded-3xl bg-slate-900/90 border border-slate-800 space-y-1">
            <div class="text-slate-400">📈 Aset Investasi (Saham/Emas/Crypt)</div>
            <div class="text-lg font-bold text-amber-400">Rp {{ number_format((float)$investmentsValue, 0, ',', '.') }}</div>
        </div>
    </div>

    <!-- Investment Asset Panel -->
    <div class="grid grid-cols-1 lg:grid-cols-12 gap-6">
        <!-- Form Add Asset -->
        <div class="lg:col-span-5 p-6 rounded-3xl {{ $tokens['card_bg'] }} space-y-4">
            <h3 class="{{ $tokens['font_heading'] }} text-base text-white">+ Tambah Aset Investasi</h3>

            <form wire:submit.prevent="addAsset" class="space-y-3 text-xs">
                <div>
                    <label class="block text-slate-300 font-bold mb-1">Nama Aset / Ticker</label>
                    <input type="text" wire:model="asset_name" placeholder="Contoh: BBCA, Antam 10g, Bitcoin, RDPU" class="w-full p-3 rounded-xl bg-slate-900 border border-slate-700 text-white focus:outline-none focus:border-cyan-400">
                    @error('asset_name') <span class="text-rose-400 text-[10px] mt-1">{{ $message }}</span> @enderror
                </div>

                <div class="grid grid-cols-2 gap-2">
                    <div>
                        <label class="block text-slate-300 font-bold mb-1">Jenis Aset</label>
                        <select wire:model="asset_type" class="w-full p-3 rounded-xl bg-slate-900 border border-slate-700 text-white focus:outline-none focus:border-cyan-400">
                            <option value="Saham">Saham 📈</option>
                            <option value="Emas">Emas 🥇</option>
                            <option value="Reksa Dana">Reksa Dana 📊</option>
                            <option value="Crypto">Crypto 🪙</option>
                            <option value="Obligasi">Obligasi/SBN 📜</option>
                            <option value="Properti">Properti 🏠</option>
                        </select>
                    </div>
                    <div>
                        <label class="block text-slate-300 font-bold mb-1">Jumlah Unit/Lot</label>
                        <input type="number" step="0.0001" wire:model="quantity" placeholder="10" class="w-full p-3 rounded-xl bg-slate-900 border border-slate-700 text-white focus:outline-none focus:border-cyan-400">
                    </div>
                </div>

                <div class="grid grid-cols-2 gap-2">
                    <div>
                        <label class="block text-slate-300 font-bold mb-1">Harga Beli / Unit (Rp)</label>
                        <input type="number" wire:model="purchase_price" placeholder="9500" class="w-full p-3 rounded-xl bg-slate-900 border border-slate-700 text-white focus:outline-none focus:border-cyan-400">
                    </div>
                    <div>
                        <label class="block text-slate-300 font-bold mb-1">Harga Sekarang / Unit (Rp)</label>
                        <input type="number" wire:model="current_price" placeholder="10200" class="w-full p-3 rounded-xl bg-slate-900 border border-slate-700 text-white focus:outline-none focus:border-cyan-400">
                    </div>
                </div>

                <button type="submit" class="w-full py-3 rounded-xl bg-emerald-500 hover:bg-emerald-400 text-slate-950 font-bold text-xs shadow-lg transition-colors">
                    + Simpan Aset Investasi
                </button>
            </form>
        </div>

        <!-- Investment Asset Table -->
        <div class="lg:col-span-7 space-y-4">
            <h3 class="{{ $tokens['font_heading'] }} text-base text-white">Daftar Portofolio Investasi</h3>

            @forelse($investmentAssets as $asset)
                @php
                    $totalVal = $asset->total_value;
                    $pnl = $asset->total_profit_loss;
                    $isProfit = $pnl >= 0;
                @endphp
                <div class="p-5 rounded-3xl {{ $tokens['card_bg'] }} border border-slate-800 space-y-2 flex items-center justify-between">
                    <div class="space-y-1">
                        <div class="flex items-center space-x-2">
                            <span class="text-white font-bold text-base">{{ $asset->asset_name }}</span>
                            <span class="px-2 py-0.5 rounded-full text-[10px] bg-slate-800 text-amber-300 border border-slate-700 font-mono">
                                {{ $asset->asset_type }}
                            </span>
                        </div>
                        <div class="text-xs font-mono text-slate-400">
                            {{ $asset->quantity }} Unit @ Rp {{ number_format((float)$asset->current_price, 0, ',', '.') }}
                        </div>
                        <div class="text-xs font-mono">
                            Nilai Total: <strong class="text-emerald-400">Rp {{ number_format((float)$totalVal, 0, ',', '.') }}</strong>
                            | PnL: <span class="{{ $isProfit ? 'text-emerald-400' : 'text-rose-400' }} font-bold">
                                {{ $isProfit ? '+' : '' }}Rp {{ number_format((float)$pnl, 0, ',', '.') }}
                            </span>
                        </div>
                    </div>

                    <button wire:click="deleteAsset({{ $asset->id }})" class="text-slate-500 hover:text-rose-400 text-xs font-mono">
                        Hapus
                    </button>
                </div>
            @empty
                <div class="p-8 rounded-3xl {{ $tokens['card_bg'] }} text-center text-slate-400 text-xs">
                    Belum ada aset investasi terdaftar dalam portofolio Anda.
                </div>
            @endforelse
        </div>
    </div>

    <!-- Quest Pools / Savings Goals Grid -->
    <div class="space-y-4 pt-4 border-t border-slate-800">
        <h3 class="{{ $tokens['font_heading'] }} text-lg text-white">Savings Quest Pools & Target Tabungan</h3>
        <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
            @foreach($questPools as $pool)
                <div class="glass-panel rounded-3xl p-6 border border-slate-800 space-y-4">
                    <div class="flex items-center justify-between">
                        <span class="text-xs font-bold uppercase tracking-wider px-3 py-1 rounded-full bg-slate-900 border border-slate-700 text-amber-400">
                            {{ $pool->category }}
                        </span>
                        <span class="text-xs font-mono font-bold text-emerald-400">
                            {{ $pool->allocated_ap }} AP Funded
                        </span>
                    </div>

                    <div>
                        <h3 class="font-display font-bold text-lg text-white">{{ $pool->name }}</h3>
                        <div class="text-2xl font-black text-emerald-400 font-mono mt-1">
                            Rp {{ number_format((float)$pool->current_amount, 0, ',', '.') }}
                        </div>
                        <div class="text-xs text-slate-400 mt-0.5">Target: Rp {{ number_format((float)$pool->target_amount, 0, ',', '.') }}</div>
                    </div>

                    <div class="w-full h-3 bg-slate-950 rounded-full overflow-hidden border border-slate-800">
                        <div class="h-full bg-gradient-to-r from-emerald-500 via-teal-400 to-cyan-400"
                             style="width: {{ $pool->getProgressPercentage() }}%;"></div>
                    </div>

                    <div class="pt-3 border-t border-slate-800/80 text-[11px] text-slate-400 flex justify-between font-mono">
                        <span>Allocated Logs: {{ $pool->apAllocations->count() }} times</span>
                        <span class="text-cyan-300 font-bold">{{ $pool->getProgressPercentage() }}% Complete</span>
                    </div>
                </div>
            @endforeach
        </div>
    </div>
</div>
