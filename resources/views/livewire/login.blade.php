<div>
    <div class="bg-white/95 backdrop-blur-md rounded-3xl shadow-2xl shadow-slate-900/20 border border-white/40 overflow-hidden">
        <div class="px-6 sm:px-8 pt-7 sm:pt-8 pb-5 sm:pb-6 text-center border-b border-slate-100">
            <h2 class="text-2xl font-bold text-slate-900 tracking-tight">Welcome Back</h2>
            <p class="text-sm text-slate-500 mt-1">Sign in to your store account</p>
        </div>

        <div class="p-6 sm:p-8">
            @if($error)
                <div class="mb-5 rounded-xl bg-rose-50 border border-rose-200 p-3 text-sm text-rose-700 flex items-center gap-2">
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4 flex-shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z" />
                    </svg>
                    {{ $error }}
                </div>
            @endif

            <form wire:submit.prevent="authenticate" class="space-y-5">
                <div>
                    <label class="block text-sm font-semibold text-slate-700 mb-1.5">Email</label>
                    <div class="relative">
                        <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4 absolute left-3.5 top-1/2 -translate-y-1/2 text-slate-400" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M3 8l7.89 5.26a2 2 0 002.22 0L21 8M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z" />
                        </svg>
                        <input type="text" wire:model="email"
                               class="w-full rounded-xl border border-slate-300 bg-white pl-10 pr-4 py-3 text-base sm:text-sm focus:border-indigo-500 focus:ring-2 focus:ring-indigo-500/20 transition placeholder:text-slate-400"
                               placeholder="you@example.com" />
                    </div>
                    @error('email') <div class="text-rose-600 text-xs mt-1.5">{{ $message }}</div> @enderror
                </div>

                <div>
                    <label class="block text-sm font-semibold text-slate-700 mb-1.5">Password</label>
                    <div class="relative" x-data="{ show: false }">
                        <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4 absolute left-3.5 top-1/2 -translate-y-1/2 text-slate-400" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z" />
                        </svg>
                        <input :type="show ? 'text' : 'password'" wire:model="password"
                               class="w-full rounded-xl border border-slate-300 bg-white pl-10 pr-10 py-3 text-base sm:text-sm focus:border-indigo-500 focus:ring-2 focus:ring-indigo-500/20 transition placeholder:text-slate-400"
                               placeholder="Enter your password" />
                        <button type="button" @click="show = !show" class="absolute right-3 top-1/2 -translate-y-1/2 text-slate-400 hover:text-slate-600 p-1" aria-label="Toggle password visibility">
                            <svg x-show="!show" xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" />
                                <path stroke-linecap="round" stroke-linejoin="round" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z" />
                            </svg>
                            <svg x-show="show" xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M13.875 18.825A10.05 10.05 0 0112 19c-4.478 0-8.268-2.943-9.543-7a9.97 9.97 0 011.563-3.029m5.858.908a3 3 0 114.243 4.243M9.878 9.878l4.242 4.242M9.88 9.88l-3.29-3.29m7.532 7.532l3.29 3.29M3 3l3.59 3.59m0 0A9.953 9.953 0 0112 5c4.478 0 8.268 2.943 9.543 7a10.025 10.025 0 01-4.132 5.411m0 0L21 21" />
                            </svg>
                        </button>
                    </div>
                    @error('password') <div class="text-rose-600 text-xs mt-1.5">{{ $message }}</div> @enderror
                </div>

                <div>
                    <label class="block text-sm font-semibold text-slate-700 mb-1.5">PIN</label>
                    <div class="relative" x-data="{ show: false }">
                        <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4 absolute left-3.5 top-1/2 -translate-y-1/2 text-slate-400" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M7 20l4-16m2 16l4-16M6 9h14M4 15h14" />
                        </svg>
                        <input :type="show ? 'text' : 'password'" wire:model="pin"
                               inputmode="numeric"
                               oninput="this.value = this.value.replace(/[^0-9]/g, '')"
                               maxlength="4"
                               class="w-full rounded-xl border border-slate-300 bg-white pl-10 pr-10 py-3 text-base sm:text-sm focus:border-indigo-500 focus:ring-2 focus:ring-indigo-500/20 transition placeholder:text-slate-400 tracking-widest"
                               placeholder="••••" />
                        <button type="button" @click="show = !show" class="absolute right-3 top-1/2 -translate-y-1/2 text-slate-400 hover:text-slate-600 p-1" aria-label="Toggle PIN visibility">
                            <svg x-show="!show" xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" />
                                <path stroke-linecap="round" stroke-linejoin="round" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z" />
                            </svg>
                            <svg x-show="show" xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M13.875 18.825A10.05 10.05 0 0112 19c-4.478 0-8.268-2.943-9.543-7a9.97 9.97 0 011.563-3.029m5.858.908a3 3 0 114.243 4.243M9.878 9.878l4.242 4.242M9.88 9.88l-3.29-3.29m7.532 7.532l3.29 3.29M3 3l3.59 3.59m0 0A9.953 9.953 0 0112 5c4.478 0 8.268 2.943 9.543 7a10.025 10.025 0 01-4.132 5.411m0 0L21 21" />
                            </svg>
                        </button>
                    </div>
                    @error('pin') <div class="text-rose-600 text-xs mt-1.5">{{ $message }}</div> @enderror
                </div>

                <button type="submit"
                        class="w-full rounded-xl bg-gradient-to-r from-indigo-600 to-blue-600 hover:from-indigo-700 hover:to-blue-700 py-3.5 text-sm font-semibold text-white shadow-lg shadow-indigo-500/30 active:scale-[0.98] transition-all duration-200">
                    Sign In
                </button>

                <div class="text-center pt-1">
                    <a href="{{ route('password.forgot') }}"
                       class="text-sm font-semibold text-indigo-600 hover:text-indigo-700 hover:underline transition">
                        Forgot Password?
                    </a>
                </div>
            </form>
        </div>
    </div>
</div>