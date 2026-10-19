<div class="space-y-4 sm:space-y-6">
    @if(session()->has('message'))
        <div class="rounded-2xl border border-blue-200 bg-blue-50 px-4 py-3 text-sm text-blue-800 flex items-center gap-2">
            <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                <path stroke-linecap="round" stroke-linejoin="round" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
            </svg>
            {{ session('message') }}
        </div>
    @endif

    <div class="bg-white border border-slate-200 rounded-2xl shadow-sm overflow-hidden">
        {{-- Header --}}
        <div class="p-4 sm:p-5 border-b border-slate-100 flex flex-col gap-3">
            <div class="flex items-center justify-between gap-3">
                <div class="min-w-0">
                    <h1 class="text-lg font-bold text-slate-900">Customers</h1>
                    <p class="text-xs text-slate-500 mt-0.5">{{ $customers->total() }} total</p>
                </div>
                <button type="button" wire:click="createCustomer"
                        class="inline-flex items-center justify-center gap-1.5 rounded-xl bg-indigo-600 px-3.5 py-2.5 text-sm font-semibold text-white shadow-sm hover:bg-indigo-700 active:scale-[0.98] transition-all flex-shrink-0">
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M12 4v16m8-8H4" />
                    </svg>
                    Add
                </button>
            </div>
            <div class="flex flex-col sm:flex-row gap-2 sm:gap-3">
                <div class="relative flex-1">
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4 absolute left-3 top-1/2 -translate-y-1/2 text-slate-400" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z" />
                    </svg>
                    <input type="text" wire:model.live="search" placeholder="Search by name or contact"
                        autocomplete="new-password"
                        class="w-full rounded-xl border border-slate-300 bg-white pl-9 pr-3 py-3 text-base sm:text-sm focus:border-indigo-500 focus:ring-2 focus:ring-indigo-500/20 transition" />
                </div>
                <select wire:model.live="creditLimitSort" class="w-full sm:w-auto rounded-xl border border-slate-300 px-3.5 py-3 text-base sm:text-sm focus:border-indigo-500 focus:ring-2 focus:ring-indigo-500/20 transition">
                    <option value="">Credit Limit (Default)</option>
                    <option value="high_low">Highest to Lowest</option>
                    <option value="low_high">Lowest to Highest</option>
                </select>
            </div>
        </div>

        {{-- Mobile: Card list --}}
        <div class="md:hidden divide-y divide-slate-100">
            @forelse($customers as $c)
                <div class="p-4 space-y-3 active:bg-slate-50/60 transition-colors">
                    <div class="flex items-start gap-3">
                        <div class="w-11 h-11 rounded-full bg-gradient-to-br from-indigo-500 to-blue-600 flex items-center justify-center flex-shrink-0">
                            <span class="text-sm font-bold text-white">{{ strtoupper(substr($c->name, 0, 1)) }}</span>
                        </div>
                        <div class="min-w-0 flex-1">
                            <div class="font-semibold text-slate-900 truncate">{{ $c->name }}</div>
                            <div class="text-xs text-slate-500 mt-0.5">{{ $c->contact_number ?: 'No contact' }}</div>
                        </div>
                        <span class="inline-flex items-center px-2.5 py-1 rounded-full text-xs font-semibold bg-slate-100 text-slate-700 flex-shrink-0">
                            ₱{{ number_format($c->credit_limit ?? 0, 2) }}
                        </span>
                    </div>
                    <div class="grid grid-cols-3 gap-2">
                        <button type="button" wire:click="viewDetails({{ $c->id }})"
                                class="rounded-xl border border-slate-300 bg-white px-3 py-2.5 text-xs font-semibold text-slate-700 active:bg-slate-100 transition">
                            Details
                        </button>
                        <button type="button" wire:click="editCustomer({{ $c->id }})"
                                class="rounded-xl bg-indigo-600 px-3 py-2.5 text-xs font-semibold text-white active:bg-indigo-700 transition">
                            Edit
                        </button>
                        <button type="button" wire:click="confirmDeleteCustomer({{ $c->id }})"
                                class="rounded-xl border border-rose-200 bg-white px-3 py-2.5 text-xs font-semibold text-rose-700 active:bg-rose-50 transition">
                            Delete
                        </button>
                    </div>
                </div>
            @empty
                <div class="py-12 text-center">
                    <div class="flex flex-col items-center gap-2">
                        <div class="w-12 h-12 rounded-full bg-slate-100 flex items-center justify-center">
                            <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6 text-slate-400" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0z" />
                            </svg>
                        </div>
                        <p class="text-sm font-medium text-slate-600">No customers yet</p>
                        <p class="text-xs text-slate-500">Add your first customer to get started</p>
                    </div>
                </div>
            @endforelse
        </div>

        {{-- Desktop: Table --}}
        <div class="hidden md:block overflow-x-auto">
            <table class="w-full text-sm">
                <thead class="bg-slate-50/80 border-b border-slate-100">
                    <tr class="text-left">
                        <th class="py-3 px-5 text-xs font-semibold uppercase tracking-wider text-slate-500">Name</th>
                        <th class="py-3 px-5 text-xs font-semibold uppercase tracking-wider text-slate-500">Contact</th>
                        <th class="py-3 px-5 text-xs font-semibold uppercase tracking-wider text-slate-500">Credit Limit</th>
                        <th class="py-3 px-5 text-xs font-semibold uppercase tracking-wider text-slate-500 text-right">Actions</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100">
                    @forelse($customers as $c)
                        <tr class="hover:bg-slate-50/70 transition-colors">
                            <td class="py-3 px-5">
                                <div class="flex items-center gap-3">
                                    <div class="w-9 h-9 rounded-full bg-gradient-to-br from-indigo-500 to-blue-600 flex items-center justify-center flex-shrink-0">
                                        <span class="text-xs font-bold text-white">{{ strtoupper(substr($c->name, 0, 1)) }}</span>
                                    </div>
                                    <span class="font-medium text-slate-900">{{ $c->name }}</span>
                                </div>
                            </td>
                            <td class="py-3 px-5 text-slate-600">{{ $c->contact_number ?: '—' }}</td>
                            <td class="py-3 px-5">
                                <span class="inline-flex items-center px-2.5 py-1 rounded-full text-xs font-semibold bg-slate-100 text-slate-700">
                                    ₱{{ number_format($c->credit_limit ?? 0, 2) }}
                                </span>
                            </td>
                            <td class="py-3 px-5">
                                <div class="flex gap-2 justify-end">
                                    <button type="button" wire:click="viewDetails({{ $c->id }})" class="rounded-lg border border-slate-300 bg-white px-3 py-1.5 text-xs font-semibold text-slate-700 hover:bg-slate-50 transition">Details</button>
                                    <button type="button" wire:click="editCustomer({{ $c->id }})" class="rounded-lg bg-indigo-600 px-3 py-1.5 text-xs font-semibold text-white hover:bg-indigo-700 transition">Edit</button>
                                    <button type="button" wire:click="confirmDeleteCustomer({{ $c->id }})" class="rounded-lg border border-rose-200 bg-white px-3 py-1.5 text-xs font-semibold text-rose-700 hover:bg-rose-50 transition">Delete</button>
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr><td colspan="4" class="py-12 text-center text-sm text-slate-500">No customers yet.</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        @if($customers->hasPages())
            <div class="px-4 sm:px-5 py-4 border-t border-slate-100 flex justify-center">{{ $customers->links() }}</div>
        @endif
    </div>

    {{-- Create/Edit Modal --}}
    <x-modal wire:model="showCreateModal" maxWidth="2xl">
        <div class="bg-white overflow-hidden">
            <div class="px-5 sm:px-6 py-4 border-b border-slate-100 flex items-center justify-between sticky top-0 bg-white z-10">
                <h2 class="text-base font-semibold text-slate-900">{{ $editingCustomer ? 'Edit Customer' : 'Add New Customer' }}</h2>
                <button type="button" class="text-slate-400 hover:text-slate-600 transition p-1" wire:click="closeModal" aria-label="Close">
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12" /></svg>
                </button>
            </div>
            <form wire:submit.prevent="{{ $editingCustomer ? 'updateCustomer' : 'saveCustomer' }}" class="p-5 sm:p-6 space-y-4">
                <div>
                    <label class="block text-sm font-semibold text-slate-700 mb-1.5">Name</label>
                    <input type="text"
                           oninput="this.value = this.value.replace(/[^a-zA-Z\s]/g, '')"
                           class="w-full rounded-xl border border-slate-300 px-3.5 py-3 text-base sm:text-sm focus:border-indigo-500 focus:ring-2 focus:ring-indigo-500/20 transition" wire:model="form.name" />
                    @error('form.name') <div class="text-sm text-rose-600 mt-1.5">{{ $message }}</div> @enderror
                </div>
                <div>
                    <label class="block text-sm font-semibold text-slate-700 mb-1.5">Contact Number</label>
                    <input type="tel"
                           inputmode="tel"
                           maxlength="13"
                           placeholder="+639171234567"
                           oninput="this.value = this.value.replace(/[^0-9+]/g, '')"
                           class="w-full rounded-xl border border-slate-300 px-3.5 py-3 text-base sm:text-sm focus:border-indigo-500 focus:ring-2 focus:ring-indigo-500/20 transition" wire:model="form.contact_number" />
                    <p class="text-xs text-slate-500 mt-1">Format: +63 9XX XXX XXXX</p>
                    @error('form.contact_number') <div class="text-sm text-rose-600 mt-1.5">{{ $message }}</div> @enderror
                </div>
                <div>
                    <label class="block text-sm font-semibold text-slate-700 mb-1.5">Address</label>
                    <textarea class="w-full rounded-xl border border-slate-300 px-3.5 py-3 text-base sm:text-sm focus:border-indigo-500 focus:ring-2 focus:ring-indigo-500/20 transition" rows="3" wire:model="form.address"></textarea>
                </div>
                <div>
                    <label class="block text-sm font-semibold text-slate-700 mb-1.5">Credit Limit (₱)</label>
                    <input type="number" step="0.01" inputmode="decimal" min="0"
                           oninput="this.value = this.value.replace(/[^0-9.]/g, '')"
                           class="w-full rounded-xl border border-slate-300 px-3.5 py-3 text-base sm:text-sm focus:border-indigo-500 focus:ring-2 focus:ring-indigo-500/20 transition" wire:model="form.credit_limit" />
                    @error('form.credit_limit') <div class="text-sm text-rose-600 mt-1.5">{{ $message }}</div> @enderror
                </div>

                {{-- Attach Signed Terms & Conditions --}}
                    <div>
                        <label class="block text-sm font-semibold text-slate-700 mb-1.5">
                            Attach Signed Terms & Conditions (Image) <span class="text-rose-600">*</span>
                        </label>
                        <input type="file"
                            wire:model="termsAttachment"
                            accept="image/*"
                            class="w-full rounded-xl border border-slate-300 px-3.5 py-2.5 text-sm focus:border-indigo-500 focus:ring-2 focus:ring-indigo-500/20 transition file:mr-3 file:rounded-lg file:border-0 file:bg-indigo-600 file:px-3 file:py-1.5 file:text-xs file:font-semibold file:text-white hover:file:bg-indigo-700" />
                        @error('termsAttachment') <div class="text-sm text-rose-600 mt-1.5">{{ $message }}</div> @enderror

                        <div wire:loading wire:target="termsAttachment" class="text-xs text-slate-500 mt-2">Uploading…</div>

                        @if ($termsAttachment)
                            <img src="{{ $termsAttachment->temporaryUrl() }}"
                                class="mt-3 max-h-48 rounded-xl border border-slate-200 object-contain bg-slate-50 p-2" />
                        @endif

                        @if($editingCustomer && $editingCustomer->terms_attachment_path && !$termsAttachment)
                            <a href="{{ asset('storage/' . $editingCustomer->terms_attachment_path) }}"
                            target="_blank"
                            class="inline-block mt-2 text-sm font-semibold text-indigo-600 hover:underline">
                                View current attached file
                            </a>
                        @endif
                    </div>

                    @if(!$editingCustomer || ($editingCustomer && is_null($editingCustomer->accepted_terms_at)))
                        <div class="rounded-xl bg-slate-50 border border-slate-200 p-4">
                            <div class="flex items-start gap-3">
                                <input type="checkbox" wire:model="agreeTerms" id="agreeTerms"
                                    class="mt-0.5 rounded border-slate-300 text-indigo-600 focus:ring-indigo-500 w-5 h-5" />
                                <label for="agreeTerms" class="text-sm text-slate-700">
                                    I have read and agree to the
                                    <button type="button" x-data @click="$dispatch('open-terms-modal')" class="text-indigo-600 hover:underline font-semibold">Terms and Conditions</button>.
                                    <span class="text-rose-600">*</span>
                                </label>
                            </div>
                            @error('agreeTerms') <div class="text-sm text-rose-600 mt-1.5">{{ $message }}</div> @enderror
                        </div>
                    @elseif($editingCustomer && !is_null($editingCustomer->accepted_terms_at))
                        <div class="rounded-xl bg-emerald-50 border border-emerald-200 p-3 text-sm text-emerald-700 flex items-center gap-2">
                            <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M5 13l4 4L19 7" /></svg>
                            Terms accepted on {{ $editingCustomer->accepted_terms_at->format('M d, Y') }}
                        </div>
                    @endif

                <div class="flex flex-col-reverse sm:flex-row sm:justify-end gap-2 sm:gap-3 pt-4 border-t border-slate-100">
                    <button type="button" wire:click="closeModal" class="w-full sm:w-auto rounded-xl border border-slate-300 bg-white px-4 py-3 text-sm font-semibold text-slate-700 active:bg-slate-100 transition">Cancel</button>
                    <button type="submit" class="w-full sm:w-auto rounded-xl bg-indigo-600 px-4 py-3 text-sm font-semibold text-white shadow-sm hover:bg-indigo-700 active:scale-[0.98] transition">Save Customer</button>
                </div>
            </form>
        </div>
    </x-modal>

    {{-- Delete Modal --}}
    <x-modal wire:model="showDeleteModal" maxWidth="md">
        <div class="p-5 sm:p-6">
            <div class="flex items-start gap-4">
                <div class="flex-shrink-0 w-11 h-11 rounded-full bg-rose-100 flex items-center justify-center">
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 text-rose-600" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z" /></svg>
                </div>
                <div class="flex-1">
                    <h2 class="text-base font-semibold text-slate-900">Delete Customer?</h2>
                    <p class="text-sm text-slate-500 mt-1">This action cannot be undone. All related transactions will be detached.</p>
                </div>
            </div>
            @if($deletePinError)
                <div class="mt-4 rounded-xl bg-rose-50 border border-rose-200 p-3 text-sm text-rose-700">{{ $deletePinError }}</div>
            @endif
            <div class="mt-4">
                <label class="block text-sm font-semibold text-slate-700 mb-1.5">Admin PIN (4 digits)</label>
                <input type="password" inputmode="numeric" maxlength="4" wire:model.defer="deletePin"
                       class="w-full rounded-xl border border-slate-300 px-3.5 py-3 text-base sm:text-sm focus:border-rose-500 focus:ring-2 focus:ring-rose-500/20 transition tracking-widest"
                       placeholder="••••" />
            </div>
            <div class="flex flex-col-reverse sm:flex-row sm:justify-end gap-2 sm:gap-3 mt-6">
                <button type="button" wire:click="cancelDelete" class="w-full sm:w-auto rounded-xl border border-slate-300 bg-white px-4 py-3 text-sm font-semibold text-slate-700 active:bg-slate-100 transition">Cancel</button>
                <button type="button" wire:click="deleteCustomer" class="w-full sm:w-auto rounded-xl bg-rose-600 px-4 py-3 text-sm font-semibold text-white shadow-sm hover:bg-rose-700 active:scale-[0.98] transition">Delete Customer</button>
            </div>
        </div>
    </x-modal>

    {{-- Details Modal --}}
    <x-modal wire:model="showDetailsModal" maxWidth="2xl">
        <div class="bg-white overflow-hidden">
            <div class="px-5 sm:px-6 py-4 border-b border-slate-100 flex items-center justify-between sticky top-0 bg-white z-10">
                <h2 class="text-base font-semibold text-slate-900">Customer Details</h2>
                <button type="button" class="text-slate-400 hover:text-slate-600 transition p-1" wire:click="closeDetailsModal" aria-label="Close">
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12" /></svg>
                </button>
            </div>
            @if($customerDetails)
                <div class="p-5 sm:p-6">
                    <div class="flex items-center gap-4 pb-5 border-b border-slate-100">
                        <div class="w-14 h-14 rounded-2xl bg-gradient-to-br from-indigo-500 to-blue-600 flex items-center justify-center shadow-lg shadow-indigo-500/20 flex-shrink-0">
                            <span class="text-lg font-bold text-white">{{ strtoupper(substr($customerDetails->name, 0, 1)) }}</span>
                        </div>
                        <div class="min-w-0">
                            <div class="font-semibold text-slate-900 text-lg truncate">{{ $customerDetails->name }}</div>
                            <div class="text-sm text-slate-500 truncate">{{ $customerDetails->contact_number ?: 'No contact number' }}</div>
                        </div>
                    </div>
                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-3 mt-5">
                        <div class="rounded-xl bg-slate-50 border border-slate-200 p-3">
                            <div class="text-xs font-semibold uppercase tracking-wider text-slate-500">Credit Limit</div>
                            <div class="text-lg font-bold text-slate-900 mt-1">₱{{ number_format($customerDetails->credit_limit ?? 0, 2) }}</div>
                        </div>
                        <div class="rounded-xl bg-slate-50 border border-slate-200 p-3">
                            <div class="text-xs font-semibold uppercase tracking-wider text-slate-500">Total Balance</div>
                            <div class="text-lg font-bold text-slate-900 mt-1">₱{{ number_format($customerDetails->total_balance ?? 0, 2) }}</div>
                        </div>
                        <div class="rounded-xl bg-slate-50 border border-slate-200 p-3">
                            <div class="text-xs font-semibold uppercase tracking-wider text-slate-500">Risk Level</div>
                            <div class="mt-1.5">
                                <span class="inline-flex items-center px-2.5 py-1 rounded-full text-xs font-semibold
                                    @if($customerDetails->risk_level === 'HIGH') bg-rose-100 text-rose-700
                                    @elseif($customerDetails->risk_level === 'MEDIUM') bg-amber-100 text-amber-700
                                    @else bg-emerald-100 text-emerald-700 @endif">
                                    {{ $customerDetails->risk_level ?? 'LOW' }}
                                </span>
                            </div>
                        </div>
                        <div class="rounded-xl bg-slate-50 border border-slate-200 p-3">
                            <div class="text-xs font-semibold uppercase tracking-wider text-slate-500">Days Since Last Payment</div>
                            <div class="text-lg font-bold text-slate-900 mt-1">{{ $customerDetails->days_since_last_payment ?? 0 }}</div>
                        </div>
                        <div class="rounded-xl bg-slate-50 border border-slate-200 p-3 sm:col-span-2">
                            <div class="text-xs font-semibold uppercase tracking-wider text-slate-500">Address</div>
                            <div class="text-sm text-slate-700 mt-1">{{ $customerDetails->address ?: '—' }}</div>
                        </div>
                        <div class="rounded-xl bg-slate-50 border border-slate-200 p-3 sm:col-span-2">
                            <div class="text-xs font-semibold uppercase tracking-wider text-slate-500">Customer Since</div>
                            <div class="text-sm text-slate-700 mt-1">{{ optional($customerDetails->created_at)->format('F d, Y') ?? '—' }}</div>
                        </div>

                        {{-- Attached Signed Terms & Conditions --}}
                        <div class="rounded-xl bg-slate-50 border border-slate-200 p-3 sm:col-span-2">
                            <div class="flex items-center justify-between gap-2 mb-2">
                                <div class="text-xs font-semibold uppercase tracking-wider text-slate-500">
                                    Attached Signed Terms &amp; Conditions
                                </div>

                                @if(!$showUpdateTermsUpload)
                                    <button type="button"
                                            wire:click="openUpdateTermsUpload"
                                            class="inline-flex items-center gap-1.5 rounded-lg bg-indigo-600 px-2.5 py-1.5 text-xs font-semibold text-white shadow-sm hover:bg-indigo-700 active:scale-[0.98] transition">
                                        <svg xmlns="http://www.w3.org/2000/svg" class="h-3.5 w-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                            <path stroke-linecap="round" stroke-linejoin="round" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-8l-4-4m0 0L8 8m4-4v12" />
                                        </svg>
                                        {{ $customerDetails->terms_attachment_path ? 'Update' : 'Attach' }}
                                    </button>
                                @endif
                            </div>

                            @if($showUpdateTermsUpload)
                                {{-- Inline update form --}}
                                <div class="rounded-lg border border-indigo-200 bg-indigo-50/50 p-3 space-y-3">
                                    <input type="file"
                                        wire:key="terms-upload-{{ $uploadKey }}"
                                        wire:model="updateTermsAttachment"
                                        accept="image/*"
                                        class="w-full rounded-xl border border-slate-300 bg-white px-3 py-2 text-sm focus:border-indigo-500 focus:ring-2 focus:ring-indigo-500/20 transition file:mr-3 file:rounded-lg file:border-0 file:bg-indigo-600 file:px-3 file:py-1.5 file:text-xs file:font-semibold file:text-white hover:file:bg-indigo-700" />
                                    @error('updateTermsAttachment') <div class="text-sm text-rose-600">{{ $message }}</div> @enderror

                                    <div wire:loading wire:target="updateTermsAttachment" class="text-xs text-slate-500">Uploading…</div>

                                    @if($updateTermsAttachment)
                                        <img src="{{ $updateTermsAttachment->temporaryUrl() }}"
                                            class="max-h-48 rounded-xl border border-slate-200 object-contain bg-white p-2" />
                                    @endif

                                    <div class="flex flex-col-reverse sm:flex-row sm:justify-end gap-2">
                                        <button type="button"
                                                wire:click="cancelUpdateTermsUpload"
                                                class="w-full sm:w-auto rounded-lg border border-slate-300 bg-white px-3 py-2 text-xs font-semibold text-slate-700 active:bg-slate-100 transition">
                                            Cancel
                                        </button>
                                        <button type="button"
                                                wire:click="saveUpdatedTermsAttachment"
                                                wire:loading.attr="disabled"
                                                wire:target="saveUpdatedTermsAttachment, updateTermsAttachment"
                                                class="w-full sm:w-auto rounded-lg bg-indigo-600 px-3 py-2 text-xs font-semibold text-white shadow-sm hover:bg-indigo-700 active:scale-[0.98] transition disabled:opacity-60">
                                            Save Attachment
                                        </button>
                                    </div>
                                </div>
                            @else
                                {{-- Display current attachment (or placeholder) --}}
                                @if($customerDetails->terms_attachment_path)
                                    <a href="{{ asset('storage/' . $customerDetails->terms_attachment_path) }}"
                                       target="_blank"
                                       class="block mt-2 group">
                                        <img src="{{ asset('storage/' . $customerDetails->terms_attachment_path) }}"
                                             alt="Signed Terms & Conditions"
                                             class="max-h-96 w-full rounded-xl border border-slate-200 object-contain bg-white group-hover:opacity-90 transition" />
                                    </a>
                                    <div class="mt-2 text-center">
                                        <a href="{{ asset('storage/' . $customerDetails->terms_attachment_path) }}"
                                           target="_blank"
                                           class="text-xs font-semibold text-indigo-600 hover:underline">
                                            Open in new tab ↗
                                        </a>
                                    </div>
                                @else
                                    <div class="text-sm text-slate-500 mt-1 italic">No file attached.</div>
                                @endif
                            @endif
                        </div>
                    </div>
                </div>
            @else
                <div class="p-12 text-center text-sm text-slate-500">No customer data available.</div>
            @endif
        </div>
    </x-modal>

    {{-- Terms & Conditions Modal --}}
    <div x-data="{ termsModalOpen: false }"
     x-on:open-terms-modal.window="termsModalOpen = true"
     x-cloak
     class="fixed inset-0 z-50 flex items-end sm:items-center justify-center sm:p-4"
     style="display: none;"
     x-show="termsModalOpen"
     x-transition:enter="ease-out duration-200" x-transition:enter-start="opacity-0" x-transition:enter-end="opacity-100"
     x-transition:leave="ease-in duration-150" x-transition:leave-start="opacity-100" x-transition:leave-end="opacity-0">
        <div class="fixed inset-0 bg-slate-900/50 backdrop-blur-sm" @click="termsModalOpen = false"></div>
        <div class="relative w-full max-w-2xl bg-white rounded-t-3xl sm:rounded-2xl shadow-2xl border border-slate-200 overflow-hidden max-h-[92dvh] sm:max-h-[85vh] flex flex-col"
             style="padding-bottom: env(safe-area-inset-bottom);"
             x-transition:enter="ease-out duration-300" x-transition:enter-start="opacity-0 translate-y-full sm:translate-y-0 sm:scale-95" x-transition:enter-end="opacity-100 translate-y-0 sm:scale-100">
            <div class="w-12 h-1.5 bg-slate-200 rounded-full mx-auto mt-3 sm:hidden"></div>
            <div class="px-5 sm:px-6 py-4 border-b border-slate-100 flex items-center justify-between">
                <h2 class="text-base font-semibold text-slate-900">Terms and Conditions</h2>
                <button type="button" @click="termsModalOpen = false" class="text-slate-400 hover:text-slate-600 transition p-1" aria-label="Close">
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12" /></svg>
                </button>
            </div>
            <div class="p-5 sm:p-6 overflow-y-auto overscroll-contain prose prose-sm text-slate-700 max-w-none">
                <p>This document serves as the official Terms and Conditions ("Agreement") governing the extension of credit and all related transactions between the Store (hereafter "Creditor") and the Customer (hereafter "Debtor"). By accepting these terms during customer registration, the Debtor agrees to be bound by all provisions herein.</p>
                <br>
                <ol class="list-decimal pl-5 space-y-3">
                    <li><strong>Credit Account Establishment</strong>
                        <ul class="list-disc pl-5 mt-1 space-y-1">
                            <li>The Creditor may extend a credit line to the Debtor, subject to the Debtor's creditworthiness and the Creditor's sole discretion.</li>
                            <li>The initial credit limit is set at the time of account creation and may be adjusted periodically based on payment history, outstanding balance, and risk assessment.</li>
                            <li>The Debtor shall provide accurate and complete personal and contact information. Any change in this information must be communicated to the Creditor immediately.</li>
                        </ul>
                    </li>
                    <li><strong>Purchases and Charges</strong>
                        <ul class="list-disc pl-5 mt-1 space-y-1">
                            <li>All purchases made on credit are documented as Transactions in the Creditor's system.</li>
                            <li>Each Transaction includes: Principal Amount, Surcharge, and Interest.</li>
                            <li>The Due Date is calculated as the Transaction Date plus the Transaction Duration Days.</li>
                            <li>Interest accrues weekly (every 7 days) at the prevailing Interest Rate.</li>
                        </ul>
                    </li>
                    <li><strong>Payment Obligations</strong>
                        <ul class="list-disc pl-5 mt-1 space-y-1">
                            <li>The Debtor is responsible for paying the full outstanding amount by the due date.</li>
                            <li>Payments can be made in cash or through any other method accepted by the Creditor.</li>
                            <li>Partial payments are accepted and will be applied to the oldest outstanding balance first.</li>
                            <li>If the Debtor fails to pay by the due date, the Transaction becomes OVERDUE and interest will begin accruing.</li>
                        </ul>
                    </li>
                    <li><strong>Late Payments and Default</strong>
                        <ul class="list-disc pl-5 mt-1 space-y-1">
                            <li>The Creditor may impose additional late fees or collection charges on overdue accounts.</li>
                            <li>Persistent delinquency may result in suspension, reporting, or legal action.</li>
                        </ul>
                    </li>
                    <li><strong>Credit Limit and Risk Assessment</strong>
                        <ul class="list-disc pl-5 mt-1 space-y-1">
                            <li>The Debtor's total outstanding balance shall not exceed the assigned credit limit.</li>
                            <li>The Creditor uses a proprietary risk-scoring model that considers payment history, overdue count, and credit utilisation.</li>
                        </ul>
                    </li>
                    <li><strong>Data Privacy</strong>
                        <ul class="list-disc pl-5 mt-1 space-y-1">
                            <li>The Creditor collects and processes personal data solely for the purpose of administering the credit account.</li>
                            <li>The Debtor consents to the storage and use of this data for credit evaluation, collection, and internal analytics.</li>
                        </ul>
                    </li>
                    <li><strong>Governing Law and Dispute Resolution</strong>
                        <ul class="list-disc pl-5 mt-1 space-y-1">
                            <li>This Agreement shall be governed by the laws of the Philippines.</li>
                            <li>Any dispute shall first be attempted to be resolved through amicable negotiation.</li>
                        </ul>
                    </li>
                    <li><strong>Amendments</strong>
                        <ul class="list-disc pl-5 mt-1 space-y-1">
                            <li>The Creditor reserves the right to modify these Terms and Conditions at any time.</li>
                        </ul>
                    </li>
                    <li><strong>Acknowledgment and Acceptance</strong>
                        <ul class="list-disc pl-5 mt-1 space-y-1">
                            <li>By checking the box, the Debtor acknowledges they have read and understood the entire Agreement.</li>
                        </ul>
                    </li>
                </ol>
                <p class="mt-3 font-semibold text-slate-800">Note: Acceptance is a mandatory requirement for opening a credit account. No transaction may be initiated without prior acceptance.</p>
            </div>
            <div class="px-5 sm:px-6 py-4 border-t border-slate-100 flex flex-col-reverse sm:flex-row sm:justify-end gap-2 sm:gap-3">
                <button type="button" @click="termsModalOpen = false"
                        class="w-full sm:w-auto rounded-xl border border-slate-300 bg-white px-4 py-3 text-sm font-semibold text-slate-700 active:bg-slate-100 transition">
                    Close
                </button>
                <a href="{{ route('terms.pdf') }}" target="_blank"
                    class="w-full sm:w-auto inline-flex items-center justify-center gap-2 rounded-xl bg-indigo-600 px-4 py-3 text-sm font-semibold text-white shadow-sm hover:bg-indigo-700 active:scale-[0.98] transition">
                        <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M17 17h2a2 2 0 002-2v-4a2 2 0 00-2-2H5a2 2 0 00-2 2v4a2 2 0 002 2h2m2 4h6a2 2 0 002-2v-4H7v4a2 2 0 002 2zm8-12V5a2 2 0 00-2-2H9a2 2 0 00-2 2v4h10z" />
                        </svg>
                        Print Terms and Conditions
                    </a>
            </div>
        </div>
    </div>
</div>