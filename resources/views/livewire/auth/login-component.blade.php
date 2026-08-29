<div class="glass-card border border-slate-800 rounded-3xl p-6 sm:p-8 shadow-2xl space-y-6">
    <!-- Header -->
    <div class="text-center space-y-2">
        <div class="inline-flex items-center justify-center w-14 h-14 rounded-2xl bg-gradient-to-tr from-blue-600 via-cyan-500 to-emerald-400 p-0.5 shadow-lg shadow-cyan-500/20 mb-2">
            <div class="w-full h-full bg-slate-950 rounded-[14px] flex items-center justify-center font-black text-2xl text-cyan-400 font-display">
                F
            </div>
        </div>
        <h1 class="text-2xl font-bold font-display text-white tracking-tight">Masuk ke FinancialPlanner</h1>
        <p class="text-xs text-slate-400 font-medium">Kelola portofolio, anggaran, dan habit keuangan Anda.</p>
    </div>

    <!-- Login Form -->
    <form wire:submit.prevent="login" class="space-y-4">
        <!-- Email Input -->
        <div class="space-y-1.5">
            <label for="email" class="block text-xs font-bold text-slate-300">Email Address</label>
            <div class="relative">
                <input wire:model="email" id="email" type="email" autocomplete="email" placeholder="nama@email.com" class="w-full px-4 py-3 rounded-2xl bg-slate-900/90 border border-slate-800 text-sm text-white placeholder-slate-500 focus:outline-none focus:border-cyan-500 focus:ring-1 focus:ring-cyan-500 transition-all" />
            </div>
            @error('email')
                <p class="text-[11px] font-bold text-rose-400 mt-1">{{ $message }}</p>
            @enderror
        </div>

        <!-- Password Input -->
        <div class="space-y-1.5">
            <label for="password" class="block text-xs font-bold text-slate-300">Password</label>
            <div class="relative">
                <input wire:model="password" id="password" type="password" autocomplete="current-password" placeholder="••••••••" class="w-full px-4 py-3 rounded-2xl bg-slate-900/90 border border-slate-800 text-sm text-white placeholder-slate-500 focus:outline-none focus:border-cyan-500 focus:ring-1 focus:ring-cyan-500 transition-all" />
            </div>
            @error('password')
                <p class="text-[11px] font-bold text-rose-400 mt-1">{{ $message }}</p>
            @enderror
        </div>

        <!-- Remember Me Checkbox -->
        <div class="flex items-center justify-between text-xs pt-1">
            <label class="flex items-center space-x-2 text-slate-400 cursor-pointer">
                <input wire:model="remember" type="checkbox" class="w-4 h-4 rounded bg-slate-900 border-slate-800 text-cyan-500 focus:ring-cyan-500 focus:ring-offset-slate-950">
                <span class="font-medium">Ingat Saya</span>
            </label>
        </div>

        <!-- Submit Button -->
        <button type="submit" class="w-full py-3.5 px-4 rounded-2xl bg-gradient-to-r from-cyan-500 via-blue-600 to-indigo-600 hover:from-cyan-400 hover:via-blue-500 hover:to-indigo-500 text-white font-extrabold text-sm shadow-lg shadow-cyan-500/25 transition-all transform active:scale-95 cursor-pointer">
            Masuk Sekarang ➔
        </button>
    </form>

    <!-- Footer link -->
    <div class="text-center pt-2 border-t border-slate-800/60">
        <p class="text-xs text-slate-400 font-medium">
            Belum memiliki akun?
            <a href="{{ route('register') }}" class="font-bold text-cyan-400 hover:text-cyan-300 underline underline-offset-4">Daftar Akun Baru</a>
        </p>
    </div>
</div>
