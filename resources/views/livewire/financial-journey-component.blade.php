@php
    $tokens = app(\App\Services\ThemeService::class)->getThemeTokens($themeMode);
@endphp

<div class="space-y-6">
    <!-- Header -->
    <div class="p-6 rounded-3xl {{ $tokens['card_bg'] }}">
        <h1 class="{{ $tokens['font_heading'] }} text-2xl text-white flex items-center space-x-2">
            <span>🧭</span>
            <span>Financial Progression Journey</span>
        </h1>
        <p class="text-xs text-slate-400 mt-1">Peta perjalanan karir keuangan Anda dari Level 1 Financial Rookie hingga Level 100 Financial Legend.</p>
    </div>

    <!-- Visual Journey Map Timeline -->
    <div class="p-6 sm:p-8 rounded-3xl {{ $tokens['card_bg'] }} space-y-6">
        <div class="relative pl-6 sm:pl-8 border-l-2 border-slate-800 space-y-8">
            @foreach ($stages as $stage)
                @php
                    $isCurrent = !empty($stage['is_current']);
                    $isPassed = $stage['level'] <= 10;
                @endphp
                <div class="relative group">
                    <!-- Timeline Node Icon -->
                    <div class="absolute -left-[35px] sm:-left-[43px] top-0 w-10 h-10 rounded-2xl {{ $isCurrent ? 'bg-amber-500 text-slate-950 ring-4 ring-amber-500/30 font-black animate-pulse' : ($isPassed ? 'bg-cyan-500/20 text-cyan-400 border border-cyan-500/40' : 'bg-slate-900 text-slate-600 border border-slate-800') }} flex items-center justify-center text-lg shadow-lg">
                        {{ $stage['icon'] }}
                    </div>

                    <!-- Card Body -->
                    <div class="p-5 rounded-2xl {{ $isCurrent ? 'bg-gradient-to-r from-amber-950/60 via-slate-900 to-slate-950 border border-amber-500/50 shadow-xl' : ($isPassed ? 'bg-slate-950/80 border border-slate-800' : 'bg-slate-950/30 border border-slate-900/60 opacity-50') }} space-y-2">
                        <div class="flex items-center justify-between">
                            <div class="flex items-center space-x-3">
                                <span class="font-mono font-bold text-xs px-2.5 py-0.5 rounded-full {{ $isCurrent ? 'bg-amber-500/20 text-amber-300 border border-amber-500/40' : 'bg-slate-900 text-slate-400 border border-slate-800' }}">
                                    Level {{ $stage['level'] }}
                                </span>
                                <h3 class="font-display font-bold text-base text-white">
                                    {{ $stage['title'] }}
                                </h3>
                            </div>
                            @if ($isCurrent)
                                <span class="px-3 py-1 rounded-full text-[10px] font-extrabold uppercase tracking-widest bg-amber-500 text-slate-950 shadow-md">
                                    📍 Posisi Anda Saat Ini
                                </span>
                            @endif
                        </div>
                        <p class="text-xs text-slate-400">{{ $stage['desc'] }}</p>
                    </div>
                </div>
            @endforeach
        </div>
    </div>
</div>
