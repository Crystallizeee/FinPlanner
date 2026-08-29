@php
    $tokens = app(\App\Services\ThemeService::class)->getThemeTokens($themeMode);
@endphp

<div class="space-y-6">
    <!-- Header -->
    <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4 p-6 rounded-3xl {{ $tokens['card_bg'] }}">
        <div>
            <h1 class="{{ $tokens['font_heading'] }} text-2xl text-white flex items-center space-x-2">
                <span>💳</span>
                <span>Transkrip & Histori Transaksi</span>
            </h1>
            <p class="text-xs text-slate-400 mt-1">Kelola dan pantau seluruh alokasi pemasukan & pengeluaran Anda.</p>
        </div>
        <button @click="$dispatch('openQuickTransactionModal')" class="px-4 py-2.5 rounded-2xl {{ $tokens['primary_bg'] }} text-xs font-bold shadow-lg transition-all flex items-center space-x-2 cursor-pointer">
            <span>➕</span>
            <span>Tambah Transaksi Baru</span>
        </button>
    </div>

    <!-- Filters & Search Bar -->
    <div class="p-4 rounded-3xl {{ $tokens['card_bg'] }} grid grid-cols-1 sm:grid-cols-12 gap-3 items-center">
        <!-- Search Input -->
        <div class="sm:col-span-6 relative">
            <input type="text" wire:model.live.debounce.300ms="search" placeholder="Cari merchant, toko, atau keterangan..." 
                   class="w-full pl-10 pr-4 py-2.5 rounded-2xl bg-slate-950 border border-slate-800 text-xs text-white placeholder-slate-500 focus:outline-none focus:border-cyan-500">
            <span class="absolute left-3.5 top-3 text-xs text-slate-500">🔍</span>
        </div>

        <!-- Filter Buttons -->
        <div class="sm:col-span-6 flex items-center justify-end space-x-2 text-xs font-bold">
            <button wire:click="$set('filterType', 'all')" class="px-3 py-2 rounded-xl {{ $filterType === 'all' ? $tokens['badge_style'] : 'bg-slate-950 text-slate-400 border border-slate-800' }}">
                Semua
            </button>
            <button wire:click="$set('filterType', 'expense')" class="px-3 py-2 rounded-xl {{ $filterType === 'expense' ? 'bg-rose-500/20 text-rose-300 border border-rose-500/30' : 'bg-slate-950 text-slate-400 border border-slate-800' }}">
                Pengeluaran
            </button>
            <button wire:click="$set('filterType', 'income')" class="px-3 py-2 rounded-xl {{ $filterType === 'income' ? 'bg-emerald-500/20 text-emerald-300 border border-emerald-500/30' : 'bg-slate-950 text-slate-400 border border-slate-800' }}">
                Pemasukan
            </button>
        </div>
    </div>

    <!-- Sample Alert Banner: Unusual Spending Highlight -->
    <div class="p-4 rounded-2xl bg-amber-950/40 border border-amber-500/40 text-xs text-amber-300 flex items-center justify-between">
        <div class="flex items-center space-x-2">
            <span class="text-base">🚨</span>
            <div>
                <strong>Unusual Spending Detected:</strong> Transaksi di Electronics Store (Rp2.450.000) 45% lebih tinggi dari rata-rata pengeluaran reguler.
            </div>
        </div>
        <span class="text-[10px] font-mono bg-amber-500/20 px-2 py-1 rounded-full border border-amber-500/40 font-bold">Review</span>
    </div>

    <!-- Transactions Table -->
    <div class="p-6 rounded-3xl {{ $tokens['card_bg'] }} space-y-3">
        <div class="space-y-2">
            @forelse ($transactions as $index => $tx)
                <div class="p-4 rounded-2xl bg-slate-950/60 border border-slate-800/80 hover:border-slate-700 transition-all flex flex-col sm:flex-row sm:items-center justify-between gap-3 text-xs">
                    <div class="flex items-center space-x-3 min-w-0">
                        <div class="w-10 h-10 rounded-xl bg-slate-900 border border-slate-800 flex items-center justify-center text-base shrink-0">
                            {{ $tx->receipt_id ? '🧾' : '💳' }}
                        </div>
                        <div class="min-w-0 space-y-0.5">
                            <div class="font-bold text-white flex items-center space-x-2">
                                <span>{{ $tx->merchant }}</span>
                                @if ($index === 0)
                                    <span class="text-[9px] font-mono px-2 py-0.5 rounded-full bg-rose-500/20 text-rose-300 border border-rose-500/40 font-bold">
                                        ⚠️ Unusual Spending Detected
                                    </span>
                                @endif
                            </div>
                            <div class="text-[10px] text-slate-400 font-mono flex items-center space-x-3">
                                <span>📅 {{ $tx->transaction_date->format('d M Y, H:i') }}</span>
                                <span>🏦 {{ $tx->account_name ?: 'Bank BCA Gaji' }}</span>
                                <span>🏷️ {{ $tx->category ?: 'Umum' }}</span>
                            </div>
                        </div>
                    </div>

                    <div class="text-right shrink-0 font-mono space-y-1">
                        <div class="font-black text-sm text-rose-400">
                            -Rp {{ number_format((float) $tx->amount, 0, ',', '.') }}
                        </div>
                        @if ($tx->receipt_id)
                            <button @click="$dispatch('openReceiptModal', { receiptId: {{ $tx->receipt_id }} })"
                                    type="button"
                                    class="block text-[9px] font-mono px-2 py-0.5 rounded-full bg-cyan-500/20 text-cyan-300 border border-cyan-500/30 hover:bg-cyan-500/30 transition-all cursor-pointer w-full text-center">
                                🧾 Lihat Struk
                            </button>
                        @else
                            <div class="text-[10px] text-slate-400">
                                XP: <strong class="text-emerald-400">+10</strong>
                            </div>
                        @endif
                    </div>
                </div>
            @empty
                <!-- Empty State -->
                <div class="text-center py-12 space-y-3">
                    <div class="text-4xl">💳</div>
                    <div class="font-bold text-slate-300 text-sm">Your financial journey starts here.</div>
                    <p class="text-xs text-slate-500">Belum ada transaksi tercatat yang sesuai dengan pencarian Anda.</p>
                    <button @click="$dispatch('openQuickTransactionModal')" class="px-5 py-2 rounded-xl {{ $tokens['primary_bg'] }} text-xs font-bold cursor-pointer">
                        Add your first transaction
                    </button>
                </div>
            @endforelse
        </div>
    </div>
</div>
