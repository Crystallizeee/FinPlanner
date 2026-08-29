<div>
    @if ($show && $receipt)
        <div class="fixed inset-0 bg-slate-950/80 backdrop-blur-md z-50 flex items-center justify-center p-4">
            <div class="w-full max-w-lg bg-slate-900 border border-slate-700 rounded-3xl shadow-2xl overflow-hidden flex flex-col max-h-[90vh]">

                {{-- ── HEADER ── --}}
                <div class="p-5 border-b border-slate-800 flex items-center justify-between bg-gradient-to-r from-cyan-950/50 to-slate-900 shrink-0">
                    <div class="flex items-center space-x-3">
                        <div class="w-10 h-10 rounded-2xl bg-cyan-500/20 flex items-center justify-center text-lg shrink-0">🧾</div>
                        <div class="min-w-0">
                            @if ($isEditing)
                                <h3 class="font-display font-bold text-amber-300 text-sm">✏️ Edit Receipt Items</h3>
                                <p class="text-[10px] text-slate-400 font-mono">Koreksi item & nominal struk</p>
                            @else
                                <h3 class="font-display font-bold text-white text-sm truncate">{{ $receipt->merchant_name }}</h3>
                                <p class="text-[10px] text-slate-400 font-mono">
                                    {{ $receipt->transaction_date?->format('d M Y, H:i') }} • Ref: {{ $receipt->receipt_number }}
                                    @if ($receipt->ocr_status === 'manually_corrected')
                                        <span class="ml-1 px-1.5 py-0.5 rounded-full bg-amber-500/20 text-amber-300 border border-amber-500/30 text-[9px] font-bold">Dikoreksi Manual</span>
                                    @else
                                        <span class="ml-1 px-1.5 py-0.5 rounded-full bg-cyan-500/20 text-cyan-300 border border-cyan-500/30 text-[9px] font-bold">🤖 AI OCR</span>
                                    @endif
                                </p>
                            @endif
                        </div>
                    </div>
                    <button wire:click="close" type="button" class="text-slate-400 hover:text-white font-bold text-lg leading-none shrink-0 ml-3">✕</button>
                </div>

                {{-- ── READ-ONLY VIEW ── --}}
                @if (!$isEditing)
                    {{-- Receipt Image Preview --}}
                    @if ($receipt->image_path)
                        <div class="px-5 pt-4 shrink-0">
                            <img src="{{ asset('storage/' . $receipt->image_path) }}"
                                 alt="Receipt Image"
                                 class="w-full max-h-40 object-contain rounded-2xl border border-slate-700 bg-slate-950">
                        </div>
                    @endif

                    {{-- Items List --}}
                    <div class="overflow-y-auto flex-1 p-5 space-y-2">
                        <div class="text-[10px] font-bold text-slate-400 uppercase tracking-wider mb-3 flex items-center space-x-1">
                            <span>🤖</span>
                            <span>Extracted Items ({{ $receipt->items->count() }} item)</span>
                        </div>

                        @forelse ($receipt->items as $item)
                            <div class="flex items-center justify-between py-2.5 border-b border-slate-800/60 last:border-0">
                                <div class="flex-1 min-w-0 pr-4">
                                    <div class="text-xs font-semibold text-white">{{ $item->item_name }}</div>
                                    <div class="text-[10px] text-slate-400 font-mono">
                                        {{ number_format((float) $item->quantity, 0) }}x @
                                        Rp {{ number_format((float) $item->unit_price, 0, ',', '.') }}
                                    </div>
                                </div>
                                <div class="font-mono font-bold text-sm text-rose-400 shrink-0">
                                    Rp {{ number_format((float) $item->total_price, 0, ',', '.') }}
                                </div>
                            </div>
                        @empty
                            <div class="text-center py-6 text-xs text-slate-500">Tidak ada item terdeteksi.</div>
                        @endforelse
                    </div>

                    {{-- Footer --}}
                    <div class="p-5 border-t border-slate-800 bg-slate-950/50 flex items-center justify-between shrink-0">
                        <div>
                            <div class="text-[10px] text-slate-400 uppercase tracking-wider font-bold">Total Struk</div>
                            <div class="font-mono font-black text-xl text-cyan-400">
                                Rp {{ number_format((float) $receipt->total_amount, 0, ',', '.') }}
                            </div>
                            <div class="text-[10px] text-emerald-400 font-semibold mt-0.5">
                                Confidence: {{ number_format((float) $receipt->confidence_score, 0) }}%
                            </div>
                        </div>
                        <div class="flex flex-col space-y-2 text-right">
                            <button wire:click="startEditing" type="button"
                                    class="px-4 py-2 rounded-xl bg-amber-500/20 border border-amber-500/30 text-amber-300 text-xs font-bold hover:bg-amber-500/30 transition-all">
                                ✏️ Edit Items
                            </button>
                            <button wire:click="close" type="button"
                                    class="px-4 py-2 rounded-xl bg-emerald-500/20 border border-emerald-500/30 text-emerald-300 text-xs font-bold hover:bg-emerald-500/30 transition-all">
                                ✅ Tutup
                            </button>
                        </div>
                    </div>

                {{-- ── EDIT FORM ── --}}
                @else
                    <form wire:submit.prevent="saveCorrection" class="flex flex-col flex-1 overflow-hidden">

                        {{-- Merchant Name --}}
                        <div class="px-5 pt-4 pb-3 shrink-0 border-b border-slate-800">
                            <label class="text-[10px] font-bold text-slate-400 uppercase tracking-wider block mb-1">Nama Merchant / Toko</label>
                            <input type="text" wire:model="editMerchantName"
                                   class="w-full px-3 py-2 rounded-xl bg-slate-950 border border-slate-700 text-white text-xs focus:outline-none focus:border-amber-500"
                                   placeholder="Nama toko...">
                            @error('editMerchantName') <span class="text-[10px] text-rose-400 block mt-1">{{ $message }}</span> @enderror
                        </div>

                        {{-- Item Rows --}}
                        <div class="overflow-y-auto flex-1 p-5 space-y-3">
                            <div class="flex items-center justify-between mb-1">
                                <span class="text-[10px] font-bold text-slate-400 uppercase tracking-wider">Item Belanja</span>
                                <button type="button" wire:click="addItemRow"
                                        class="text-[10px] font-bold text-cyan-400 hover:text-cyan-300 flex items-center space-x-1">
                                    <span>＋</span><span>Tambah Item</span>
                                </button>
                            </div>

                            {{-- Column Header --}}
                            <div class="grid grid-cols-12 gap-1 text-[9px] font-bold text-slate-500 uppercase tracking-wider px-1">
                                <div class="col-span-5">Nama Item</div>
                                <div class="col-span-2 text-center">Qty</div>
                                <div class="col-span-2 text-right">Harga</div>
                                <div class="col-span-2 text-right">Total</div>
                                <div class="col-span-1"></div>
                            </div>

                            @foreach ($editItems as $idx => $editItem)
                                <div class="grid grid-cols-12 gap-1 items-center">
                                    <div class="col-span-5">
                                        <input type="text"
                                               wire:model.live="editItems.{{ $idx }}.name"
                                               placeholder="Nama produk..."
                                               class="w-full px-2 py-1.5 rounded-lg bg-slate-950 border border-slate-700 text-white text-[11px] focus:outline-none focus:border-amber-500 placeholder:text-slate-600">
                                        @error("editItems.{$idx}.name") <span class="text-[9px] text-rose-400 block">{{ $message }}</span> @enderror
                                    </div>

                                    <div class="col-span-2">
                                        <input type="number" wire:model.live="editItems.{{ $idx }}.qty"
                                               min="0.01" step="any"
                                               class="w-full px-2 py-1.5 rounded-lg bg-slate-950 border border-slate-700 text-white text-[11px] text-center focus:outline-none focus:border-amber-500">
                                    </div>

                                    <div class="col-span-2">
                                        <input type="number" wire:model.live="editItems.{{ $idx }}.unit_price"
                                               min="0" step="any"
                                               class="w-full px-2 py-1.5 rounded-lg bg-slate-950 border border-slate-700 text-white text-[11px] text-right focus:outline-none focus:border-amber-500">
                                    </div>

                                    <div class="col-span-2">
                                        <div class="px-2 py-1.5 rounded-lg bg-slate-800 border border-slate-700 text-cyan-300 text-[11px] text-right font-mono font-bold">
                                            {{ number_format((float) ($editItem['total_price'] ?? 0), 0, ',', '.') }}
                                        </div>
                                    </div>

                                    <div class="col-span-1 flex justify-center">
                                        <button type="button" wire:click="removeItemRow({{ $idx }})"
                                                class="w-6 h-6 rounded-full bg-rose-500/20 text-rose-400 hover:bg-rose-500/40 text-xs font-bold flex items-center justify-center transition-all">
                                            ✕
                                        </button>
                                    </div>
                                </div>
                            @endforeach
                        </div>

                        {{-- Footer --}}
                        <div class="p-5 border-t border-slate-800 bg-slate-950/50 shrink-0">
                            <div class="flex items-center justify-between">
                                <div>
                                    <div class="text-[10px] text-slate-400 uppercase tracking-wider font-bold">Total Baru</div>
                                    <div class="font-mono font-black text-xl text-amber-400">
                                        Rp {{ number_format((float) ($editTotalAmount ?? 0), 0, ',', '.') }}
                                    </div>
                                    @error('editTotalAmount') <span class="text-[10px] text-rose-400 block">{{ $message }}</span> @enderror
                                </div>
                                <div class="flex space-x-2">
                                    <button type="button" wire:click="cancelEditing"
                                            class="px-4 py-2 rounded-xl bg-slate-800 border border-slate-700 text-slate-300 text-xs font-bold hover:bg-slate-700 transition-all">
                                        Batal
                                    </button>
                                    <button type="submit"
                                            class="px-5 py-2 rounded-xl bg-amber-500 hover:bg-amber-400 text-slate-950 text-xs font-extrabold shadow-lg shadow-amber-500/30 transition-all"
                                            wire:loading.attr="disabled">
                                        <span wire:loading.remove wire:target="saveCorrection">💾 Simpan Koreksi</span>
                                        <span wire:loading wire:target="saveCorrection">Menyimpan…</span>
                                    </button>
                                </div>
                            </div>
                        </div>

                    </form>
                @endif

            </div>
        </div>
    @endif
</div>
