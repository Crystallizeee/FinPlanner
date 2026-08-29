@php
    $tokens = app(\App\Services\ThemeService::class)->getThemeTokens($themeMode);
@endphp

<div class="space-y-8">
    <!-- Header -->
    <div class="glass-panel rounded-3xl p-6 sm:p-8 border border-slate-800 flex flex-col md:flex-row md:items-center justify-between gap-4">
        <div>
            <div class="flex items-center space-x-2">
                <span class="text-2xl">💳</span>
                <h1 class="font-display font-black text-2xl md:text-3xl text-white">
                    Debt & Loan Payoff Planner
                </h1>
            </div>
            <p class="text-xs sm:text-sm text-slate-400 mt-1">
                Strategi percepatan pelunasan utang/cicilan menggunakan metode Snowball & Avalanche.
            </p>
        </div>

        <div class="px-6 py-4 rounded-2xl bg-gradient-to-r from-rose-500/20 via-amber-500/20 to-orange-500/20 border border-rose-500/30 text-rose-300 text-xs font-bold font-mono text-right">
            <div class="text-[10px] text-slate-400 uppercase">TOTAL SISA UTANG</div>
            <div class="text-2xl font-black text-white">Rp {{ number_format($totalRemainingDebt, 0, ',', '.') }}</div>
            <div class="text-[11px] text-amber-300">Cicilan/Bln: Rp {{ number_format($totalMonthlyPayment, 0, ',', '.') }}</div>
        </div>
    </div>

    @if ($successMessage)
        <div class="p-4 rounded-2xl bg-emerald-950/80 border border-emerald-500/50 text-emerald-300 text-xs font-semibold">
            {{ $successMessage }}
        </div>
    @endif

    <!-- Strategy Selector -->
    <div class="p-6 rounded-3xl {{ $tokens['card_bg'] }} space-y-4">
        <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4">
            <h3 class="{{ $tokens['font_heading'] }} text-base text-white">Pilih Strategi Pelunasan</h3>

            <div class="flex bg-slate-900 p-1 rounded-2xl border border-slate-800 text-xs font-mono">
                <button wire:click="$set('strategy', 'snowball')" class="px-4 py-2 rounded-xl transition-all {{ $strategy === 'snowball' ? 'bg-cyan-500 text-slate-950 font-bold' : 'text-slate-400 hover:text-white' }}">
                    🏔️ Debt Snowball (Saldo Terkecil)
                </button>
                <button wire:click="$set('strategy', 'avalanche')" class="px-4 py-2 rounded-xl transition-all {{ $strategy === 'avalanche' ? 'bg-amber-500 text-slate-950 font-bold' : 'text-slate-400 hover:text-white' }}">
                    🌋 Debt Avalanche (Bunga Tertinggi)
                </button>
            </div>
        </div>

        <div class="p-4 rounded-2xl bg-slate-950/60 border border-slate-800 text-xs text-slate-300 space-y-1">
            @if($strategy === 'snowball')
                <div class="text-cyan-400 font-bold">ℹ️ Keterangan Strategi Debt Snowball:</div>
                <p>Prioritas pelunasan dimulai dari utang dengan <strong>sisa saldo terkecil terlebih dahulu</strong>. Sangat efektif membakar motivasi psikologis dengan melunasi utang satu per satu lebih cepat!</p>
            @else
                <div class="text-amber-400 font-bold">ℹ️ Keterangan Strategi Debt Avalanche:</div>
                <p>Prioritas pelunasan dimulai dari utang dengan <strong>suku bunga/interest rate terbesar terlebih dahulu</strong>. Strategi ini secara matematis paling menghemat total pengeluaran bunga!</p>
            @endif
        </div>
    </div>

    <!-- Main Grid: Form Add Debt vs Priority List -->
    <div class="grid grid-cols-1 lg:grid-cols-12 gap-6">
        <!-- Form Add Debt -->
        <div class="lg:col-span-5 p-6 rounded-3xl {{ $tokens['card_bg'] }} space-y-4">
            <h3 class="{{ $tokens['font_heading'] }} text-base text-white">+ Tambah Utang / Cicilan Baru</h3>

            <form wire:submit.prevent="addDebt" class="space-y-3 text-xs">
                <div>
                    <label class="block text-slate-300 font-bold mb-1">Nama Cicilan / Utang</label>
                    <input type="text" wire:model="name" placeholder="Contoh: KPR Rumah, Kartu Kredit BCA, Paylater" class="w-full p-3 rounded-xl bg-slate-900 border border-slate-700 text-white focus:outline-none focus:border-cyan-400">
                    @error('name') <span class="text-rose-400 text-[10px] mt-1">{{ $message }}</span> @enderror
                </div>

                <div>
                    <label class="block text-slate-300 font-bold mb-1">Pemberi Pinjaman / Bank</label>
                    <input type="text" wire:model="lender" placeholder="Contoh: Bank Mandiri, Traveloka" class="w-full p-3 rounded-xl bg-slate-900 border border-slate-700 text-white focus:outline-none focus:border-cyan-400">
                </div>

                <div class="grid grid-cols-2 gap-2">
                    <div>
                        <label class="block text-slate-300 font-bold mb-1">Sisa Utang (Rp)</label>
                        <input type="number" wire:model="remaining_amount" placeholder="5000000" class="w-full p-3 rounded-xl bg-slate-900 border border-slate-700 text-white focus:outline-none focus:border-cyan-400">
                        @error('remaining_amount') <span class="text-rose-400 text-[10px] mt-1">{{ $message }}</span> @enderror
                    </div>
                    <div>
                        <label class="block text-slate-300 font-bold mb-1">Total Utang Awal (Rp)</label>
                        <input type="number" wire:model="total_amount" placeholder="10000000" class="w-full p-3 rounded-xl bg-slate-900 border border-slate-700 text-white focus:outline-none focus:border-cyan-400">
                    </div>
                </div>

                <div class="grid grid-cols-2 gap-2">
                    <div>
                        <label class="block text-slate-300 font-bold mb-1">Cicilan / Bln (Rp)</label>
                        <input type="number" wire:model="minimum_monthly_payment" placeholder="500000" class="w-full p-3 rounded-xl bg-slate-900 border border-slate-700 text-white focus:outline-none focus:border-cyan-400">
                    </div>
                    <div>
                        <label class="block text-slate-300 font-bold mb-1">Bunga (% / Thn)</label>
                        <input type="number" step="0.1" wire:model="interest_rate" placeholder="5.5" class="w-full p-3 rounded-xl bg-slate-900 border border-slate-700 text-white focus:outline-none focus:border-cyan-400">
                    </div>
                </div>

                <div>
                    <label class="block text-slate-300 font-bold mb-1">Tanggal Jatuh Tempo (Tgl 1-31)</label>
                    <input type="number" wire:model="due_day" min="1" max="31" class="w-full p-3 rounded-xl bg-slate-900 border border-slate-700 text-white focus:outline-none focus:border-cyan-400">
                </div>

                <button type="submit" class="w-full py-3 rounded-xl bg-rose-500 hover:bg-rose-400 text-slate-950 font-bold text-xs shadow-lg transition-colors">
                    + Simpan Data Utang
                </button>
            </form>
        </div>

        <!-- Priority List Cards -->
        <div class="lg:col-span-7 space-y-4">
            <h3 class="{{ $tokens['font_heading'] }} text-base text-white flex items-center justify-between">
                <span>Daftar Utang Sesuai Urutan Prioritas {{ ucfirst($strategy) }}</span>
                <span class="text-xs font-mono text-slate-400">{{ count($debts) }} Utang Aktif</span>
            </h3>

            @forelse($debts as $index => $d)
                @php
                    $payoffPct = $d->total_amount > 0 ? (int) round((($d->total_amount - $d->remaining_amount) / $d->total_amount) * 100) : 0;
                @endphp
                <div class="p-5 rounded-3xl {{ $tokens['card_bg'] }} border border-slate-800 space-y-3">
                    <div class="flex items-center justify-between">
                        <div class="flex items-center space-x-3">
                            <span class="w-8 h-8 rounded-full bg-rose-500/20 text-rose-300 border border-rose-500/30 flex items-center justify-center font-bold font-mono text-xs">
                                #{{ $index + 1 }}
                            </span>
                            <div>
                                <h4 class="text-white font-bold text-sm">{{ $d->name }}</h4>
                                <div class="text-[10px] font-mono text-slate-400">{{ $d->lender ?? 'Kreditur' }} | Due: Tgl {{ $d->due_day }}</div>
                            </div>
                        </div>

                        <div class="text-right font-mono">
                            <div class="text-rose-400 font-bold text-sm">Rp {{ number_format((float)$d->remaining_amount, 0, ',', '.') }}</div>
                            <div class="text-[10px] text-slate-400">Bunga: {{ $d->interest_rate }}%/thn</div>
                        </div>
                    </div>

                    <!-- Progress Bar -->
                    <div class="w-full h-2.5 bg-slate-950 rounded-full overflow-hidden border border-slate-800">
                        <div class="h-full bg-gradient-to-r from-emerald-500 via-teal-400 to-cyan-400" style="width: {{ $payoffPct }}%;"></div>
                    </div>

                    <div class="flex items-center justify-between pt-2 border-t border-slate-800/80 text-[11px] font-mono">
                        <div class="text-slate-400">
                            Min Payment: <strong class="text-white">Rp {{ number_format((float)$d->minimum_monthly_payment, 0, ',', '.') }}/bln</strong>
                        </div>
                        <div class="flex items-center space-x-2">
                            <button wire:click="makePayment({{ $d->id }}, {{ $d->minimum_monthly_payment }})" class="px-3 py-1 rounded-xl bg-emerald-500/20 border border-emerald-500/40 text-emerald-300 hover:bg-emerald-500/40 text-[10px] font-bold transition-all">
                                💸 Bayar Cicilan
                            </button>
                            <button wire:click="deleteDebt({{ $d->id }})" class="text-slate-500 hover:text-rose-400 text-[10px]">
                                Hapus
                            </button>
                        </div>
                    </div>
                </div>
            @empty
                <div class="p-8 rounded-3xl {{ $tokens['card_bg'] }} text-center text-slate-400 text-xs">
                    🎉 Selamat! Anda belum mencatatkan utang atau cicilan aktif.
                </div>
            @endforelse
        </div>
    </div>
</div>
