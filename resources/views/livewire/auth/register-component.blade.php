<div class="glass-card border border-slate-800 rounded-3xl p-6 sm:p-8 shadow-2xl space-y-6">
    <!-- Header -->
    <div class="text-center space-y-2">
        <div class="inline-flex items-center justify-center w-14 h-14 rounded-2xl bg-gradient-to-tr from-blue-600 via-cyan-500 to-emerald-400 p-0.5 shadow-lg shadow-cyan-500/20 mb-2">
            <div class="w-full h-full bg-slate-950 rounded-[14px] flex items-center justify-center font-black text-2xl text-cyan-400 font-display">
                F
            </div>
        </div>
        <h1 class="text-2xl font-bold font-display text-white tracking-tight">Daftar Akun FinancialPlanner</h1>
        <p class="text-xs text-slate-400 font-medium">Buat akun untuk memulai pengelolaan keuangan pribadi Anda.</p>
    </div>

    <!-- Register Form -->
    <form wire:submit.prevent="register" class="space-y-4">
        <!-- Name Input -->
        <div class="space-y-1.5">
            <label for="name" class="block text-xs font-bold text-slate-300">Nama Lengkap</label>
            <input wire:model="name" id="name" type="text" placeholder="Beni Strategist" class="w-full px-4 py-3 rounded-2xl bg-slate-900/90 border border-slate-800 text-sm text-white placeholder-slate-500 focus:outline-none focus:border-cyan-500 focus:ring-1 focus:ring-cyan-500 transition-all" />
            @error('name')
                <p class="text-[11px] font-bold text-rose-400 mt-1">{{ $message }}</p>
            @enderror
        </div>

        <!-- Email Input -->
        <div class="space-y-1.5">
            <label for="email" class="block text-xs font-bold text-slate-300">Email Address</label>
            <input wire:model="email" id="email" type="email" autocomplete="email" placeholder="nama@email.com" class="w-full px-4 py-3 rounded-2xl bg-slate-900/90 border border-slate-800 text-sm text-white placeholder-slate-500 focus:outline-none focus:border-cyan-500 focus:ring-1 focus:ring-cyan-500 transition-all" />
            @error('email')
                <p class="text-[11px] font-bold text-rose-400 mt-1">{{ $message }}</p>
            @enderror
        </div>

        <!-- Password Input -->
        <div class="space-y-1.5">
            <label for="password" class="block text-xs font-bold text-slate-300">Password</label>
            <input wire:model="password" id="password" type="password" autocomplete="new-password" placeholder="Minimal 8 karakter" class="w-full px-4 py-3 rounded-2xl bg-slate-900/90 border border-slate-800 text-sm text-white placeholder-slate-500 focus:outline-none focus:border-cyan-500 focus:ring-1 focus:ring-cyan-500 transition-all" />
            @error('password')
                <p class="text-[11px] font-bold text-rose-400 mt-1">{{ $message }}</p>
            @enderror
        </div>

        <!-- Password Confirmation Input -->
        <div class="space-y-1.5">
            <label for="passwordConfirmation" class="block text-xs font-bold text-slate-300">Konfirmasi Password</label>
            <input wire:model="passwordConfirmation" id="passwordConfirmation" type="password" autocomplete="new-password" placeholder="Ulangi password" class="w-full px-4 py-3 rounded-2xl bg-slate-900/90 border border-slate-800 text-sm text-white placeholder-slate-500 focus:outline-none focus:border-cyan-500 focus:ring-1 focus:ring-cyan-500 transition-all" />
            @error('passwordConfirmation')
                <p class="text-[11px] font-bold text-rose-400 mt-1">{{ $message }}</p>
            @enderror
        </div>

        <!-- Submit Button -->
        <button type="submit" class="w-full py-3.5 px-4 rounded-2xl bg-gradient-to-r from-cyan-500 via-blue-600 to-indigo-600 hover:from-cyan-400 hover:via-blue-500 hover:to-indigo-500 text-white font-extrabold text-sm shadow-lg shadow-cyan-500/25 transition-all transform active:scale-95 cursor-pointer mt-2">
            Daftar Akun Baru ➔
        </button>
    </form>

    <!-- Footer link -->
    <div class="text-center pt-2 border-t border-slate-800/60">
        <p class="text-xs text-slate-400 font-medium">
            Sudah memiliki akun?
            <a href="{{ route('login') }}" class="font-bold text-cyan-400 hover:text-cyan-300 underline underline-offset-4">Masuk ke Akun</a>
        </p>
    </div>
</div>
