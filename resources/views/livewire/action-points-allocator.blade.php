<div class="space-y-8"
     x-data="{
         availableAp: {{ $user ? $user->action_points_balance : 0 }},
         allocations: {
             @foreach($questPools as $pool)
                 {{ $pool->id }}: 0,
             @endforeach
         },
         get totalAllocated() {
             let total = 0;
             for (let key in this.allocations) {
                 total += parseInt(this.allocations[key] || 0);
             }
             return total;
         },
         get remainingAp() {
             return this.availableAp - this.totalAllocated;
         },
         increment(poolId) {
             if (this.remainingAp > 0) {
                 this.allocations[poolId] = (parseInt(this.allocations[poolId] || 0) + 1);
             }
         },
         decrement(poolId) {
             if (parseInt(this.allocations[poolId] || 0) > 0) {
                 this.allocations[poolId] = (parseInt(this.allocations[poolId] || 0) - 1);
             }
         },
         resetAllocations() {
             for (let key in this.allocations) {
                 this.allocations[key] = 0;
             }
         }
     }">

    <!-- Top Banner & Surplus Evaluation -->
    <div class="glass-panel rounded-3xl p-6 sm:p-8 border border-slate-800 space-y-6">
        <div class="flex flex-col md:flex-row md:items-center justify-between gap-6">
            <div>
                <div class="flex items-center space-x-2">
                    <span class="text-2xl">{{ $labels['icon_ap'] ?? '⚡' }}</span>
                    <h1 class="font-display font-black text-2xl md:text-3xl text-white">
                        {{ $labels['allocator_title'] ?? 'Action Points (AP) Allocator' }}
                    </h1>
                </div>
                <p class="text-xs sm:text-sm text-slate-400 mt-1 max-w-2xl">
                    {{ $labels['allocator_desc'] ?? 'Alokasikan Action Points dari penghematan budget bulanan ke target tabungan riil Anda.' }}
                </p>
            </div>

            <!-- Cycle Surplus Trigger Button -->
            <div class="flex flex-col sm:flex-row items-stretch sm:items-center gap-3">
                <button wire:click="evaluateCycleSurplus" class="px-5 py-3 rounded-2xl bg-gradient-to-r from-amber-500 to-orange-600 hover:from-amber-400 hover:to-orange-500 text-slate-950 font-black text-xs uppercase tracking-wider shadow-lg shadow-amber-950/50 transition-all flex items-center justify-center space-x-2">
                    <span>{{ $labels['evaluate_surplus_btn'] ?? '⚡ Evaluasi Surplus Budget' }}</span>
                </button>
            </div>
        </div>

        <!-- Feedback Alert Messages -->
        @if ($feedbackMessage)
            <div class="p-4 rounded-2xl bg-emerald-950/80 border border-emerald-500/50 text-emerald-300 text-xs font-semibold">
                {{ $feedbackMessage }}
            </div>
        @endif

        @if ($errorMessage)
            <div class="p-4 rounded-2xl bg-red-950/80 border border-red-500/50 text-red-300 text-xs font-semibold">
                {{ $errorMessage }}
            </div>
        @endif

        <!-- AP Balance Summary Bar -->
        <div class="grid grid-cols-1 sm:grid-cols-3 gap-4 pt-2">
            <div class="p-4 rounded-2xl bg-slate-950/80 border border-slate-800 text-center">
                <div class="text-xs font-bold text-slate-400 uppercase tracking-wider">Total Available AP</div>
                <div class="text-3xl font-black text-emerald-400 mt-1 font-display" x-text="availableAp">
                    {{ $user ? $user->action_points_balance : 0 }}
                </div>
                <div class="text-[10px] text-slate-500 mt-1">1 AP = Rp 10.000 Fund Value</div>
            </div>

            <div class="p-4 rounded-2xl bg-slate-950/80 border border-slate-800 text-center">
                <div class="text-xs font-bold text-slate-400 uppercase tracking-wider">Allocating in Session</div>
                <div class="text-3xl font-black text-amber-400 mt-1 font-display" x-text="totalAllocated">
                    0
                </div>
                <div class="text-[10px] text-slate-500 mt-1">Points Selected Below</div>
            </div>

            <div class="p-4 rounded-2xl bg-slate-950/80 border border-slate-800 text-center">
                <div class="text-xs font-bold text-slate-400 uppercase tracking-wider">Unallocated AP Buffer</div>
                <div class="text-3xl font-black text-cyan-400 mt-1 font-display" x-text="remainingAp">
                    {{ $user ? $user->action_points_balance : 0 }}
                </div>
                <div class="text-[10px] text-slate-500 mt-1">Remaining to Distribute</div>
            </div>
        </div>
    </div>

    <!-- INTERACTIVE QUEST POOL ALLOCATOR UI (ALPINE.JS POWERED) -->
    <div class="space-y-6">
        <div class="flex items-center justify-between">
            <h2 class="font-display font-bold text-xl text-white">Predefined Real-World Quest Pools</h2>
            <button type="button" @click="resetAllocations()" class="text-xs text-slate-400 hover:text-white font-semibold">
                Reset Selection
            </button>
        </div>

        <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
            @foreach($questPools as $pool)
                <div class="glass-panel rounded-3xl p-6 border border-slate-800 hover:border-amber-500/40 transition-all flex flex-col justify-between space-y-6 relative overflow-hidden">
                    <div class="space-y-3">
                        <div class="flex items-center justify-between">
                            <span class="inline-flex items-center px-3 py-1 rounded-full text-[10px] font-bold uppercase tracking-wider bg-slate-900 border border-slate-700 text-amber-400">
                                {{ strtoupper($pool->category) }}
                            </span>
                            <span class="text-xs font-mono text-emerald-400 font-bold">
                                {{ $pool->getProgressPercentage() }}% Reached
                            </span>
                        </div>

                        <h3 class="font-display font-bold text-lg text-white leading-snug">
                            {{ $pool->name }}
                        </h3>

                        <!-- Progress Bar -->
                        <div class="w-full h-2.5 bg-slate-950 rounded-full overflow-hidden border border-slate-800">
                            <div class="h-full bg-gradient-to-r from-amber-500 to-emerald-400 transition-all duration-500"
                                 style="width: {{ $pool->getProgressPercentage() }}%;"></div>
                        </div>

                        <div class="flex justify-between text-xs font-mono text-slate-400">
                            <span>Current: Rp {{ number_format((float)$pool->current_amount, 0, ',', '.') }}</span>
                            <span>Target: Rp {{ number_format((float)$pool->target_amount, 0, ',', '.') }}</span>
                        </div>
                    </div>

                    <!-- Alpine Interactive Point Counter -->
                    <div class="pt-4 border-t border-slate-800/80 space-y-3">
                        <div class="text-xs font-bold text-slate-300 flex justify-between items-center">
                            <span>Allocate Points:</span>
                            <span class="font-mono text-amber-400 font-bold" x-text="allocations[{{ $pool->id }}] + ' AP'">0 AP</span>
                        </div>

                        <div class="flex items-center space-x-3">
                            <button type="button"
                                    @click="decrement({{ $pool->id }})"
                                    class="w-10 h-10 rounded-xl bg-slate-900 hover:bg-slate-800 border border-slate-700 text-white font-black text-lg flex items-center justify-center transition-colors">
                                -
                            </button>

                            <input type="number"
                                   min="0"
                                   x-model.number="allocations[{{ $pool->id }}]"
                                   class="w-full bg-slate-950 border border-slate-800 rounded-xl py-2 px-3 text-center text-sm font-bold text-amber-300 focus:outline-none focus:border-amber-500 font-mono">

                            <button type="button"
                                    @click="increment({{ $pool->id }})"
                                    :disabled="remainingAp <= 0"
                                    class="w-10 h-10 rounded-xl bg-amber-500 hover:bg-amber-400 disabled:opacity-30 disabled:hover:bg-amber-500 text-slate-950 font-black text-lg flex items-center justify-center transition-colors shadow-md shadow-amber-950/50">
                                +
                            </button>
                        </div>

                        <div class="text-[10px] text-center font-mono text-slate-400">
                            Fund Increase: <strong class="text-emerald-400">+Rp <span x-text="(parseInt(allocations[{{ $pool->id }}] || 0) * 10000).toLocaleString('id-ID')">0</span></strong>
                        </div>
                    </div>
                </div>
            @endforeach
        </div>

        <!-- Submit Allocations Button -->
        <div class="flex justify-end pt-4">
            <button type="button"
                    @click="$wire.submitAllocations(allocations)"
                    :disabled="totalAllocated <= 0 || remainingAp < 0"
                    class="px-8 py-4 rounded-2xl bg-gradient-to-r from-emerald-500 via-teal-500 to-cyan-500 hover:from-emerald-400 hover:to-cyan-400 disabled:opacity-40 text-slate-950 font-black text-sm uppercase tracking-wider shadow-xl shadow-emerald-950/50 transition-all flex items-center space-x-2">
                <span>🚀 CONFIRM & SAVE AP ALLOCATIONS</span>
            </button>
        </div>
    </div>
</div>
