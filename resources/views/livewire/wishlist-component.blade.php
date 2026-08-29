@php
    $tokens = app(\App\Services\ThemeService::class)->getThemeTokens($themeMode);
@endphp

<div class="space-y-8">
    <!-- Header -->
    <div class="glass-panel rounded-3xl p-6 sm:p-8 border border-slate-800 flex flex-col md:flex-row md:items-center justify-between gap-4">
        <div>
            <div class="flex items-center space-x-2">
                <span class="text-2xl">⏳</span>
                <h1 class="font-display font-black text-2xl md:text-3xl text-white">
                    Impulse Control Wishlist (30-Day Cooling-Off Matrix)
                </h1>
            </div>
            <p class="text-xs sm:text-sm text-slate-400 mt-1">
                Tahan impulsivitas belanja! Kunci rencana pembelian barang non-pokok selama 30 hari sebelum mengeksekusi transaksi.
            </p>
        </div>
    </div>

    @if ($successMessage)
        <div class="p-4 rounded-2xl bg-emerald-950/80 border border-emerald-500/50 text-emerald-300 text-xs font-semibold">
            {{ $successMessage }}
        </div>
    @endif

    <div class="grid grid-cols-1 lg:grid-cols-12 gap-6">
        <!-- Form Add Wishlist -->
        <div class="lg:col-span-5 p-6 rounded-3xl {{ $tokens['card_bg'] }} space-y-4">
            <h3 class="{{ $tokens['font_heading'] }} text-base text-white">+ Tambah Barang Impian Ke Matrix</h3>

            <form wire:submit.prevent="addWishlist" class="space-y-3 text-xs">
                <div>
                    <label class="block text-slate-300 font-bold mb-1">Nama Barang / Pengeluaran</label>
                    <input type="text" wire:model="item_name" placeholder="Contoh: PlayStation 5, Sepatu Sneakers, Headphone" class="w-full p-3 rounded-xl bg-slate-900 border border-slate-700 text-white focus:outline-none focus:border-cyan-400">
                    @error('item_name') <span class="text-rose-400 text-[10px] mt-1">{{ $message }}</span> @enderror
                </div>

                <div>
                    <label class="block text-slate-300 font-bold mb-1">Estimasi Harga (Rp)</label>
                    <input type="number" wire:model="price" placeholder="7500000" class="w-full p-3 rounded-xl bg-slate-900 border border-slate-700 text-white focus:outline-none focus:border-cyan-400">
                    @error('price') <span class="text-rose-400 text-[10px] mt-1">{{ $message }}</span> @enderror
                </div>

                <div class="grid grid-cols-2 gap-2">
                    <div>
                        <label class="block text-slate-300 font-bold mb-1">Kategori</label>
                        <select wire:model="category" class="w-full p-3 rounded-xl bg-slate-900 border border-slate-700 text-white focus:outline-none focus:border-cyan-400">
                            <option value="lifestyle">Gaya Hidup & Hobi</option>
                            <option value="electronics">Elektronik & Gadget</option>
                            <option value="fashion">Pakaian & Fashion</option>
                            <option value="entertainment">Hiburan & Liburan</option>
                        </select>
                    </div>

                    <div>
                        <label class="block text-slate-300 font-bold mb-1">Jeda Waktu (Hari)</label>
                        <input type="number" wire:model="cooling_off_days" min="1" max="90" class="w-full p-3 rounded-xl bg-slate-900 border border-slate-700 text-white focus:outline-none focus:border-cyan-400">
                    </div>
                </div>

                <button type="submit" class="w-full py-3 rounded-xl bg-cyan-500 hover:bg-cyan-400 text-slate-950 font-bold text-xs shadow-lg transition-colors">
                    🔒 Kunci Ke Matrix Jeda Waktu
                </button>
            </form>
        </div>

        <!-- Wishlist List -->
        <div class="lg:col-span-7 space-y-4">
            <h3 class="{{ $tokens['font_heading'] }} text-base text-white flex items-center justify-between">
                <span>Matriks Barang Dalam Masa Jeda</span>
                <span class="text-xs font-mono text-slate-400">{{ count($items) }} Item</span>
            </h3>

            @forelse($items as $item)
                @php
                    $isUnlocked = $item->isUnlocked();
                    $daysRemaining = $item->getDaysRemaining();
                @endphp
                <div class="p-5 rounded-3xl {{ $tokens['card_bg'] }} border {{ $isUnlocked ? 'border-emerald-500/50' : 'border-slate-800' }} space-y-3">
                    <div class="flex items-center justify-between">
                        <div>
                            <span class="px-2.5 py-0.5 rounded-full text-[10px] font-mono font-bold uppercase bg-slate-800 text-slate-300">
                                {{ $item->category }}
                            </span>
                            <h4 class="text-white font-bold text-base mt-1">{{ $item->item_name }}</h4>
                        </div>

                        <div class="text-right font-mono">
                            <div class="text-cyan-400 font-black text-base">Rp {{ number_format((float)$item->price, 0, ',', '.') }}</div>
                            <div class="text-[10px] text-slate-400">Target Unlocked: {{ $item->unlock_at->format('d M Y') }}</div>
                        </div>
                    </div>

                    <div class="flex items-center justify-between pt-3 border-t border-slate-800 text-xs font-mono">
                        @if($item->is_purchased)
                            <span class="text-emerald-400 font-bold">✅ Selesai Dibeli</span>
                        @elseif($isUnlocked)
                            <div class="flex items-center space-x-3 w-full justify-between">
                                <span class="text-emerald-400 font-bold animate-pulse">🔓 KUNCI TERBUKA! Siap Dibeli.</span>
                                <div class="flex items-center space-x-2">
                                    <select wire:model="selectedAccountId" class="p-1.5 rounded-xl bg-slate-900 border border-slate-700 text-white text-[11px]">
                                        @foreach($accounts as $acc)
                                            <option value="{{ $acc->id }}">{{ $acc->name }} (Rp{{ number_format($acc->balance,0,',','.') }})</option>
                                        @endforeach
                                    </select>
                                    <button wire:click="purchaseItem({{ $item->id }})" class="px-4 py-2 rounded-xl bg-emerald-500 hover:bg-emerald-400 text-slate-950 font-bold text-xs shadow-lg transition-colors">
                                        🛒 Beli Sekarang
                                    </button>
                                </div>
                            </div>
                        @else
                            <div class="flex items-center justify-between w-full">
                                <span class="text-amber-400 font-bold">🔒 Terkunci (Sisa {{ $daysRemaining }} Hari Jeda)</span>
                                <button wire:click="deleteItem({{ $item->id }})" class="text-slate-500 hover:text-rose-400 text-[11px]">
                                    Batalkan Pembelian
                                </button>
                            </div>
                        @endif
                    </div>
                </div>
            @empty
                <div class="p-8 rounded-3xl {{ $tokens['card_bg'] }} text-center text-slate-400 text-xs">
                    🎉 Belum ada barang impian dalam matriks cooling-off.
                </div>
            @endforelse
        </div>
    </div>
</div>
