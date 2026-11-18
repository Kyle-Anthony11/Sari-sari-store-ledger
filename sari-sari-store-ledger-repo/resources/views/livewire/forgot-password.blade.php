<div>
    <div class="bg-white/95 backdrop-blur-md rounded-3xl shadow-2xl shadow-slate-900/20 border border-white/40 overflow-hidden">
        {{-- Header --}}
        <div class="px-6 sm:px-8 pt-7 sm:pt-8 pb-5 sm:pb-6 text-center border-b border-slate-100">
            <div class="inline-flex items-center justify-center w-12 h-12 rounded-2xl bg-gradient-to-br from-indigo-500 to-blue-600 shadow-lg shadow-indigo-500/30 mb-3">
                <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6 text-white" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M15 7a2 2 0 012 2m4 0a6 6 0 01-7.743 5.743L11 17H9v2H7v2H4a1 1 0 01-1-1v-2.586a1 1 0 01.293-.707l5.964-5.964A6 6 0 1121 9z" />
                </svg>
            </div>
            <h2 class="text-2xl font-bold text-slate-900 tracking-tight">Reset Password</h2>
            <p class="text-sm text-slate-500 mt-1">
                @if($step === 2)
                    Enter the 6-digit code we sent to your email
                @elseif($step === 3)
                    Choose a new password for your account
                @else
                    Recover access to your store account
                @endif
            </p>
        </div>

        <div class="p-6 sm:p-8">
            {{-- Error --}}
            @if($error)
                <div class="mb-5 rounded-xl bg-rose-50 border border-rose-200 p-3 text-sm text-rose-700 flex items-start gap-2">
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4 flex-shrink-0 mt-0.5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z" />
                    </svg>
                    <span>{{ $error }}</span>
                </div>
            @endif

            {{-- Info --}}
            @if($info && !$error)
                <div class="mb-5 rounded-xl bg-blue-50 border border-blue-200 p-3 text-sm text-blue-700 flex items-start gap-2">
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4 flex-shrink-0 mt-0.5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                    </svg>
                    <span>{{ $info }}</span>
                </div>
            @endif

            @if($step === 1)
                {{-- No admin account / idle --}}
                <div class="text-center">
                    <a href="{{ route('login') }}"
                       class="inline-flex items-center justify-center rounded-xl border border-slate-300 bg-white px-5 py-3 text-sm font-semibold text-slate-700 active:bg-slate-100 transition">
                        ← Back to Login
                    </a>
                </div>

            @elseif($step === 2)
                {{-- OTP Entry --}}
                <form wire:submit.prevent="verifyOtp" class="space-y-5">
                    <div>
                        <label class="block text-sm font-semibold text-slate-700 mb-1.5">Verification Code</label>
                        <input type="text"
                               wire:model.defer="otp"
                               inputmode="numeric"
                               maxlength="6"
                               oninput="this.value = this.value.replace(/[^0-9]/g, '')"
                               placeholder="000000"
                               autocomplete="one-time-code"
                               class="w-full rounded-xl border border-slate-300 bg-white px-3.5 py-3.5 text-center text-2xl font-bold tracking-[0.5em] text-slate-900 focus:border-indigo-500 focus:ring-2 focus:ring-indigo-500/20 transition placeholder:text-slate-300 placeholder:tracking-[0.5em]" />
                        @error('otp') <div class="text-rose-600 text-xs mt-1.5 text-center">{{ $message }}</div> @enderror
                    </div>

                    <button type="submit"
                            class="w-full rounded-xl bg-gradient-to-r from-indigo-600 to-blue-600 hover:from-indigo-700 hover:to-blue-700 py-3.5 text-sm font-semibold text-white shadow-lg shadow-indigo-500/30 active:scale-[0.98] transition-all duration-200">
                        Verify Code
                    </button>

                    {{-- Resend with 60s countdown --}}
                    <div class="text-center"
                         wire:key="resend-{{ $otpSentAt }}"
                         x-data="{
                             sent: {{ (int) ($otpSentAt ?? 0) }},
                             now: Math.floor(Date.now() / 1000),
                             init() {
                                 setInterval(() => {
                                     this.now = Math.floor(Date.now() / 1000);
                                 }, 1000);
                             },
                             get remaining() {
                                 return Math.max(0, 60 - (this.now - this.sent));
                             }
                         }">
                        <button type="button"
                                wire:click="resendOtp"
                                :disabled="remaining > 0"
                                class="text-sm font-semibold text-indigo-600 hover:text-indigo-700 disabled:text-slate-400 disabled:cursor-not-allowed transition">
                            <span x-show="remaining > 0" x-text="'Send again in ' + remaining + 's'"></span>
                            <span x-show="remaining <= 0">Send again</span>
                        </button>
                    </div>

                    <div class="text-center pt-1">
                        <a href="{{ route('login') }}" class="text-xs text-slate-500 hover:text-slate-700 transition">← Back to Login</a>
                    </div>
                </form>

            @elseif($step === 3)
                {{-- New Password --}}
                <form wire:submit.prevent="updatePassword" class="space-y-5">
                    <div>
                        <label class="block text-sm font-semibold text-slate-700 mb-1.5">New Password</label>
                        <div class="relative" x-data="{ show: false }">
                            <input :type="show ? 'text' : 'password'"
                                   wire:model.defer="new_password"
                                   autocomplete="new-password"
                                   class="w-full rounded-xl border border-slate-300 bg-white px-3.5 py-3 pr-11 text-base sm:text-sm focus:border-indigo-500 focus:ring-2 focus:ring-indigo-500/20 transition" />
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
                        @error('new_password') <div class="text-rose-600 text-xs mt-1.5">{{ $message }}</div> @enderror
                        <p class="text-xs text-slate-500 mt-1.5">At least 6 characters, 1 uppercase, 1 digit, and 1 special character.</p>
                    </div>

                    <div>
                        <label class="block text-sm font-semibold text-slate-700 mb-1.5">Confirm New Password</label>
                        <div class="relative" x-data="{ show: false }">
                            <input :type="show ? 'text' : 'password'"
                                   wire:model.defer="new_password_confirmation"
                                   autocomplete="new-password"
                                   class="w-full rounded-xl border border-slate-300 bg-white px-3.5 py-3 pr-11 text-base sm:text-sm focus:border-indigo-500 focus:ring-2 focus:ring-indigo-500/20 transition" />
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
                    </div>

                    <button type="submit"
                            class="w-full rounded-xl bg-gradient-to-r from-indigo-600 to-blue-600 hover:from-indigo-700 hover:to-blue-700 py-3.5 text-sm font-semibold text-white shadow-lg shadow-indigo-500/30 active:scale-[0.98] transition-all duration-200">
                        Update Password
                    </button>
                </form>
            @endif
        </div>
    </div>
</div>