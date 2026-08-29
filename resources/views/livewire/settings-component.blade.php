@php
    $tokens = app(\App\Services\ThemeService::class)->getThemeTokens($currentTheme);
@endphp

<div class="space-y-6">
    <!-- Header -->
    <div class="p-6 rounded-3xl {{ $tokens['card_bg'] }}">
        <h1 class="{{ $tokens['font_heading'] }} text-2xl text-white flex items-center space-x-2">
            <span>⚙️</span>
            <span>Konfigurasi & Pengaturan Sistem</span>
        </h1>
        <p class="text-xs text-slate-400 mt-1">Sesuaikan identitas visual tema dan tingkat gamifikasi sesuai preferensi Anda.</p>
    </div>

    <!-- 1. VISUAL THEME SELECTION SYSTEM (4 THEMES) -->
    <div class="p-6 sm:p-8 rounded-3xl {{ $tokens['card_bg'] }} space-y-6">
        <div>
            <h2 class="{{ $tokens['font_heading'] }} text-lg text-white">Visual Design System Themes</h2>
            <p class="text-xs text-slate-400 mt-0.5">Pilih salah satu dari 4 tema visual tanpa mengubah arsitektur informasi sistem.</p>
        </div>

        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
            @foreach ($availableThemes as $themeKey => $theme)
                @php
                    $isSelected = $currentTheme === $themeKey;
                @endphp
                <div wire:click="selectTheme('{{ $themeKey }}')" 
                     class="p-5 rounded-3xl cursor-pointer transition-all border-2 relative space-y-3 shadow-lg {{ $isSelected ? 'border-cyan-400 bg-cyan-950/40 ring-4 ring-cyan-500/20' : 'border-slate-800 bg-slate-950/80 hover:border-slate-700' }}">
                    
                    <div class="flex items-center justify-between">
                        <span class="text-sm font-bold text-white flex items-center space-x-2">
                            <span>{{ $theme['badge'] }}</span>
                        </span>
                        @if ($isSelected)
                            <span class="px-2.5 py-0.5 rounded-full text-[10px] font-mono font-bold bg-cyan-400 text-slate-950">
                                ACTIVE TEMA ✓
                            </span>
                        @endif
                    </div>

                    <p class="text-xs text-slate-300">{{ $theme['description'] }}</p>

                    <!-- Color Swatches Bar -->
                    <div class="flex items-center space-x-2 pt-2">
                        <span class="text-[10px] text-slate-500 font-mono">Palette:</span>
                        <div class="flex items-center space-x-1.5">
                            @foreach ($theme['colors'] as $hex)
                                <span class="w-4 h-4 rounded-full border border-slate-700 inline-block shadow-sm" style="background-color: {{ $hex }};"></span>
                            @endforeach
                        </div>
                    </div>
                </div>
            @endforeach
        </div>
    </div>

    <!-- 2. GAMIFICATION CONTROLS & TOGGLES -->
    <div class="p-6 sm:p-8 rounded-3xl {{ $tokens['card_bg'] }} space-y-6">
        <div>
            <h2 class="{{ $tokens['font_heading'] }} text-lg text-white">Gamification Preferences</h2>
            <p class="text-xs text-slate-400 mt-0.5">Kontrol fitur gamifikasi. Anda dapat menonaktifkan gamifikasi tanpa mengganggu fungsionalitas pencatatan keuangan.</p>
        </div>

        <div class="space-y-4 text-xs">
            <div class="flex items-center justify-between p-4 rounded-2xl bg-slate-950/60 border border-slate-800">
                <div>
                    <div class="font-bold text-white">Enable XP System</div>
                    <div class="text-slate-400">Dapatkan XP setiap kali mencatat transaksi & disiplin budget.</div>
                </div>
                <input type="checkbox" wire:model.live="enableXp" class="w-5 h-5 accent-cyan-500 rounded cursor-pointer">
            </div>

            <div class="flex items-center justify-between p-4 rounded-2xl bg-slate-950/60 border border-slate-800">
                <div>
                    <div class="font-bold text-white">Enable Challenges</div>
                    <div class="text-slate-400">Tampilkan tantangan harian, mingguan, dan bulanan.</div>
                </div>
                <input type="checkbox" wire:model.live="enableChallenges" class="w-5 h-5 accent-cyan-500 rounded cursor-pointer">
            </div>

            <div class="flex items-center justify-between p-4 rounded-2xl bg-slate-950/60 border border-slate-800">
                <div>
                    <div class="font-bold text-white">Enable Streaks</div>
                    <div class="text-slate-400">Pantau streak pencatatan transaksi harian berturut-turut.</div>
                </div>
                <input type="checkbox" wire:model.live="enableStreaks" class="w-5 h-5 accent-cyan-500 rounded cursor-pointer">
            </div>

            <div class="flex items-center justify-between p-4 rounded-2xl bg-slate-950/60 border border-slate-800">
                <div>
                    <div class="font-bold text-white">Show Celebration Animations</div>
                    <div class="text-slate-400">Tampilkan animasi selebrasi saat menaikkan level atau membuka achievement.</div>
                </div>
                <input type="checkbox" wire:model.live="enableAnimations" class="w-5 h-5 accent-cyan-500 rounded cursor-pointer">
            </div>
        </div>
    </div>
</div>
