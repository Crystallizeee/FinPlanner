@php
    $tokens = app(\App\Services\ThemeService::class)->getThemeTokens($themeMode);
@endphp

<div class="space-y-6">
    <!-- Header with Export Action -->
    <div class="p-6 rounded-3xl {{ $tokens['card_bg'] }} flex flex-col md:flex-row md:items-center justify-between gap-4">
        <div>
            <h1 class="{{ $tokens['font_heading'] }} text-2xl text-white flex items-center space-x-2">
                <span>📈</span>
                <span>Analytics & AI Financial Insights</span>
            </h1>
            <p class="text-xs text-slate-400 mt-1">Evaluasi tren alokasi dana, rasio tabungan, dan ekspor laporan keuangan.</p>
        </div>

        <button wire:click="exportCsv" class="px-5 py-3 rounded-2xl bg-gradient-to-r from-emerald-500 to-teal-500 hover:from-emerald-400 hover:to-teal-400 text-slate-950 font-bold text-xs font-mono shadow-lg transition-all flex items-center space-x-2 shrink-0">
            <span>📥</span>
            <span>Ekspor Laporan Keuangan (.CSV)</span>
        </button>
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
                    <span>Total Struk Terverifikasi</span>
                </div>
                <p class="text-slate-300">Terdapat <strong>{{ $receiptCount }} struk OCR</strong> dan <strong>{{ $bankSyncCount }} webhook bank</strong> yang tercatat secara sah.</p>
            </div>
            <div class="p-4 rounded-2xl bg-slate-950/80 border border-slate-800 space-y-2">
                <div class="text-amber-400 font-bold flex items-center space-x-1">
                    <span>⚡</span>
                    <span>Status Pengeluaran Bulanan</span>
                </div>
                <p class="text-slate-300">Total akumulasi pengeluaran Anda saat ini adalah <strong>Rp {{ number_format($totalSpent, 0, ',', '.') }}</strong>.</p>
            </div>
            <div class="p-4 rounded-2xl bg-slate-950/80 border border-slate-800 space-y-2">
                <div class="text-purple-400 font-bold flex items-center space-x-1">
                    <span>🍲</span>
                    <span>Distribusi Kategori Utama</span>
                </div>
                <p class="text-slate-300">Terdapat <strong>{{ count($categoryBreakdown) }} kategori</strong> aktif dengan pencatatan pengeluaran bulan ini.</p>
            </div>
        </div>
    </div>

    <!-- Interactive Charts Grid -->
    <div class="grid grid-cols-1 lg:grid-cols-12 gap-6">

        <!-- 1. Interactive Monthly Trend Chart -->
        <div class="lg:col-span-7 p-6 rounded-3xl {{ $tokens['card_bg'] }} space-y-4">
            <div class="flex items-center justify-between">
                <h3 class="{{ $tokens['font_heading'] }} text-base text-white">Tren Pengeluaran (6 Bulan Terakhir)</h3>
                <span class="text-xs font-mono text-slate-400">Monthly Aggregates</span>
            </div>

            <div class="p-4 bg-slate-950/60 rounded-2xl border border-slate-800">
                <canvas id="monthlyTrendChart" class="w-full max-h-64"></canvas>
            </div>
        </div>

        <!-- 2. Category Distribution Donut Chart -->
        <div class="lg:col-span-5 p-6 rounded-3xl {{ $tokens['card_bg'] }} space-y-4 flex flex-col justify-between">
            <h3 class="{{ $tokens['font_heading'] }} text-base text-white">Category Spending Distribution</h3>

            <div class="p-4 bg-slate-950/60 rounded-2xl border border-slate-800 flex items-center justify-center">
                <canvas id="categoryDonutChart" class="w-full max-h-56"></canvas>
            </div>

            <div class="grid grid-cols-1 sm:grid-cols-2 gap-2 text-xs font-mono mt-4">
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

<!-- Load Chart.js CDN and Initialize Dynamic Interactive Charts -->
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script>
    document.addEventListener('DOMContentLoaded', function () {
        // 1. Monthly Trend Bar Chart
        const trendCtx = document.getElementById('monthlyTrendChart');
        if (trendCtx) {
            const trendData = @json($monthlyTrends);
            new Chart(trendCtx, {
                type: 'bar',
                data: {
                    labels: trendData.map(d => d.month),
                    datasets: [{
                        label: 'Total Pengeluaran (Rp)',
                        data: trendData.map(d => d.spent),
                        backgroundColor: '#10b981',
                        borderRadius: 8,
                    }]
                },
                options: {
                    responsive: true,
                    plugins: {
                        legend: { labels: { color: '#94a3b8' } }
                    },
                    scales: {
                        x: { ticks: { color: '#94a3b8' }, grid: { color: '#1e293b' } },
                        y: { ticks: { color: '#94a3b8' }, grid: { color: '#1e293b' } }
                    }
                }
            });
        }

        // 2. Category Donut Chart
        const catCtx = document.getElementById('categoryDonutChart');
        if (catCtx) {
            const catData = @json($categoryBreakdown);
            const labels = Object.keys(catData);
            const sums = labels.map(k => catData[k].sum);

            new Chart(catCtx, {
                type: 'doughnut',
                data: {
                    labels: labels,
                    datasets: [{
                        data: sums,
                        backgroundColor: ['#3b82f6', '#10b981', '#f59e0b', '#ec4899', '#8b5cf6', '#06b6d4'],
                        borderWidth: 0
                    }]
                },
                options: {
                    responsive: true,
                    plugins: {
                        legend: { labels: { color: '#94a3b8' } }
                    }
                }
            });
        }
    });
</script>
