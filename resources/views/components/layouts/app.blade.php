@php
    $themeService = app(\App\Services\ThemeService::class);
    $user = \Illuminate\Support\Facades\Auth::user() ?? \App\Models\User::first();
    $currentMode = $themeService->getActiveMode($user);
    $labels = $themeService->getLabels($currentMode);
    $tokens = $themeService->getThemeTokens($currentMode);

    $level = 12;
    $currentXp = 7450;
    $nextLevelXp = 10000;
    $xpPercent = round(($currentXp / $nextLevelXp) * 100);
    $userName = $user ? $user->name : 'Warrior';
    $userInitial = strtoupper(substr($userName, 0, 1));
@endphp
<!DOCTYPE html>
<html lang="id" class="dark h-full">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>{{ $labels['brand_name'] }} — {{ $labels['brand_tagline'] }}</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Cinzel:wght@600;700;900&family=Outfit:wght@300;400;500;600;700;800;900&family=Plus+Jakarta+Sans:wght@400;500;600;700;800&display=swap" rel="stylesheet">
    <script src="https://cdn.tailwindcss.com"></script>
    <script>
        tailwind.config = {
            darkMode: 'class',
            theme: {
                extend: {
                    fontFamily: {
                        sans: ['Plus Jakarta Sans', 'sans-serif'],
                        display: ['Outfit', 'sans-serif'],
                        serif: ['Cinzel', 'serif'],
                    },
                    animation: {
                        'pulse-fast': 'pulse 1s cubic-bezier(0.4, 0, 0.6, 1) infinite',
                        'glow': 'glow 2s ease-in-out infinite alternate',
                    },
                    keyframes: {
                        glow: {
                            '0%': { 'box-shadow': '0 0 10px rgba(6, 182, 212, 0.4)' },
                            '100%': { 'box-shadow': '0 0 25px rgba(6, 182, 212, 0.8)' },
                        }
                    }
                }
            }
        }
    </script>
    <style>
        [x-cloak] { display: none !important; }
        .glass-panel {
            backdrop-filter: blur(16px);
        }
    </style>
    @livewireStyles
</head>
<body class="h-full antialiased {{ $tokens['bg_body'] }}" x-data="{ openAddModal: false, showNotifications: false }">
    <div class="min-h-screen flex flex-col md:flex-row">

        <!-- DESKTOP SIDEBAR -->
        <aside class="hidden md:flex flex-col w-64 border-r border-slate-800/80 bg-slate-950/90 backdrop-blur-xl shrink-0 sticky top-0 h-screen z-40">
            <!-- Sidebar Header Branding -->
            <div class="p-6 border-b border-slate-900 flex items-center space-x-3">
                <div class="w-10 h-10 rounded-2xl p-0.5 shadow-lg bg-gradient-to-tr from-blue-600 via-cyan-500 to-emerald-400">
                    <div class="w-full h-full bg-slate-950 rounded-[14px] flex items-center justify-center font-black text-lg text-cyan-400 font-display">
                        F
                    </div>
                </div>
                <div>
                    <span class="{{ $tokens['font_heading'] }} text-lg tracking-tight bg-clip-text text-transparent bg-gradient-to-r from-white via-slate-200 to-slate-400">
                        {{ $labels['brand_name'] }}
                    </span>
                    <span class="text-[9px] font-extrabold tracking-widest uppercase block -mt-1 {{ $tokens['primary_accent'] }}">
                        {{ $labels['brand_tagline'] }}
                    </span>
                </div>
            </div>

            <!-- Navigation Links -->
            <nav class="flex-1 px-4 py-6 space-y-1.5 overflow-y-auto">
                <a href="{{ route('dashboard') }}" class="flex items-center space-x-3 px-4 py-3 rounded-2xl text-xs font-bold transition-all {{ request()->routeIs('dashboard') ? $tokens['badge_style'] . ' shadow-sm' : 'text-slate-400 hover:text-white hover:bg-slate-900/60' }}">
                    <span>{{ $labels['nav_dashboard'] }}</span>
                </a>

                <a href="{{ route('transactions') }}" class="flex items-center space-x-3 px-4 py-3 rounded-2xl text-xs font-bold transition-all {{ request()->routeIs('transactions') ? $tokens['badge_style'] . ' shadow-sm' : 'text-slate-400 hover:text-white hover:bg-slate-900/60' }}">
                    <span>{{ $labels['nav_transactions'] }}</span>
                </a>

                <a href="{{ route('accounts') }}" class="flex items-center space-x-3 px-4 py-3 rounded-2xl text-xs font-bold transition-all {{ request()->routeIs('accounts') ? $tokens['badge_style'] . ' shadow-sm' : 'text-slate-400 hover:text-white hover:bg-slate-900/60' }}">
                    <span>{{ $labels['nav_accounts'] ?? '👛 Accounts' }}</span>
                </a>

                <a href="{{ route('budget') }}" class="flex items-center space-x-3 px-4 py-3 rounded-2xl text-xs font-bold transition-all {{ request()->routeIs('budget') ? $tokens['badge_style'] . ' shadow-sm' : 'text-slate-400 hover:text-white hover:bg-slate-900/60' }}">
                    <span>{{ $labels['nav_budget'] }}</span>
                </a>

                <a href="{{ route('category-budgets') }}" class="flex items-center space-x-3 px-4 py-3 rounded-2xl text-xs font-bold transition-all {{ request()->routeIs('category-budgets') ? $tokens['badge_style'] . ' shadow-sm' : 'text-slate-400 hover:text-white hover:bg-slate-900/60' }}">
                    <span>🛡️ Limit Kategori</span>
                </a>

                <a href="{{ route('subscriptions') }}" class="flex items-center space-x-3 px-4 py-3 rounded-2xl text-xs font-bold transition-all {{ request()->routeIs('subscriptions') ? $tokens['badge_style'] . ' shadow-sm' : 'text-slate-400 hover:text-white hover:bg-slate-900/60' }}">
                    <span>🔄 Tagihan Rutin</span>
                </a>

                <a href="{{ route('financial-health') }}" class="flex items-center space-x-3 px-4 py-3 rounded-2xl text-xs font-bold transition-all {{ request()->routeIs('financial-health') ? $tokens['badge_style'] . ' shadow-sm' : 'text-slate-400 hover:text-white hover:bg-slate-900/60' }}">
                    <span>🏥 Health Index</span>
                </a>

                <a href="{{ route('debt-planner') }}" class="flex items-center space-x-3 px-4 py-3 rounded-2xl text-xs font-bold transition-all {{ request()->routeIs('debt-planner') ? $tokens['badge_style'] . ' shadow-sm' : 'text-slate-400 hover:text-white hover:bg-slate-900/60' }}">
                    <span>💳 Debt Planner</span>
                </a>

                <a href="{{ route('cashflow-predictor') }}" class="flex items-center space-x-3 px-4 py-3 rounded-2xl text-xs font-bold transition-all {{ request()->routeIs('cashflow-predictor') ? $tokens['badge_style'] . ' shadow-sm' : 'text-slate-400 hover:text-white hover:bg-slate-900/60' }}">
                    <span>🔮 AI Cashflow</span>
                </a>

                <a href="{{ route('wishlist') }}" class="flex items-center space-x-3 px-4 py-3 rounded-2xl text-xs font-bold transition-all {{ request()->routeIs('wishlist') ? $tokens['badge_style'] . ' shadow-sm' : 'text-slate-400 hover:text-white hover:bg-slate-900/60' }}">
                    <span>⏳ Cooling Wishlist</span>
                </a>

                <a href="{{ route('exchange-rates') }}" class="flex items-center space-x-3 px-4 py-3 rounded-2xl text-xs font-bold transition-all {{ request()->routeIs('exchange-rates') ? $tokens['badge_style'] . ' shadow-sm' : 'text-slate-400 hover:text-white hover:bg-slate-900/60' }}">
                    <span>💱 Valas & Emas</span>
                </a>

                <a href="{{ route('goals') }}" class="flex items-center space-x-3 px-4 py-3 rounded-2xl text-xs font-bold transition-all {{ request()->routeIs('goals') ? $tokens['badge_style'] . ' shadow-sm' : 'text-slate-400 hover:text-white hover:bg-slate-900/60' }}">
                    <span>{{ $labels['nav_goals'] }}</span>
                </a>

                <a href="{{ route('analytics') }}" class="flex items-center space-x-3 px-4 py-3 rounded-2xl text-xs font-bold transition-all {{ request()->routeIs('analytics') ? $tokens['badge_style'] . ' shadow-sm' : 'text-slate-400 hover:text-white hover:bg-slate-900/60' }}">
                    <span>{{ $labels['nav_analytics'] }}</span>
                </a>

                <a href="{{ route('challenges') }}" class="flex items-center space-x-3 px-4 py-3 rounded-2xl text-xs font-bold transition-all {{ request()->routeIs('challenges') ? $tokens['badge_style'] . ' shadow-sm' : 'text-slate-400 hover:text-white hover:bg-slate-900/60' }}">
                    <span>{{ $labels['nav_challenges'] }}</span>
                </a>

                <a href="{{ route('achievements') }}" class="flex items-center space-x-3 px-4 py-3 rounded-2xl text-xs font-bold transition-all {{ request()->routeIs('achievements') ? $tokens['badge_style'] . ' shadow-sm' : 'text-slate-400 hover:text-white hover:bg-slate-900/60' }}">
                    <span>{{ $labels['nav_achievements'] }}</span>
                </a>

                <a href="{{ route('journey') }}" class="flex items-center space-x-3 px-4 py-3 rounded-2xl text-xs font-bold transition-all {{ request()->routeIs('journey') ? $tokens['badge_style'] . ' shadow-sm' : 'text-slate-400 hover:text-white hover:bg-slate-900/60' }}">
                    <span>{{ $labels['nav_journey'] }}</span>
                </a>

                <a href="{{ route('settings') }}" class="flex items-center space-x-3 px-4 py-3 rounded-2xl text-xs font-bold transition-all {{ request()->routeIs('settings') ? $tokens['badge_style'] . ' shadow-sm' : 'text-slate-400 hover:text-white hover:bg-slate-900/60' }}">
                    <span>{{ $labels['nav_settings'] }}</span>
                </a>
            </nav>

            <!-- User Gamification Widget & Logout at Bottom of Sidebar -->
            <div class="p-4 border-t border-slate-900 bg-slate-950/60 space-y-2">
                <a href="{{ route('profile') }}" class="flex items-center space-x-3 p-3 rounded-2xl bg-slate-900/80 border border-slate-800/80 hover:border-slate-700 transition-all">
                    <div class="w-10 h-10 rounded-xl bg-gradient-to-tr from-amber-500 to-orange-500 flex items-center justify-center font-bold text-slate-950 font-display">
                        {{ $userInitial }}
                    </div>
                    <div class="flex-1 min-w-0">
                        <div class="flex items-center justify-between">
                            <span class="text-xs font-black text-white truncate">{{ $userName }}</span>
                            <span class="text-[10px] font-extrabold px-2 py-0.5 rounded-full bg-amber-500/20 text-amber-300 border border-amber-500/30">
                                {{ $labels['level_name'] }} {{ $level }}
                            </span>
                        </div>
                        <div class="text-[10px] text-slate-400 font-semibold mt-0.5 truncate">Money Strategist</div>

                        <!-- Progress Bar -->
                        <div class="w-full h-1.5 bg-slate-950 rounded-full mt-2 overflow-hidden border border-slate-800">
                            <div class="h-full {{ $tokens['progress_bar'] }}" style="width: {{ $xpPercent }}%;"></div>
                        </div>
                        <div class="flex justify-between text-[9px] text-slate-400 font-mono mt-1">
                            <span>{{ number_format($currentXp) }} {{ $labels['xp_name'] }}</span>
                            <span>{{ $xpPercent }}%</span>
                        </div>
                    </div>
                </a>

                @auth
                <form method="POST" action="{{ route('logout') }}">
                    @csrf
                    <button type="submit" class="w-full py-2 px-3 rounded-xl bg-rose-950/40 hover:bg-rose-900/60 border border-rose-800/50 text-rose-300 text-xs font-bold flex items-center justify-center space-x-2 transition-all cursor-pointer">
                        <span>🚪</span>
                        <span>Keluar Akun</span>
                    </button>
                </form>
                @endauth
            </div>
        </aside>

        <!-- MAIN CONTAINER -->
        <div class="flex-1 flex flex-col min-w-0 pb-20 md:pb-8">

            <!-- Top Header Bar -->
            <header class="{{ $tokens['header_bg'] }} border-b sticky top-0 z-30 h-16">
                <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 h-full flex items-center justify-between gap-4">
                    
                    <!-- Mobile Branding -->
                    <div class="flex items-center space-x-3 md:hidden">
                        <div class="w-8 h-8 rounded-xl p-0.5 shadow-lg bg-gradient-to-tr from-blue-600 to-cyan-400">
                            <div class="w-full h-full bg-slate-950 rounded-[9px] flex items-center justify-center font-black text-xs text-cyan-400 font-display">
                                F
                            </div>
                        </div>
                        <div>
                            <span class="{{ $tokens['font_heading'] }} text-base text-white">
                                {{ $labels['brand_name'] }}
                            </span>
                        </div>
                    </div>

                    <!-- Desktop Header Action Buttons -->
                    <div class="hidden md:flex items-center space-x-3">
                        <span class="text-xs font-bold px-3 py-1 rounded-full {{ $tokens['badge_style'] }}">
                            {{ $labels['theme_badge'] }}
                        </span>
                    </div>

                    <!-- Right Action Controls -->
                    <div class="flex items-center space-x-3">
                        <!-- Quick Add Transaction Desktop Button -->
                        <button @click="$dispatch('openQuickTransactionModal')" class="hidden sm:flex items-center space-x-2 px-4 py-2 rounded-2xl {{ $tokens['primary_bg'] }} shadow-lg transition-all text-xs cursor-pointer">
                            <span class="text-sm">➕</span>
                            <span>Catat Transaksi</span>
                        </button>

                        <!-- Theme Switcher Livewire Component -->
                        <livewire:theme-switcher />

                        <!-- Notification Bell Button -->
                        <button @click="showNotifications = !showNotifications" class="p-2.5 rounded-2xl bg-slate-900 border border-slate-800 text-slate-300 hover:text-white relative transition-all">
                            <span class="text-base">🔔</span>
                            <span class="absolute top-1.5 right-1.5 w-2 h-2 rounded-full bg-cyan-400 animate-ping"></span>
                            <span class="absolute top-1.5 right-1.5 w-2 h-2 rounded-full bg-cyan-400"></span>
                        </button>
                    </div>
                </div>
            </header>

            <!-- Notifications Drawer Modal -->
            <div x-show="showNotifications" x-cloak @click.away="showNotifications = false" class="fixed top-16 right-4 sm:right-8 w-80 sm:w-96 bg-slate-950/95 border border-slate-800 rounded-3xl p-5 shadow-2xl z-50 space-y-4 backdrop-blur-xl">
                <div class="flex items-center justify-between border-b border-slate-800 pb-3">
                    <h3 class="font-display font-bold text-sm text-white flex items-center space-x-2">
                        <span>🔔</span>
                        <span>Pemberitahuan Sistem</span>
                    </h3>
                    <button @click="showNotifications = false" class="text-xs text-slate-400 hover:text-white">✕</button>
                </div>
                <div class="space-y-3 max-h-80 overflow-y-auto pr-1">
                    <div class="p-3 rounded-2xl bg-slate-900/80 border border-amber-500/30 text-xs space-y-1">
                        <div class="flex items-center justify-between text-amber-400 font-bold">
                            <span>⚡ Challenge Tersedia</span>
                            <span class="text-[10px] text-slate-400">Baru saja</span>
                        </div>
                        <p class="text-slate-300">Daily Challenge "Financial Check-in" siap diselesaikan (+100 XP).</p>
                    </div>
                    <div class="p-3 rounded-2xl bg-slate-900/80 border border-emerald-500/30 text-xs space-y-1">
                        <div class="flex items-center justify-between text-emerald-400 font-bold">
                            <span>🏆 Achievement Unlocked!</span>
                            <span class="text-[10px] text-slate-400">1 jam lalu</span>
                        </div>
                        <p class="text-slate-300">Anda berhasil mempertahankan 14 Day Financial Streak (+500 XP).</p>
                    </div>
                    <div class="p-3 rounded-2xl bg-slate-900/80 border border-blue-500/30 text-xs space-y-1">
                        <div class="flex items-center justify-between text-blue-400 font-bold">
                            <span>💡 Insight Keuangan AI</span>
                            <span class="text-[10px] text-slate-400">Kemarin</span>
                        </div>
                        <p class="text-slate-300">Pengeluaran Makanan turun 12% dibandingkan minggu lalu. Kerja bagus!</p>
                    </div>
                </div>
            </div>

            <!-- Page Main Content Slot -->
            <main class="flex-1 max-w-7xl w-full mx-auto px-4 sm:px-6 lg:px-8 py-6 sm:py-8 space-y-4">
                @if (session('global_success'))
                    <div class="p-4 rounded-2xl bg-emerald-950/90 border border-emerald-500/40 text-xs text-emerald-300 font-bold flex items-center justify-between shadow-xl">
                        <div class="flex items-center space-x-2">
                            <span>🎉</span>
                            <span>{{ session('global_success') }}</span>
                        </div>
                    </div>
                @endif

                {{ $slot }}
            </main>

            <!-- Footer -->
            <footer class="border-t border-slate-900 py-6 text-center text-xs text-slate-500 mt-auto">
                <div class="max-w-7xl mx-auto px-4 flex flex-col sm:flex-row items-center justify-between gap-2">
                    <span>{{ $labels['brand_name'] }} &copy; 2026 — {{ $labels['brand_tagline'] }}</span>
                    <span class="font-mono text-slate-600">Integrated IDR Financial Ledger & Gamified Habits</span>
                </div>
            </footer>
        </div>

        <!-- MOBILE BOTTOM NAVIGATION -->
        <nav class="sm:hidden fixed bottom-0 left-0 right-0 z-40 bg-slate-950/90 backdrop-blur-md border-t border-slate-900 px-6 py-2 flex items-center justify-between text-[10px] font-bold">
            <a href="{{ route('dashboard') }}" class="flex flex-col items-center space-y-1 {{ request()->routeIs('dashboard') ? 'text-cyan-400' : 'text-slate-400' }}">
                <span class="text-base">⚡</span>
                <span>Hub</span>
            </a>
            <a href="{{ route('transactions') }}" class="flex flex-col items-center space-y-1 {{ request()->routeIs('transactions') ? 'text-cyan-400' : 'text-slate-400' }}">
                <span class="text-base">💳</span>
                <span>Transaksi</span>
            </a>

            <!-- Floating Action '+' Add Button -->
            <button @click="$dispatch('openQuickTransactionModal')" class="-mt-6 w-12 h-12 rounded-full bg-gradient-to-tr from-cyan-500 via-blue-600 to-indigo-600 text-white font-black text-xl flex items-center justify-center shadow-lg shadow-cyan-500/40 border-2 border-slate-950 active:scale-95 transition-all cursor-pointer">
                ＋
            </button>

            <a href="{{ route('goals') }}" class="flex flex-col items-center space-y-1 {{ request()->routeIs('goals') ? 'text-cyan-400' : 'text-slate-400' }}">
                <span class="text-base">🎯</span>
                <span>Target</span>
            </a>
            <a href="{{ route('challenges') }}" class="flex flex-col items-center space-y-1 {{ request()->routeIs('challenges') ? 'text-cyan-400' : 'text-slate-400' }}">
                <span class="text-base">⚡</span>
                <span>Quests</span>
            </a>
        </nav>

        <!-- Livewire Quick Transaction Modal Component -->
        <livewire:quick-transaction-modal />

        <!-- Livewire Receipt Detail & Edit Modal (global, triggered by openReceiptModal event) -->
        <livewire:receipt-detail-modal />
    </div>

    @livewireScripts
</body>
</html>
