<div>
    <div class="bg-white rounded-3xl shadow-2xl border border-white/40 overflow-hidden">
        {{-- Header --}}
        <div class="px-8 pt-8 pb-6 text-center border-b border-slate-100">
            <div class="inline-flex items-center justify-center w-14 h-14 rounded-2xl bg-gradient-to-br from-indigo-500 to-blue-600 shadow-lg shadow-indigo-500/30 mb-4">
                <svg xmlns="http://www.w3.org/2000/svg" class="h-7 w-7 text-white" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4" />
                </svg>
            </div>
            <h2 class="text-2xl font-bold text-slate-900 tracking-tight">{{ $isUpdate ? 'Update Credentials' : 'Store Account Setup' }}</h2>
            <p class="text-sm text-slate-500 mt-1">{{ $isUpdate ? 'Update your email and/or password' : 'Create your store profile and login credentials' }}</p>
        </div>

        {{-- Progress --}}
        <div class="px-8 pt-6">
            <div class="flex items-center gap-3">
                <div class="flex items-center gap-2">
                    <div class="w-7 h-7 rounded-full flex items-center justify-center text-xs font-bold {{ $step >= 1 ? 'bg-indigo-600 text-white shadow-md shadow-indigo-500/30' : 'bg-slate-200 text-slate-500' }}">1</div>
                    <span class="text-xs font-semibold {{ $step >= 1 ? 'text-slate-900' : 'text-slate-400' }}">Store Info</span>
                </div>
                <div class="flex-1 h-0.5 {{ $step >= 2 ? 'bg-indigo-500' : 'bg-slate-200' }} rounded"></div>
                <div class="flex items-center gap-2">
                    <div class="w-7 h-7 rounded-full flex items-center justify-center text-xs font-bold {{ $step >= 2 ? 'bg-indigo-600 text-white shadow-md shadow-indigo-500/30' : 'bg-slate-200 text-slate-500' }}">2</div>
                    <span class="text-xs font-semibold {{ $step >= 2 ? 'text-slate-900' : 'text-slate-400' }}">Credentials</span>
                </div>
            </div>
        </div>

        <div class="p-8">
            {{-- Step 1: Store Owner Information --}}
            @if($step == 1)
                <div wire:key="step-1" class="space-y-4">
                    <h3 class="font-semibold text-slate-900 flex items-center gap-2">
                        <span class="inline-flex items-center justify-center w-6 h-6 rounded-full bg-indigo-100 text-indigo-700 text-xs font-bold">1</span>
                        Store Owner Information
                    </h3>
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                        <div>
                            <label for="owner_first_name" class="block text-sm font-semibold text-slate-700 mb-1.5">First Name *</label>
                            <input type="text" wire:model="owner_first_name" id="owner_first_name" 
                                   oninput="this.value = this.value.replace(/[^a-zA-Z\s]/g, '')"
                                   class="w-full rounded-xl border border-slate-300 px-3.5 py-2.5 text-sm focus:border-indigo-500 focus:ring-2 focus:ring-indigo-500/20 transition" />
                            @error('owner_first_name') <div class="text-sm text-rose-600 mt-1.5">{{ $message }}</div> @enderror
                        </div>
                        <div>
                            <label for="owner_middle_name" class="block text-sm font-semibold text-slate-700 mb-1.5">Middle Name (Optional)</label>
                            <input type="text" wire:model="owner_middle_name" id="owner_middle_name"
                                   oninput="this.value = this.value.replace(/[^a-zA-Z\s]/g, '')"
                                   class="w-full rounded-xl border border-slate-300 px-3.5 py-2.5 text-sm focus:border-indigo-500 focus:ring-2 focus:ring-indigo-500/20 transition" />
                        </div>
                        <div>
                            <label for="owner_last_name" class="block text-sm font-semibold text-slate-700 mb-1.5">Last Name *</label>
                            <input type="text" wire:model="owner_last_name" id="owner_last_name"
                                   oninput="this.value = this.value.replace(/[^a-zA-Z\s]/g, '')"
                                   class="w-full rounded-xl border border-slate-300 px-3.5 py-2.5 text-sm focus:border-indigo-500 focus:ring-2 focus:ring-indigo-500/20 transition" />
                            @error('owner_last_name') <div class="text-sm text-rose-600 mt-1.5">{{ $message }}</div> @enderror
                        </div>
                        <div>
                            <label for="owner_suffix" class="block text-sm font-semibold text-slate-700 mb-1.5">Suffix (Optional)</label>
                            <input type="text" wire:model="owner_suffix" id="owner_suffix" class="w-full rounded-xl border border-slate-300 px-3.5 py-2.5 text-sm focus:border-indigo-500 focus:ring-2 focus:ring-indigo-500/20 transition" placeholder="Jr., Sr., III" />
                        </div>
                    </div>
                    <div>
                        <label for="store_name" class="block text-sm font-semibold text-slate-700 mb-1.5">Store Name *</label>
                        <input type="text" wire:model="store_name" id="store_name" class="w-full rounded-xl border border-slate-300 px-3.5 py-2.5 text-sm focus:border-indigo-500 focus:ring-2 focus:ring-indigo-500/20 transition" />
                        @error('store_name') <div class="text-sm text-rose-600 mt-1.5">{{ $message }}</div> @enderror
                    </div>
                    <div>
                        <label for="contact_number" class="block text-sm font-semibold text-slate-700 mb-1.5">Contact Number *</label>
                        <input type="tel" wire:model="contact_number" id="contact_number"
                               inputmode="tel"
                               maxlength="13"
                               placeholder="+639171234567"
                               oninput="this.value = this.value.replace(/[^0-9+]/g, '')"
                               class="w-full rounded-xl border border-slate-300 px-3.5 py-2.5 text-sm focus:border-indigo-500 focus:ring-2 focus:ring-indigo-500/20 transition" />
                        <p class="text-xs text-slate-500 mt-1">Format: +63 9XX XXX XXXX</p>
                        @error('contact_number') <div class="text-sm text-rose-600 mt-1.5">{{ $message }}</div> @enderror
                    </div>
                    <div>
                        <label for="store_address" class="block text-sm font-semibold text-slate-700 mb-1.5">Store Address *</label>
                        <textarea wire:model="store_address" id="store_address" rows="2" class="w-full rounded-xl border border-slate-300 px-3.5 py-2.5 text-sm focus:border-indigo-500 focus:ring-2 focus:ring-indigo-500/20 transition"></textarea>
                        @error('store_address') <div class="text-sm text-rose-600 mt-1.5">{{ $message }}</div> @enderror
                    </div>
                    <div>
                        <label for="address_notes" class="block text-sm font-semibold text-slate-700 mb-1.5">Address Notes (Optional)</label>
                        <textarea wire:model="address_notes" id="address_notes" rows="2" class="w-full rounded-xl border border-slate-300 px-3.5 py-2.5 text-sm focus:border-indigo-500 focus:ring-2 focus:ring-indigo-500/20 transition" placeholder="Landmarks, special instructions"></textarea>
                    </div>
                </div>
            @else
                {{-- Step 2: Login Credentials --}}
                <div wire:key="step-2" class="space-y-4">
                    <h3 class="font-semibold text-slate-900 flex items-center gap-2">
                        <span class="inline-flex items-center justify-center w-6 h-6 rounded-full bg-indigo-100 text-indigo-700 text-xs font-bold">2</span>
                        Login Credentials
                    </h3>
                    <div>
                        <label for="email" class="block text-sm font-semibold text-slate-700 mb-1.5">Email (Gmail) *</label>
                        <input type="text" wire:model="email" id="email" class="w-full rounded-xl border border-slate-300 px-3.5 py-2.5 text-sm focus:border-indigo-500 focus:ring-2 focus:ring-indigo-500/20 transition" />
                        @error('email') <div class="text-sm text-rose-600 mt-1.5">{{ $message }}</div> @enderror
                    </div>
                    <div>
                        <label for="password" class="block text-sm font-semibold text-slate-700 mb-1.5">Password *</label>
                        <div class="relative" x-data="{ show: false }">
                            <input :type="show ? 'text' : 'password'" wire:model.defer="password" id="password" class="w-full rounded-xl border border-slate-300 px-3.5 py-2.5 pr-10 text-sm focus:border-indigo-500 focus:ring-2 focus:ring-indigo-500/20 transition" />
                            <button type="button" @click="show = !show" class="absolute right-3 top-1/2 -translate-y-1/2 text-slate-400 hover:text-slate-600">
                                <svg x-show="!show" xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" />
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z" />
                                </svg>
                                <svg x-show="show" xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M13.875 18.825A10.05 10.05 0 0112 19c-4.478 0-8.268-2.943-9.543-7a9.97 9.97 0 011.563-3.029m5.858.908a3 3 0 114.243 4.243M9.878 9.878l4.242 4.242M9.88 9.88l-3.29-3.29m7.532 7.532l3.29 3.29M3 3l3.59 3.59m0 0A9.953 9.953 0 0112 5c4.478 0 8.268 2.943 9.543 7a10.025 10.025 0 01-4.132 5.411m0 0L21 21" />
                                </svg>
                            </button>
                        </div>
                        @error('password') <div class="text-sm text-rose-600 mt-1.5">{{ $message }}</div> @enderror
                    </div>
                    <div>
                        <label for="password_confirmation" class="block text-sm font-semibold text-slate-700 mb-1.5">Confirm Password *</label>
                        <div class="relative" x-data="{ show: false }">
                            <input :type="show ? 'text' : 'password'" wire:model.defer="password_confirmation" id="password_confirmation" class="w-full rounded-xl border border-slate-300 px-3.5 py-2.5 pr-10 text-sm focus:border-indigo-500 focus:ring-2 focus:ring-indigo-500/20 transition" />
                            <button type="button" @click="show = !show" class="absolute right-3 top-1/2 -translate-y-1/2 text-slate-400 hover:text-slate-600">
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
                    <div>
                        <label for="pin" class="block text-sm font-semibold text-slate-700 mb-1.5">PIN (4 digits) *</label>
                        <div class="relative" x-data="{ show: false }">
                            <input :type="show ? 'text' : 'password'" wire:model.defer="pin" id="pin" 
                                   inputmode="numeric"
                                   oninput="this.value = this.value.replace(/[^0-9]/g, '')"
                                   maxlength="4"
                                   class="w-full rounded-xl border border-slate-300 px-3.5 py-2.5 pr-10 text-sm focus:border-indigo-500 focus:ring-2 focus:ring-indigo-500/20 transition tracking-widest" />
                            <button type="button" @click="show = !show" class="absolute right-3 top-1/2 -translate-y-1/2 text-slate-400 hover:text-slate-600">
                                <svg x-show="!show" xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" />
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z" />
                                </svg>
                                <svg x-show="show" xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M13.875 18.825A10.05 10.05 0 0112 19c-4.478 0-8.268-2.943-9.543-7a9.97 9.97 0 011.563-3.029m5.858.908a3 3 0 114.243 4.243M9.878 9.878l4.242 4.242M9.88 9.88l-3.29-3.29m7.532 7.532l3.29 3.29M3 3l3.59 3.59m0 0A9.953 9.953 0 0112 5c4.478 0 8.268 2.943 9.543 7a10.025 10.025 0 01-4.132 5.411m0 0L21 21" />
                                </svg>
                            </button>
                        </div>
                        @error('pin') <div class="text-sm text-rose-600 mt-1.5">{{ $message }}</div> @enderror
                    </div>
                    <div>
                        <label for="pin_confirmation" class="block text-sm font-semibold text-slate-700 mb-1.5">Confirm PIN *</label>
                        <div class="relative" x-data="{ show: false }">
                            <input :type="show ? 'text' : 'password'" wire:model.defer="pin_confirmation" id="pin_confirmation" 
                                   inputmode="numeric"
                                   oninput="this.value = this.value.replace(/[^0-9]/g, '')"
                                   maxlength="4"
                                   class="w-full rounded-xl border border-slate-300 px-3.5 py-2.5 pr-10 text-sm focus:border-indigo-500 focus:ring-2 focus:ring-indigo-500/20 transition tracking-widest" />
                            <button type="button" @click="show = !show" class="absolute right-3 top-1/2 -translate-y-1/2 text-slate-400 hover:text-slate-600">
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
                </div>
            @endif

            <div class="flex justify-between mt-8 pt-6 border-t border-slate-100">
                @if($step > 1)
                    <button type="button" wire:click="previousStep" class="rounded-xl border border-slate-300 bg-white px-5 py-2.5 text-sm font-semibold text-slate-700 hover:bg-slate-50 transition">
                        ← Back
                    </button>
                @else
                    <div></div>
                @endif

                @if($step == 1)
                    <button type="button" wire:click="nextStep" class="rounded-xl bg-indigo-600 px-6 py-2.5 text-sm font-semibold text-white shadow-sm hover:bg-indigo-700 transition">
                        Next →
                    </button>
                @else
                    <button type="button" wire:click="saveSetup" class="rounded-xl bg-emerald-600 px-6 py-2.5 text-sm font-semibold text-white shadow-sm hover:bg-emerald-700 transition">
                        {{ $isUpdate ? 'Update Credentials' : 'Complete Setup' }}
                    </button>
                @endif
            </div>
        </div>
    </div>
</div>