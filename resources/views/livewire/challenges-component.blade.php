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
                    <p class="text-xs text-amber-300/90 mt-1">"Food spending increased 24% compared to last week."</p>
                </div>

                <div class="p-3 rounded-2xl bg-slate-950/80 border border-slate-800 space-y-2 text-xs">
                    <div class="flex justify-between font-mono">
                        <span class="text-slate-400">Current Weekly Food Spend:</span>
                        <span class="text-rose-400 font-bold">Rp 450.000</span>
                    </div>
                    <div class="flex justify-between font-mono">
                        <span class="text-slate-400">Target Spending (Reduce 15%):</span>
                        <span class="text-emerald-400 font-bold">Rp 382.500</span>
                    </div>
                </div>

                <button class="w-full py-2.5 rounded-xl bg-amber-500 hover:bg-amber-400 text-slate-950 font-bold text-xs shadow-lg">
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
