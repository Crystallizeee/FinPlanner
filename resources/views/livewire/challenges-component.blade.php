@php
    $tokens = app(\App\Services\ThemeService::class)->getThemeTokens($themeMode);
@endphp

<div class="space-y-6">
    <!-- Header -->
    <div class="p-6 rounded-3xl {{ $tokens['card_bg'] }}">
        <h1 class="{{ $tokens['font_heading'] }} text-2xl text-white flex items-center space-x-2">
            <span>⚡</span>
            <span>Quests & Challenge Hub</span>
        </h1>
        <p class="text-xs text-slate-400 mt-1">Selesaikan tantangan finansial harian dan mingguan untuk mendapatkan XP dan meningkatkan reputasi.</p>
    </div>

    <!-- BOSS RAID EVENT BANNER -->
    @if($activeBoss)
        <div class="p-6 sm:p-8 rounded-3xl bg-gradient-to-r from-rose-950 via-slate-950 to-purple-950 border border-rose-500/50 space-y-4 shadow-2xl relative overflow-hidden">
            <div class="flex flex-col md:flex-row md:items-center justify-between gap-4">
                <div class="space-y-1">
                    <div class="inline-flex items-center space-x-2 px-3 py-1 rounded-full bg-rose-500/20 text-rose-300 border border-rose-500/40 text-[10px] font-mono font-bold">
                        <span>🐉 MONTHLY BOSS RAID</span>
                        <span>• {{ $activeBoss->month_year }}</span>
                    </div>
                    <h2 class="font-display font-black text-2xl text-white">{{ $activeBoss->boss_name }}</h2>
                    <p class="text-xs text-rose-200/80">Kalahkan Boss Pengeluaran Impulsif ini dengan menyelesaikan challenge & menabung!</p>
                </div>

                <div class="text-right font-mono">
                    <div class="text-xs text-slate-400">REWARD KEMENANGAN</div>
                    <div class="text-xl font-black text-amber-400">+{{ number_format($activeBoss->reward_xp) }} XP</div>
                </div>
            </div>

            <!-- Boss Health Bar -->
            <div class="space-y-1.5">
                <div class="flex justify-between text-xs font-mono text-slate-300">
                    <span>STATUS HP BOSS: <strong class="{{ $activeBoss->current_hp <= 0 ? 'text-emerald-400' : 'text-rose-400' }}">{{ $activeBoss->current_hp }} / {{ $activeBoss->max_hp }} HP</strong></span>
                    <span>{{ $activeBoss->getHpPercentage() }}% HP Remaining</span>
                </div>
                <div class="w-full h-4 bg-slate-950 rounded-full overflow-hidden border border-rose-900/60 p-0.5">
                    <div class="h-full bg-gradient-to-r from-rose-600 via-red-500 to-amber-500 rounded-full transition-all duration-500" style="width: {{ $activeBoss->getHpPercentage() }}%;"></div>
                </div>
            </div>

            <div class="flex items-center justify-between pt-2">
                <span class="text-[11px] font-mono text-slate-400">Setiap Challenge memberikan 150 DMG ke Boss</span>
                @if($activeBoss->status === 'active')
                    <button wire:click="attackBoss" class="px-5 py-2.5 rounded-xl bg-gradient-to-r from-rose-500 to-orange-500 hover:from-rose-400 hover:to-orange-400 text-slate-950 font-bold text-xs font-mono shadow-lg transition-all">
                        ⚔️ Serang Boss (-200 HP)
                    </button>
                @else
                    <span class="px-4 py-2 rounded-xl bg-emerald-500/20 text-emerald-300 border border-emerald-500/40 text-xs font-bold font-mono">
                        🏆 VICTORY DEFEATED!
                    </span>
                @endif
            </div>
        </div>
    @endif

    <!-- DYNAMIC PERSONALIZED CHALLENGES SECTION -->
    <div class="space-y-4">
        <h2 class="{{ $tokens['font_heading'] }} text-lg text-white flex items-center space-x-2">
            <span>⚡</span>
            <span>DYNAMIC PERSONALIZED CHALLENGES</span>
        </h2>

        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
            
            <!-- Dynamic Challenge 1: Food Spending Reduction -->
            <div class="p-6 rounded-3xl bg-gradient-to-br from-amber-950/80 via-slate-900 to-slate-950 border border-amber-500/50 space-y-4 shadow-xl">
                <div class="flex items-center justify-between">
                    <span class="px-3 py-1 rounded-full text-[10px] font-mono font-bold bg-amber-500/20 text-amber-300 border border-amber-500/40">
                        ⚡ BEHAVIOR TRIGGERED
                    </span>
                    <span class="text-xs font-mono font-black text-amber-400">+800 XP</span>
                </div>

                <div>
                    <h3 class="font-display font-bold text-base text-white">Food Spending Control</h3>
                    <p class="text-xs text-amber-300/90 mt-1">"Kendalikan pengeluaran makanan & kumpulkan XP bonus."</p>
                </div>

                <div class="p-3 rounded-2xl bg-slate-950/80 border border-slate-800 space-y-2 text-xs">
                    <div class="flex justify-between font-mono">
                        <span class="text-slate-400">Current Weekly Food Spend:</span>
                        <span class="text-rose-400 font-bold">Rp {{ number_format($recentFoodSpent, 0, ',', '.') }}</span>
                    </div>
                    <div class="flex justify-between font-mono">
                        <span class="text-slate-400">Target Spending (Reduce 15%):</span>
                        <span class="text-emerald-400 font-bold">Rp {{ number_format($recentFoodSpent * 0.85, 0, ',', '.') }}</span>
                    </div>
                </div>

                <button wire:click="acceptChallenge(800)" class="w-full py-2.5 rounded-xl bg-amber-500 hover:bg-amber-400 text-slate-950 font-bold text-xs shadow-lg transition-colors">
                    Accept Personalized Challenge →
                </button>
            </div>

            <!-- Dynamic Challenge 2: Savings Rate Boost -->
            <div class="p-6 rounded-3xl bg-gradient-to-br from-cyan-950/80 via-slate-900 to-slate-950 border border-cyan-500/50 space-y-4 shadow-xl">
                <div class="flex items-center justify-between">
                    <span class="px-3 py-1 rounded-full text-[10px] font-mono font-bold bg-cyan-500/20 text-cyan-300 border border-cyan-500/40">
                        🎯 SAVINGS RATE BOOST
                    </span>
                    <span class="text-xs font-mono font-black text-cyan-400">+1,000 XP</span>
                </div>

                <div>
                    <h3 class="font-display font-bold text-base text-white">Increase Monthly Savings Rate</h3>
                    <p class="text-xs text-cyan-300/90 mt-1">"Your savings rate is currently 8%."</p>
                </div>

                <div class="p-3 rounded-2xl bg-slate-950/80 border border-slate-800 space-y-2 text-xs">
                    <div class="flex justify-between font-mono">
                        <span class="text-slate-400">Target Savings Rate:</span>
                        <span class="text-emerald-400 font-bold">Increase to 12%</span>
                    </div>
                    <div class="flex justify-between font-mono">
                        <span class="text-slate-400">Monthly Surplus Goal:</span>
                        <span class="text-cyan-400 font-bold">+Rp 420.000</span>
                    </div>
                </div>

                <button class="w-full py-2.5 rounded-xl bg-cyan-500 hover:bg-cyan-400 text-slate-950 font-bold text-xs shadow-lg">
                    Accept Challenge →
                </button>
            </div>

        </div>
    </div>

    <!-- STANDARD CHALLENGE LIST (Daily, Weekly, Monthly) -->
    <div class="p-6 rounded-3xl {{ $tokens['card_bg'] }} space-y-4">
        <h2 class="{{ $tokens['font_heading'] }} text-lg text-white">Standard Active Challenges</h2>

        <div class="space-y-3">
            <div class="p-4 rounded-2xl bg-slate-950/60 border border-slate-800 flex items-center justify-between gap-4 text-xs">
                <div class="flex items-center space-x-3">
                    <span class="text-2xl">🛡️</span>
                    <div>
                        <div class="font-bold text-white">Budget Guardian</div>
                        <p class="text-slate-400">Stay within your weekly food budget (Rp325K / Rp500K).</p>
                    </div>
                </div>
                <div class="text-right shrink-0">
                    <span class="font-mono font-bold text-emerald-400">+500 XP</span>
                </div>
            </div>

            <div class="p-4 rounded-2xl bg-slate-950/60 border border-slate-800 flex items-center justify-between gap-4 text-xs">
                <div class="flex items-center space-x-3">
                    <span class="text-2xl">💰</span>
                    <div>
                        <div class="font-bold text-white">Smart Saver</div>
                        <p class="text-slate-400">Save Rp500.000 this week (Rp325K / Rp500K).</p>
                    </div>
                </div>
                <div class="text-right shrink-0">
                    <span class="font-mono font-bold text-emerald-400">+750 XP</span>
                </div>
            </div>
        </div>
    </div>
</div>
