@php
    $tokens = app(\App\Services\ThemeService::class)->getThemeTokens($themeMode);
@endphp

<div class="space-y-6">
    <!-- Main Profile Card Header -->
    <div class="p-6 sm:p-8 rounded-3xl {{ $tokens['card_bg'] }} space-y-6">
        <div class="flex flex-col sm:flex-row items-center space-y-4 sm:space-y-0 sm:space-x-6">
            <div class="w-24 h-24 rounded-3xl bg-gradient-to-tr from-amber-500 via-orange-500 to-yellow-400 p-1 shadow-2xl shrink-0">
                <div class="w-full h-full bg-slate-950 rounded-[20px] flex items-center justify-center font-black text-4xl text-amber-400 font-display">
                    B
                </div>
            </div>

            <div class="space-y-1 text-center sm:text-left flex-1 min-w-0">
                <div class="flex flex-wrap items-center justify-center sm:justify-start gap-2">
                    <h1 class="{{ $tokens['font_heading'] }} text-2xl text-white">
                        Beni
                    </h1>
                    <span class="px-3 py-1 rounded-full text-xs font-mono font-bold bg-amber-500/20 text-amber-300 border border-amber-500/30">
                        {{ $labels['level_name'] }} 12
                    </span>
                </div>
                <div class="text-sm text-cyan-400 font-semibold">Money Strategist</div>
                <p class="text-xs text-slate-400">Financial Warrior & Habit Builder since August 2026</p>
            </div>
        </div>

        <!-- Metrics Stats Cards Grid -->
        <div class="grid grid-cols-2 sm:grid-cols-5 gap-3 pt-4 border-t border-slate-800">
            <div class="p-3.5 rounded-2xl bg-slate-950/70 border border-slate-800 text-center">
                <div class="text-[10px] text-slate-400 font-bold uppercase">Current XP</div>
                <div class="font-mono font-black text-lg text-amber-400 mt-1">7,450 XP</div>
            </div>
            <div class="p-3.5 rounded-2xl bg-slate-950/70 border border-slate-800 text-center">
                <div class="text-[10px] text-slate-400 font-bold uppercase">Financial Health</div>
                <div class="font-mono font-black text-lg text-emerald-400 mt-1">82 / 100</div>
            </div>
            <div class="p-3.5 rounded-2xl bg-slate-950/70 border border-slate-800 text-center">
                <div class="text-[10px] text-slate-400 font-bold uppercase">Active Streak</div>
                <div class="font-mono font-black text-lg text-rose-400 mt-1">14 Days</div>
            </div>
            <div class="p-3.5 rounded-2xl bg-slate-950/70 border border-slate-800 text-center">
                <div class="text-[10px] text-slate-400 font-bold uppercase">Achievements</div>
                <div class="font-mono font-black text-lg text-purple-400 mt-1">27 Badges</div>
            </div>
            <div class="p-3.5 rounded-2xl bg-slate-950/70 border border-slate-800 text-center col-span-2 sm:col-span-1">
                <div class="text-[10px] text-slate-400 font-bold uppercase">Quests Completed</div>
                <div class="font-mono font-black text-lg text-cyan-400 mt-1">42 Quests</div>
            </div>
        </div>
    </div>
</div>
