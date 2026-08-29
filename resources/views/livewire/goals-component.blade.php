@php
    $tokens = app(\App\Services\ThemeService::class)->getThemeTokens($themeMode);
@endphp

<div class="space-y-6">
    <!-- Flash Success Notification -->
    @if ($successMessage || session()->has('global_success'))
        <div class="p-4 rounded-2xl bg-emerald-500/20 border border-emerald-500/40 text-emerald-300 text-xs font-bold flex items-center justify-between shadow-lg">
            <div class="flex items-center space-x-2">
                <span>🎉</span>
                <span>{{ $successMessage ?? session('global_success') }}</span>
            </div>
            <button wire:click="$set('successMessage', null)" class="text-emerald-400 font-bold hover:text-white">✕</button>
        </div>
    @endif

    <!-- Header & Action Points Bar -->
    <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4 p-6 rounded-3xl {{ $tokens['card_bg'] }}">
        <div>
            <h1 class="{{ $tokens['font_heading'] }} text-2xl text-white flex items-center space-x-2">
                <span>🎯</span>
                <span>Financial Target Goals & Storage Pools</span>
            </h1>
            <p class="text-xs text-slate-400 mt-1">Kelola target impian dan ketahui wadah/rekening tempat uang tabungan Anda tersimpan.</p>
        </div>
        <div class="flex items-center space-x-3">
            <a href="{{ route('allocator') }}" class="px-4 py-2.5 rounded-2xl bg-slate-950 border border-amber-500/40 text-amber-300 text-xs font-bold shadow-lg transition-all hover:bg-amber-950/30 flex items-center space-x-2">
                <span>⚡</span>
                <span>Alokasikan AP ({{ $userApBalance }} AP)</span>
            </a>
            <button wire:click="openCreateGoalModal" class="px-4 py-2.5 rounded-2xl {{ $tokens['primary_bg'] }} text-xs font-bold shadow-lg transition-all flex items-center space-x-2">
                <span>➕</span>
                <span>Tambah Goal Target</span>
            </button>
        </div>
    </div>

    <!-- How Saving Goals & Account Pools Work Banner -->
    <div class="p-5 rounded-2xl bg-gradient-to-r from-slate-900 via-slate-900 to-cyan-950/60 border border-cyan-500/30 text-xs text-slate-300 space-y-2">
        <div class="flex items-center space-x-2 text-cyan-400 font-bold">
            <span>🏦</span>
            <span class="uppercase tracking-wider">Di mana dana Saving Goal Anda disimpan?</span>
        </div>
        <div class="grid grid-cols-1 md:grid-cols-2 gap-4 pt-1">
            <div class="flex items-start space-x-2 bg-slate-950/50 p-3 rounded-xl border border-slate-800">
                <span class="text-lg">📍</span>
                <div>
                    <strong class="text-white">Transparansi Wadah Pool Keuangan:</strong>
                    <p class="text-slate-400 text-[11px] mt-0.5">Setiap Goal dihubungkan langsung dengan Akun / Rekening nyata Anda (seperti Bank BCA, Mandiri, E-Wallet, atau Rekening Investasi) agar posisi fisik dana selalu jelas.</p>
                </div>
            </div>
            <div class="flex items-start space-x-2 bg-slate-950/50 p-3 rounded-xl border border-slate-800">
                <span class="text-lg">⚡</span>
                <div>
                    <strong class="text-white">Alokasi & Auto Deduct Saldo:</strong>
                    <p class="text-slate-400 text-[11px] mt-0.5">Saat melakukan <strong>+ Setor Tabungan</strong>, dana dapat langsung dipotong dari rekening sumber yang Anda pilih secara otomatis.</p>
                </div>
            </div>
        </div>
    </div>

    <!-- Goals Grid -->
    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
        @forelse ($questPools as $pool)
            @php
                $pct = $pool->getProgressPercentage();
                $isCompleted = $pct >= 100;
            @endphp
            <div class="p-6 rounded-3xl {{ $isCompleted ? 'bg-emerald-950/40 border border-emerald-500/40' : $tokens['card_bg'] }} space-y-4 relative overflow-hidden flex flex-col justify-between">
                <div class="space-y-4">
                    <div class="flex items-center justify-between">
                        <div class="flex items-center space-x-3">
                            <div class="w-12 h-12 rounded-2xl bg-slate-950 border border-slate-800 flex items-center justify-center text-2xl">
                                {{ $pool->display_icon }}
                            </div>
                            <div>
                                <h3 class="font-display font-bold text-base text-white flex items-center space-x-2">
                                    <span>{{ $pool->name }}</span>
                                    @if ($isCompleted)
                                        <span class="text-[10px] font-mono px-2 py-0.5 rounded-full bg-emerald-500/20 text-emerald-300 border border-emerald-500/40 font-bold">
                                            Goal Achieved 🎉
                                        </span>
                                    @endif
                                </h3>
                                <div class="flex items-center space-x-2 text-[10px] font-mono text-slate-400">
                                    <span class="uppercase tracking-widest">{{ $pool->category }}</span>
                                </div>
                            </div>
                        </div>
                        <span class="font-mono font-black text-lg {{ $isCompleted ? 'text-emerald-400' : 'text-cyan-400' }}">
                            {{ $pct }}%
                        </span>
                    </div>

                    <!-- Storage Account Pool Location Tag -->
                    <div class="p-2.5 rounded-xl bg-slate-950/80 border border-slate-800 flex items-center justify-between text-xs">
                        <div class="flex items-center space-x-2">
                            <span class="text-sm">{{ $pool->account?->icon ?: '🏦' }}</span>
                            <div>
                                <div class="text-[9px] uppercase tracking-wider text-slate-500 font-mono">Wadah Lokasi Dana</div>
                                <div class="font-bold text-slate-200">
                                    {{ $pool->account ? $pool->account->name : 'Belum dihubungkan ke akun' }}
                                </div>
                            </div>
                        </div>
                        @if ($pool->account)
                            <span class="text-[10px] font-mono text-slate-400 bg-slate-900 px-2 py-1 rounded-lg border border-slate-800">
                                Saldo: Rp {{ number_format((float)$pool->account->balance, 0, ',', '.') }}
                            </span>
                        @endif
                    </div>

                    <!-- Progress Bar -->
                    <div class="space-y-1">
                        <div class="flex justify-between text-xs font-mono font-bold text-slate-300">
                            <span>Terkumpul: Rp {{ number_format((float)$pool->current_amount, 0, ',', '.') }}</span>
                            <span class="text-slate-400">Target: Rp {{ number_format((float)$pool->target_amount, 0, ',', '.') }}</span>
                        </div>
                        <div class="h-3 w-full bg-slate-950 rounded-full overflow-hidden p-0.5 border border-slate-800">
                            <div class="h-full rounded-full {{ $isCompleted ? 'bg-emerald-400' : $tokens['progress_bar'] }}" style="width: {{ $pct }}%;"></div>
                        </div>
                    </div>

                    <!-- Metrics Grid -->
                    <div class="grid grid-cols-2 gap-2 pt-2 text-[11px] font-mono text-slate-400 border-t border-slate-800/80">
                        <div>
                            <div class="text-[9px] uppercase tracking-wider text-slate-500">Action Points (AP)</div>
                            <div class="font-bold text-amber-300 flex items-center space-x-1">
                                <span>⚡</span>
                                <span>{{ number_format((int)$pool->allocated_ap) }} AP</span>
                            </div>
                        </div>
                        <div>
                            <div class="text-[9px] uppercase tracking-wider text-slate-500">Sisa Target</div>
                            <div class="font-bold text-slate-200">
                                Rp {{ number_format(max(0, (float)$pool->target_amount - (float)$pool->current_amount), 0, ',', '.') }}
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Action Button -->
                <div class="pt-3 border-t border-slate-800/50 flex justify-end space-x-2">
                    <button wire:click="openDeposit({{ $pool->id }})" class="px-4 py-2 rounded-xl bg-slate-950 hover:bg-slate-800 border border-slate-700 text-cyan-300 text-xs font-bold transition-all flex items-center space-x-2">
                        <span>💵</span>
                        <span>+ Setor Tabungan</span>
                    </button>
                </div>
            </div>
        @empty
            <div class="col-span-2 p-12 text-center rounded-3xl bg-slate-900/50 border border-slate-800 text-slate-400 space-y-3">
                <div class="text-4xl">🎯</div>
                <p class="font-bold text-sm">Belum ada target tabungan yang dibuat.</p>
                <button wire:click="openCreateGoalModal" class="px-4 py-2 rounded-xl bg-cyan-500 text-slate-950 font-bold text-xs">
                    + Buat Target Pertama
                </button>
            </div>
        @endforelse
    </div>

    <!-- Create Goal Modal -->
    @if ($openGoalModal)
        <div class="fixed inset-0 bg-slate-950/80 backdrop-blur-md z-50 flex items-center justify-center p-4">
            <div class="w-full max-w-md bg-slate-900 border border-slate-800 rounded-3xl p-6 space-y-4 shadow-2xl">
                <div class="flex items-center justify-between border-b border-slate-800 pb-3">
                    <h3 class="font-display font-bold text-base text-white flex items-center space-x-2">
                        <span>🎯</span>
                        <span>Tambah Target Tabungan Baru</span>
                    </h3>
                    <button wire:click="$set('openGoalModal', false)" class="text-slate-400 hover:text-white font-bold">✕</button>
                </div>
                <form wire:submit.prevent="createGoal" class="space-y-4 text-xs">
                    <div>
                        <label class="block font-bold text-slate-400 uppercase tracking-wider mb-1">Nama Goal Target</label>
                        <input type="text" wire:model="name" placeholder="Contoh: Tabungan S2 / Beli Motor" class="w-full px-4 py-2.5 rounded-xl bg-slate-950 border border-slate-800 text-white focus:outline-none focus:border-cyan-500">
                        @error('name') <span class="text-rose-400 text-[11px] font-bold">{{ $message }}</span> @enderror
                    </div>

                    <div>
                        <label class="block font-bold text-slate-400 uppercase tracking-wider mb-1">Wadah Pool / Rekening Storage</label>
                        <select wire:model="accountId" class="w-full px-3 py-2.5 rounded-xl bg-slate-950 border border-slate-800 text-white focus:outline-none focus:border-cyan-500">
                            <option value="">-- Pilih Wadah Akun / Rekening --</option>
                            @foreach ($userAccounts as $acc)
                                <option value="{{ $acc->id }}">{{ $acc->icon ?: '🏦' }} {{ $acc->name }} (Rp {{ number_format((float)$acc->balance, 0, ',', '.') }})</option>
                            @endforeach
                        </select>
                        <span class="text-[10px] text-slate-500">Menentukan di mana dana target tabungan ini disimpan.</span>
                    </div>

                    <div>
                        <label class="block font-bold text-slate-400 uppercase tracking-wider mb-1">Target Nominal (Rp)</label>
                        <input type="number" wire:model="targetAmount" placeholder="Contoh: 15000000" class="w-full px-4 py-2.5 rounded-xl bg-slate-950 border border-slate-800 text-white font-mono focus:outline-none focus:border-cyan-500">
                        @error('targetAmount') <span class="text-rose-400 text-[11px] font-bold">{{ $message }}</span> @enderror
                    </div>

                    <div class="grid grid-cols-2 gap-3">
                        <div>
                            <label class="block font-bold text-slate-400 uppercase tracking-wider mb-1">Kategori</label>
                            <select wire:model="category" class="w-full px-3 py-2.5 rounded-xl bg-slate-950 border border-slate-800 text-white focus:outline-none focus:border-cyan-500">
                                <option value="emergency">Emergency (Dana Darurat)</option>
                                <option value="investment">Investment (Investasi)</option>
                                <option value="vehicle">Vehicle (Kendaraan)</option>
                                <option value="hobby">Hobby & Assets (Hobi / Aset)</option>
                            </select>
                        </div>
                        <div>
                            <label class="block font-bold text-slate-400 uppercase tracking-wider mb-1">Ikon (Emoji)</label>
                            <input type="text" wire:model="icon" placeholder="🏎️ / 🏡 / 💻" class="w-full px-4 py-2.5 rounded-xl bg-slate-950 border border-slate-800 text-white focus:outline-none focus:border-cyan-500">
                        </div>
                    </div>

                    <div class="flex justify-end space-x-2 pt-3 border-t border-slate-800">
                        <button type="button" wire:click="$set('openGoalModal', false)" class="px-4 py-2 rounded-xl text-slate-400 font-bold">Batal</button>
                        <button type="submit" class="px-5 py-2 rounded-xl bg-cyan-500 hover:bg-cyan-400 text-slate-950 font-bold">Simpan Target Goal</button>
                    </div>
                </form>
            </div>
        </div>
    @endif

    <!-- Setor Tabungan Modal -->
    @if ($openDepositModal)
        <div class="fixed inset-0 bg-slate-950/80 backdrop-blur-md z-50 flex items-center justify-center p-4">
            <div class="w-full max-w-md bg-slate-900 border border-slate-800 rounded-3xl p-6 space-y-4 shadow-2xl">
                <div class="flex items-center justify-between border-b border-slate-800 pb-3">
                    <h3 class="font-display font-bold text-base text-white flex items-center space-x-2">
                        <span>💵</span>
                        <span>Setor / Tambah Alokasi Tabungan</span>
                    </h3>
                    <button wire:click="$set('openDepositModal', false)" class="text-slate-400 hover:text-white font-bold">✕</button>
                </div>
                <form wire:submit.prevent="deposit" class="space-y-4 text-xs">
                    <div>
                        <label class="block font-bold text-slate-400 uppercase tracking-wider mb-1">Sumber Rekening Asal</label>
                        <select wire:model="depositAccountId" class="w-full px-3 py-2.5 rounded-xl bg-slate-950 border border-slate-800 text-white focus:outline-none focus:border-cyan-500">
                            <option value="">-- Tanpa Potong Akun (Catatan Saja) --</option>
                            @foreach ($userAccounts as $acc)
                                <option value="{{ $acc->id }}">{{ $acc->icon ?: '🏦' }} {{ $acc->name }} (Saldo: Rp {{ number_format((float)$acc->balance, 0, ',', '.') }})</option>
                            @endforeach
                        </select>
                        <span class="text-[10px] text-slate-500">Jika dipilih, saldo rekening asal akan otomatis berkurang sesuai nominal setoran.</span>
                    </div>

                    <div>
                        <label class="block font-bold text-slate-400 uppercase tracking-wider mb-1">Nominal Setoran (Rp)</label>
                        <input type="number" wire:model="depositAmount" placeholder="Contoh: 500000" class="w-full px-4 py-2.5 rounded-xl bg-slate-950 border border-slate-800 text-white font-mono text-base focus:outline-none focus:border-cyan-500">
                        @error('depositAmount') <span class="text-rose-400 text-[11px] font-bold">{{ $message }}</span> @enderror
                    </div>

                    <div class="p-3 rounded-xl bg-slate-950 border border-slate-800 text-[11px] text-slate-400">
                        <span>Setoran ini akan menambahkan akumulasi tabungan pada target terpilih dan menyinkronkan wadah akunnya.</span>
                    </div>

                    <div class="flex justify-end space-x-2 pt-3 border-t border-slate-800">
                        <button type="button" wire:click="$set('openDepositModal', false)" class="px-4 py-2 rounded-xl text-slate-400 font-bold">Batal</button>
                        <button type="submit" class="px-5 py-2 rounded-xl bg-emerald-500 hover:bg-emerald-400 text-slate-950 font-bold">Konfirmasi Setor</button>
                    </div>
                </form>
            </div>
        </div>
    @endif
</div>
