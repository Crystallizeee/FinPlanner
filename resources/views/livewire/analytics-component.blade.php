@php
    $tokens = app(\App\Services\ThemeService::class)->getThemeTokens($themeMode);
@endphp

<div class="space-y-6">
    <!-- Header -->
    <div class="p-6 rounded-3xl {{ $tokens['card_bg'] }}">
        <h1 class="{{ $tokens['font_heading'] }} text-2xl text-white flex items-center space-x-2">
            <span>📈</span>
            <span>Analytics & AI Financial Insights</span>
        </h1>
        <p class="text-xs text-slate-400 mt-1">Evaluasi tren alokasi dana, rasio tabungan, dan proyeksi kekayaan otomatis.</p>
    </div>

    <!-- AI Insights Section -->
    <div class="p-6 rounded-3xl bg-gradient-to-r from-blue-950/80 via-slate-900 to-slate-950 border border-blue-500/40 shadow-xl space-y-4">
        <div class="flex items-center space-x-2 text-blue-400 font-bold text-sm">
            <span>🤖</span>
            <span>AI Actionable Financial Insights</span>
        </div>
        <div class="grid grid-cols-1 md:grid-cols-3 gap-4 text-xs">
            <div class="p-4 rounded-2xl bg-slate-950/80 border border-slate-800 space-y-2">
                <div class="text-emerald-400 font-bold flex items-center space-x-1">
                    <span>📈</span>
                    <span>Rasio Tabungan Meningkat</span>
                </div>
                <p class="text-slate-300">"You saved <strong>12% more</strong> than last month due to lower dining expenses."</p>
            </div>
            <div class="p-4 rounded-2xl bg-slate-950/80 border border-slate-800 space-y-2">
                <div class="text-amber-400 font-bold flex items-center space-x-1">
                    <span>⚡</span>
                    <span>Proyeksi Target Dana Darurat</span>
                </div>
                <p class="text-slate-300">"At your current saving rate, you'll reach your Emergency Fund goal <strong>2 months early</strong>."</p>
            </div>
            <div class="p-4 rounded-2xl bg-slate-950/80 border border-slate-800 space-y-2">
                <div class="text-purple-400 font-bold flex items-center space-x-1">
                    <span>🍲</span>
                    <span>Kenaikan Pengeluaran Makanan</span>
                </div>
                <p class="text-slate-300">"Your Food spending increased <strong>18%</strong> compared to last month. Consider meal planning."</p>
            </div>
        </div>
    </div>

    <!-- Charts Grid -->
    <div class="grid grid-cols-1 lg:grid-cols-12 gap-6">

        <!-- 1. Income vs Expense Bar Chart -->
        <div class="lg:col-span-7 p-6 rounded-3xl {{ $tokens['card_bg'] }} space-y-4">
            <div class="flex items-center justify-between">
                <h3 class="{{ $tokens['font_heading'] }} text-base text-white">Income vs Expense Trend</h3>
                <span class="text-xs font-mono text-slate-400">Last 6 Months</span>
            </div>

            <!-- Simulated SVG Bar Chart -->
            <div class="h-64 flex items-end justify-between gap-4 pt-8 px-4 bg-slate-950/60 rounded-2xl border border-slate-800 text-[10px] font-mono">
                <div class="flex flex-col items-center gap-2 w-full">
                    <div class="w-full flex items-end justify-center gap-1.5 h-44">
                        <div class="w-4 bg-emerald-400 rounded-t-sm" style="height: 70%;"></div>
                        <div class="w-4 bg-rose-400 rounded-t-sm" style="height: 50%;"></div>
                    </div>
                    <span class="text-slate-400">Mar</span>
                </div>
                <div class="flex flex-col items-center gap-2 w-full">
                    <div class="w-full flex items-end justify-center gap-1.5 h-44">
                        <div class="w-4 bg-emerald-400 rounded-t-sm" style="height: 75%;"></div>
                        <div class="w-4 bg-rose-400 rounded-t-sm" style="height: 55%;"></div>
                    </div>
                    <span class="text-slate-400">Apr</span>
                </div>
                <div class="flex flex-col items-center gap-2 w-full">
                    <div class="w-full flex items-end justify-center gap-1.5 h-44">
                        <div class="w-4 bg-emerald-400 rounded-t-sm" style="height: 80%;"></div>
                        <div class="w-4 bg-rose-400 rounded-t-sm" style="height: 60%;"></div>
                    </div>
                    <span class="text-slate-400">May</span>
                </div>
                <div class="flex flex-col items-center gap-2 w-full">
                    <div class="w-full flex items-end justify-center gap-1.5 h-44">
                        <div class="w-4 bg-emerald-400 rounded-t-sm" style="height: 85%;"></div>
                        <div class="w-4 bg-rose-400 rounded-t-sm" style="height: 62%;"></div>
                    </div>
                    <span class="text-slate-400">Jun</span>
                </div>
                <div class="flex flex-col items-center gap-2 w-full">
                    <div class="w-full flex items-end justify-center gap-1.5 h-44">
                        <div class="w-4 bg-emerald-400 rounded-t-sm" style="height: 90%;"></div>
                        <div class="w-4 bg-rose-400 rounded-t-sm" style="height: 65%;"></div>
                    </div>
                    <span class="text-slate-400">Jul</span>
                </div>
                <div class="flex flex-col items-center gap-2 w-full">
                    <div class="w-full flex items-end justify-center gap-1.5 h-44">
                        <div class="w-4 bg-emerald-400 rounded-t-sm" style="height: 100%;"></div>
                        <div class="w-4 bg-rose-400 rounded-t-sm" style="height: 68%;"></div>
                    </div>
                    <span class="text-cyan-400 font-bold">Aug</span>
                </div>
            </div>

            <div class="flex justify-center space-x-6 text-xs font-mono">
                <div class="flex items-center space-x-2">
                    <div class="w-3 h-3 bg-emerald-400 rounded-sm"></div>
                    <span class="text-slate-300">Income (Rp8.5M avg)</span>
                </div>
                <div class="flex items-center space-x-2">
                    <div class="w-3 h-3 bg-rose-400 rounded-sm"></div>
                    <span class="text-slate-300">Expense (Rp5.75M avg)</span>
                </div>
            </div>
        </div>

        <!-- 2. Category Distribution Donut -->
        <div class="lg:col-span-5 p-6 rounded-3xl {{ $tokens['card_bg'] }} space-y-4 flex flex-col justify-between">
            <h3 class="{{ $tokens['font_heading'] }} text-base text-white">Category Distribution</h3>

            <div class="flex items-center justify-center p-4">
                <div class="relative w-44 h-44 flex items-center justify-center">
                    <svg class="w-full h-full transform -rotate-90" viewBox="0 0 100 100">
                        <circle cx="50" cy="50" r="38" stroke="#3b82f6" stroke-width="12" stroke-dasharray="238.7" stroke-dashoffset="60" fill="transparent"/>
                        <circle cx="50" cy="50" r="38" stroke="#10b981" stroke-width="12" stroke-dasharray="238.7" stroke-dashoffset="140" fill="transparent"/>
                        <circle cx="50" cy="50" r="38" stroke="#f59e0b" stroke-width="12" stroke-dasharray="238.7" stroke-dashoffset="200" fill="transparent"/>
                    </svg>
                    <div class="absolute text-center font-mono">
                        <div class="text-xs text-slate-400">Total Spent</div>
                        <div class="text-xs font-black text-emerald-400">Rp {{ number_format($totalSpent, 0, ',', '.') }}</div>
                    </div>
                </div>
            </div>

            <div class="grid grid-cols-1 sm:grid-cols-2 gap-2 text-xs font-mono">
                @forelse($categoryBreakdown as $catName => $data)
                    <div class="p-2.5 rounded-xl bg-slate-950/60 border border-slate-800 flex justify-between items-center">
                        <span class="text-cyan-400 font-bold capitalize">{{ $catName }} ({{ $data['pct'] }}%)</span>
                        <span class="text-slate-300">Rp {{ number_format($data['sum'], 0, ',', '.') }}</span>
                    </div>
                @empty
                    <div class="col-span-2 text-center text-slate-500 text-xs py-2">
                        Belum ada transaksi pengeluaran tercatat.
                    </div>
                @endforelse
            </div>
        </div>

    </div>
</div>
