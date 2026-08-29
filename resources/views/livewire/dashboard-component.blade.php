@php
    $tokens = app(\App\Services\ThemeService::class)->getThemeTokens($themeMode);
@endphp

<div class="space-y-8" x-data="{ showHealthModal: false }">

    <!-- 1. GREETING HEADER & BANNER -->
    <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4 p-6 sm:p-8 rounded-3xl {{ $tokens['card_bg'] }} backdrop-blur-xl relative overflow-hidden">
        <div class="space-y-1.5 z-10">
            <div class="flex items-center space-x-2">
                <span class="px-3 py-1 rounded-full text-[11px] font-extrabold uppercase tracking-widest {{ $tokens['badge_style'] }}">
                    {{ $labels['theme_badge'] ?? 'FinTech Dashboard' }}
                </span>
                <span class="text-xs font-mono text-slate-400">{{ $activeCycleName }}</span>
            </div>
            <h1 class="{{ $tokens['font_heading'] }} text-2xl sm:text-3xl text-white">
                {{ $greetingTime }}, {{ $userName }} 👋
            </h1>
            <p class="text-xs sm:text-sm text-slate-400">
                Here's your financial progress and habit telemetry today.
            </p>
        </div>
        <div class="flex items-center space-x-3 z-10">
            <a href="{{ route('challenges') }}" class="px-5 py-3 rounded-2xl {{ $tokens['primary_bg'] }} text-xs font-bold shadow-lg transition-all flex items-center space-x-2">
                <span>{{ $labels['icon_xp'] ?? '⚡' }}</span>
                <span>Claim Daily XP</span>
            </a>
        </div>
        <!-- Decorative Glow Background -->
        <div class="absolute -right-10 -bottom-10 w-48 h-48 bg-cyan-500/10 rounded-full blur-3xl pointer-events-none"></div>
    </div>

    <!-- 2. TOP FINANCIAL KPI METRICS GRID -->
    <div class="grid grid-cols-2 md:grid-cols-3 lg:grid-cols-6 gap-4">
        
        <!-- Balance -->
        <div class="p-5 rounded-3xl {{ $tokens['card_bg'] }} space-y-2 relative group hover:border-cyan-500/40 transition-all">
            <div class="flex items-center justify-between text-xs text-slate-400 font-bold">
                <span>Total Balance</span>
                <span class="text-cyan-400">💰</span>
            </div>
            <div class="font-mono font-black text-lg sm:text-xl text-white">
                Rp {{ number_format($totalBalance, 0, ',', '.') }}
            </div>
            <div class="text-[10px] text-emerald-400 font-semibold flex items-center space-x-1">
                <span>↑ +4.2%</span>
                <span class="text-slate-500">vs last month</span>
            </div>
        </div>

        <!-- Monthly Planned Budget -->
        <div class="p-5 rounded-3xl {{ $tokens['card_bg'] }} space-y-2 relative group hover:border-emerald-500/40 transition-all">
            <div class="flex items-center justify-between text-xs text-slate-400 font-bold">
                <span>Planned Budget</span>
                <span class="text-emerald-400">📥</span>
            </div>
            <div class="font-mono font-black text-lg sm:text-xl text-emerald-400">
                Rp {{ number_format($totalPlannedBudget, 0, ',', '.') }}
            </div>
            <div class="text-[10px] text-slate-400 font-medium">
                Active Cycle Vault Limit
            </div>
        </div>

        <!-- Monthly Expense -->
        <div class="p-5 rounded-3xl {{ $tokens['card_bg'] }} space-y-2 relative group hover:border-rose-500/40 transition-all">
            <div class="flex items-center justify-between text-xs text-slate-400 font-bold">
                <span>Monthly Expense</span>
                <span class="text-rose-400">📤</span>
            </div>
            <div class="font-mono font-black text-lg sm:text-xl text-rose-400">
                Rp {{ number_format($totalSpent, 0, ',', '.') }}
            </div>
            <div class="text-[10px] text-slate-400 font-medium">
                Limit: Rp {{ number_format($totalPlannedBudget, 0, ',', '.') }}
            </div>
        </div>

        <!-- Savings -->
        <div class="p-5 rounded-3xl {{ $tokens['card_bg'] }} space-y-2 relative group hover:border-amber-500/40 transition-all">
            <div class="flex items-center justify-between text-xs text-slate-400 font-bold">
                <span>Total Savings</span>
                <span class="text-amber-400">🏦</span>
            </div>
            <div class="font-mono font-black text-lg sm:text-xl text-amber-400">
                Rp {{ number_format($totalSavings, 0, ',', '.') }}
            </div>
            <div class="text-[10px] text-slate-400 font-medium">
                Quest & Emergency Vaults
            </div>
        </div>

        <!-- Savings Rate -->
        <div class="p-5 rounded-3xl {{ $tokens['card_bg'] }} space-y-2 relative group hover:border-purple-500/40 transition-all">
            <div class="flex items-center justify-between text-xs text-slate-400 font-bold">
                <span>Savings Rate</span>
                <span class="text-purple-400">📊</span>
            </div>
            <div class="font-mono font-black text-lg sm:text-xl text-purple-400">
                {{ number_format($savingsRate, 1) }}%
            </div>
            <div class="text-[10px] {{ $savingsRate >= 20 ? 'text-emerald-400' : 'text-amber-400' }} font-semibold">
                {{ $savingsRate >= 20 ? 'Target: 20% (Good!)' : 'Target: 20%' }}
            </div>
        </div>

        <!-- Remaining Cash / Net Flow -->
        <div class="p-5 rounded-3xl {{ $tokens['card_bg'] }} space-y-2 relative group hover:border-blue-500/40 transition-all">
            <div class="flex items-center justify-between text-xs text-slate-400 font-bold">
                <span>Sisa Anggaran</span>
                <span class="text-blue-400">⚡</span>
            </div>
            <div class="font-mono font-black text-lg sm:text-xl text-blue-400">
                Rp {{ number_format($remainingBudget, 0, ',', '.') }}
            </div>
            <div class="text-[10px] text-slate-400 font-medium">
                Remaining Safe Spending
            </div>
        </div>
    </div>

    <!-- 3. MAIN SECTION: FINANCIAL HEALTH SCORE & GAMIFICATION CARD -->
    <div class="grid grid-cols-1 lg:grid-cols-12 gap-6">

        <!-- FINANCIAL HEALTH SCORE (Circular Indicator + Breakdown) -->
        <div class="lg:col-span-7 p-6 sm:p-8 rounded-3xl {{ $isCriticalMode ? $tokens['card_critical'] : $tokens['card_bg'] }} space-y-6">
            <div class="flex items-center justify-between">
                <div class="space-y-1">
                    <h2 class="{{ $tokens['font_heading'] }} text-lg text-white flex items-center space-x-2">
                        <span>{{ $labels['icon_hp'] ?? '💳' }}</span>
                        <span>{{ $labels['hp_title'] }}</span>
                    </h2>
                    <p class="text-xs text-slate-400">Real-time composite metric based on 5 financial stability vectors.</p>
                </div>
                <button @click="showHealthModal = true" class="px-4 py-2 rounded-2xl bg-cyan-500/20 text-cyan-300 border border-cyan-500/30 text-xs font-bold hover:bg-cyan-500/30 transition-all flex items-center space-x-1.5">
                    <span>💡 Improve Score</span>
                </button>
            </div>

            <div class="grid grid-cols-1 sm:grid-cols-12 gap-6 items-center">
                <!-- Circular SVG Progress Indicator -->
                <div class="sm:col-span-5 flex flex-col items-center justify-center p-4 bg-slate-950/60 rounded-3xl border border-slate-800">
                    <div class="relative w-36 h-36 flex items-center justify-center">
                        <svg class="w-full h-full transform -rotate-90" viewBox="0 0 100 100">
                            <!-- Background Track -->
                            <circle cx="50" cy="50" r="40" stroke="currentColor" stroke-width="8" class="text-slate-800" fill="transparent" />
                            <!-- Dynamic Progress Arc -->
                            <circle cx="50" cy="50" r="40" stroke="currentColor" stroke-width="8" 
                                     stroke-dasharray="251.2" 
                                     stroke-dashoffset="{{ 251.2 * (1 - ($hpPercentage / 100)) }}" 
                                     stroke-linecap="round"
                                     class="{{ $isCriticalMode ? 'text-rose-500' : 'text-cyan-400' }}" fill="transparent" />
                        </svg>
                        <div class="absolute flex flex-col items-center justify-center text-center">
                            <span class="font-mono font-black text-3xl text-white">{{ $hpPercentage }}</span>
                            <span class="text-[10px] text-slate-400 font-extrabold uppercase tracking-widest">OUT OF 100</span>
                        </div>
                    </div>
                    <div class="mt-3 text-center">
                        <span class="px-3 py-1 rounded-full text-[11px] font-bold {{ $hpPercentage >= 70 ? 'bg-emerald-500/20 text-emerald-300 border border-emerald-500/30' : ($hpPercentage >= 30 ? 'bg-amber-500/20 text-amber-300 border border-amber-500/30' : 'bg-rose-500/20 text-rose-300 border border-rose-500/30') }}">
                            {{ $hpPercentage >= 70 ? '"You\'re doing great!"' : ($hpPercentage >= 30 ? '"Keep an eye on budget!"' : '"Vault HP Critical!"') }}
                        </span>
                    </div>
                </div>

                <!-- Score Factor Breakdown Bars -->
                <div class="sm:col-span-7 space-y-3">
                    <div>
                        <div class="flex justify-between text-xs font-bold mb-1">
                            <span class="text-slate-300">Saving (Rasio Tabungan)</span>
                            <span class="text-cyan-400 font-mono">{{ $savingRatioPercent }}%</span>
                        </div>
                        <div class="h-2 w-full bg-slate-950 rounded-full overflow-hidden border border-slate-800">
                            <div class="h-full bg-cyan-400" style="width: {{ $savingRatioPercent }}%;"></div>
                        </div>
                    </div>

                    <div>
                        <div class="flex justify-between text-xs font-bold mb-1">
                            <span class="text-slate-300">Budget Discipline (Disiplin Anggaran)</span>
                            <span class="text-emerald-400 font-mono">{{ $budgetDisciplinePercent }}%</span>
                        </div>
                        <div class="h-2 w-full bg-slate-950 rounded-full overflow-hidden border border-slate-800">
                            <div class="h-full bg-emerald-400" style="width: {{ $budgetDisciplinePercent }}%;"></div>
                        </div>
                    </div>

                    <div>
                        <div class="flex justify-between text-xs font-bold mb-1">
                            <span class="text-slate-300">Spending Control (Kontrol Pengeluaran)</span>
                            <span class="text-purple-400 font-mono">{{ $spendingControlPercent }}%</span>
                        </div>
                        <div class="h-2 w-full bg-slate-950 rounded-full overflow-hidden border border-slate-800">
                            <div class="h-full bg-purple-400" style="width: {{ $spendingControlPercent }}%;"></div>
                        </div>
                    </div>

                    <div>
                        <div class="flex justify-between text-xs font-bold mb-1">
                            <span class="text-slate-300">Emergency Fund (Dana Darurat)</span>
                            <span class="text-amber-400 font-mono">{{ $emergencyFundPercent }}%</span>
                        </div>
                        <div class="h-2 w-full bg-slate-950 rounded-full overflow-hidden border border-slate-800">
                            <div class="h-full bg-amber-400" style="width: {{ $emergencyFundPercent }}%;"></div>
                        </div>
                    </div>

                    <div>
                        <div class="flex justify-between text-xs font-bold mb-1">
                            <span class="text-slate-300">Debt Management (Manajemen Utang)</span>
                            <span class="text-blue-400 font-mono">{{ $debtManagementPercent }}%</span>
                        </div>
                        <div class="h-2 w-full bg-slate-950 rounded-full overflow-hidden border border-slate-800">
                            <div class="h-full bg-blue-400" style="width: {{ $debtManagementPercent }}%;"></div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- GAMIFICATION LEVEL & XP PROGRESS CARD -->
        <div class="lg:col-span-5 p-6 sm:p-8 rounded-3xl {{ $tokens['card_bg'] }} space-y-6 flex flex-col justify-between">
            <div class="space-y-4">
                <div class="flex items-center justify-between">
                    <span class="px-3 py-1 rounded-full text-[10px] font-extrabold uppercase tracking-widest bg-amber-500/20 text-amber-300 border border-amber-500/30">
                        {{ $labels['level_name'] ?? 'Level' }} Progression
                    </span>
                    <span class="text-xs font-mono text-slate-400">AP Balance: {{ $apBalance }} AP</span>
                </div>

                <div class="flex items-center space-x-4">
                    <div class="w-16 h-16 rounded-2xl bg-gradient-to-tr from-amber-500 via-orange-500 to-yellow-400 p-0.5 shadow-xl">
                        <div class="w-full h-full bg-slate-950 rounded-[14px] flex flex-col items-center justify-center">
                            <span class="text-[10px] font-black text-amber-400 uppercase">LVL</span>
                            <span class="font-mono font-black text-2xl text-white leading-none">{{ $userLevel }}</span>
                        </div>
                    </div>
                    <div>
                        <div class="{{ $tokens['font_heading'] }} text-xl text-white">
                            {{ $rankTitle }}
                        </div>
                        <div class="text-xs text-slate-400 font-medium">
                            Rank {{ $userLevel }} Financial Operator
                        </div>
                    </div>
                </div>

                <!-- XP Bar -->
                <div class="space-y-2 pt-2">
                    <div class="flex justify-between text-xs font-bold">
                        <span class="text-slate-300">Total {{ $labels['xp_name'] ?? 'XP' }} Earned</span>
                        <span class="text-amber-400 font-mono">{{ $apBalance }} AP ({{ $xpPercentage }}% to Level {{ $userLevel + 1 }})</span>
                    </div>
                    <div class="h-3.5 w-full bg-slate-950 rounded-full overflow-hidden p-0.5 border border-slate-800">
                        <div class="h-full rounded-full {{ $tokens['progress_bar'] }}" style="width: {{ $xpPercentage }}%;"></div>
                    </div>
                </div>
            </div>

            <!-- XP Earning Rules Info Box -->
            <div class="p-4 rounded-2xl bg-slate-950/70 border border-slate-800 space-y-2 text-xs">
                <div class="font-bold text-slate-200 flex items-center space-x-1.5">
                    <span>✨</span>
                    <span>Cara Mendapatkan {{ $labels['xp_name'] ?? 'XP' }}:</span>
                </div>
                <div class="grid grid-cols-2 gap-2 text-[11px] text-slate-400">
                    <div>• Catat Transaksi: <strong class="text-emerald-400">+10 XP</strong></div>
                    <div>• Daily Challenge: <strong class="text-emerald-400">+50 XP</strong></div>
                    <div>• Disiplin Budget: <strong class="text-emerald-400">+100 XP</strong></div>
                    <div>• Selesai Target: <strong class="text-emerald-400">+250 XP</strong></div>
                </div>
            </div>
        </div>

    </div>

    <!-- 4. CHALLENGES & STREAK SECTION -->
    <div class="grid grid-cols-1 lg:grid-cols-12 gap-6">

        <!-- DAILY CHALLENGE COMPONENT -->
        <div class="lg:col-span-5 p-6 sm:p-8 rounded-3xl {{ $tokens['card_bg'] }} space-y-5 relative">
            <div class="flex items-center justify-between">
                <div class="flex items-center space-x-2">
                    <span class="text-lg">⚡</span>
                    <h3 class="{{ $tokens['font_heading'] }} text-base text-white">TODAY'S CHALLENGE</h3>
                </div>
                <span class="px-2.5 py-1 rounded-full text-[10px] font-mono font-bold bg-rose-500/20 text-rose-300 border border-rose-500/30">
                    ⏱️ 08:42:17
                </span>
            </div>

            <div class="p-4 rounded-2xl bg-slate-950/80 border border-slate-800 space-y-3">
                <div class="flex items-center justify-between">
                    <span class="font-bold text-sm text-white">Financial Check-in</span>
                    <span class="text-xs font-mono font-extrabold text-emerald-400">+100 XP</span>
                </div>

                <div class="space-y-2 text-xs">
                    <div class="flex items-center space-x-2 text-emerald-400">
                        <span>✓</span>
                        <span class="line-through text-slate-400">Review today's transactions</span>
                    </div>
                    <div class="flex items-center space-x-2 text-emerald-400">
                        <span>✓</span>
                        <span class="line-through text-slate-400">Review remaining budget</span>
                    </div>
                    <div class="flex items-center space-x-2 text-slate-300 font-semibold">
                        <span class="w-4 h-4 rounded-full border border-slate-600 inline-block"></span>
                        <span>Update savings progress</span>
                    </div>
                </div>

                <!-- Challenge Progress Bar -->
                <div class="space-y-1 pt-2">
                    <div class="flex justify-between text-[11px] font-bold text-slate-400">
                        <span>Progress Tasks (2 / 3)</span>
                        <span class="text-cyan-400 font-mono">67%</span>
                    </div>
                    <div class="h-2 w-full bg-slate-900 rounded-full overflow-hidden border border-slate-800">
                        <div class="h-full bg-cyan-400" style="width: 67%;"></div>
                    </div>
                </div>
            </div>

            <button class="w-full py-3 rounded-2xl {{ $tokens['primary_bg'] }} text-xs font-bold shadow-lg transition-all text-center">
                Continue Challenge →
            </button>
        </div>

        <!-- WEEKLY CHALLENGES CARDS -->
        <div class="lg:col-span-7 p-6 sm:p-8 rounded-3xl {{ $tokens['card_bg'] }} space-y-5">
            <div class="flex items-center justify-between">
                <h3 class="{{ $tokens['font_heading'] }} text-base text-white flex items-center space-x-2">
                    <span>🏆</span>
                    <span>WEEKLY CHALLENGES</span>
                </h3>
                <a href="{{ route('challenges') }}" class="text-xs text-cyan-400 hover:underline font-bold">Lihat Semua →</a>
            </div>

            <div class="grid grid-cols-1 sm:grid-cols-3 gap-4">
                
                <!-- 1. Budget Guardian -->
                <div class="p-4 rounded-2xl bg-slate-950/70 border border-slate-800 space-y-3 flex flex-col justify-between">
                    <div>
                        <div class="flex items-center justify-between mb-1">
                            <span class="text-xs font-bold text-white">Budget Guardian</span>
                            <span class="text-[10px] font-mono text-emerald-400 font-bold">+500 XP</span>
                        </div>
                        <p class="text-[11px] text-slate-400">Stay within your weekly food budget.</p>
                    </div>
                    <div class="space-y-1">
                        <div class="flex justify-between text-[10px] font-mono text-slate-400">
                            <span>Rp325K / Rp500K</span>
                            <span class="text-cyan-400 font-bold">65%</span>
                        </div>
                        <div class="h-1.5 w-full bg-slate-900 rounded-full overflow-hidden">
                            <div class="h-full bg-cyan-400" style="width: 65%;"></div>
                        </div>
                    </div>
                </div>

                <!-- 2. Smart Saver -->
                <div class="p-4 rounded-2xl bg-slate-950/70 border border-slate-800 space-y-3 flex flex-col justify-between">
                    <div>
                        <div class="flex items-center justify-between mb-1">
                            <span class="text-xs font-bold text-white">Smart Saver</span>
                            <span class="text-[10px] font-mono text-emerald-400 font-bold">+750 XP</span>
                        </div>
                        <p class="text-[11px] text-slate-400">Save Rp500.000 this week.</p>
                    </div>
                    <div class="space-y-1">
                        <div class="flex justify-between text-[10px] font-mono text-slate-400">
                            <span>Rp325K / Rp500K</span>
                            <span class="text-emerald-400 font-bold">65%</span>
                        </div>
                        <div class="h-1.5 w-full bg-slate-900 rounded-full overflow-hidden">
                            <div class="h-full bg-emerald-400" style="width: 65%;"></div>
                        </div>
                    </div>
                </div>

                <!-- 3. Expense Detective -->
                <div class="p-4 rounded-2xl bg-slate-950/70 border border-slate-800 space-y-3 flex flex-col justify-between">
                    <div>
                        <div class="flex items-center justify-between mb-1">
                            <span class="text-xs font-bold text-white">Expense Detective</span>
                            <span class="text-[10px] font-mono text-emerald-400 font-bold">+300 XP</span>
                        </div>
                        <p class="text-[11px] text-slate-400">Review all transactions daily.</p>
                    </div>
                    <div class="space-y-1">
                        <div class="flex justify-between text-[10px] font-mono text-slate-400">
                            <span>5 / 7 days</span>
                            <span class="text-purple-400 font-bold">71%</span>
                        </div>
                        <div class="h-1.5 w-full bg-slate-900 rounded-full overflow-hidden">
                            <div class="h-full bg-purple-400" style="width: 71%;"></div>
                        </div>
                    </div>
                </div>

            </div>
        </div>

    </div>

    <!-- 5. FEATURED MONTHLY CHALLENGE & STREAK SYSTEM CARD -->
    <div class="grid grid-cols-1 lg:grid-cols-12 gap-6">

        <!-- FEATURED MONTHLY CHALLENGE CARD -->
        <div class="lg:col-span-8 p-6 sm:p-8 rounded-3xl bg-gradient-to-br from-indigo-950/90 via-slate-900 to-slate-950 border border-indigo-500/40 shadow-2xl space-y-6 relative overflow-hidden">
            <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4">
                <div>
                    <span class="px-3 py-1 rounded-full text-[10px] font-extrabold uppercase tracking-widest bg-indigo-500/20 text-indigo-300 border border-indigo-500/30">
                        🌟 FEATURED MONTHLY CHALLENGE
                    </span>
                    <h3 class="{{ $tokens['font_heading'] }} text-xl text-white mt-2">
                        Budget Master
                    </h3>
                </div>
                <div class="text-right">
                    <span class="text-xs font-mono font-bold text-emerald-400 bg-emerald-500/10 px-3 py-1.5 rounded-full border border-emerald-500/30">
                        +2,500 XP + Exclusive Badge
                    </span>
                    <div class="text-[11px] text-slate-400 mt-1">⏳ 12 days remaining</div>
                </div>
            </div>

            <div class="grid grid-cols-1 sm:grid-cols-2 gap-3 text-xs">
                <div class="p-3 rounded-2xl bg-slate-950/60 border border-slate-800 flex items-center space-x-2 text-emerald-400 font-medium">
                    <span>✓</span>
                    <span class="text-slate-200">Track all expenses</span>
                </div>
                <div class="p-3 rounded-2xl bg-slate-950/60 border border-slate-800 flex items-center space-x-2 text-emerald-400 font-medium">
                    <span>✓</span>
                    <span class="text-slate-200">Stay within monthly budget</span>
                </div>
                <div class="p-3 rounded-2xl bg-slate-950/60 border border-slate-800 flex items-center space-x-2 text-emerald-400 font-medium">
                    <span>✓</span>
                    <span class="text-slate-200">Save at least Rp1.500.000</span>
                </div>
                <div class="p-3 rounded-2xl bg-slate-950/60 border border-slate-800 flex items-center space-x-2 text-slate-400 font-medium">
                    <span class="w-4 h-4 rounded-full border border-slate-600 inline-block"></span>
                    <span class="text-slate-300">Complete monthly financial review</span>
                </div>
            </div>

            <!-- Progress Bar -->
            <div class="space-y-2">
                <div class="flex justify-between text-xs font-bold">
                    <span class="text-slate-300">Monthly Completion</span>
                    <span class="text-indigo-400 font-mono">75%</span>
                </div>
                <div class="h-3 w-full bg-slate-950 rounded-full overflow-hidden p-0.5 border border-slate-800">
                    <div class="h-full bg-gradient-to-r from-indigo-500 to-cyan-400 rounded-full" style="width: 75%;"></div>
                </div>
            </div>
        </div>

        <!-- STREAK SYSTEM CARD -->
        <div class="lg:col-span-4 p-6 sm:p-8 rounded-3xl {{ $tokens['card_bg'] }} space-y-5 flex flex-col justify-between">
            <div class="space-y-3">
                <div class="flex items-center justify-between">
                    <span class="text-2xl">🔥</span>
                    <span class="text-xs font-mono font-bold text-amber-400 bg-amber-500/20 px-3 py-1 rounded-full border border-amber-500/30">
                        14 DAY STREAK
                    </span>
                </div>

                <h3 class="{{ $tokens['font_heading'] }} text-base text-white">
                    Financial Habit Streak
                </h3>
                <p class="text-xs text-slate-400">
                    "You're building a strong financial discipline habit."
                </p>
            </div>

            <!-- 7-Day Tracker Calendar -->
            <div class="space-y-2">
                <div class="text-[10px] font-bold text-slate-400 uppercase tracking-wider">This Week Tracker</div>
                <div class="grid grid-cols-7 gap-1.5 text-center text-xs font-mono">
                    <div class="p-2 rounded-xl bg-emerald-500/20 text-emerald-400 border border-emerald-500/40 font-bold">M<br>✓</div>
                    <div class="p-2 rounded-xl bg-emerald-500/20 text-emerald-400 border border-emerald-500/40 font-bold">T<br>✓</div>
                    <div class="p-2 rounded-xl bg-emerald-500/20 text-emerald-400 border border-emerald-500/40 font-bold">W<br>✓</div>
                    <div class="p-2 rounded-xl bg-emerald-500/20 text-emerald-400 border border-emerald-500/40 font-bold">T<br>✓</div>
                    <div class="p-2 rounded-xl bg-emerald-500/20 text-emerald-400 border border-emerald-500/40 font-bold">F<br>✓</div>
                    <div class="p-2 rounded-xl bg-emerald-500/20 text-emerald-400 border border-emerald-500/40 font-bold">S<br>✓</div>
                    <div class="p-2 rounded-xl bg-amber-500/30 text-amber-300 border border-amber-500/50 font-bold animate-pulse">S<br>🔥</div>
                </div>
            </div>
        </div>

    </div>

    <!-- 6. OCR RECEIPT INPUT & RECENT TRANSACTIONS -->
    <div class="grid grid-cols-1 lg:grid-cols-12 gap-6">

        <!-- OCR RECEIPT SCANNER CARD -->
        <div id="ocr-scanner" class="lg:col-span-5 p-6 sm:p-8 rounded-3xl {{ $tokens['card_bg'] }} space-y-4 scroll-mt-20">
            <div class="space-y-1">
                <h3 class="{{ $tokens['font_heading'] }} text-base text-white flex items-center space-x-2">
                    <span>{{ $labels['icon_ocr'] ?? '📷' }}</span>
                    <span>{{ $labels['ocr_card_title'] }}</span>
                </h3>
                <p class="text-xs text-slate-400">{{ $labels['ocr_rule_desc'] }}</p>
            </div>

            <!-- Upload Area -->
            <form wire:submit.prevent="uploadReceipt" class="space-y-4">
                <div class="border-2 border-dashed border-slate-700 hover:border-cyan-500/50 rounded-3xl p-6 text-center space-y-3 bg-slate-950/60 transition-all">
                    <div class="w-12 h-12 mx-auto rounded-2xl bg-cyan-500/10 text-cyan-400 flex items-center justify-center text-xl">
                        🧾
                    </div>
                    <div>
                        <label class="block text-xs font-bold text-slate-300 cursor-pointer hover:text-white">
                            <span>Upload Gambar Struk Retail</span>
                            <input type="file" wire:model="receiptImage" class="hidden" accept="image/*">
                        </label>
                        <p class="text-[10px] text-slate-500 mt-1">Super Indo, Alfamart, Indomaret, Transmart (JPG, PNG, WebP)</p>
                    </div>
                    @if ($receiptImage)
                        <div class="text-xs text-cyan-400 font-mono font-bold">
                            File terpilih: {{ $receiptImage->getClientOriginalName() }}
                        </div>
                    @endif
                </div>

                @if ($ocrSuccessMessage)
                    <div class="p-3 rounded-2xl bg-emerald-950/80 border border-emerald-500/40 text-xs text-emerald-300">
                        {{ $ocrSuccessMessage }}
                    </div>
                @endif

                @if ($ocrErrorMessage)
                    <div class="p-3 rounded-2xl bg-rose-950/80 border border-rose-500/40 text-xs text-rose-300">
                        {{ $ocrErrorMessage }}
                    </div>
                @endif

                <button type="submit" class="w-full py-3 rounded-2xl {{ $tokens['primary_bg'] }} text-xs font-bold shadow-lg transition-all" wire:loading.attr="disabled">
                    <span wire:loading.remove>Scan & Process Receipt</span>
                    <span wire:loading>Processing OCR AI...</span>
                </button>
            </form>
        </div>

        <!-- RECENT TRANSACTIONS TABLE -->
        <div class="lg:col-span-7 p-6 sm:p-8 rounded-3xl {{ $tokens['card_bg'] }} space-y-4">
            <div class="flex items-center justify-between">
                <h3 class="{{ $tokens['font_heading'] }} text-base text-white flex items-center space-x-2">
                    <span>💳</span>
                    <span>Transaksi Terbaru</span>
                </h3>
                <a href="{{ route('transactions') }}" class="text-xs text-cyan-400 font-bold hover:underline">Lihat Semua →</a>
            </div>

            <div class="space-y-2 overflow-x-auto">
                @forelse ($recentTransactions as $tx)
                    <div class="p-3.5 rounded-2xl bg-slate-950/60 border border-slate-800 flex items-center justify-between gap-4 hover:border-slate-700 transition-all text-xs">
                        <div class="flex items-center space-x-3 min-w-0">
                            <div class="w-9 h-9 rounded-xl bg-slate-900 border border-slate-800 flex items-center justify-center text-sm shrink-0">
                                {{ $tx->receipt_id ? '🧾' : '💳' }}
                            </div>
                            <div class="min-w-0">
                                <div class="font-bold text-white truncate">{{ $tx->merchant }}</div>
                                <div class="text-[10px] text-slate-400 font-mono">
                                    {{ $tx->transaction_date->format('d M Y, H:i') }}
                                </div>
                            </div>
                        </div>
                        <div class="text-right shrink-0">
                            <div class="font-mono font-black text-rose-400">
                                -Rp {{ number_format((float) $tx->amount, 0, ',', '.') }}
                            </div>
                            @if ($tx->receipt_id)
                                <button @click="$dispatch('openReceiptModal', { receiptId: {{ $tx->receipt_id }} })"
                                        type="button"
                                        class="mt-1 text-[9px] font-mono px-2 py-0.5 rounded-full bg-cyan-500/20 text-cyan-300 border border-cyan-500/30 hover:bg-cyan-500/30 transition-all cursor-pointer">
                                    🧾 Lihat Struk
                                </button>
                            @endif
                        </div>
                    </div>
                @empty
                    <div class="text-center py-8 text-xs text-slate-500">
                        Belum ada transaksi tercatat hari ini.
                    </div>
                @endforelse
            </div>
        </div>

    </div>

    <!-- 7. HEALTH SCORE RECOMMENDATIONS MODAL -->
    <div x-show="showHealthModal" x-cloak class="fixed inset-0 bg-slate-950/80 backdrop-blur-md z-50 flex items-center justify-center p-4">
        <div @click.away="showHealthModal = false" class="w-full max-w-lg bg-slate-900 border border-slate-800 rounded-3xl p-6 space-y-5 shadow-2xl">
            <div class="flex items-center justify-between border-b border-slate-800 pb-3">
                <h3 class="font-display font-bold text-base text-white flex items-center space-x-2">
                    <span>💡</span>
                    <span>Rekomendasi Meningkatkan Skor Keuangan</span>
                </h3>
                <button @click="showHealthModal = false" class="text-slate-400 hover:text-white font-bold">✕</button>
            </div>

            <div class="space-y-3 text-xs">
                <div class="p-3.5 rounded-2xl bg-amber-950/40 border border-amber-500/30 space-y-1">
                    <div class="font-bold text-amber-300">1. Tingkatkan Dana Darurat (+5 Poin Score)</div>
                    <p class="text-slate-300">Saat ini Dana Darurat Anda berada di angka 65%. Sisihkan tambahan Rp500.000 bulan ini untuk mencapai target 6 bulan pengeluaran.</p>
                </div>
                <div class="p-3.5 rounded-2xl bg-purple-950/40 border border-purple-500/30 space-y-1">
                    <div class="font-bold text-purple-300">2. Kontrol Pengeluaran Hiburan (+3 Poin Score)</div>
                    <p class="text-slate-300">Pengeluaran kategori Entertainment melebihi budget acuan minggu lalu. Kurangi langganan yang tidak terpakai.</p>
                </div>
                <div class="p-3.5 rounded-2xl bg-emerald-950/40 border border-emerald-500/30 space-y-1">
                    <div class="font-bold text-emerald-300">3. Pertahankan Disiplin Budgeting</div>
                    <p class="text-slate-300">Skor Budget Discipline Anda sangat baik (91%). Pertahankan rutin logging harian selama 7 hari ke depan!</p>
                </div>
            </div>

            <div class="flex justify-end pt-2">
                <button @click="showHealthModal = false" class="px-5 py-2.5 rounded-xl bg-cyan-500 hover:bg-cyan-400 text-slate-950 font-bold text-xs">
                    Paham, Tutup
                </button>
            </div>
        </div>
    </div>
    {{-- 8. OCR RECEIPT REVIEW MODAL --}}
    @if ($showReceiptModal && $activeReceiptModal)
        <div class="fixed inset-0 bg-slate-950/80 backdrop-blur-md z-50 flex items-center justify-center p-4">
            <div class="w-full max-w-lg bg-slate-900 border border-slate-700 rounded-3xl shadow-2xl overflow-hidden flex flex-col max-h-[90vh]">

                {{-- ── MODAL HEADER ── --}}
                <div class="p-5 border-b border-slate-800 flex items-center justify-between bg-gradient-to-r from-cyan-950/50 to-slate-900 shrink-0">
                    <div class="flex items-center space-x-3">
                        <div class="w-10 h-10 rounded-2xl bg-cyan-500/20 flex items-center justify-center text-lg">🧾</div>
                        <div>
                            @if ($isEditingReceipt)
                                <h3 class="font-display font-bold text-amber-300 text-sm">✏️ Edit Receipt Items</h3>
                                <p class="text-[10px] text-slate-400 font-mono">Koreksi item & nominal struk</p>
                            @else
                                <h3 class="font-display font-bold text-white text-sm">{{ $activeReceiptModal->merchant_name }}</h3>
                                <p class="text-[10px] text-slate-400 font-mono">{{ $activeReceiptModal->transaction_date?->format('d M Y, H:i') }} • Ref: {{ $activeReceiptModal->receipt_number }}</p>
                            @endif
                        </div>
                    </div>
                    <button wire:click="$set('showReceiptModal', false)" class="text-slate-400 hover:text-white font-bold text-lg leading-none">✕</button>
                </div>

                {{-- ── READ-ONLY VIEW ── --}}
                @if (!$isEditingReceipt)
                    <div class="overflow-y-auto flex-1 p-5 space-y-2">
                        <div class="text-[10px] font-bold text-slate-400 uppercase tracking-wider mb-3 flex items-center space-x-1">
                            <span>🤖</span>
                            <span>Gemini AI Extracted Items ({{ count($activeReceiptModal->items) }} item)</span>
                        </div>
                        @forelse ($activeReceiptModal->items as $item)
                            <div class="flex items-center justify-between py-2.5 border-b border-slate-800/60 last:border-0">
                                <div class="flex-1 min-w-0 pr-4">
                                    <div class="text-xs font-semibold text-white">{{ $item->item_name }}</div>
                                    <div class="text-[10px] text-slate-400 font-mono">
                                        {{ number_format((float)$item->quantity, 0) }}x @ Rp {{ number_format((float)$item->unit_price, 0, ',', '.') }}
                                    </div>
                                </div>
                                <div class="font-mono font-bold text-sm text-rose-400 shrink-0">
                                    Rp {{ number_format((float)$item->total_price, 0, ',', '.') }}
                                </div>
                            </div>
                        @empty
                            <div class="text-center py-6 text-xs text-slate-500">Tidak ada item terdeteksi.</div>
                        @endforelse
                    </div>

                    <div class="p-5 border-t border-slate-800 bg-slate-950/50 flex items-center justify-between shrink-0">
                        <div>
                            <div class="text-[10px] text-slate-400 uppercase tracking-wider font-bold">Total Struk</div>
                            <div class="font-mono font-black text-xl text-cyan-400">
                                Rp {{ number_format((float)$activeReceiptModal->total_amount, 0, ',', '.') }}
                            </div>
                            <div class="text-[10px] text-emerald-400 font-semibold mt-0.5">✅ Dicatat ke budget cycle • +10 XP</div>
                        </div>
                        <div class="flex flex-col space-y-2 text-right">
                            <button wire:click="startEditingReceipt"
                                    class="px-4 py-2 rounded-xl bg-amber-500/20 border border-amber-500/30 text-amber-300 text-xs font-bold hover:bg-amber-500/30 transition-all">
                                ✏️ Edit Items
                            </button>
                            <button wire:click="$set('showReceiptModal', false)"
                                    class="px-4 py-2 rounded-xl bg-emerald-500/20 border border-emerald-500/30 text-emerald-300 text-xs font-bold hover:bg-emerald-500/30 transition-all">
                                ✅ Selesai
                            </button>
                        </div>
                    </div>

                {{-- ── EDIT FORM ── --}}
                @else
                    <form wire:submit.prevent="saveReceiptCorrection" class="flex flex-col flex-1 overflow-hidden">

                        {{-- Merchant Name --}}
                        <div class="px-5 pt-4 pb-2 shrink-0 border-b border-slate-800">
                            <label class="text-[10px] font-bold text-slate-400 uppercase tracking-wider block mb-1">Nama Merchant / Toko</label>
                            <input type="text" wire:model="editMerchantName"
                                   class="w-full px-3 py-2 rounded-xl bg-slate-950 border border-slate-700 text-white text-xs focus:outline-none focus:border-amber-500"
                                   placeholder="Nama toko...">
                            @error('editMerchantName') <span class="text-[10px] text-rose-400 block mt-1">{{ $message }}</span> @enderror
                        </div>

                        {{-- Item Rows --}}
                        <div class="overflow-y-auto flex-1 p-5 space-y-3">
                            <div class="flex items-center justify-between mb-1">
                                <span class="text-[10px] font-bold text-slate-400 uppercase tracking-wider">Item Belanja</span>
                                <button type="button" wire:click="addItemRow"
                                        class="text-[10px] font-bold text-cyan-400 hover:text-cyan-300 flex items-center space-x-1">
                                    <span>＋</span><span>Tambah Item</span>
                                </button>
                            </div>

                            {{-- Column Header --}}
                            <div class="grid grid-cols-12 gap-1 text-[9px] font-bold text-slate-500 uppercase tracking-wider px-1">
                                <div class="col-span-5">Nama Item</div>
                                <div class="col-span-2 text-center">Qty</div>
                                <div class="col-span-2 text-right">Harga</div>
                                <div class="col-span-2 text-right">Total</div>
                                <div class="col-span-1"></div>
                            </div>

                            @foreach ($editItems as $idx => $editItem)
                                <div class="grid grid-cols-12 gap-1 items-center">
                                    {{-- Item Name --}}
                                    <div class="col-span-5">
                                        <input type="text"
                                               wire:model.live="editItems.{{ $idx }}.name"
                                               placeholder="Nama produk..."
                                               class="w-full px-2 py-1.5 rounded-lg bg-slate-950 border border-slate-700 text-white text-[11px] focus:outline-none focus:border-amber-500 placeholder:text-slate-600">
                                        @error("editItems.{$idx}.name") <span class="text-[9px] text-rose-400 block">{{ $message }}</span> @enderror
                                    </div>

                                    {{-- Qty --}}
                                    <div class="col-span-2">
                                        <input type="number"
                                               wire:model.live="editItems.{{ $idx }}.qty"
                                               min="0.01" step="any"
                                               class="w-full px-2 py-1.5 rounded-lg bg-slate-950 border border-slate-700 text-white text-[11px] text-center focus:outline-none focus:border-amber-500">
                                    </div>

                                    {{-- Unit Price --}}
                                    <div class="col-span-2">
                                        <input type="number"
                                               wire:model.live="editItems.{{ $idx }}.unit_price"
                                               min="0" step="any"
                                               class="w-full px-2 py-1.5 rounded-lg bg-slate-950 border border-slate-700 text-white text-[11px] text-right focus:outline-none focus:border-amber-500">
                                    </div>

                                    {{-- Total (auto-calculated, read-only) --}}
                                    <div class="col-span-2">
                                        <div class="px-2 py-1.5 rounded-lg bg-slate-800 border border-slate-700 text-cyan-300 text-[11px] text-right font-mono font-bold">
                                            {{ number_format((float)($editItem['total_price'] ?? 0), 0, ',', '.') }}
                                        </div>
                                    </div>

                                    {{-- Remove Button --}}
                                    <div class="col-span-1 flex justify-center">
                                        <button type="button" wire:click="removeItemRow({{ $idx }})"
                                                class="w-6 h-6 rounded-full bg-rose-500/20 text-rose-400 hover:bg-rose-500/40 text-xs font-bold flex items-center justify-center transition-all">
                                            ✕
                                        </button>
                                    </div>
                                </div>
                            @endforeach
                        </div>

                        {{-- Footer: Total & Actions --}}
                        <div class="p-5 border-t border-slate-800 bg-slate-950/50 shrink-0">
                            <div class="flex items-center justify-between mb-3">
                                <div>
                                    <div class="text-[10px] text-slate-400 uppercase tracking-wider font-bold">Total Baru</div>
                                    <div class="font-mono font-black text-xl text-amber-400">
                                        Rp {{ number_format((float)($editTotalAmount ?? 0), 0, ',', '.') }}
                                    </div>
                                    @error('editTotalAmount') <span class="text-[10px] text-rose-400 block">{{ $message }}</span> @enderror
                                </div>
                                <div class="flex space-x-2">
                                    <button type="button" wire:click="cancelEditingReceipt"
                                            class="px-4 py-2 rounded-xl bg-slate-800 border border-slate-700 text-slate-300 text-xs font-bold hover:bg-slate-700 transition-all">
                                        Batal
                                    </button>
                                    <button type="submit"
                                            class="px-5 py-2 rounded-xl bg-amber-500 hover:bg-amber-400 text-slate-950 text-xs font-extrabold shadow-lg shadow-amber-500/30 transition-all"
                                            wire:loading.attr="disabled">
                                        <span wire:loading.remove wire:target="saveReceiptCorrection">💾 Simpan Koreksi</span>
                                        <span wire:loading wire:target="saveReceiptCorrection">Menyimpan…</span>
                                    </button>
                                </div>
                            </div>
                        </div>

                    </form>
                @endif

            </div>
        </div>
    @endif

</div>
