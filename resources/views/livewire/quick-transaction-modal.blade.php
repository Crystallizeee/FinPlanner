@php
    $tokens = app(\App\Services\ThemeService::class)->getThemeTokens($themeMode);
@endphp

<div>
    <!-- GLOBAL QUICK ADD TRANSACTION MODAL -->
    @if ($openAddModal)
        <div class="fixed inset-0 bg-slate-950/80 backdrop-blur-md z-50 flex items-center justify-center p-4">
            <div class="w-full max-w-md bg-slate-900 border border-slate-800 rounded-3xl p-6 space-y-5 shadow-2xl">
                <div class="flex items-center justify-between border-b border-slate-800 pb-4">
                    <h3 class="font-display font-bold text-lg text-white flex items-center space-x-2">
                        <span>💳</span>
                        <span>Catat Transaksi / Pindah Saldo</span>
                    </h3>
                    <button wire:click="close" type="button" class="text-slate-400 hover:text-white font-bold">✕</button>
                </div>

                <form wire:submit.prevent="saveTransaction" class="space-y-4">
                    <div>
                        <label class="block text-xs font-bold text-slate-400 uppercase tracking-wider mb-2">Tipe Transaksi</label>
                        <div class="grid grid-cols-3 gap-2">
                            <button type="button" wire:click="$set('type', 'expense')" class="py-2.5 rounded-xl font-bold text-[11px] transition-all {{ $type === 'expense' ? 'bg-rose-500/20 text-rose-300 border border-rose-500/40 shadow-sm' : 'bg-slate-950 text-slate-400 border border-slate-800' }}">
                                💸 Pengeluaran
                            </button>
                            <button type="button" wire:click="$set('type', 'income')" class="py-2.5 rounded-xl font-bold text-[11px] transition-all {{ $type === 'income' ? 'bg-emerald-500/20 text-emerald-300 border border-emerald-500/40 shadow-sm' : 'bg-slate-950 text-slate-400 border border-slate-800' }}">
                                💰 Pemasukan
                            </button>
                            <button type="button" wire:click="$set('type', 'transfer')" class="py-2.5 rounded-xl font-bold text-[11px] transition-all {{ $type === 'transfer' ? 'bg-cyan-500/20 text-cyan-300 border border-cyan-500/40 shadow-sm' : 'bg-slate-950 text-slate-400 border border-slate-800' }}">
                                🔄 Pindah Saldo
                            </button>
                        </div>
                    </div>

                    <div>
                        <label class="block text-xs font-bold text-slate-400 uppercase tracking-wider mb-1">Nominal (Rp)</label>
                        <input type="number" wire:model="amount" placeholder="Contoh: 150000" class="w-full px-4 py-3 rounded-2xl bg-slate-950 border border-slate-800 text-white font-mono text-sm focus:outline-none focus:border-cyan-500">
                        @error('amount') <span class="text-xs text-rose-400 block mt-1">{{ $message }}</span> @enderror
                    </div>

                    @if ($type === 'transfer')
                        <div class="grid grid-cols-2 gap-3">
                            <div>
                                <label class="block text-xs font-bold text-slate-400 uppercase tracking-wider mb-1">Dari Akun (Asal)</label>
                                <select wire:model="sourceAccount" class="w-full px-3 py-2.5 rounded-2xl bg-slate-950 border border-slate-800 text-white text-xs focus:outline-none focus:border-cyan-500">
                                    @foreach ($userAccounts as $acc)
                                        <option value="{{ $acc->name }}">{{ $acc->icon }} {{ $acc->name }}</option>
                                    @endforeach
                                </select>
                            </div>

                            <div>
                                <label class="block text-xs font-bold text-slate-400 uppercase tracking-wider mb-1">Ke Akun (Tujuan)</label>
                                <select wire:model="targetAccount" class="w-full px-3 py-2.5 rounded-2xl bg-slate-950 border border-slate-800 text-white text-xs focus:outline-none focus:border-cyan-500">
                                    @foreach ($userAccounts as $acc)
                                        <option value="{{ $acc->name }}">{{ $acc->icon }} {{ $acc->name }}</option>
                                    @endforeach
                                </select>
                                @error('targetAccount') <span class="text-xs text-rose-400 block mt-1">{{ $message }}</span> @enderror
                            </div>
                        </div>
                    @else
                        <div>
                            <label class="block text-xs font-bold text-slate-400 uppercase tracking-wider mb-1">Merchant / Keterangan Toko</label>
                            <input type="text" wire:model="merchant" placeholder="Contoh: Super Indo Grocery / Warung Makan" class="w-full px-4 py-3 rounded-2xl bg-slate-950 border border-slate-800 text-white text-sm focus:outline-none focus:border-cyan-500">
                            @error('merchant') <span class="text-xs text-rose-400 block mt-1">{{ $message }}</span> @enderror
                        </div>

                        <div class="grid grid-cols-2 gap-3">
                            <div>
                                <label class="block text-xs font-bold text-slate-400 uppercase tracking-wider mb-1">Kategori</label>
                                <select wire:model="category" class="w-full px-3 py-2.5 rounded-2xl bg-slate-950 border border-slate-800 text-white text-xs focus:outline-none focus:border-cyan-500">
                                    <option value="Makanan & Minuman">🍱 Makanan & Minuman</option>
                                    <option value="Belanja Bulanan">🛒 Belanja Bulanan</option>
                                    <option value="Bahan Bakar & Transportasi">🚗 Transportasi</option>
                                    <option value="Listrik & Tagihan">⚡ Tagihan & Utility</option>
                                    <option value="Hiburan & Lifestyle">🎮 Hiburan & Hobi</option>
                                    <option value="Lainnya">📦 Lainnya</option>
                                </select>
                            </div>

                            <div>
                                <label class="block text-xs font-bold text-slate-400 uppercase tracking-wider mb-1">Sumber Akun</label>
                                <select wire:model="account" class="w-full px-3 py-2.5 rounded-2xl bg-slate-950 border border-slate-800 text-white text-xs focus:outline-none focus:border-cyan-500">
                                    @foreach ($userAccounts as $acc)
                                        <option value="{{ $acc->name }}">{{ $acc->icon }} {{ $acc->name }}</option>
                                    @endforeach
                                </select>
                            </div>
                        </div>
                    @endif

                    @if ($type !== 'transfer')
                        <div class="p-3 rounded-2xl bg-cyan-950/40 border border-cyan-500/30 text-[11px] text-cyan-300 font-semibold flex items-center justify-between">
                            <div class="flex items-center space-x-2">
                                <span>🧾</span>
                                <span>Punya Struk Fisik / Digital?</span>
                            </div>
                            <a href="{{ route('dashboard') }}#ocr-scanner" wire:click="close" class="px-3 py-1 rounded-lg bg-cyan-500/20 text-cyan-200 border border-cyan-500/40 hover:bg-cyan-500/30 font-bold text-[10px]">
                                Gunakan AI OCR Scanner →
                            </a>
                        </div>
                    @endif

                    <div class="p-3 rounded-2xl bg-emerald-950/40 border border-emerald-500/30 text-[11px] text-emerald-300 font-semibold flex items-center space-x-2">
                        <span>✨</span>
                        <span>{{ $type === 'transfer' ? 'Pindah saldo antar akun memberikan' : 'Menyelesaikan transaksi hari ini akan memberikan' }} <strong>+10 XP</strong>!</span>
                    </div>

                    <div class="flex justify-end space-x-3 pt-2">
                        <button type="button" wire:click="close" class="px-4 py-2.5 rounded-xl text-slate-400 text-xs font-bold">Batal</button>
                        <button type="submit" class="px-6 py-2.5 rounded-xl {{ $tokens['primary_bg'] }} text-xs font-bold shadow-lg shadow-cyan-500/30">
                            {{ $type === 'transfer' ? 'Proses Pindah Saldo' : 'Simpan Transaksi' }}
                        </button>
                    </div>
                </form>
            </div>
        </div>
    @endif
</div>
