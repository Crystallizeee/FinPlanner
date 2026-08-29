@php
    $tokens = app(\App\Services\ThemeService::class)->getThemeTokens($themeMode);
@endphp

<div class="space-y-6">
    <!-- Header -->
    <div class="p-6 rounded-3xl {{ $tokens['card_bg'] }}">
        <h1 class="{{ $tokens['font_heading'] }} text-2xl text-white flex items-center space-x-2">
            <span>🔄</span>
            <span>Subscriptions & Recurring Bills Tracker</span>
        </h1>
        <p class="text-xs text-slate-400 mt-1">Kelola langganan bulanan dan tagihan rutin dengan pengingat otomatis serta pembayaran 1-klik.</p>
    </div>

    @if ($successMessage)
        <div class="p-4 rounded-2xl bg-emerald-950/80 border border-emerald-500/50 text-emerald-300 text-xs font-semibold">
            {{ $successMessage }}
        </div>
    @endif

    <div class="grid grid-cols-1 lg:grid-cols-12 gap-6">

        <!-- Form Add Bill -->
        <div class="lg:col-span-5 p-6 rounded-3xl {{ $tokens['card_bg'] }} space-y-4">
            <h3 class="{{ $tokens['font_heading'] }} text-base text-white">Tambah Tagihan Rutin</h3>

            <form wire:submit.prevent="addSubscription" class="space-y-4 text-xs">
                <div>
                    <label class="block text-slate-300 font-bold mb-1">Nama Tagihan / Langganan</label>
                    <input type="text" wire:model="title" placeholder="Contoh: Netflix 4K, Kos Bulanan, PLN" class="w-full p-3 rounded-xl bg-slate-900 border border-slate-700 text-white focus:outline-none focus:border-cyan-400">
                    @error('title') <span class="text-rose-400 text-[10px] mt-1">{{ $message }}</span> @enderror
                </div>

                <div>
                    <label class="block text-slate-300 font-bold mb-1">Nominal (Rp)</label>
                    <input type="number" wire:model="amount" placeholder="186000" class="w-full p-3 rounded-xl bg-slate-900 border border-slate-700 text-white focus:outline-none focus:border-cyan-400">
                    @error('amount') <span class="text-rose-400 text-[10px] mt-1">{{ $message }}</span> @enderror
                </div>

                <div>
                    <label class="block text-slate-300 font-bold mb-1">Tanggal Jatuh Tempo Bulanan (1-31)</label>
                    <input type="number" wire:model="due_day" min="1" max="31" class="w-full p-3 rounded-xl bg-slate-900 border border-slate-700 text-white focus:outline-none focus:border-cyan-400">
                </div>

                <div>
                    <label class="block text-slate-300 font-bold mb-1">Potong Dari Akun Rekening</label>
                    <select wire:model="account_id" class="w-full p-3 rounded-xl bg-slate-900 border border-slate-700 text-white focus:outline-none focus:border-cyan-400">
                        <option value="">-- Pilih Rekening Sumber --</option>
                        @foreach($accounts as $acc)
                            <option value="{{ $acc->id }}">{{ $acc->name }} (Rp {{ number_format($acc->balance, 0, ',', '.') }})</option>
                        @endforeach
                    </select>
                </div>

                <button type="submit" class="w-full py-3 rounded-xl bg-cyan-500 hover:bg-cyan-400 text-slate-950 font-bold text-xs shadow-lg transition-colors">
                    + Simpan Tagihan Rutin
                </button>
            </form>
        </div>

        <!-- List Bills -->
        <div class="lg:col-span-7 space-y-4">
            @forelse($subscriptions as $sub)
                <div class="p-5 rounded-3xl {{ $tokens['card_bg'] }} border border-slate-800 space-y-3 flex items-center justify-between gap-4">
                    <div class="space-y-1">
                        <div class="flex items-center space-x-2">
                            <span class="text-white font-bold text-base">{{ $sub->title }}</span>
                            <span class="px-2 py-0.5 rounded-full text-[10px] bg-cyan-500/20 text-cyan-300 border border-cyan-500/40 font-mono">
                                Tgl {{ $sub->due_day }} Tiap Bulan
                            </span>
                        </div>
                        <div class="text-xs font-mono text-slate-400">
                            Nominal: <strong class="text-rose-400">Rp {{ number_format($sub->amount, 0, ',', '.') }}</strong>
                            @if ($sub->account)
                                | Rekening: <span class="text-cyan-400">{{ $sub->account->name }}</span>
                            @endif
                        </div>
                        @if ($sub->last_paid_at)
                            <div class="text-[10px] font-mono text-emerald-400">
                                ✓ Terakhir dibayar: {{ $sub->last_paid_at->format('d M Y H:i') }}
                            </div>
                        @endif
                    </div>

                    <div class="flex flex-col items-end space-y-2 shrink-0">
                        <button wire:click="payBill({{ $sub->id }})" class="px-4 py-2 rounded-xl bg-emerald-500 hover:bg-emerald-400 text-slate-950 font-bold text-xs shadow-md transition-colors">
                            Bayar Sekarang 💳
                        </button>
                        <button wire:click="deleteBill({{ $sub->id }})" class="text-[10px] font-mono text-slate-500 hover:text-rose-400">
                            Hapus
                        </button>
                    </div>
                </div>
            @empty
                <div class="p-8 rounded-3xl {{ $tokens['card_bg'] }} text-center text-slate-400 text-xs">
                    Belum ada langganan atau tagihan rutin tercatat.
                </div>
            @endforelse
        </div>

    </div>
</div>
