<x-layouts.app>
    <div class="max-w-3xl mx-auto py-12">
        <div class="glass-panel-critical rounded-3xl p-8 md:p-12 text-center relative overflow-hidden border-2 border-red-600/60 shadow-[0_0_50px_rgba(239,68,68,0.3)]">
            <!-- Animated background red flare -->
            <div class="absolute -top-24 -left-24 w-72 h-72 bg-red-600/20 rounded-full blur-3xl pointer-events-none"></div>
            <div class="absolute -bottom-24 -right-24 w-72 h-72 bg-rose-600/20 rounded-full blur-3xl pointer-events-none"></div>

            <div class="inline-flex items-center justify-center w-24 h-24 rounded-3xl bg-red-950/80 border-2 border-red-500/50 text-red-500 mb-6 shadow-[0_0_20px_rgba(239,68,68,0.5)] animate-bounce">
                <svg class="w-12 h-12" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 15v2m0 4h.01M5.07 19H18.93c1.34 0 2.2-1.4 1.58-2.58L13.58 4.42c-.62-1.18-2.54-1.18-3.16 0L3.49 16.42C2.87 17.6 3.73 19 5.07 19z"/>
                </svg>
            </div>

            <h1 class="font-display font-black text-3xl md:text-4xl text-red-400 tracking-tight mb-3">
                ACCESS LOCKED BY FINANCIAL DISCIPLINE SYSTEM
            </h1>
            <p class="text-red-200/90 text-sm md:text-base max-w-xl mx-auto mb-8 font-medium">
                {{ $reason ?? 'Your daily financial entry streak was broken due to inactivity (>24h without receipt upload or bank sync).' }}
            </p>

            <div class="p-4 rounded-2xl bg-black/40 border border-red-900/60 text-left max-w-md mx-auto mb-8 text-xs font-mono text-slate-300 space-y-2">
                <div class="flex justify-between border-b border-red-950 pb-2">
                    <span class="text-slate-400">Penalty Status:</span>
                    <span class="text-red-400 font-bold">STREAK BROKEN (0 DAYS)</span>
                </div>
                <div class="flex justify-between border-b border-red-950 pb-2">
                    <span class="text-slate-400">Lock Target:</span>
                    <span class="text-amber-400">Advanced Analytics & Quest Portfolio</span>
                </div>
                <div class="flex justify-between">
                    <span class="text-slate-400">Required Action to Unlock:</span>
                    <span class="text-emerald-400 font-bold">Upload Daily Receipt</span>
                </div>
            </div>

            <div class="flex flex-col sm:flex-row items-center justify-center gap-4">
                <a href="{{ route('dashboard') }}" class="w-full sm:w-auto px-8 py-3.5 rounded-2xl bg-gradient-to-r from-red-600 to-rose-700 hover:from-red-500 hover:to-rose-600 text-white font-bold text-sm tracking-wide shadow-lg shadow-red-900/50 transition-all flex items-center justify-center space-x-2">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-8l-4-4m0 0L8 8m4-4v12"/>
                    </svg>
                    <span>GO TO DASHBOARD & UPLOAD RECEIPT</span>
                </a>
            </div>
        </div>
    </div>
</x-layouts.app>
