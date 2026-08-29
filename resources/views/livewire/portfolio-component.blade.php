<div class="space-y-8">
    <!-- Header -->
    <div class="glass-panel rounded-3xl p-6 sm:p-8 border border-slate-800 flex flex-col md:flex-row md:items-center justify-between gap-4">
        <div>
            <div class="flex items-center space-x-2">
                <span class="text-2xl">{{ $labels['icon_portfolio'] ?? '🏆' }}</span>
                <h1 class="font-display font-black text-2xl md:text-3xl text-white">
                    {{ $labels['quest_pools_title'] }}
                </h1>
            </div>
            <p class="text-xs sm:text-sm text-slate-400 mt-1">
                {{ $labels['quest_pools_desc'] }}
            </p>
        </div>

        <div class="px-5 py-3 rounded-2xl bg-gradient-to-r from-cyan-500/20 to-teal-500/20 border border-cyan-500/30 text-cyan-300 text-xs font-bold font-mono">
            PORTFOLIO VALUE: Rp {{ number_format((float)$totalPortfolioValue, 0, ',', '.') }}
        </div>
    </div>

    <!-- Quest Pools Grid -->
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
