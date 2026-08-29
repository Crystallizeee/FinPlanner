@php
    $tokens = app(\App\Services\ThemeService::class)->getThemeTokens($themeMode);
@endphp

<div class="space-y-6">
    @if ($successMessage)
        <div class="p-4 rounded-2xl bg-emerald-950/80 border border-emerald-500/40 text-xs text-emerald-300 font-bold flex items-center justify-between shadow-lg">
            <div class="flex items-center space-x-2">
                <span>🎉</span>
                <span>{{ $successMessage }}</span>
            </div>
            <button wire:click="$set('successMessage', null)" class="text-slate-400 hover:text-white">✕</button>
        </div>
    @endif

    <!-- Header Summary Card -->
    <div class="p-6 sm:p-8 rounded-3xl {{ $tokens['card_bg'] }} space-y-6">
        <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4">
            <div>
                <h1 class="{{ $tokens['font_heading'] }} text-2xl text-white flex items-center space-x-2">
                    <span>👛</span>
                    <span>{{ $labels['nav_accounts'] ?? 'Accounts & Wallets' }}</span>
                </h1>
                <p class="text-xs text-slate-400 mt-1">Kelola seluruh rekening bank, e-wallet, dompet tunai, dan akun investasi dalam satu tempat.</p>
            </div>
            <button wire:click="openCreateModal" class="px-5 py-2.5 rounded-2xl {{ $tokens['primary_bg'] }} text-xs font-bold shadow-lg flex items-center space-x-2 transition-all">
                <span>＋</span>
                <span>Tambah Akun / Wallet</span>
            </button>
        </div>

        <div class="grid grid-cols-1 sm:grid-cols-3 gap-4">
            <div class="p-5 rounded-2xl bg-slate-950/70 border border-slate-800 space-y-1">
                <div class="text-xs text-slate-400 font-bold uppercase tracking-wider">Total Saldo Likuid (Liquid Assets)</div>
                <div class="font-mono font-black text-2xl text-cyan-400">Rp {{ number_format($totalBalance, 0, ',', '.') }}</div>
                <div class="text-[10px] text-slate-500">Tersedia di {{ count($accounts) }} rekening/wallet terhubung</div>
            </div>

            <div class="p-5 rounded-2xl bg-slate-950/70 border border-slate-800 space-y-1">
                <div class="text-xs text-slate-400 font-bold uppercase tracking-wider">Bank & E-Wallets</div>
                <div class="font-mono font-black text-xl text-emerald-400">
                    Rp {{ number_format($accounts->whereIn('type', ['bank', 'ewallet', 'cash'])->sum('balance'), 0, ',', '.') }}
                </div>
                <div class="text-[10px] text-slate-500">Total dana operasional harian</div>
            </div>

            <div class="p-5 rounded-2xl bg-slate-950/70 border border-slate-800 space-y-1">
                <div class="text-xs text-slate-400 font-bold uppercase tracking-wider">Investasi & Crypto</div>
                <div class="font-mono font-black text-xl text-purple-400">
                    Rp {{ number_format($accounts->whereIn('type', ['investment', 'crypto'])->sum('balance'), 0, ',', '.') }}
                </div>
                <div class="text-[10px] text-slate-500">Aset pertumbuhan jangka panjang</div>
            </div>
        </div>
    </div>

    <!-- Accounts Grid -->
    <div class="space-y-4">
        <div class="flex items-center justify-between">
            <h2 class="{{ $tokens['font_heading'] }} text-lg text-white">Daftar Akun & Wallet Aktif</h2>
            <span class="text-xs text-slate-400 font-mono">{{ count($accounts) }} Total Akun</span>
        </div>

        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-5">
            @foreach ($accounts as $acc)
                <div class="p-5 rounded-3xl {{ $tokens['card_bg'] }} hover:border-cyan-500/40 transition-all space-y-4 relative group flex flex-col justify-between">
                    <div class="space-y-4">
                        <div class="flex items-start justify-between">
                            <div class="flex items-center space-x-3">
                                <div class="w-12 h-12 rounded-2xl bg-slate-950/80 border border-slate-800 flex items-center justify-center text-2xl shadow-inner">
                                    {{ $acc->icon }}
                                </div>
                                <div>
                                    <h3 class="font-bold text-sm text-white group-hover:text-cyan-300 transition-colors">{{ $acc->name }}</h3>
                                    <span class="inline-block px-2 py-0.5 rounded-full text-[10px] font-mono uppercase tracking-wider bg-slate-950 text-slate-400 border border-slate-800 mt-0.5">
                                        {{ $acc->type }}
                                    </span>
                                </div>
                            </div>
                            <div class="flex items-center space-x-1">
                                <button wire:click="openEditModal({{ $acc->id }})" class="p-2 rounded-xl bg-slate-950 hover:bg-slate-800 text-slate-400 hover:text-white transition-all text-xs font-bold" title="Edit / Update Saldo">
                                    ✏️
                                </button>
                                <button wire:click="deleteAccount({{ $acc->id }})" wire:confirm="Hapus akun ini?" class="p-2 rounded-xl bg-slate-950 hover:bg-rose-950 text-slate-400 hover:text-rose-400 transition-all text-xs font-bold" title="Hapus Akun">
                                    🗑️
                                </button>
                            </div>
                        </div>

                        <div class="space-y-1 pt-2 border-t border-slate-800/80">
                            <div class="text-[10px] text-slate-500 font-bold uppercase tracking-wider">Saldo Terakhir</div>
                            <div class="font-mono font-black text-xl text-white">
                                Rp {{ number_format((float) $acc->balance, 0, ',', '.') }}
                            </div>
                        </div>

                        <div class="flex items-center justify-between text-[10px] font-mono text-slate-500 pt-1">
                            <span>No: {{ $acc->account_number ?: '-' }}</span>
                            <span>{{ $acc->expense_transactions_count }} Transaksi</span>
                        </div>
                    </div>

                    <!-- Action Button: Transaction History -->
                    <div class="pt-3 border-t border-slate-800/60">
                        <button wire:click="viewTransactionHistory({{ $acc->id }})" class="w-full py-2.5 rounded-2xl bg-slate-950/80 hover:bg-cyan-950/50 border border-slate-800 hover:border-cyan-500/40 text-xs font-bold text-slate-300 hover:text-cyan-300 transition-all flex items-center justify-center space-x-2">
                            <span>📜</span>
                            <span>Lihat Riwayat Transaksi</span>
                        </button>
                    </div>
                </div>
            @endforeach
        </div>
    </div>

    <!-- TRANSACTION HISTORY MODAL FOR ACCOUNT -->
    @if ($showHistoryModal && $selectedAccount)
        <div class="fixed inset-0 bg-slate-950/80 backdrop-blur-md z-50 flex items-center justify-center p-4">
            <div class="w-full max-w-2xl bg-slate-900 border border-slate-800 rounded-3xl p-6 sm:p-8 space-y-6 shadow-2xl max-h-[90vh] flex flex-col">
                <!-- Modal Header -->
                <div class="flex items-center justify-between border-b border-slate-800 pb-4">
                    <div class="flex items-center space-x-3">
                        <div class="w-10 h-10 rounded-2xl bg-slate-950 border border-slate-800 flex items-center justify-center text-xl">
                            {{ $selectedAccount->icon }}
                        </div>
                        <div>
                            <h3 class="font-display font-bold text-lg text-white">
                                Riwayat Transaksi — {{ $selectedAccount->name }}
                            </h3>
                            <div class="text-xs text-slate-400 font-mono">
                                Saldo Terkini: <span class="text-cyan-400 font-bold">Rp {{ number_format((float) $selectedAccount->balance, 0, ',', '.') }}</span>
                            </div>
                        </div>
                    </div>
                    <button wire:click="closeModal" class="text-slate-400 hover:text-white font-bold text-lg">✕</button>
                </div>

                <!-- Transaction List Container -->
                <div class="flex-1 overflow-y-auto space-y-3 pr-1">
                    @if ($selectedAccount->expenseTransactions->count() === 0)
                        <div class="p-8 text-center bg-slate-950/50 rounded-2xl border border-slate-800 space-y-2">
                            <div class="text-3xl">📭</div>
                            <div class="text-sm font-bold text-slate-300">Belum Ada Transaksi Tercatat</div>
                            <div class="text-xs text-slate-500">Transaksiyang dihubungkan ke akun ini akan muncul otomatis di sini.</div>
                        </div>
                    @else
                        @foreach ($selectedAccount->expenseTransactions as $tx)
                            <div class="p-4 rounded-2xl bg-slate-950/80 border border-slate-800 hover:border-slate-700 transition-all flex items-center justify-between gap-4">
                                <div class="flex items-center space-x-3">
                                    <div class="w-10 h-10 rounded-xl bg-slate-900 border border-slate-800 flex items-center justify-center text-lg">
                                        {{ $tx->receipt_id ? '🧾' : '💳' }}
                                    </div>
                                    <div>
                                        <div class="font-bold text-sm text-white flex items-center space-x-2">
                                            <span>{{ $tx->merchant }}</span>
                                            @if ($tx->expenseCategory)
                                                <span class="px-2 py-0.5 rounded-full text-[10px] font-mono bg-slate-900 text-slate-400 border border-slate-800">
                                                    {{ $tx->expenseCategory->name }}
                                                </span>
                                            @endif
                                        </div>
                                        <div class="text-xs text-slate-400">
                                            {{ $tx->description ?: 'Pengeluaran Terdaftar' }} • <span class="font-mono text-slate-500">{{ $tx->transaction_date->format('d M Y, H:i') }}</span>
                                        </div>
                                    </div>
                                </div>
                                <div class="text-right">
                                    <div class="font-mono font-black text-sm text-rose-400">
                                        -Rp {{ number_format((float) $tx->amount, 0, ',', '.') }}
                                    </div>
                                    @if ($tx->receipt_id)
                                        <button wire:click="$dispatch('openReceiptModal', { receiptId: {{ $tx->receipt_id }} })" class="text-[10px] font-bold text-cyan-400 hover:underline">
                                            🔍 Lihat Struk
                                        </button>
                                    @endif
                                </div>
                            </div>
                        @endforeach
                    @endif
                </div>

                <!-- Modal Footer -->
                <div class="border-t border-slate-800 pt-4 flex justify-between items-center text-xs text-slate-400">
                    <span>Total {{ $selectedAccount->expenseTransactions->count() }} transaksi tercatat</span>
                    <button wire:click="closeModal" class="px-5 py-2.5 rounded-xl bg-slate-800 hover:bg-slate-700 text-white font-bold">
                        Tutup
                    </button>
                </div>
            </div>
        </div>
    @endif

    <!-- CREATE ACCOUNT MODAL -->
    @if ($showCreateModal)
        <div class="fixed inset-0 bg-slate-950/80 backdrop-blur-md z-50 flex items-center justify-center p-4">
            <div class="w-full max-w-md bg-slate-900 border border-slate-800 rounded-3xl p-6 space-y-5 shadow-2xl">
                <div class="flex items-center justify-between border-b border-slate-800 pb-4">
                    <h3 class="font-display font-bold text-lg text-white flex items-center space-x-2">
                        <span>👛</span>
                        <span>Tambah Akun / Wallet Baru</span>
                    </h3>
                    <button wire:click="closeModal" class="text-slate-400 hover:text-white font-bold">✕</button>
                </div>

                <form wire:submit.prevent="createAccount" class="space-y-4">
                    <div>
                        <label class="block text-xs font-bold text-slate-400 uppercase tracking-wider mb-1">Nama Akun / Provider</label>
                        <input type="text" wire:model="name" placeholder="Contoh: BCA Tabungan Utama / GoPay" class="w-full px-4 py-3 rounded-2xl bg-slate-950 border border-slate-800 text-white text-sm focus:outline-none focus:border-cyan-500">
                        @error('name') <span class="text-xs text-rose-400 block mt-1">{{ $message }}</span> @enderror
                    </div>

                    <div>
                        <label class="block text-xs font-bold text-slate-400 uppercase tracking-wider mb-1">Tipe Akun</label>
                        <select wire:model="type" class="w-full px-4 py-3 rounded-2xl bg-slate-950 border border-slate-800 text-white text-sm focus:outline-none focus:border-cyan-500">
                            <option value="bank">🏦 Bank Account (BCA, Mandiri, BRI, dll)</option>
                            <option value="ewallet">📱 E-Wallet (GoPay, OVO, ShopeePay, DANA)</option>
                            <option value="cash">💵 Cash / Dompet Tunai</option>
                            <option value="credit">💳 Credit Card / Kartu Kredit</option>
                            <option value="investment">📈 Investasi / Reksa Dana / Saham</option>
                            <option value="crypto">🪙 Crypto Wallet</option>
                        </select>
                    </div>

                    <div>
                        <label class="block text-xs font-bold text-slate-400 uppercase tracking-wider mb-1">Saldo Awal (Rp)</label>
                        <input type="number" wire:model="balance" placeholder="Contoh: 5000000" class="w-full px-4 py-3 rounded-2xl bg-slate-950 border border-slate-800 text-white font-mono text-sm focus:outline-none focus:border-cyan-500">
                        @error('balance') <span class="text-xs text-rose-400 block mt-1">{{ $message }}</span> @enderror
                    </div>

                    <div>
                        <label class="block text-xs font-bold text-slate-400 uppercase tracking-wider mb-1">Nomor Rekening / Keterangan (Opsional)</label>
                        <input type="text" wire:model="accountNumber" placeholder="Contoh: 1234-XXXX-5678" class="w-full px-4 py-3 rounded-2xl bg-slate-950 border border-slate-800 text-white text-sm focus:outline-none focus:border-cyan-500">
                    </div>

                    <div class="flex justify-end space-x-3 pt-2">
                        <button type="button" wire:click="closeModal" class="px-4 py-2.5 rounded-xl text-slate-400 text-xs font-bold">Batal</button>
                        <button type="submit" class="px-6 py-2.5 rounded-xl {{ $tokens['primary_bg'] }} text-xs font-bold shadow-lg">Simpan Akun</button>
                    </div>
                </form>
            </div>
        </div>
    @endif

    <!-- EDIT ACCOUNT MODAL -->
    @if ($showEditModal)
        <div class="fixed inset-0 bg-slate-950/80 backdrop-blur-md z-50 flex items-center justify-center p-4">
            <div class="w-full max-w-md bg-slate-900 border border-slate-800 rounded-3xl p-6 space-y-5 shadow-2xl">
                <div class="flex items-center justify-between border-b border-slate-800 pb-4">
                    <h3 class="font-display font-bold text-lg text-white flex items-center space-x-2">
                        <span>✏️</span>
                        <span>Update Saldo & Detail Akun</span>
                    </h3>
                    <button wire:click="closeModal" class="text-slate-400 hover:text-white font-bold">✕</button>
                </div>

                <form wire:submit.prevent="updateAccount" class="space-y-4">
                    <div>
                        <label class="block text-xs font-bold text-slate-400 uppercase tracking-wider mb-1">Nama Akun / Provider</label>
                        <input type="text" wire:model="name" class="w-full px-4 py-3 rounded-2xl bg-slate-950 border border-slate-800 text-white text-sm focus:outline-none focus:border-cyan-500">
                        @error('name') <span class="text-xs text-rose-400 block mt-1">{{ $message }}</span> @enderror
                    </div>

                    <div>
                        <label class="block text-xs font-bold text-slate-400 uppercase tracking-wider mb-1">Tipe Akun</label>
                        <select wire:model="type" class="w-full px-4 py-3 rounded-2xl bg-slate-950 border border-slate-800 text-white text-sm focus:outline-none focus:border-cyan-500">
                            <option value="bank">🏦 Bank Account</option>
                            <option value="ewallet">📱 E-Wallet</option>
                            <option value="cash">💵 Cash / Dompet Tunai</option>
                            <option value="credit">💳 Credit Card</option>
                            <option value="investment">📈 Investasi / Reksa Dana / Saham</option>
                            <option value="crypto">🪙 Crypto Wallet</option>
                        </select>
                    </div>

                    <div>
                        <label class="block text-xs font-bold text-slate-400 uppercase tracking-wider mb-1">Saldo Terkini (Rp)</label>
                        <input type="number" wire:model="balance" class="w-full px-4 py-3 rounded-2xl bg-slate-950 border border-slate-800 text-white font-mono text-sm focus:outline-none focus:border-cyan-500">
                        @error('balance') <span class="text-xs text-rose-400 block mt-1">{{ $message }}</span> @enderror
                    </div>

                    <div>
                        <label class="block text-xs font-bold text-slate-400 uppercase tracking-wider mb-1">Nomor Rekening / Keterangan</label>
                        <input type="text" wire:model="accountNumber" class="w-full px-4 py-3 rounded-2xl bg-slate-950 border border-slate-800 text-white text-sm focus:outline-none focus:border-cyan-500">
                    </div>

                    <div class="flex justify-end space-x-3 pt-2">
                        <button type="button" wire:click="closeModal" class="px-4 py-2.5 rounded-xl text-slate-400 text-xs font-bold">Batal</button>
                        <button type="submit" class="px-6 py-2.5 rounded-xl {{ $tokens['primary_bg'] }} text-xs font-bold shadow-lg">Simpan Perubahan</button>
                    </div>
                </form>
            </div>
        </div>
    @endif
</div>
