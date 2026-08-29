@php
    $tokens = app(\App\Services\ThemeService::class)->getThemeTokens($themeMode);
@endphp

<div class="space-y-6">
    <!-- Header -->
    <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4 p-6 rounded-3xl {{ $tokens['card_bg'] }}">
        <div>
            <h1 class="{{ $tokens['font_heading'] }} text-2xl text-white flex items-center space-x-2">
                <span>🏆</span>
                <span>Achievement Gallery</span>
            </h1>
            <p class="text-xs text-slate-400 mt-1">Koleksi pencapaian finansial dan lencana kehormatan Anda.</p>
        </div>
        @php
            $unlockedCount = count(array_filter($achievements, fn($a) => $a['is_unlocked']));
            $totalCount = count($achievements);
            $percentage = $totalCount > 0 ? round(($unlockedCount / $totalCount) * 100) : 0;
        @endphp
        <div class="text-right">
            <span class="text-xs font-mono font-bold text-amber-400 bg-amber-500/20 px-4 py-2 rounded-2xl border border-amber-500/30">
                {{ $unlockedCount }} / {{ $totalCount }} Unlocked ({{ $percentage }}%)
            </span>
        </div>
    </div>

    <!-- Category Filter Tabs -->
    <div class="flex items-center space-x-2 overflow-x-auto pb-1 text-xs font-bold">
        <button wire:click="$set('selectedCategory', 'all')" class="px-4 py-2.5 rounded-2xl shrink-0 {{ $selectedCategory === 'all' ? $tokens['badge_style'] : 'bg-slate-900 text-slate-400 border border-slate-800' }}">
            Semua Category
        </button>
        <button wire:click="$set('selectedCategory', 'Getting Started')" class="px-4 py-2.5 rounded-2xl shrink-0 {{ $selectedCategory === 'Getting Started' ? $tokens['badge_style'] : 'bg-slate-900 text-slate-400 border border-slate-800' }}">
            Getting Started
        </button>
        <button wire:click="$set('selectedCategory', 'Budget')" class="px-4 py-2.5 rounded-2xl shrink-0 {{ $selectedCategory === 'Budget' ? $tokens['badge_style'] : 'bg-slate-900 text-slate-400 border border-slate-800' }}">
            Budget
        </button>
        <button wire:click="$set('selectedCategory', 'Saving')" class="px-4 py-2.5 rounded-2xl shrink-0 {{ $selectedCategory === 'Saving' ? $tokens['badge_style'] : 'bg-slate-900 text-slate-400 border border-slate-800' }}">
            Saving
        </button>
        <button wire:click="$set('selectedCategory', 'Consistency')" class="px-4 py-2.5 rounded-2xl shrink-0 {{ $selectedCategory === 'Consistency' ? $tokens['badge_style'] : 'bg-slate-900 text-slate-400 border border-slate-800' }}">
            Consistency
        </button>
    </div>

    <!-- Achievements Grid -->
    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4">
        @foreach ($achievements as $ach)
            @if ($selectedCategory === 'all' || $selectedCategory === $ach['category'])
                <div class="p-5 rounded-3xl {{ $ach['is_unlocked'] ? $tokens['card_bg'] : 'bg-slate-950/40 border border-slate-900 opacity-60' }} space-y-3 relative flex flex-col justify-between">
                    <div class="space-y-3">
                        <div class="flex items-center justify-between">
                            <div class="w-12 h-12 rounded-2xl {{ $ach['is_unlocked'] ? 'bg-amber-500/20 text-amber-300 border border-amber-500/40' : 'bg-slate-900 text-slate-600 border border-slate-800' }} flex items-center justify-center text-2xl shadow-lg">
                                {{ $ach['icon'] }}
                            </div>
                            <span class="text-[10px] font-mono font-bold px-2 py-0.5 rounded-full {{ $ach['is_unlocked'] ? 'bg-emerald-500/20 text-emerald-300 border border-emerald-500/40' : 'bg-slate-900 text-slate-500 border border-slate-800' }}">
                                {{ $ach['is_unlocked'] ? 'UNLOCKED ✓' : 'LOCKED 🔒' }}
                            </span>
                        </div>

                        <div>
                            <h3 class="font-display font-bold text-sm text-white">{{ $ach['title'] }}</h3>
                            <p class="text-xs text-slate-400 mt-1">{{ $ach['description'] }}</p>
                        </div>
                    </div>

                    <div class="pt-3 border-t border-slate-800/80 space-y-1">
                        <div class="flex justify-between text-[10px] font-mono text-slate-400">
                            <span>Progress: {{ $ach['progress'] }}</span>
                            <span class="text-amber-400 font-bold">{{ $ach['reward'] }}</span>
                        </div>
                    </div>
                </div>
            @endif
        @endforeach
    </div>
</div>
