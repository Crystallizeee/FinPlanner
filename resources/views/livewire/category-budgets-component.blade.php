@php
    $tokens = app(\App\Services\ThemeService::class)->getThemeTokens($themeMode);
@endphp

<div class="space-y-6">
    <!-- Header -->
    <div class="p-6 rounded-3xl {{ $tokens['card_bg'] }}">
        <h1 class="{{ $tokens['font_heading'] }} text-2xl text-white flex items-center space-x-2">
            <span>🛡️</span>
            <span>Category Budget Limits & Spending Alerts</span>
        </h1>
        <p class="text-xs text-slate-400 mt-1">Atur batas pengeluaran maksimum bulanan per kategori dan dapatkan peringatan sebelum anggaran terlampaui.</p>
    </div>

    @if ($successMessage)
        <div class="p-4 rounded-2xl bg-emerald-950/80 border border-emerald-500/50 text-emerald-300 text-xs font-semibold">
            {{ $successMessage }}
        </div>
    @endif

    <!-- Set Budget Form & Overview -->
    <div class="grid grid-cols-1 lg:grid-cols-12 gap-6">

        <!-- Form Modal / Panel -->
        <div class="lg:col-span-5 p-6 rounded-3xl {{ $tokens['card_bg'] }} space-y-4">
            <h3 class="{{ $tokens['font_heading'] }} text-base text-white">Set / Update Limit Kategori</h3>
            
            <form wire:submit.prevent="setBudget" class="space-y-4 text-xs">
                <div>
                    <label class="block text-slate-300 font-bold mb-1">Kategori Pengeluaran</label>
                    <select wire:model="category" class="w-full p-3 rounded-xl bg-slate-900 border border-slate-700 text-white focus:outline-none focus:border-cyan-400">
                        <option value="food">Food & Dining 🍲</option>
                        <option value="transport">Transportation 🚗</option>
                        <option value="entertainment">Entertainment 🎮</option>
                        <option value="utilities">Utilities & Bills 💡</option>
                        <option value="shopping">Shopping 🛒</option>
                        <option value="others">Others 📦</option>
                    </select>
                </div>

                <div>
                    <label class="block text-slate-300 font-bold mb-1">Batas Maksimum Bulanan (Rp)</label>
                    <input type="number" wire:model="amount_limit" placeholder="Contoh: 1500000" class="w-full p-3 rounded-xl bg-slate-900 border border-slate-700 text-white focus:outline-none focus:border-cyan-400">
                    @error('amount_limit') <span class="text-rose-400 text-[10px] mt-1">{{ $message }}</span> @enderror
                </div>

                <button type="submit" class="w-full py-3 rounded-xl bg-emerald-500 hover:bg-emerald-400 text-slate-950 font-bold text-xs shadow-lg transition-colors">
                    Simpan Batas Anggaran →
                </button>
            </form>
        </div>

        <!-- Budget Stats & Alerts -->
        <div class="lg:col-span-7 space-y-4">
            @forelse($budgetStats as $stat)
                <div class="p-5 rounded-3xl {{ $tokens['card_bg'] }} space-y-3 relative overflow-hidden border {{ $stat['is_exceeded'] ? 'border-rose-500/80 bg-rose-950/20' : ($stat['is_warning'] ? 'border-amber-500/80 bg-amber-950/20' : 'border-slate-800') }}">
                    <div class="flex items-center justify-between">
                        <div class="flex items-center space-x-2">
                            <span class="text-lg font-bold text-white uppercase">{{ $stat['category'] }}</span>
                            @if ($stat['is_exceeded'])
                                <span class="px-2 py-0.5 rounded-full text-[10px] bg-rose-500/20 text-rose-300 border border-rose-500/40 font-mono font-bold animate-pulse">
                                    🚨 EXCEEDED
                                </span>
                            @elseif ($stat['is_warning'])
                                <span class="px-2 py-0.5 rounded-full text-[10px] bg-amber-500/20 text-amber-300 border border-amber-500/40 font-mono font-bold">
                                    ⚠️ WARNING (80%+)
                                </span>
                            @else
                                <span class="px-2 py-0.5 rounded-full text-[10px] bg-emerald-500/20 text-emerald-300 border border-emerald-500/40 font-mono font-bold">
                                    SAFE
                                </span>
                            @endif
                        </div>
                        <button wire:click="deleteBudget({{ $stat['id'] }})" class="text-slate-500 hover:text-rose-400 text-xs font-mono">Hapus</button>
                    </div>

                    <div class="flex justify-between text-xs font-mono">
                        <span class="text-slate-400">Terpakai: <strong class="text-white">Rp {{ number_format($stat['spent'], 0, ',', '.') }}</strong></span>
                        <span class="text-slate-400">Limit: <strong class="text-slate-200">Rp {{ number_format($stat['limit'], 0, ',', '.') }}</strong></span>
                    </div>

                    <!-- Progress Bar -->
                    <div class="w-full bg-slate-900 rounded-full h-3 overflow-hidden border border-slate-800">
                        <div class="h-full rounded-full transition-all duration-500 {{ $stat['is_exceeded'] ? 'bg-rose-500' : ($stat['is_warning'] ? 'bg-amber-400' : 'bg-emerald-400') }}" style="width: {{ min(100, $stat['percentage']) }}%;"></div>
                    </div>

                    <div class="text-right text-[10px] font-mono text-slate-400">
                        Penggunaan: {{ $stat['percentage'] }}%
                    </div>
                </div>
            @empty
                <div class="p-8 rounded-3xl {{ $tokens['card_bg'] }} text-center text-slate-400 text-xs">
                    Belum ada batas anggaran kategori yang ditetapkan. Gunakan formulir di samping untuk membuat batas anggaran baru!
                </div>
            @endforelse
        </div>

    </div>
</div>
