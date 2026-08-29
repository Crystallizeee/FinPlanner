@php
    $tokens = app(\App\Services\ThemeService::class)->getThemeTokens($themeMode);
@endphp

<div class="space-y-8">
    <!-- Header -->
    <div class="glass-panel rounded-3xl p-6 sm:p-8 border border-slate-800 flex flex-col md:flex-row md:items-center justify-between gap-4">
        <div>
            <div class="flex items-center space-x-2">
                <span class="text-2xl">🏥</span>
                <h1 class="font-display font-black text-2xl md:text-3xl text-white">
                    Financial Health Index & Emergency Stress Test
                </h1>
            </div>
            <p class="text-xs sm:text-sm text-slate-400 mt-1">
                Kalkulator skor kesehatan finansial (0-100) dan simulasi daya tahan kas terhadap risiko krisis.
            </p>
        </div>

        <div class="px-6 py-4 rounded-2xl bg-gradient-to-r from-emerald-500/20 via-cyan-500/20 to-teal-500/20 border border-emerald-500/30 text-emerald-300 text-xs font-bold font-mono text-right">
            <div class="text-[10px] text-slate-400 uppercase">FINANCIAL HEALTH SCORE</div>
            <div class="text-3xl font-black text-white flex items-center justify-end space-x-2">
                <span>{{ $healthScore }}</span>
                <span class="text-sm text-emerald-400 font-normal">/ 100</span>
            </div>
            <div class="text-[11px] font-bold text-cyan-300">{{ $grade }}</div>
        </div>
    </div>

    <!-- Health Breakdown Cards -->
    <div class="grid grid-cols-1 md:grid-cols-4 gap-4 text-xs font-mono">
        <div class="p-5 rounded-3xl bg-slate-900/90 border border-slate-800 space-y-1">
            <div class="text-slate-400">🛡️ Dana Darurat Tercover</div>
            <div class="text-xl font-black text-cyan-400">{{ $monthsCovered }} Bulan</div>
            <div class="text-[10px] text-slate-500">Score: {{ $emergencyFundScore }}/30 pts</div>
        </div>
        <div class="p-5 rounded-3xl bg-slate-900/90 border border-slate-800 space-y-1">
            <div class="text-slate-400">💵 Saldo Likuid Kas</div>
            <div class="text-xl font-black text-emerald-400">Rp {{ number_format($liquidBalance, 0, ',', '.') }}</div>
            <div class="text-[10px] text-slate-500">Score Tabungan: {{ $savingsScore }}/30 pts</div>
        </div>
        <div class="p-5 rounded-3xl bg-slate-900/90 border border-slate-800 space-y-1">
            <div class="text-slate-400">💳 Total Utang/Cicilan</div>
            <div class="text-xl font-black text-rose-400">Rp {{ number_format($totalDebt, 0, ',', '.') }}</div>
            <div class="text-[10px] text-slate-500">Score Cicilan: {{ $debtScore }}/20 pts</div>
        </div>
        <div class="p-5 rounded-3xl bg-slate-900/90 border border-slate-800 space-y-1">
            <div class="text-slate-400">🔥 Estimasi Pengeluaran/Bln</div>
            <div class="text-xl font-black text-amber-400">Rp {{ number_format($monthlyExpenses, 0, ',', '.') }}</div>
            <div class="text-[10px] text-slate-500">Score Anggaran: {{ $budgetScore }}/20 pts</div>
        </div>
    </div>

    <!-- Emergency Fund Stress Test Simulator -->
    <div class="p-6 sm:p-8 rounded-3xl bg-gradient-to-r from-slate-900 via-slate-950 to-slate-900 border border-cyan-500/30 space-y-6 shadow-2xl">
        <div class="flex items-center space-x-3">
            <span class="text-2xl">🧪</span>
            <div>
                <h3 class="{{ $tokens['font_heading'] }} text-lg text-white">Simulasi Emergency Fund Stress Test</h3>
                <p class="text-xs text-slate-400">Uji seberapa kuat keuangan Anda jika terjadi krisis tak terduga (Kehilangan Pekerjaan / Darurat Kesehatan).</p>
            </div>
        </div>

        <div class="grid grid-cols-1 md:grid-cols-2 gap-6 text-xs">
            <div class="space-y-4 p-5 rounded-2xl bg-slate-950/80 border border-slate-800">
                <div>
                    <label class="block text-slate-300 font-bold mb-1">Durasi Krisis (Bulan Tanpa Penghasilan)</label>
                    <input type="range" min="1" max="12" wire:model.live="simulatedMonths" class="w-full accent-cyan-400">
                    <div class="flex justify-between text-cyan-300 font-mono font-bold mt-1">
                        <span>1 Bulan</span>
                        <span>{{ $simulatedMonths }} Bulan Simulasi</span>
                        <span>12 Bulan</span>
                    </div>
                </div>

                <div>
                    <label class="block text-slate-300 font-bold mb-1">Biaya Tak Terduga DaruratTambahan (Rp)</label>
                    <input type="number" step="500000" wire:model.live="simulatedEmergencyCost" placeholder="0" class="w-full p-3 rounded-xl bg-slate-900 border border-slate-700 text-white font-mono focus:outline-none focus:border-cyan-400">
                </div>
            </div>

            <div class="p-5 rounded-2xl bg-slate-950/80 border border-slate-800 space-y-4 flex flex-col justify-between">
                <div>
                    <div class="text-slate-400 font-bold mb-2">HASIL SIMULASI STRESS TEST:</div>
                    <div class="space-y-2 font-mono">
                        <div class="flex justify-between text-slate-300">
                            <span>Total Biaya Krisis ({{ $simulatedMonths }} bln):</span>
                            <span class="font-bold text-white">Rp {{ number_format($simulatedTotalCost, 0, ',', '.') }}</span>
                        </div>
                        <div class="flex justify-between text-slate-300">
                            <span>Saldo Kas Likuid Anda:</span>
                            <span class="font-bold text-cyan-400">Rp {{ number_format($liquidBalance, 0, ',', '.') }}</span>
                        </div>
                        <div class="flex justify-between text-slate-300 pt-2 border-t border-slate-800">
                            <span>Sisa Saldo Kas (Surplus/Defisit):</span>
                            <span class="{{ $isStressTestPassed ? 'text-emerald-400' : 'text-rose-400' }} font-bold text-base">
                                {{ $isStressTestPassed ? '+' : '' }}Rp {{ number_format($stressTestSurplus, 0, ',', '.') }}
                            </span>
                        </div>
                    </div>
                </div>

                <div class="p-3 rounded-xl {{ $isStressTestPassed ? 'bg-emerald-950/60 border border-emerald-500/40 text-emerald-300' : 'bg-rose-950/60 border border-rose-500/40 text-rose-300' }} text-xs">
                    @if($isStressTestPassed)
                        ✅ <strong>STRESS TEST LULUS!</strong> Kas likuid Anda sanggup bertahan selama {{ $simulatedMonths }} bulan tanpa pendapatan.
                    @else
                        ⚠️ <strong>STRESS TEST GAGAL!</strong> Saldo kas Anda akan habis dalam durasi ini. Disarankan menambah alokasi dana darurat.
                    @endif
                </div>
            </div>
        </div>
    </div>
</div>
