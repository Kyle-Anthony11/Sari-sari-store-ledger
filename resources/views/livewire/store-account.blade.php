<div class="space-y-4 sm:space-y-6">
    <div class="bg-white border border-slate-200 rounded-2xl shadow-sm overflow-hidden">
        <div class="px-4 sm:px-6 py-5 border-b border-slate-100 flex flex-col gap-4">
            <div>
                <h1 class="text-base sm:text-lg font-bold text-slate-900"></h1>
                <p class="text-xs text-slate-500 mt-0.5"></p>
            </div>
            <div class="grid grid-cols-2 sm:flex sm:flex-wrap gap-2">
                <button wire:click="openEditProfileModal" class="rounded-xl bg-indigo-600 px-3 py-2 text-xs font-semibold text-white shadow-sm hover:bg-indigo-700 transition">Edit Profile</button>
                <button wire:click="openChangePasswordModal" class="rounded-xl border border-slate-300 bg-white px-3 py-2 text-xs font-semibold text-slate-700 hover:bg-slate-50 transition">Change Password</button>
                <button wire:click="openChangePinModal" class="rounded-xl border border-slate-300 bg-white px-3 py-2 text-xs font-semibold text-slate-700 hover:bg-slate-50 transition">Change PIN</button>
                <button wire:click="openTransactionSettingsModal" class="rounded-xl border border-slate-300 bg-white px-3 py-2 text-xs font-semibold text-slate-700 hover:bg-slate-50 transition">Transaction Settings</button>
            </div>
        </div>

        <div class="p-4 sm:p-6 grid grid-cols-1 md:grid-cols-2 gap-3 sm:gap-4">
            @php
                $fields = [
                    'Owner' => trim("{$profile->owner_first_name} {$profile->owner_middle_name} {$profile->owner_last_name} {$profile->owner_suffix}"),
                    'Store Name' => $profile->store_name,
                    'Contact' => $profile->contact_number,
                    'Address' => $profile->store_address,
                ];
                if ($profile->address_notes) {
                    $fields['Notes'] = $profile->address_notes;
                }
            @endphp

            @foreach($fields as $label => $value)
                <div class="rounded-xl bg-slate-50 border border-slate-200 p-3 sm:p-4">
                    <div class="text-xs font-semibold uppercase tracking-wider text-slate-500">{{ $label }}</div>
                    <div class="text-sm font-medium text-slate-900 mt-1 break-words">{{ $value ?: '—' }}</div>
                </div>
            @endforeach
        </div>
    </div>

    {{-- Edit Profile Modal --}}
    <x-modal wire:model="showEditProfileModal" maxWidth="2xl">
        <div class="bg-white overflow-hidden">
            <div class="px-5 sm:px-6 py-4 border-b border-slate-100 flex items-center justify-between sticky top-0 bg-white z-10">
                <h2 class="text-base font-semibold text-slate-900">Edit Store Profile</h2>
                <button type="button" class="text-slate-400 hover:text-slate-600 transition p-1" wire:click="$set('showEditProfileModal', false)">
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12" /></svg>
                </button>
            </div>
            <form wire:submit.prevent="updateProfile" class="p-5 sm:p-6 space-y-4">
                <div class="grid grid-cols-1 sm:grid-cols-2 gap-3 sm:gap-4">
                    <div>
                        <label class="block text-sm font-semibold text-slate-700 mb-1.5">First Name</label>
                        <input type="text" wire:model="editForm.owner_first_name"
                               oninput="this.value = this.value.replace(/[^a-zA-Z\s]/g, '')"
                               class="w-full rounded-xl border border-slate-300 px-3.5 py-2.5 text-sm focus:border-indigo-500 focus:ring-2 focus:ring-indigo-500/20 transition" />
                        @error('editForm.owner_first_name') <div class="text-sm text-rose-600 mt-1.5">{{ $message }}</div> @enderror
                    </div>
                    <div>
                        <label class="block text-sm font-semibold text-slate-700 mb-1.5">Middle Name</label>
                        <input type="text" wire:model="editForm.owner_middle_name"
                               oninput="this.value = this.value.replace(/[^a-zA-Z\s]/g, '')"
                               class="w-full rounded-xl border border-slate-300 px-3.5 py-2.5 text-sm focus:border-indigo-500 focus:ring-2 focus:ring-indigo-500/20 transition" />
                        @error('editForm.owner_middle_name') <div class="text-sm text-rose-600 mt-1.5">{{ $message }}</div> @enderror
                    </div>
                    <div>
                        <label class="block text-sm font-semibold text-slate-700 mb-1.5">Last Name</label>
                        <input type="text" wire:model="editForm.owner_last_name"
                               oninput="this.value = this.value.replace(/[^a-zA-Z\s]/g, '')"
                               class="w-full rounded-xl border border-slate-300 px-3.5 py-2.5 text-sm focus:border-indigo-500 focus:ring-2 focus:ring-indigo-500/20 transition" />
                        @error('editForm.owner_last_name') <div class="text-sm text-rose-600 mt-1.5">{{ $message }}</div> @enderror
                    </div>
                    <div>
                        <label class="block text-sm font-semibold text-slate-700 mb-1.5">Suffix</label>
                        <input type="text" wire:model="editForm.owner_suffix" class="w-full rounded-xl border border-slate-300 px-3.5 py-2.5 text-sm focus:border-indigo-500 focus:ring-2 focus:ring-indigo-500/20 transition" />
                    </div>
                </div>
                <div>
                    <label class="block text-sm font-semibold text-slate-700 mb-1.5">Store Name</label>
                    <input type="text" wire:model="editForm.store_name" class="w-full rounded-xl border border-slate-300 px-3.5 py-2.5 text-sm focus:border-indigo-500 focus:ring-2 focus:ring-indigo-500/20 transition" />
                    @error('editForm.store_name') <div class="text-sm text-rose-600 mt-1.5">{{ $message }}</div> @enderror
                </div>
                <div>
                    <label class="block text-sm font-semibold text-slate-700 mb-1.5">Contact Number</label>
                    <input type="tel"
                           inputmode="tel"
                           maxlength="13"
                           placeholder="+639171234567"
                           wire:model="editForm.contact_number"
                           oninput="this.value = this.value.replace(/[^0-9+]/g, '')"
                           class="w-full rounded-xl border border-slate-300 px-3.5 py-2.5 text-sm focus:border-indigo-500 focus:ring-2 focus:ring-indigo-500/20 transition" />
                    <p class="text-xs text-slate-500 mt-1">Format: +63 9XX XXX XXXX</p>
                    @error('editForm.contact_number') <div class="text-sm text-rose-600 mt-1.5">{{ $message }}</div> @enderror
                </div>
                <div>
                    <label class="block text-sm font-semibold text-slate-700 mb-1.5">Store Address</label>
                    <textarea wire:model="editForm.store_address" rows="2" class="w-full rounded-xl border border-slate-300 px-3.5 py-2.5 text-sm focus:border-indigo-500 focus:ring-2 focus:ring-indigo-500/20 transition"></textarea>
                    @error('editForm.store_address') <div class="text-sm text-rose-600 mt-1.5">{{ $message }}</div> @enderror
                </div>
                <div>
                    <label class="block text-sm font-semibold text-slate-700 mb-1.5">Address Notes</label>
                    <textarea wire:model="editForm.address_notes" rows="2" class="w-full rounded-xl border border-slate-300 px-3.5 py-2.5 text-sm focus:border-indigo-500 focus:ring-2 focus:ring-indigo-500/20 transition"></textarea>
                </div>
                <div class="flex flex-col-reverse sm:flex-row sm:justify-end gap-2 sm:gap-3 pt-4 border-t border-slate-100">
                    <button type="button" wire:click="$set('showEditProfileModal', false)" class="w-full sm:w-auto rounded-xl border border-slate-300 bg-white px-4 py-2.5 text-sm font-semibold text-slate-700 hover:bg-slate-50 transition">Cancel</button>
                    <button type="submit" class="w-full sm:w-auto rounded-xl bg-indigo-600 px-4 py-2.5 text-sm font-semibold text-white shadow-sm hover:bg-indigo-700 transition">Save Changes</button>
                </div>
            </form>
        </div>
    </x-modal>

    {{-- Change Password Modal --}}
    <x-modal wire:model="showChangePasswordModal" maxWidth="md">
        <div class="bg-white overflow-hidden">
            <div class="px-5 sm:px-6 py-4 border-b border-slate-100 flex items-center justify-between sticky top-0 bg-white z-10">
                <h2 class="text-base font-semibold text-slate-900">Change Password</h2>
                <button type="button" class="text-slate-400 hover:text-slate-600 transition p-1" wire:click="$set('showChangePasswordModal', false)">
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12" /></svg>
                </button>
            </div>
            <form wire:submit.prevent="updatePassword" class="p-5 sm:p-6 space-y-4">
                @if($password_error)
                    <div class="rounded-xl bg-rose-50 border border-rose-200 p-3 text-sm text-rose-700">{{ $password_error }}</div>
                @endif

                <div>
                    <label class="block text-sm font-semibold text-slate-700 mb-1.5">Current Password</label>
                    <div class="relative" x-data="{ show: false }">
                        <input :type="show ? 'text' : 'password'" wire:model="current_password"
                               class="w-full rounded-xl border border-slate-300 px-3.5 py-2.5 pr-10 text-sm focus:border-indigo-500 focus:ring-2 focus:ring-indigo-500/20 transition" />
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
                    @error('current_password') <div class="text-sm text-rose-600 mt-1.5">{{ $message }}</div> @enderror
                </div>

                <div>
                    <label class="block text-sm font-semibold text-slate-700 mb-1.5">New Password</label>
                    <div class="relative" x-data="{ show: false }">
                        <input :type="show ? 'text' : 'password'" wire:model="new_password"
                               class="w-full rounded-xl border border-slate-300 px-3.5 py-2.5 pr-10 text-sm focus:border-indigo-500 focus:ring-2 focus:ring-indigo-500/20 transition" />
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
                    @error('new_password') <div class="text-sm text-rose-600 mt-1.5">{{ $message }}</div> @enderror
                </div>

                <div>
                    <label class="block text-sm font-semibold text-slate-700 mb-1.5">Confirm New Password</label>
                    <div class="relative" x-data="{ show: false }">
                        <input :type="show ? 'text' : 'password'" wire:model="new_password_confirmation"
                               class="w-full rounded-xl border border-slate-300 px-3.5 py-2.5 pr-10 text-sm focus:border-indigo-500 focus:ring-2 focus:ring-indigo-500/20 transition" />
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

                <div class="flex flex-col-reverse sm:flex-row sm:justify-end gap-2 sm:gap-3 pt-4 border-t border-slate-100">
                    <button type="button" wire:click="$set('showChangePasswordModal', false)" class="w-full sm:w-auto rounded-xl border border-slate-300 bg-white px-4 py-2.5 text-sm font-semibold text-slate-700 hover:bg-slate-50 transition">Cancel</button>
                    <button type="submit" class="w-full sm:w-auto rounded-xl bg-indigo-600 px-4 py-2.5 text-sm font-semibold text-white shadow-sm hover:bg-indigo-700 transition">Update Password</button>
                </div>
            </form>
        </div>
    </x-modal>

    {{-- Change PIN Modal --}}
    <x-modal wire:model="showChangePinModal" maxWidth="md">
        <div class="bg-white overflow-hidden">
            <div class="px-5 sm:px-6 py-4 border-b border-slate-100 flex items-center justify-between sticky top-0 bg-white z-10">
                <h2 class="text-base font-semibold text-slate-900">Change PIN</h2>
                <button type="button" class="text-slate-400 hover:text-slate-600 transition p-1" wire:click="$set('showChangePinModal', false)">
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12" /></svg>
                </button>
            </div>
            <form wire:submit.prevent="updatePin" class="p-5 sm:p-6 space-y-4">
                @if($pin_error)
                    <div class="rounded-xl bg-rose-50 border border-rose-200 p-3 text-sm text-rose-700">{{ $pin_error }}</div>
                @endif

                <div>
                    <label class="block text-sm font-semibold text-slate-700 mb-1.5">Current PIN</label>
                    <div class="relative" x-data="{ show: false }">
                        <input :type="show ? 'text' : 'password'" inputmode="numeric" maxlength="4"
                               wire:model="current_pin"
                               oninput="this.value = this.value.replace(/[^0-9]/g, '')"
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
                    @error('current_pin') <div class="text-sm text-rose-600 mt-1.5">{{ $message }}</div> @enderror
                </div>

                <div>
                    <label class="block text-sm font-semibold text-slate-700 mb-1.5">New PIN</label>
                    <div class="relative" x-data="{ show: false }">
                        <input :type="show ? 'text' : 'password'" inputmode="numeric" maxlength="4"
                               wire:model="new_pin"
                               oninput="this.value = this.value.replace(/[^0-9]/g, '')"
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
                    @error('new_pin') <div class="text-sm text-rose-600 mt-1.5">{{ $message }}</div> @enderror
                </div>

                <div>
                    <label class="block text-sm font-semibold text-slate-700 mb-1.5">Confirm New PIN</label>
                    <div class="relative" x-data="{ show: false }">
                        <input :type="show ? 'text' : 'password'" inputmode="numeric" maxlength="4"
                               wire:model="new_pin_confirmation"
                               oninput="this.value = this.value.replace(/[^0-9]/g, '')"
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

                <div class="flex flex-col-reverse sm:flex-row sm:justify-end gap-2 sm:gap-3 pt-4 border-t border-slate-100">
                    <button type="button" wire:click="$set('showChangePinModal', false)" class="w-full sm:w-auto rounded-xl border border-slate-300 bg-white px-4 py-2.5 text-sm font-semibold text-slate-700 hover:bg-slate-50 transition">Cancel</button>
                    <button type="submit" class="w-full sm:w-auto rounded-xl bg-indigo-600 px-4 py-2.5 text-sm font-semibold text-white shadow-sm hover:bg-indigo-700 transition">Update PIN</button>
                </div>
            </form>
        </div>
    </x-modal>

    {{-- Transaction Settings Modal --}}
    <x-modal wire:model="showTransactionSettingsModal" maxWidth="md">
        <div class="bg-white overflow-hidden">
            <div class="px-5 sm:px-6 py-4 border-b border-slate-100 flex items-center justify-between sticky top-0 bg-white z-10">
                <h2 class="text-base font-semibold text-slate-900">Transaction Settings</h2>
                <button type="button" class="text-slate-400 hover:text-slate-600 transition p-1" wire:click="$set('showTransactionSettingsModal', false)">
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12" /></svg>
                </button>
            </div>
            <form wire:submit.prevent="saveTransactionSettings" class="p-5 sm:p-6 space-y-4">
                <div>
                    <label class="block text-sm font-semibold text-slate-700 mb-1.5">Surcharge (₱)</label>
                    <input type="number" inputmode="decimal" step="0.01" min="0"
                           oninput="this.value = this.value.replace(/[^0-9.]/g, '')"
                           wire:model="surcharge"
                           class="w-full rounded-xl border border-slate-300 px-3.5 py-2.5 text-sm focus:border-indigo-500 focus:ring-2 focus:ring-indigo-500/20 transition" />
                    @error('surcharge') <div class="text-sm text-rose-600 mt-1.5">{{ $message }}</div> @enderror
                </div>
                <div>
                    <label class="block text-sm font-semibold text-slate-700 mb-1.5">Interest Rate (% per week)</label>
                    <input type="number" inputmode="decimal" step="0.01" min="0" max="100"
                           oninput="this.value = this.value.replace(/[^0-9.]/g, '')"
                           wire:model="interest_rate"
                           class="w-full rounded-xl border border-slate-300 px-3.5 py-2.5 text-sm focus:border-indigo-500 focus:ring-2 focus:ring-indigo-500/20 transition" />
                    @error('interest_rate') <div class="text-sm text-rose-600 mt-1.5">{{ $message }}</div> @enderror
                </div>
                <div>
                    <label class="block text-sm font-semibold text-slate-700 mb-1.5">Transaction Duration (days)</label>
                    <input type="number" inputmode="numeric" step="1" min="1" max="365"
                           oninput="this.value = this.value.replace(/[^0-9]/g, '')"
                           wire:model="transaction_duration_days"
                           class="w-full rounded-xl border border-slate-300 px-3.5 py-2.5 text-sm focus:border-indigo-500 focus:ring-2 focus:ring-indigo-500/20 transition" />
                    @error('transaction_duration_days') <div class="text-sm text-rose-600 mt-1.5">{{ $message }}</div> @enderror
                </div>
                <div>
                    <label class="block text-sm font-semibold text-slate-700 mb-1.5">Max Credit Limit (₱)</label>
                    <input type="number" inputmode="decimal" step="0.01" min="0"
                           oninput="this.value = this.value.replace(/[^0-9.]/g, '')"
                           wire:model="max_credit_limit"
                           class="w-full rounded-xl border border-slate-300 px-3.5 py-2.5 text-sm focus:border-indigo-500 focus:ring-2 focus:ring-indigo-500/20 transition" />
                    @error('max_credit_limit') <div class="text-sm text-rose-600 mt-1.5">{{ $message }}</div> @enderror
                </div>
                <div>
                    <label class="block text-sm font-semibold text-slate-700 mb-1.5">Minimum Credit Limit (₱)</label>
                    <input type="number" inputmode="decimal" step="0.01" min="0"
                           oninput="this.value = this.value.replace(/[^0-9.]/g, '')"
                           wire:model="min_credit_limit"
                           class="w-full rounded-xl border border-slate-300 px-3.5 py-2.5 text-sm focus:border-indigo-500 focus:ring-2 focus:ring-indigo-500/20 transition" />
                    <p class="text-xs text-slate-500 mt-1">Customers cannot be assigned a credit limit below this value.</p>
                    @error('min_credit_limit') <div class="text-sm text-rose-600 mt-1.5">{{ $message }}</div> @enderror
                </div>
                <div class="flex flex-col-reverse sm:flex-row sm:justify-end gap-2 sm:gap-3 pt-4 border-t border-slate-100">
                    <button type="button" wire:click="$set('showTransactionSettingsModal', false)" class="w-full sm:w-auto rounded-xl border border-slate-300 bg-white px-4 py-2.5 text-sm font-semibold text-slate-700 hover:bg-slate-50 transition">Cancel</button>
                    <button type="submit" class="w-full sm:w-auto rounded-xl bg-indigo-600 px-4 py-2.5 text-sm font-semibold text-white shadow-sm hover:bg-indigo-700 transition">Save Settings</button>
                </div>
            </form>
        </div>
    </x-modal>
</div>