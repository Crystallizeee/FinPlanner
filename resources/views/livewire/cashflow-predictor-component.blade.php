@php
    $tokens = app(\App\Services\ThemeService::class)->getThemeTokens($themeMode);
@endphp

<div class="space-y-8">
    <!-- Header -->
    <div class="glass-panel rounded-3xl p-6 sm:p-8 border border-slate-800 flex flex-col md:flex-row md:items-center justify-between gap-4">
        <div>
            <div class="flex items-center space-x-2">
                <span class="text-2xl">🔮</span>
                <h1 class="font-display font-black text-2xl md:text-3xl text-white">
                    AI Cashflow Predictor & End-of-Month Forecast
                </h1>
            </div>
            <p class="text-xs sm:text-sm text-slate-400 mt-1">
                Prediksi saldo kas akhir bulan berdasarkan tren histori burn-rate harian & estimasi tagihan mendatang.
            </p>
        </div>

        <div class="px-6 py-4 rounded-2xl bg-slate-900 border border-slate-800 font-mono text-right">
            <div class="text-[10px] text-slate-400 uppercase">STATUS RESIKO CASHFLOW</div>
            <div class="text-lg font-black text-{{ $riskBadgeColor }}-400 flex items-center justify-end space-x-1">
                <span>{{ $riskStatus }}</span>
            </div>
        </div>
    </div>

    <!-- Cards Summary -->
    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4 text-xs font-mono">
        <div class="p-5 rounded-3xl bg-slate-900/90 border border-slate-800 space-y-1">
            <div class="text-slate-400">💵 Kas Likuid Saat Ini</div>
            <div class="text-xl font-black text-cyan-400">Rp {{ number_format($liquidBalance, 0, ',', '.') }}</div>
            <div class="text-[10px] text-slate-500">Saldo seluruh rekening</div>
        </div>

        <div class="p-5 rounded-3xl bg-slate-900/90 border border-slate-800 space-y-1">
            <div class="text-slate-400">🔥 Burn Rate Harian</div>
            <div class="text-xl font-black text-amber-400">Rp {{ number_format($dailyBurnRate, 0, ',', '.') }}/hari</div>
            <div class="text-[10px] text-slate-500">Histori 30 hari terakhir</div>
        </div>

        <div class="p-5 rounded-3xl bg-slate-900/90 border border-slate-800 space-y-1">
            <div class="text-slate-400">🔄 Tagihan Mendatang</div>
            <div class="text-xl font-black text-rose-400">Rp {{ number_format($upcomingBills, 0, ',', '.') }}</div>
            <div class="text-[10px] text-slate-500">Tagihan rutin sisa bulan ini</div>
        </div>

        <div class="p-5 rounded-3xl bg-slate-900/90 border border-slate-800 space-y-1">
            <div class="text-slate-400">📈 Prediksi Saldo Akhir Bln</div>
            <div class="text-xl font-black text-{{ $projectedEndBalance >= 0 ? 'emerald' : 'rose' }}-400">
                Rp {{ number_format($projectedEndBalance, 0, ',', '.') }}
            </div>
            <div class="text-[10px] text-slate-500">Sisa {{ $daysRemaining }} hari dalam bulan ini</div>
        </div>
    </div>

    <!-- Simulator Interactive Box -->
    <div class="p-6 sm:p-8 rounded-3xl bg-slate-900/80 border border-cyan-500/30 space-y-6">
        <div class="flex items-center justify-between">
            <h3 class="{{ $tokens['font_heading'] }} text-lg text-white flex items-center space-x-2">
                <span>⚡</span>
                <span>Simulasi Sensitivitas Pengeluaran (What-If Analysis)</span>
            </h3>
            <span class="px-3 py-1 rounded-full bg-cyan-500/20 text-cyan-300 font-mono text-xs font-bold border border-cyan-500/40">
                {{ $spendingAdjustmentPct > 0 ? '+' : '' }}{{ $spendingAdjustmentPct }}% Penyesuaian Pengeluaran
            </span>
        </div>

        <div class="space-y-2">
            <label class="block text-xs font-bold text-slate-300">Geser untuk mensimulasikan kenaikan / penurunan pengeluaran harian:</label>
            <input type="range" min="-50" max="50" step="5" wire:model.live="spendingAdjustmentPct" class="w-full accent-cyan-400">
            <div class="flex justify-between text-[11px] font-mono text-slate-400">
                <span>-50% Hemat Ekstrem</span>
                <span>0% Tren Normal</span>
                <span>+50% Boros Ekstrem</span>
            </div>
        </div>

        <div class="p-5 rounded-2xl bg-slate-950/80 border border-slate-800 grid grid-cols-1 md:grid-cols-2 gap-4 text-xs font-mono">
            <div class="space-y-2">
                <div class="text-slate-400">Burn Rate Harian Disesuaikan:</div>
                <div class="text-lg font-bold text-amber-300">Rp {{ number_format($adjustedBurnRate, 0, ',', '.') }}</div>
                <div class="text-slate-400">Total Biaya Harian Sisa {{ $daysRemaining }} Hari:</div>
                <div class="text-lg font-bold text-white">Rp {{ number_format($projectedDailyExpenses, 0, ',', '.') }}</div>
            </div>

            <div class="space-y-2 border-t md:border-t-0 md:border-l border-slate-800 pt-3 md:pt-0 md:pl-4">
                <div class="text-slate-400">Total Proyeksi Outflow Sisa Bulan:</div>
                <div class="text-lg font-bold text-rose-400">Rp {{ number_format($projectedTotalOutflow, 0, ',', '.') }}</div>
                <div class="text-slate-400">Hasil Akhir Saldo Proyeksi:</div>
                <div class="text-xl font-black text-{{ $projectedEndBalance >= 0 ? 'emerald' : 'rose' }}-400">
                    Rp {{ number_format($projectedEndBalance, 0, ',', '.') }}
                </div>
            </div>
        </div>
    </div>
</div>
