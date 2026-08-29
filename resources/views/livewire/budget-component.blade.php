@php
    $tokens = app(\App\Services\ThemeService::class)->getThemeTokens($themeMode);
    $totalLimit = array_sum(array_column($categories, 'limit'));
    $totalSpent = array_sum(array_column($categories, 'spent'));
    $totalRemaining = max(0, $totalLimit - $totalSpent);
    $overallPercent = $totalLimit > 0 ? round(($totalSpent / $totalLimit) * 100) : 0;
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

    <!-- Header Summary -->
    <div class="p-6 sm:p-8 rounded-3xl {{ $tokens['card_bg'] }} space-y-6">
        <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4">
            <div>
                <h1 class="{{ $tokens['font_heading'] }} text-2xl text-white flex items-center space-x-2">
                    <span>📊</span>
                    <span>Monthly Budget Planner</span>
                </h1>
                <p class="text-xs text-slate-400 mt-1">Disiplin alokasi anggaran bulanan untuk mempertahankan kesehatan finansial.</p>
            </div>
            <div class="flex items-center space-x-3">
                <span class="px-4 py-2 rounded-full text-xs font-mono font-bold bg-cyan-500/20 text-cyan-300 border border-cyan-500/30">
                    August 2026 Budget Cycle
                </span>
                <button wire:click="openModal" class="px-5 py-2.5 rounded-2xl {{ $tokens['primary_bg'] }} text-xs font-bold shadow-lg flex items-center space-x-2 transition-all">
                    <span>＋</span>
                    <span>Tambah Budget</span>
                </button>
            </div>
        </div>

        <div class="grid grid-cols-1 sm:grid-cols-3 gap-4">
            <div class="p-4 rounded-2xl bg-slate-950/70 border border-slate-800">
                <div class="text-xs text-slate-400 font-bold">Total Monthly Budget Limit</div>
                <div class="font-mono font-black text-xl text-white mt-1">Rp {{ number_format($totalLimit) }}</div>
            </div>
            <div class="p-4 rounded-2xl bg-slate-950/70 border border-slate-800">
                <div class="text-xs text-slate-400 font-bold">Total Spent so far</div>
                <div class="font-mono font-black text-xl text-rose-400 mt-1">Rp {{ number_format($totalSpent) }}</div>
            </div>
            <div class="p-4 rounded-2xl bg-slate-950/70 border border-slate-800">
                <div class="text-xs text-slate-400 font-bold">Remaining Budget</div>
                <div class="font-mono font-black text-xl text-emerald-400 mt-1">Rp {{ number_format($totalRemaining) }}</div>
            </div>
        </div>

        <!-- Overall Progress -->
        <div class="space-y-2">
            <div class="flex justify-between text-xs font-bold">
                <span class="text-slate-300">Total Budget Utilization</span>
                <span class="text-cyan-400 font-mono">{{ $overallPercent }}%</span>
            </div>
            <div class="h-3 w-full bg-slate-950 rounded-full overflow-hidden p-0.5 border border-slate-800">
                <div class="h-full rounded-full {{ $tokens['progress_bar'] }}" style="width: {{ min($overallPercent, 100) }}%;"></div>
            </div>
        </div>
    </div>

    <!-- Category Breakdowns Grid -->
    <div class="p-6 rounded-3xl {{ $tokens['card_bg'] }} space-y-4">
        <div class="flex items-center justify-between">
            <h2 class="{{ $tokens['font_heading'] }} text-lg text-white">Category Breakdowns</h2>
            <button wire:click="openModal" class="text-xs text-cyan-400 hover:text-cyan-300 font-bold flex items-center space-x-1">
                <span>＋ Buat Kategori Baru</span>
            </button>
        </div>

        <div class="space-y-4">
            @foreach ($categories as $cat)
                @php
                    $pct = $cat['limit'] > 0 ? round(($cat['spent'] / $cat['limit']) * 100) : 0;
                    $isExceeded = $cat['spent'] > $cat['limit'];
                    $overAmount = $isExceeded ? $cat['spent'] - $cat['limit'] : 0;
                @endphp
                <div class="p-4 rounded-2xl bg-slate-950/60 border {{ $isExceeded ? 'border-rose-500/50 bg-rose-950/20' : 'border-slate-800' }} space-y-3">
                    <div class="flex items-center justify-between">
                        <div class="flex items-center space-x-3">
                            <span class="text-xl p-2 rounded-xl bg-slate-900 border border-slate-800">{{ $cat['icon'] }}</span>
                            <div>
                                <div class="font-bold text-sm text-white">{{ $cat['name'] }}</div>
                                <div class="text-[11px] font-mono text-slate-400">
                                    Rp {{ number_format($cat['spent']) }} / Rp {{ number_format($cat['limit']) }}
                                </div>
                            </div>
                        </div>
                        <div class="text-right">
                            <span class="font-mono font-black text-sm {{ $isExceeded ? 'text-rose-400' : ($pct > 85 ? 'text-amber-400' : 'text-emerald-400') }}">
                                {{ $pct }}%
                            </span>
                        </div>
                    </div>

                    <!-- Progress Bar -->
                    <div class="h-2 w-full bg-slate-900 rounded-full overflow-hidden">
                        <div class="h-full {{ $isExceeded ? 'bg-rose-500' : ($pct > 85 ? 'bg-amber-400' : 'bg-cyan-400') }}" style="width: {{ min($pct, 100) }}%;"></div>
                    </div>

                    @if ($isExceeded)
                        <div class="p-3 rounded-xl bg-rose-950/60 border border-rose-500/40 text-xs text-rose-300 flex items-center justify-between">
                            <div class="flex items-center space-x-2">
                                <span>⚠️</span>
                                <span>Budget exceeded by <strong>Rp {{ number_format($overAmount) }}</strong>.</span>
                            </div>
                            <a href="{{ route('transactions') }}" class="px-3 py-1 rounded-lg bg-rose-500/30 text-rose-200 hover:bg-rose-500/40 font-bold text-[11px]">
                                View Spending →
                            </a>
                        </div>
                    @endif
                </div>
            @endforeach
        </div>
    </div>

    <!-- CREATE BUDGET CATEGORY MODAL -->
    @if ($showCreateModal)
        <div class="fixed inset-0 bg-slate-950/80 backdrop-blur-md z-50 flex items-center justify-center p-4">
            <div class="w-full max-w-md bg-slate-900 border border-slate-800 rounded-3xl p-6 space-y-5 shadow-2xl">
                <div class="flex items-center justify-between border-b border-slate-800 pb-4">
                    <h3 class="font-display font-bold text-lg text-white flex items-center space-x-2">
                        <span>📊</span>
                        <span>Tambah Alokasi Budget Baru</span>
                    </h3>
                    <button wire:click="closeModal" class="text-slate-400 hover:text-white font-bold">✕</button>
                </div>

                <form wire:submit.prevent="createBudget" class="space-y-4">
                    <div>
                        <label class="block text-xs font-bold text-slate-400 uppercase tracking-wider mb-1">Nama Kategori Budget</label>
                        <input type="text" wire:model="newCategoryName" placeholder="Contoh: Investasi & Saham / Belanja Bulanan" class="w-full px-4 py-3 rounded-2xl bg-slate-950 border border-slate-800 text-white text-sm focus:outline-none focus:border-cyan-500">
                        @error('newCategoryName') <span class="text-xs text-rose-400 block mt-1">{{ $message }}</span> @enderror
                    </div>

                    <div>
                        <label class="block text-xs font-bold text-slate-400 uppercase tracking-wider mb-1">Batas Maksimal Anggaran (Limit Rp)</label>
                        <input type="number" wire:model="newCategoryLimit" placeholder="Contoh: 2000000" class="w-full px-4 py-3 rounded-2xl bg-slate-950 border border-slate-800 text-white font-mono text-sm focus:outline-none focus:border-cyan-500">
                        @error('newCategoryLimit') <span class="text-xs text-rose-400 block mt-1">{{ $message }}</span> @enderror
                    </div>

                    <div>
                        <label class="block text-xs font-bold text-slate-400 uppercase tracking-wider mb-1">Pilih Icon Emoji</label>
                        <select wire:model="newCategoryIcon" class="w-full px-4 py-3 rounded-2xl bg-slate-950 border border-slate-800 text-white text-sm focus:outline-none focus:border-cyan-500">
                            <option value="🍲">🍲 Food & Groceries</option>
                            <option value="🚗">🚗 Transport & Fuel</option>
                            <option value="🏠">🏠 Housing & Rent</option>
                            <option value="⚡">⚡ Utilities & Bills</option>
                            <option value="🎮">🎮 Entertainment & Hobbies</option>
                            <option value="🛍️">🛍️ Shopping & Apparel</option>
                            <option value="📈">📈 Investment & Stocks</option>
                            <option value="🎓">🎓 Education & Books</option>
                            <option value="💊">💊 Health & Fitness</option>
                            <option value="✈️">✈️ Travel & Vacation</option>
                            <option value="📁">📁 Other / General</option>
                        </select>
                    </div>

                    <div class="p-3 rounded-2xl bg-cyan-950/40 border border-cyan-500/30 text-[11px] text-cyan-300 font-semibold flex items-center space-x-2">
                        <span>💡</span>
                        <span>Menambah batas alokasi akan otomatis memperbarui perhitungan HP Financial Score!</span>
                    </div>

                    <div class="flex justify-end space-x-3 pt-2">
                        <button type="button" wire:click="closeModal" class="px-4 py-2.5 rounded-xl text-slate-400 text-xs font-bold">Batal</button>
                        <button type="submit" class="px-6 py-2.5 rounded-xl {{ $tokens['primary_bg'] }} text-xs font-bold shadow-lg">Simpan Budget</button>
                    </div>
                </form>
            </div>
        </div>
    @endif
</div>

