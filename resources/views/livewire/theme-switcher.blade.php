@php
    $themeLabels = [
        'fintech' => ['icon' => '💼', 'name' => 'FinTech Minimal'],
        'cyber' => ['icon' => '⚡', 'name' => 'Cyber Finance'],
        'gameful' => ['icon' => '🎮', 'name' => 'Gameful RPG'],
        'wealth' => ['icon' => '💎', 'name' => 'Premium Wealth'],
    ];
    $current = $themeLabels[$currentMode] ?? $themeLabels['fintech'];
@endphp

<button wire:click="cycleTheme" 
        title="Switch Visual Theme Mode"
        class="flex items-center space-x-2 px-3 py-1.5 rounded-full bg-slate-900 border border-slate-800 hover:border-slate-700 text-xs text-slate-300 font-bold transition-all shadow-md active:scale-95">
    <span>{{ $current['icon'] }}</span>
    <span class="hidden md:inline">{{ $current['name'] }}</span>
    <span class="text-[10px] text-slate-500 font-mono">🔄</span>
</button>
