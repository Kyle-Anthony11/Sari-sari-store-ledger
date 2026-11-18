<div class="space-y-4 sm:space-y-6">
    @if(session()->has('message'))
        <div class="rounded-2xl border border-emerald-200 bg-emerald-50 px-4 py-3 text-sm text-emerald-800">
            {{ session('message') }}
        </div>
    @endif

    {{-- Filters Card --}}
    <div class="bg-white border border-slate-200 rounded-2xl shadow-sm p-4 sm:p-5">
        <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-3 sm:gap-4">
            <div>
                <label class="block text-sm font-semibold text-slate-700 mb-1.5">Customer</label>
                <input type="text" class="w-full rounded-xl border border-slate-300 px-3.5 py-3 text-base sm:text-sm focus:border-indigo-500 focus:ring-2 focus:ring-indigo-500/20 transition" placeholder="Search customer" wire:model.live="searchCustomerName" autocomplete="new-password" />
            </div>
            <div>
                <label class="block text-sm font-semibold text-slate-700 mb-1.5">Item</label>
                <input type="text" class="w-full rounded-xl border border-slate-300 px-3.5 py-3 text-base sm:text-sm focus:border-indigo-500 focus:ring-2 focus:ring-indigo-500/20 transition" placeholder="Search item" wire:model.live="searchItemName" autocomplete="new-password" />
            </div>
            <div>
                <label class="block text-sm font-semibold text-slate-700 mb-1.5">Date</label>
                <input type="date" class="w-full rounded-xl border border-slate-300 px-3.5 py-3 text-base sm:text-sm focus:border-indigo-500 focus:ring-2 focus:ring-indigo-500/20 transition" wire:model.live="searchDate" />
            </div>
            <div>
                <label class="block text-sm font-semibold text-slate-700 mb-1.5">Status</label>
                <select wire:model.live="statusFilter" class="w-full rounded-xl border border-slate-300 px-3.5 py-3 text-base sm:text-sm focus:border-indigo-500 focus:ring-2 focus:ring-indigo-500/20 transition">
                    <option value="All">All</option>
                    <option value="PENDING">Pending</option>
                    <option value="PAID">Paid</option>
                    <option value="OVERDUE">Overdue</option>
                </select>
            </div>
        </div>

        <div class="mt-4 sm:mt-5 flex justify-stretch sm:justify-end">
            <button type="button" wire:click="createTransaction"
                    class="w-full sm:w-auto inline-flex items-center justify-center gap-2 rounded-xl bg-indigo-600 px-4 py-3 text-sm font-semibold text-white shadow-sm hover:bg-indigo-700 active:scale-[0.98] transition-all">
                <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M12 4v16m8-8H4" /></svg>
                New Transaction
            </button>
        </div>
    </div>

    {{-- Transaction Wizard Modals --}}
    <div>
        {{-- 1) Customer Type Modal --}}
        <x-modal id="transactions-customer-type-modal" wire:model="showCustomerTypeModal" maxWidth="2xl" :closeOnBackdropClick="false">
            <div class="bg-white overflow-hidden">
                <div class="px-5 sm:px-6 py-4 border-b border-slate-100 flex items-center justify-between">
                    <h3 class="text-base font-semibold text-slate-900">Transaction Setup</h3>
                    <button type="button" wire:click="cancelWizard" class="text-slate-400 hover:text-slate-600 transition p-1" aria-label="Close">
                        <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12" /></svg>
                    </button>
                </div>
                <div class="p-5 sm:p-6">
                    <p class="text-sm text-slate-600">
                        Will this transaction use an <span class="font-semibold text-slate-900">Existing Customer</span> or a <span class="font-semibold text-slate-900">New Customer</span>?
                    </p>
                </div>
                <div class="px-5 sm:px-6 py-4 bg-slate-50 border-t border-slate-100 flex flex-col sm:flex-row sm:justify-end gap-2 sm:gap-3">
                    <button type="button" wire:click="chooseExistingCustomer" class="w-full sm:w-auto rounded-xl border border-slate-300 bg-white px-4 py-3 text-sm font-semibold text-slate-700 active:bg-slate-100 transition">Existing Customer</button>
                    <button type="button" wire:click="chooseNewCustomer" class="w-full sm:w-auto rounded-xl bg-indigo-600 px-4 py-3 text-sm font-semibold text-white shadow-sm hover:bg-indigo-700 active:scale-[0.98] transition">New Customer</button>
                </div>
            </div>
        </x-modal>

        {{-- 2a) New Customer Form Modal --}}
        @if($customerType === 'new')
        <x-modal id="transactions-new-customer-modal" wire:model="showNewCustomerModal" maxWidth="2xl" :closeOnBackdropClick="false">
            <div class="bg-white overflow-hidden">
                <div class="px-5 sm:px-6 py-4 border-b border-slate-100 flex items-center justify-between sticky top-0 bg-white z-10">
                    <h3 class="text-base font-semibold text-slate-900">Add New Customer</h3>
                    <button type="button" wire:click="cancelWizard" class="text-slate-400 hover:text-slate-600 transition p-1" aria-label="Close">
                        <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12" /></svg>
                    </button>
                </div>
                <div class="p-5 sm:p-6">
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-3 sm:gap-4">
                        <div class="md:col-span-2">
                            <label class="block text-sm font-semibold text-slate-700 mb-1.5">Customer Name</label>
                            <input type="text"
                                   oninput="this.value = this.value.replace(/[^a-zA-Z\s]/g, '')"
                                   class="w-full rounded-xl border border-slate-300 px-3.5 py-3 text-base sm:text-sm focus:border-indigo-500 focus:ring-2 focus:ring-indigo-500/20 transition" wire:model.defer="newCustomerName" />
                            @error('newCustomerName') <div class="text-sm text-rose-600 mt-1.5">{{ $message }}</div> @enderror
                        </div>
                        <div>
                            <label class="block text-sm font-semibold text-slate-700 mb-1.5">Contact Number</label>
                            <input type="tel"
                                   inputmode="tel"
                                   maxlength="13"
                                   placeholder="+639171234567"
                                   oninput="this.value = this.value.replace(/[^0-9+]/g, '')"
                                   class="w-full rounded-xl border border-slate-300 px-3.5 py-3 text-base sm:text-sm focus:border-indigo-500 focus:ring-2 focus:ring-indigo-500/20 transition" wire:model.defer="newCustomerContactNumber" />
                            <p class="text-xs text-slate-500 mt-1">Format: +63 9XX XXX XXXX</p>
                            @error('newCustomerContactNumber') <div class="text-sm text-rose-600 mt-1.5">{{ $message }}</div> @enderror
                        </div>
                        <div>
                            <label class="block text-sm font-semibold text-slate-700 mb-1.5">Credit Limit (₱)</label>
                            <input type="number" inputmode="decimal" step="0.01" min="0"
                                   oninput="this.value = this.value.replace(/[^0-9.]/g, '')"
                                   class="w-full rounded-xl border border-slate-300 px-3.5 py-3 text-base sm:text-sm focus:border-indigo-500 focus:ring-2 focus:ring-indigo-500/20 transition" wire:model.defer="newCustomerCreditLimit" />
                            @error('newCustomerCreditLimit') <div class="text-sm text-rose-600 mt-1.5">{{ $message }}</div> @enderror
                        </div>
                        <div class="md:col-span-2">
                            <label class="block text-sm font-semibold text-slate-700 mb-1.5">Address</label>
                            <input type="text" class="w-full rounded-xl border border-slate-300 px-3.5 py-3 text-base sm:text-sm focus:border-indigo-500 focus:ring-2 focus:ring-indigo-500/20 transition" wire:model.defer="newCustomerAddress" />
                            @error('newCustomerAddress') <div class="text-sm text-rose-600 mt-1.5">{{ $message }}</div> @enderror
                        </div>

                        {{-- Attach signed T&C --}}
                        <div class="md:col-span-2">
                            <label class="block text-sm font-semibold text-slate-700 mb-1.5">
                                Attach Signed Terms &amp; Conditions (Image) <span class="text-rose-600">*</span>
                            </label>
                            <input type="file"
                                   wire:model="newCustomerTermsAttachment"
                                   accept="image/*"
                                   class="w-full rounded-xl border border-slate-300 px-3.5 py-2.5 text-sm focus:border-indigo-500 focus:ring-2 focus:ring-indigo-500/20 transition file:mr-3 file:rounded-lg file:border-0 file:bg-indigo-600 file:px-3 file:py-1.5 file:text-xs file:font-semibold file:text-white hover:file:bg-indigo-700" />
                            @error('newCustomerTermsAttachment') <div class="text-sm text-rose-600 mt-1.5">{{ $message }}</div> @enderror
                            <div wire:loading wire:target="newCustomerTermsAttachment" class="text-xs text-slate-500 mt-2">Uploading…</div>
                            @if ($newCustomerTermsAttachment)
                                <img src="{{ $newCustomerTermsAttachment->temporaryUrl() }}"
                                     class="mt-3 max-h-48 rounded-xl border border-slate-200 object-contain bg-slate-50 p-2" />
                            @endif
                        </div>

                        <div class="md:col-span-2">
                            <div class="rounded-xl bg-slate-50 border border-slate-200 p-4">
                                <div class="flex items-start gap-3">
                                    <input type="checkbox" wire:model="newCustomerAgreeTerms" id="agreeTerms"
                                           class="mt-0.5 rounded border-slate-300 text-indigo-600 focus:ring-indigo-500 w-5 h-5" />
                                    <label for="agreeTerms" class="text-sm text-slate-700">
                                        I have read and agree to the
                                        <button type="button" x-data @click="$dispatch('open-terms-modal')" class="text-indigo-600 hover:underline font-semibold">Terms and Conditions</button>.
                                        <span class="text-rose-600">*</span>
                                    </label>
                                </div>
                                @error('newCustomerAgreeTerms') <div class="text-sm text-rose-600 mt-1.5">{{ $message }}</div> @enderror
                            </div>
                        </div>
                    </div>
                </div>
                <div class="px-5 sm:px-6 py-4 bg-slate-50 border-t border-slate-100 flex flex-col-reverse sm:flex-row sm:justify-end gap-2 sm:gap-3">
                    <button type="button" wire:click="backToCustomerType" class="w-full sm:w-auto rounded-xl border border-slate-300 bg-white px-4 py-3 text-sm font-semibold text-slate-700 active:bg-slate-100 transition">Back</button>
                    <button type="button" wire:click="saveNewCustomerAndContinue" class="w-full sm:w-auto rounded-xl bg-indigo-600 px-4 py-3 text-sm font-semibold text-white shadow-sm hover:bg-indigo-700 active:scale-[0.98] transition">Save & Continue</button>
                </div>
            </div>
        </x-modal>
        @endif

        {{-- 2b) Existing Customer Picker Modal --}}
        @if($customerType === 'existing')
        <x-modal id="transactions-existing-customer-modal" wire:model="showExistingCustomerModal" maxWidth="4xl" :closeOnBackdropClick="false">
            <div class="bg-white overflow-hidden">
                <div class="px-5 sm:px-6 py-4 border-b border-slate-100 flex items-center justify-between sticky top-0 bg-white z-10">
                    <div class="flex items-center gap-3 min-w-0">
                        <button type="button" wire:click="backToCustomerType" title="Back to Transaction Setup"
                                class="inline-flex items-center justify-center w-9 h-9 rounded-lg border border-slate-200 bg-white text-slate-600 hover:bg-slate-50 hover:text-slate-900 active:bg-slate-100 transition flex-shrink-0">
                            <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M10 19l-7-7m0 0l7-7m-7 7h18" />
                            </svg>
                        </button>
                        <h3 class="text-base font-semibold text-slate-900 truncate">Choose Existing Customer</h3>
                    </div>
                    <button type="button" wire:click="cancelWizard" class="text-slate-400 hover:text-slate-600 transition p-1" aria-label="Close">
                        <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12" /></svg>
                    </button>
                </div>
                <div class="p-5 sm:p-6 space-y-4">
                    <div class="relative">
                        <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4 absolute left-3 top-1/2 -translate-y-1/2 text-slate-400" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z" /></svg>
                        <input type="text" class="w-full rounded-xl border border-slate-300 pl-9 pr-3 py-3 text-base sm:text-sm focus:border-indigo-500 focus:ring-2 focus:ring-indigo-500/20 transition" placeholder="Search by name" wire:model.live="existingCustomerSearch" autocomplete="new-password" />
                    </div>

                    {{-- Mobile cards --}}
                    <div class="md:hidden max-h-[420px] overflow-y-auto divide-y divide-slate-100 rounded-xl border border-slate-200">
                        @forelse($this->existingCustomers as $c)
                            <div class="p-4 space-y-3 active:bg-slate-50/60 transition-colors">
                                <div>
                                    <div class="font-semibold text-slate-900 truncate">{{ $c->name }}</div>
                                    <div class="text-xs text-slate-500 mt-0.5">{{ $c->contact_number ?: '—' }}</div>
                                </div>
                                <div class="flex items-center justify-between">
                                    <div class="text-sm font-semibold text-slate-900">₱{{ number_format($c->credit_limit ?? 0, 2) }}</div>
                                    <button type="button" wire:click="selectExistingCustomer({{ $c->id }})" class="rounded-xl bg-emerald-600 px-4 py-2 text-xs font-semibold text-white active:bg-emerald-700 transition">Next</button>
                                </div>
                            </div>
                        @empty
                            <div class="p-8 text-center text-sm text-slate-500">No customers found.</div>
                        @endforelse
                    </div>

                    {{-- Desktop table --}}
                    <div class="hidden md:block max-h-[420px] overflow-y-auto rounded-xl border border-slate-200">
                        <table class="min-w-full text-sm">
                            <thead class="bg-slate-50 sticky top-0 border-b border-slate-100">
                                <tr class="text-left">
                                    <th class="p-3 text-xs font-semibold uppercase tracking-wider text-slate-500">Name</th>
                                    <th class="p-3 text-xs font-semibold uppercase tracking-wider text-slate-500">Contact</th>
                                    <th class="p-3 text-xs font-semibold uppercase tracking-wider text-slate-500">Credit Limit</th>
                                    <th class="p-3 text-xs font-semibold uppercase tracking-wider text-slate-500 text-right">Select</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-slate-100">
                            @forelse($this->existingCustomers as $c)
                                <tr class="hover:bg-slate-50/70 transition-colors">
                                    <td class="p-3 font-medium text-slate-900">{{ $c->name }}</td>
                                    <td class="p-3 text-slate-600">{{ $c->contact_number ?: '—' }}</td>
                                    <td class="p-3 text-slate-600">₱{{ number_format($c->credit_limit ?? 0, 2) }}</td>
                                    <td class="p-3 text-right">
                                        <button type="button" wire:click="selectExistingCustomer({{ $c->id }})" class="rounded-lg bg-emerald-600 px-3 py-1.5 text-xs font-semibold text-white hover:bg-emerald-700 transition">Next</button>
                                    </td>
                                </tr>
                            @empty
                                <tr><td colspan="4" class="p-8 text-center text-sm text-slate-500">No customers found.</td></tr>
                            @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </x-modal>
        @endif

        {{-- 3) Choose Products Modal --}}
        <x-modal id="transactions-products-modal" wire:model="showProductsModal" maxWidth="4xl" :closeOnBackdropClick="false">
            <div class="bg-white overflow-hidden">
                <div class="px-5 sm:px-6 py-4 border-b border-slate-100 flex items-center justify-between sticky top-0 bg-white z-10">
                    <h3 class="text-base font-semibold text-slate-900">Select Products</h3>
                    <button type="button" wire:click="cancelWizard" class="text-slate-400 hover:text-slate-600 transition p-1" aria-label="Close">
                        <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12" /></svg>
                    </button>
                </div>
                <div class="p-4 sm:p-6">
                    <div class="grid grid-cols-1 lg:grid-cols-2 gap-4 sm:gap-5">
                        {{-- Add Product --}}
                        <div class="rounded-2xl border border-slate-200 p-4 bg-slate-50/50">
                            <h3 class="font-bold text-slate-900 mb-3 text-sm">Add New Product (if needed)</h3>
                            <div class="space-y-3">
                                <div>
                                    <label class="block text-sm font-semibold text-slate-700 mb-1.5">Product Name</label>
                                    <input type="text" class="w-full rounded-xl border border-slate-300 px-3.5 py-3 text-base sm:text-sm focus:border-indigo-500 focus:ring-2 focus:ring-indigo-500/20 transition bg-white" wire:model.defer="productName" />
                                </div>
                                <div>
                                    <label class="block text-sm font-semibold text-slate-700 mb-1.5">Unit</label>
                                    <select class="w-full rounded-xl border border-slate-300 px-3.5 py-3 text-base sm:text-sm focus:border-indigo-500 focus:ring-2 focus:ring-indigo-500/20 transition bg-white" wire:model="productUnitType">
                                        <option>Per Piece</option>
                                        <option>Per Kilos</option>
                                    </select>
                                </div>
                                <div>
                                    <label class="block text-sm font-semibold text-slate-700 mb-1.5">Price (₱)</label>
                                    <input type="number" inputmode="decimal" step="0.01" min="0"
                                           oninput="this.value = this.value.replace(/[^0-9.]/g, '')"
                                           class="w-full rounded-xl border border-slate-300 px-3.5 py-3 text-base sm:text-sm focus:border-indigo-500 focus:ring-2 focus:ring-indigo-500/20 transition bg-white" wire:model.defer="productPrice" />
                                </div>
                                <button type="button" class="w-full rounded-xl bg-indigo-600 px-4 py-3 text-sm font-semibold text-white shadow-sm hover:bg-indigo-700 active:scale-[0.98] transition" wire:click="createProductFromWizard">Add Product</button>
                            </div>
                        </div>

                        {{-- Product List --}}
                        <div class="rounded-2xl border border-slate-200 p-4">
                            <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-2 mb-3">
                                <h3 class="font-bold text-slate-900 text-sm">Product List</h3>
                                <input type="text" class="rounded-xl border border-slate-300 px-3 py-2.5 text-base sm:text-sm focus:border-indigo-500 focus:ring-2 focus:ring-indigo-500/20 transition w-full sm:w-auto" placeholder="Search..." wire:model.live="productSearch" autocomplete="new-password" />
                            </div>
                            <div class="max-h-[280px] overflow-y-auto">
                                <table class="min-w-full text-sm">
                                    <thead class="sticky top-0 bg-white border-b border-slate-100">
                                        <tr class="text-left">
                                            <th class="p-2 text-xs font-semibold uppercase tracking-wider text-slate-500">Name</th>
                                            <th class="p-2 text-xs font-semibold uppercase tracking-wider text-slate-500">Unit</th>
                                            <th class="p-2 text-xs font-semibold uppercase tracking-wider text-slate-500">Price</th>
                                            <th class="p-2 text-xs font-semibold uppercase tracking-wider text-slate-500 text-center">Pick</th>
                                        </tr>
                                    </thead>
                                    <tbody class="divide-y divide-slate-100">
                                        @foreach($this->availableProducts as $p)
                                            <tr class="hover:bg-slate-50/70 transition-colors">
                                                <td class="p-2 font-medium text-slate-900">{{ $p->name }}</td>
                                                <td class="p-2 text-slate-600 whitespace-nowrap">{{ $p->unit }}</td>
                                                <td class="p-2 text-slate-600 whitespace-nowrap">₱{{ number_format($p->price ?? 0, 2) }}</td>
                                                <td class="p-2 text-center">
                                                    <input type="checkbox"
                                                           wire:click="toggleProduct({{ $p->id }}, '{{ $p->unit }}', {{ (float)($p->price ?? 0) }})"
                                                           {{ in_array($p->id, array_column($selectedProducts,'product_id')) ? 'checked' : '' }}
                                                           class="rounded border-slate-300 text-indigo-600 focus:ring-indigo-500 w-5 h-5" />
                                                </td>
                                            </tr>
                                        @endforeach
                                    </tbody>
                                </table>
                            </div>
                        </div>

                        {{-- Chosen Products --}}
                        <div class="lg:col-span-2">
                            <div class="rounded-2xl border border-slate-200 p-4">
                                <h3 class="font-bold text-slate-900 mb-3 text-sm">Chosen Products</h3>
                                <div class="overflow-x-auto max-h-[300px] overflow-y-auto -mx-2 px-2">
                                    <table class="min-w-full text-sm">
                                        <thead class="bg-slate-50 sticky top-0 border-b border-slate-100">
                                            <tr class="text-left">
                                                <th class="p-2 text-xs font-semibold uppercase tracking-wider text-slate-500">Item</th>
                                                <th class="p-2 text-xs font-semibold uppercase tracking-wider text-slate-500 whitespace-nowrap">Unit Type</th>
                                                <th class="p-2 text-xs font-semibold uppercase tracking-wider text-slate-500 whitespace-nowrap">Qty</th>
                                                <th class="p-2 text-xs font-semibold uppercase tracking-wider text-slate-500 whitespace-nowrap">Price</th>
                                                <th class="p-2 text-xs font-semibold uppercase tracking-wider text-slate-500 whitespace-nowrap">Surcharge</th>
                                                <th class="p-2 text-xs font-semibold uppercase tracking-wider text-slate-500 whitespace-nowrap">Line Total</th>
                                                <th class="p-2"></th>
                                            </tr>
                                        </thead>
                                        <tbody class="divide-y divide-slate-100">
                                        @forelse($selectedProducts as $i => $sp)
                                            <tr>
                                                <td class="p-2 font-medium text-slate-900">{{ $sp['name'] }}</td>
                                                <td class="p-2 text-slate-600 whitespace-nowrap">{{ $sp['unit_override'] ?? 'Per Piece' }}</td>
                                                <td class="p-2">
                                                    <input type="number" inputmode="decimal" step="0.01" min="0"
                                                        oninput="this.value = this.value.replace(/[^0-9.]/g, '')"
                                                        class="w-20 sm:w-24 rounded-xl border border-slate-300 px-2 py-2 text-base sm:text-sm focus:border-indigo-500 focus:ring-2 focus:ring-indigo-500/20 transition"
                                                        wire:model="selectedProducts.{{ $i }}.qty" />
                                                </td>
                                                <td class="p-2 text-slate-600 whitespace-nowrap">₱{{ number_format($sp['price'] ?? 0, 2) }}</td>
                                                <td class="p-2 text-slate-600 whitespace-nowrap">₱{{ number_format($sp['surcharge'] ?? 0, 2) }}</td>
                                                <td class="p-2 font-semibold text-slate-900 whitespace-nowrap">₱{{ number_format($sp['total'] ?? 0, 2) }}</td>
                                                <td class="p-2">
                                                    <button type="button" class="rounded-lg bg-rose-100 px-3 py-1.5 text-xs font-semibold text-rose-700 active:bg-rose-200 transition" wire:click="removeSelectedProduct({{ $i }})">✕</button>
                                                </td>
                                            </tr>
                                        @empty
                                            <tr><td colspan="7" class="p-6 text-center text-sm text-slate-500">No products selected.</td></tr>
                                        @endforelse
                                        </tbody>
                                    </table>
                                </div>

                                <div class="mt-4 grid grid-cols-1 md:grid-cols-3 gap-3 items-end">
                                    <div class="md:col-span-2">
                                        <label class="block text-sm font-semibold text-slate-700 mb-1.5">Due Date</label>
                                        <div class="text-sm text-slate-700 bg-slate-50 rounded-xl border border-slate-200 px-3.5 py-3">
                                            {{ \Carbon\Carbon::parse($transactionDate)->addDays((int) (App\Services\AuthService::user()?->settings->transaction_duration_days ?? 30))->format('F j, Y') }}
                                        </div>
                                    </div>
                                    <div class="text-left md:text-right">
                                        <div class="text-xs font-semibold uppercase tracking-wider text-slate-500">Total Amount</div>
                                        <div class="text-xl sm:text-2xl font-bold text-slate-900">₱{{ number_format($this->computedSelectedTotal ?? 0, 2) }}</div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="px-5 sm:px-6 py-4 bg-slate-50 border-t border-slate-100 flex flex-col-reverse sm:flex-row sm:justify-end gap-2 sm:gap-3">
                    <button type="button" wire:click="backToCustomerSelection" class="w-full sm:w-auto rounded-xl border border-slate-300 bg-white px-4 py-3 text-sm font-semibold text-slate-700 active:bg-slate-100 transition">Back</button>
                    <button type="button" wire:click="goToOverview" class="w-full sm:w-auto rounded-xl bg-indigo-600 px-4 py-3 text-sm font-semibold text-white shadow-sm hover:bg-indigo-700 active:scale-[0.98] transition">Next</button>
                </div>
            </div>
        </x-modal>

        {{-- 4) Overview Modal --}}
        <x-modal id="transactions-overview-modal" wire:model="showOverviewModal" maxWidth="2xl" :closeOnBackdropClick="false">
            <div class="bg-white overflow-hidden">
                <div class="px-5 sm:px-6 py-4 border-b border-slate-100 flex items-center justify-between sticky top-0 bg-white z-10">
                    <h3 class="text-base font-semibold text-slate-900">Overview</h3>
                    <button type="button" wire:click="cancelWizard" class="text-slate-400 hover:text-slate-600 transition p-1" aria-label="Close">
                        <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12" /></svg>
                    </button>
                </div>
                <div class="p-5 sm:p-6">
                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-3 sm:gap-4">
                        <div>
                            <div class="text-xs font-semibold uppercase tracking-wider text-slate-500">Customer</div>
                            <div class="font-semibold text-slate-900 mt-1">{{ $selectedCustomer?->name ?? '' }}</div>
                        </div>
                        <div>
                            <div class="text-xs font-semibold uppercase tracking-wider text-slate-500">Transaction Date</div>
                            <div class="font-semibold text-slate-900 mt-1">{{ \Carbon\Carbon::parse($transactionDate)->format('F j, Y') }}</div>
                        </div>
                        <div>
                            <div class="text-xs font-semibold uppercase tracking-wider text-slate-500">Due Date</div>
                            <div class="font-semibold text-slate-900 mt-1">
                                {{ \Carbon\Carbon::parse($transactionDate)->addDays((int) (App\Services\AuthService::user()?->settings->transaction_duration_days ?? 30))->format('F j, Y') }}
                            </div>
                        </div>
                    </div>

                    <div class="mt-4 rounded-2xl border border-slate-200 overflow-x-auto max-h-[400px] overflow-y-auto -mx-2 px-2">
                        <table class="min-w-full text-sm">
                            <thead class="bg-slate-50 sticky top-0 border-b border-slate-100">
                                <tr class="text-left">
                                    <th class="p-3 text-xs font-semibold uppercase tracking-wider text-slate-500">Item</th>
                                    <th class="p-3 text-xs font-semibold uppercase tracking-wider text-slate-500 whitespace-nowrap">Unit</th>
                                    <th class="p-3 text-xs font-semibold uppercase tracking-wider text-slate-500 whitespace-nowrap">Qty</th>
                                    <th class="p-3 text-xs font-semibold uppercase tracking-wider text-slate-500 whitespace-nowrap">Price</th>
                                    <th class="p-3 text-xs font-semibold uppercase tracking-wider text-slate-500 whitespace-nowrap">Surcharge</th>
                                    <th class="p-3 text-xs font-semibold uppercase tracking-wider text-slate-500 whitespace-nowrap">Total</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-slate-100">
                                @forelse($selectedProducts as $sp)
                                    <tr>
                                        <td class="p-3 font-medium text-slate-900">{{ $sp['name'] }}</td>
                                        <td class="p-3 text-slate-600 whitespace-nowrap">{{ $sp['unit_override'] ?? 'Per Piece' }}</td>
                                        <td class="p-3 text-slate-600 whitespace-nowrap">{{ $sp['qty'] ?? 0 }}</td>
                                        <td class="p-3 text-slate-600 whitespace-nowrap">₱{{ number_format($sp['price'] ?? 0, 2) }}</td>
                                        <td class="p-3 text-slate-600 whitespace-nowrap">₱{{ number_format($sp['surcharge'] ?? 0, 2) }}</td>
                                        <td class="p-3 font-semibold text-slate-900 whitespace-nowrap">₱{{ number_format($sp['total'] ?? 0, 2) }}</td>
                                    </tr>
                                @empty
                                    <tr><td colspan="6" class="p-6 text-center text-sm text-slate-500">No items selected.</td></tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>

                    <div class="mt-4 rounded-2xl bg-slate-50 border border-slate-200 p-4">
                        @php
                            $baseTotal = array_sum(array_column($selectedProducts, 'base_total'));
                            $surchargeTotal = array_sum(array_column($selectedProducts, 'surcharge'));
                        @endphp
                        <div class="space-y-2 text-sm">
                            <div class="flex justify-between">
                                <span class="text-slate-600">Subtotal (products)</span>
                                <span class="font-semibold text-slate-900">₱{{ number_format($baseTotal, 2) }}</span>
                            </div>
                            <div class="flex justify-between">
                                <span class="text-slate-600">Surcharge (₱{{ number_format($this->surchargePerUnit, 2) }} per unit)</span>
                                <span class="font-semibold text-slate-900">+ ₱{{ number_format($surchargeTotal, 2) }}</span>
                            </div>
                            <div class="flex justify-between pt-2 border-t border-slate-200">
                                <span class="font-semibold text-slate-700">Total (excl. interest)</span>
                                <span class="text-lg sm:text-xl font-bold text-slate-900">₱{{ number_format($baseTotal + $surchargeTotal, 2) }}</span>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="px-5 sm:px-6 py-4 bg-slate-50 border-t border-slate-100 flex flex-col-reverse sm:flex-row sm:justify-end gap-2 sm:gap-3">
                    <button type="button" wire:click="backToProducts" class="w-full sm:w-auto rounded-xl border border-slate-300 bg-white px-4 py-3 text-sm font-semibold text-slate-700 active:bg-slate-100 transition">Back</button>
                    <button type="button" wire:click="saveTransactionFromWizard" class="w-full sm:w-auto rounded-xl bg-emerald-600 px-4 py-3 text-sm font-semibold text-white shadow-sm hover:bg-emerald-700 active:scale-[0.98] transition">Save Transaction</button>
                </div>
            </div>
        </x-modal>
    </div>

    {{-- Transactions List --}}
    <div class="bg-white border border-slate-200 rounded-2xl shadow-sm overflow-hidden">
        {{-- Mobile cards --}}
        <div class="md:hidden divide-y divide-slate-100">
            @forelse($groups as $group)
                <div class="p-4 space-y-3 active:bg-slate-50/60 transition-colors">
                    <div class="flex items-start justify-between gap-3">
                        <div class="min-w-0 flex-1">
                            <div class="font-semibold text-slate-900 truncate">{{ $group->customer?->name ?? 'N/A' }}</div>
                            <div class="text-xs text-slate-500 mt-0.5">{{ $group->transaction_date->format('M d, Y') }}</div>
                        </div>
                        <span class="inline-flex items-center px-2.5 py-1 rounded-full text-xs font-semibold flex-shrink-0
                            @if($group->status === 'PAID') bg-emerald-100 text-emerald-700
                            @elseif($group->status === 'PENDING') bg-blue-100 text-blue-700
                            @elseif($group->status === 'OVERDUE') bg-rose-100 text-rose-700
                            @else bg-slate-100 text-slate-700 @endif">
                            {{ $group->status }}
                        </span>
                    </div>
                    <div class="text-xs space-y-1.5">
                        <div class="font-mono text-slate-700 bg-slate-100 px-2 py-1 rounded inline-block">{{ $group->transaction_number ?? '—' }}</div>
                        <div class="text-slate-600 truncate">{{ $group->product_names }}</div>
                        <div class="text-slate-500">Due: {{ $group->due_date ? $group->due_date->format('M d, Y') : '—' }}</div>
                    </div>
                    <div class="flex items-center justify-between pt-1 border-t border-slate-100">
                        @if(($group->remaining_amount ?? 0) > 0 && $group->remaining_amount < $group->total_amount)
                            <div>
                                <div class="text-base font-bold text-slate-900">₱{{ number_format($group->remaining_amount, 2) }}</div>
                                <div class="text-xs text-slate-500 line-through">₱{{ number_format($group->total_amount, 2) }}</div>
                            </div>
                        @else
                            <div class="text-base font-bold text-slate-900">₱{{ number_format($group->total_amount, 2) }}</div>
                        @endif
                    </div>
                    <div class="grid grid-cols-3 gap-2">
                        <button type="button" class="rounded-xl border border-slate-300 bg-white px-3 py-2.5 text-xs font-semibold text-slate-700 active:bg-slate-100 transition" wire:click="viewGroup('{{ $group->group_id }}')">View</button>
                        @if($group->status !== 'PAID')
                            <button type="button" class="rounded-xl bg-emerald-600 px-3 py-2.5 text-xs font-semibold text-white active:bg-emerald-700 transition" wire:click="payGroup('{{ $group->group_id }}')">Pay</button>
                        @else
                            <button type="button" class="rounded-xl bg-slate-100 px-3 py-2.5 text-xs font-semibold text-slate-400 cursor-not-allowed" disabled>Paid</button>
                        @endif
                        <button type="button" class="rounded-xl border border-rose-200 bg-white px-3 py-2.5 text-xs font-semibold text-rose-700 active:bg-rose-50 transition" wire:click="confirmDeleteGroup('{{ $group->group_id }}')">Delete</button>
                    </div>
                </div>
            @empty
                <div class="py-12 text-center text-sm text-slate-500">No transactions found.</div>
            @endforelse
        </div>

        {{-- Desktop table --}}
        <div class="hidden md:block overflow-x-auto">
            <table class="w-full min-w-[1080px] table-fixed text-sm">
                <colgroup>
                    <col class="w-[8%]">
                    <col class="w-[11%]">
                    <col class="w-[13%]">
                    <col class="w-[19%]">
                    <col class="w-[9%]">
                    <col class="w-[10%]">
                    <col class="w-[10%]">
                    <col class="w-[20%]">
                </colgroup>
                <thead class="bg-slate-50/80 border-b border-slate-100">
                    <tr class="text-left">
                        <th class="py-3 px-4 text-xs font-semibold uppercase tracking-wider text-slate-500">Date</th>
                        <th class="py-3 px-4 text-xs font-semibold uppercase tracking-wider text-slate-500">Transaction #</th>
                        <th class="py-3 px-4 text-xs font-semibold uppercase tracking-wider text-slate-500">Customer</th>
                        <th class="py-3 px-4 text-xs font-semibold uppercase tracking-wider text-slate-500">Products</th>
                        <th class="py-3 px-4 text-xs font-semibold uppercase tracking-wider text-slate-500">Due</th>
                        <th class="py-3 px-4 text-xs font-semibold uppercase tracking-wider text-slate-500">Status</th>
                        <th class="py-3 px-4 text-xs font-semibold uppercase tracking-wider text-slate-500">Total</th>
                        <th class="py-3 px-4 text-xs font-semibold uppercase tracking-wider text-slate-500 text-right">Actions</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100">
                    @forelse($groups as $group)
                        <tr class="hover:bg-slate-50/70 transition-colors">
                            <td class="py-3 px-4 text-slate-600 whitespace-nowrap">
                                {{ $group->transaction_date->format('M d, Y') }}
                            </td>
                            <td class="py-3 px-4">
                                <div class="truncate">
                                    <span class="font-mono text-xs font-semibold text-slate-700 bg-slate-100 px-2 py-1 rounded-md">
                                        {{ $group->transaction_number ?? '—' }}
                                    </span>
                                </div>
                            </td>
                            <td class="py-3 px-4">
                                <div class="font-medium text-slate-900 truncate">
                                    {{ $group->customer?->name ?? 'N/A' }}
                                </div>
                            </td>
                            <td class="py-3 px-4">
                                <div class="text-slate-600 truncate" title="{{ $group->product_names }}">
                                    {{ $group->product_names }}
                                </div>
                            </td>
                            <td class="py-3 px-4 text-slate-600 whitespace-nowrap">
                                {{ $group->due_date ? $group->due_date->format('M d, Y') : '—' }}
                            </td>
                            <td class="py-3 px-4">
                                <span class="inline-flex items-center px-2.5 py-1 rounded-full text-xs font-semibold
                                    @if($group->status === 'PAID') bg-emerald-100 text-emerald-700
                                    @elseif($group->status === 'PENDING') bg-blue-100 text-blue-700
                                    @elseif($group->status === 'OVERDUE') bg-rose-100 text-rose-700
                                    @else bg-slate-100 text-slate-700 @endif">
                                    {{ $group->status }}
                                </span>
                            </td>
                            <td class="py-3 px-4 font-semibold text-slate-900 whitespace-nowrap">
                                @if(($group->remaining_amount ?? 0) > 0 && $group->remaining_amount < $group->total_amount)
                                    <div>₱{{ number_format($group->remaining_amount, 2) }}</div>
                                    <div class="text-xs text-slate-500 line-through font-normal">₱{{ number_format($group->total_amount, 2) }}</div>
                                @else
                                    ₱{{ number_format($group->total_amount, 2) }}
                                @endif
                            </td>
                            <td class="py-3 px-4">
                                <div class="flex gap-1.5 justify-end flex-nowrap">
                                    <button type="button"
                                            class="rounded-lg border border-slate-300 bg-white px-2.5 py-1.5 text-xs font-semibold text-slate-700 hover:bg-slate-50 transition whitespace-nowrap"
                                            wire:click="viewGroup('{{ $group->group_id }}')">View</button>
                                    @if($group->status !== 'PAID')
                                        <button type="button"
                                                class="rounded-lg bg-emerald-600 px-2.5 py-1.5 text-xs font-semibold text-white hover:bg-emerald-700 transition whitespace-nowrap"
                                                wire:click="payGroup('{{ $group->group_id }}')">Pay</button>
                                    @endif
                                    <button type="button"
                                            class="rounded-lg border border-rose-200 bg-white px-2.5 py-1.5 text-xs font-semibold text-rose-700 hover:bg-rose-50 transition whitespace-nowrap"
                                            wire:click="confirmDeleteGroup('{{ $group->group_id }}')">Delete</button>
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="8" class="py-12 text-center text-sm text-slate-500">No transactions found.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        @if($groups->hasPages())
            <div class="px-4 sm:px-5 py-4 border-t border-slate-100 flex justify-center">{{ $groups->links() }}</div>
        @endif
    </div>

    {{-- Delete Confirmation Modal --}}
    <x-modal wire:model="showDeleteModal" maxWidth="md">
        <div class="p-5 sm:p-6">
            <div class="flex items-start gap-4">
                <div class="flex-shrink-0 w-11 h-11 rounded-full bg-rose-100 flex items-center justify-center">
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 text-rose-600" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z" /></svg>
                </div>
                <div class="flex-1">
                    <h2 class="text-base font-semibold text-slate-900">
                        {{ $transactionToDelete ? 'Delete Transaction?' : 'Delete Transaction Group?' }}
                    </h2>
                    <p class="text-sm text-slate-500 mt-1">This action cannot be undone.</p>
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
                <button type="button" class="w-full sm:w-auto rounded-xl border border-slate-300 bg-white px-4 py-3 text-sm font-semibold text-slate-700 active:bg-slate-100 transition" wire:click="cancelDelete">Cancel</button>
                <button type="button" class="w-full sm:w-auto rounded-xl bg-rose-600 px-4 py-3 text-sm font-semibold text-white shadow-sm hover:bg-rose-700 active:scale-[0.98] transition"
                    wire:click="{{ $transactionToDelete ? 'deleteTransaction' : 'deleteGroup' }}">
                    Confirm Delete
                </button>
            </div>
        </div>
    </x-modal>

    {{-- Payment Modal --}}
    <x-modal wire:model="showPaymentModal" maxWidth="4xl" :closeOnBackdropClick="false">
        <div class="bg-white overflow-hidden">
            <div class="px-5 sm:px-6 py-4 border-b border-slate-100 flex items-center justify-between sticky top-0 bg-white z-10">
                <h2 class="text-base font-semibold text-slate-900">
                    @if($paymentMode === 'bulk') Process Bulk Payment @else Process Payment @endif
                </h2>
                <button type="button" class="text-slate-400 hover:text-slate-600 transition p-1" wire:click="closePaymentModal" aria-label="Close">
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12" /></svg>
                </button>
            </div>

            <div class="p-4 sm:p-6 grid grid-cols-1 md:grid-cols-2 gap-5 sm:gap-6">
                {{-- Left: Items --}}
                <div>
                    <h3 class="font-semibold text-slate-900 mb-3 text-sm">Products to Pay</h3>
                    @php
                        $itemsBase      = collect($transactionItems)->sum(fn($i) => ($i['unit_price'] ?? 0) * ($i['quantity'] ?? 0));
                        $itemsSurcharge = collect($transactionItems)->sum('surcharge');
                        $itemsInterest  = collect($transactionItems)->sum('interest');
                        $itemsGrand     = collect($transactionItems)->sum('total');
                    @endphp
                    <div class="bg-slate-50 rounded-2xl p-4 border border-slate-200 overflow-x-auto max-h-96">
                        @if(!empty($transactionItems))
                            <table class="w-full text-sm">
                                <thead>
                                    <tr class="border-b border-slate-200">
                                        <th class="text-left py-2 text-xs font-semibold uppercase tracking-wider text-slate-500">Item</th>
                                        <th class="text-right py-2 text-xs font-semibold uppercase tracking-wider text-slate-500">Qty</th>
                                        <th class="text-right py-2 text-xs font-semibold uppercase tracking-wider text-slate-500">Price</th>
                                        <th class="text-right py-2 text-xs font-semibold uppercase tracking-wider text-slate-500">Surcharge</th>
                                        <th class="text-right py-2 text-xs font-semibold uppercase tracking-wider text-slate-500">Interest</th>
                                        <th class="text-right py-2 text-xs font-semibold uppercase tracking-wider text-slate-500">Total</th>
                                    </tr>
                                </thead>
                                <tbody class="divide-y divide-slate-100">
                                    @foreach($transactionItems as $item)
                                        <tr>
                                            <td class="py-2 text-slate-900">{{ $item['product_name'] }}</td>
                                            <td class="py-2 text-right text-slate-600 whitespace-nowrap">{{ number_format($item['quantity'], 2) }}</td>
                                            <td class="py-2 text-right text-slate-600 whitespace-nowrap">₱{{ number_format($item['unit_price'], 2) }}</td>
                                            <td class="py-2 text-right text-slate-600 whitespace-nowrap">₱{{ number_format($item['surcharge'] ?? 0, 2) }}</td>
                                            <td class="py-2 text-right text-rose-600 whitespace-nowrap">₱{{ number_format($item['interest'] ?? 0, 2) }}</td>
                                            <td class="py-2 text-right font-semibold text-slate-900 whitespace-nowrap">₱{{ number_format($item['total'], 2) }}</td>
                                        </tr>
                                    @endforeach
                                </tbody>
                                <tfoot class="border-t-2 border-slate-200">
                                    <tr>
                                        <td colspan="3" class="text-right py-2 text-xs text-slate-500">Totals</td>
                                        <td class="text-right py-2 font-semibold text-slate-700 whitespace-nowrap">₱{{ number_format($itemsSurcharge, 2) }}</td>
                                        <td class="text-right py-2 font-semibold text-rose-700 whitespace-nowrap">₱{{ number_format($itemsInterest, 2) }}</td>
                                        <td class="text-right py-2 font-bold text-slate-900 whitespace-nowrap">₱{{ number_format($itemsGrand, 2) }}</td>
                                    </tr>
                                </tfoot>
                            </table>
                        @else
                            <p class="text-sm text-slate-500">No items selected.</p>
                        @endif
                    </div>
                </div>

                {{-- Right: Payment Form --}}
                <div>
                    <h3 class="font-semibold text-slate-900 mb-3 text-sm">Payment Details</h3>
                    <div class="bg-amber-50/50 rounded-2xl p-4 border border-amber-200 space-y-4">
                        <div>
                            <label class="block text-sm font-semibold text-slate-700 mb-1.5">Payment Method</label>
                            <select wire:model.live="paymentMethod" class="w-full rounded-xl border border-slate-300 px-3.5 py-3 text-base sm:text-sm focus:border-indigo-500 focus:ring-2 focus:ring-indigo-500/20 transition bg-white">
                                <option value="cash">Cash</option>
                                <option value="gcash">GCash</option>
                            </select>
                        </div>

                        @if($paymentMethod === 'gcash')
                            <div>
                                <label class="block text-sm font-semibold text-slate-700 mb-1.5">Reference Number</label>
                                <input type="text" wire:model="referenceNumber" class="w-full rounded-xl border border-slate-300 px-3.5 py-3 text-base sm:text-sm focus:border-indigo-500 focus:ring-2 focus:ring-indigo-500/20 transition bg-white" placeholder="e.g. 1234567890" />
                                @error('referenceNumber') <div class="text-sm text-rose-600 mt-1.5">{{ $message }}</div> @enderror
                            </div>
                        @endif

                        <div class="rounded-xl bg-white border border-slate-200 p-3 space-y-1.5">
                            <div class="flex justify-between text-xs">
                                <span class="text-slate-500">Subtotal (products)</span>
                                <span class="text-slate-700 font-semibold">₱{{ number_format($itemsBase, 2) }}</span>
                            </div>
                            <div class="flex justify-between text-xs">
                                <span class="text-slate-500">Surcharge</span>
                                <span class="text-slate-700 font-semibold">+ ₱{{ number_format($itemsSurcharge, 2) }}</span>
                            </div>
                            <div class="flex justify-between text-xs">
                                <span class="text-slate-500">Interest</span>
                                <span class="text-rose-700 font-semibold">+ ₱{{ number_format($itemsInterest, 2) }}</span>
                            </div>
                        </div>

                        <div>
                            <label class="block text-sm font-semibold text-slate-700 mb-1.5">Total Amount (incl. interest)</label>
                            <div class="text-2xl font-bold text-slate-900">₱{{ number_format($itemsGrand, 2) }}</div>
                        </div>

                        <div>
                            <label class="block text-sm font-semibold text-slate-700 mb-1.5">
                                {{ $paymentMethod === 'gcash' ? 'Payment sent' : 'Cash Given' }}
                            </label>
                            <input type="number" inputmode="decimal" step="0.01" min="0"
                                   oninput="this.value = this.value.replace(/[^0-9.]/g, '')"
                                   wire:model.live="cashGiven" wire:change="updateChange" class="w-full rounded-xl border border-slate-300 px-3.5 py-3 text-base sm:text-sm focus:border-indigo-500 focus:ring-2 focus:ring-indigo-500/20 transition bg-white" />
                            @error('cashGiven') <div class="text-sm text-rose-600 mt-1.5">{{ $message }}</div> @enderror
                        </div>

                        <div class="rounded-xl bg-emerald-50 border border-emerald-200 p-3">
                            <label class="block text-xs font-semibold uppercase tracking-wider text-emerald-700">Change</label>
                            <div class="text-xl font-bold text-emerald-700 mt-0.5">₱{{ number_format($change, 2) }}</div>
                        </div>
                    </div>
                </div>
            </div>

            <div class="px-5 sm:px-6 py-4 bg-slate-50 border-t border-slate-100 flex flex-col-reverse sm:flex-row sm:justify-end gap-2 sm:gap-3">
                <button type="button" wire:click="closePaymentModal" class="w-full sm:w-auto rounded-xl border border-slate-300 bg-white px-4 py-3 text-sm font-semibold text-slate-700 active:bg-slate-100 transition">Cancel</button>
                <button type="button" wire:click="submitPayment" class="w-full sm:w-auto rounded-xl bg-emerald-600 px-4 py-3 text-sm font-semibold text-white shadow-sm hover:bg-emerald-700 active:scale-[0.98] transition">Confirm Payment</button>
            </div>
        </div>
    </x-modal>

    {{-- Receipt Modal --}}
    <x-modal wire:model="showReceiptModal" maxWidth="2xl" :closeOnBackdropClick="false">
        <div class="p-4 sm:p-6">
            @if(!empty($receiptData))
                <x-receipt :receiptData="$receiptData" />
            @endif
        </div>
    </x-modal>

    {{-- Group Payment Modal --}}
    <x-modal wire:model="showGroupPaymentModal" maxWidth="4xl" :closeOnBackdropClick="false">
        <div class="bg-white overflow-hidden">
            <div class="px-5 sm:px-6 py-4 border-b border-slate-100 flex items-center justify-between sticky top-0 bg-white z-10">
                <h2 class="text-base font-semibold text-slate-900">Select Products to Pay</h2>
                <button type="button" class="text-slate-400 hover:text-slate-600 transition p-1" wire:click="$set('showGroupPaymentModal', false)" aria-label="Close">
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12" /></svg>
                </button>
            </div>
            <div class="p-4 sm:p-6 max-h-96 overflow-y-auto">
                <table class="w-full text-sm">
                    <thead class="bg-slate-50 border-b border-slate-100">
                        <tr>
                            <th class="p-3 text-left text-xs font-semibold uppercase tracking-wider text-slate-500 w-16">Select</th>
                            <th class="p-3 text-left text-xs font-semibold uppercase tracking-wider text-slate-500">Product</th>
                            <th class="p-3 text-left text-xs font-semibold uppercase tracking-wider text-slate-500 text-right whitespace-nowrap">Amount</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-100">
                        @foreach($paymentGroupTransactions ?? [] as $tx)
                            @php $isPaid = $tx->status === 'PAID'; @endphp
                            <tr class="{{ $isPaid ? 'bg-slate-100' : 'hover:bg-slate-50/70' }} transition-colors">
                                <td class="p-3">
                                    <input type="checkbox"
                                           wire:model="selectedPaymentTransactionIds"
                                           value="{{ $tx->id }}"
                                           @if($isPaid) disabled @endif
                                           class="rounded border-slate-300 text-indigo-600 focus:ring-indigo-500 w-5 h-5 {{ $isPaid ? 'cursor-not-allowed opacity-50' : '' }}" />
                                </td>
                                <td class="p-3 {{ $isPaid ? 'line-through text-slate-400' : 'text-slate-900' }}">
                                    {{ $tx->display_product_name }}
                                </td>
                                <td class="p-3 font-semibold whitespace-nowrap text-right {{ $isPaid ? 'text-slate-400' : 'text-slate-900' }}">
                                    ₱{{ number_format($tx->current_total, 2) }}
                                    @if($isPaid)
                                        <span class="ml-2 inline-block px-2 py-0.5 bg-emerald-100 text-emerald-700 rounded-full text-xs font-bold">PAID</span>
                                    @endif
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
            <div class="px-5 sm:px-6 py-4 bg-slate-50 border-t border-slate-100 flex flex-col-reverse sm:flex-row sm:justify-between gap-2 sm:gap-3">
                <button type="button" wire:click="selectAllPaymentTransactions" class="w-full sm:w-auto rounded-xl border border-slate-300 bg-white px-4 py-3 text-sm font-semibold text-slate-700 active:bg-slate-100 transition">Select All</button>
                <div class="flex flex-col-reverse sm:flex-row gap-2 sm:gap-3">
                    <button type="button" wire:click="$set('showGroupPaymentModal', false)" class="w-full sm:w-auto rounded-xl border border-slate-300 bg-white px-4 py-3 text-sm font-semibold text-slate-700 active:bg-slate-100 transition">Cancel</button>
                    <button type="button" wire:click="processGroupPayment" class="w-full sm:w-auto rounded-xl bg-emerald-600 px-4 py-3 text-sm font-semibold text-white shadow-sm hover:bg-emerald-700 active:scale-[0.98] transition">Pay Selected</button>
                </div>
            </div>
        </div>
    </x-modal>

    {{-- Whole-Transaction Edit Modal --}}
    <x-modal wire:model="showEditGroupModal" maxWidth="3xl">
        @if($editingCustomer)
            <div class="bg-white overflow-hidden">
                <div class="px-5 sm:px-6 py-4 border-b border-slate-100 flex items-center justify-between sticky top-0 bg-white z-10">
                    <h2 class="text-base font-semibold text-slate-900">Edit Transaction</h2>
                    <button wire:click="$set('showEditGroupModal', false)" class="text-slate-400 hover:text-slate-600 transition p-1" aria-label="Close">
                        <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12" /></svg>
                    </button>
                </div>
                <div class="p-5 sm:p-6">
                    <div class="grid grid-cols-1 lg:grid-cols-2 gap-5 sm:gap-6">
                        <div class="space-y-4">
                            <div class="rounded-xl bg-indigo-50 border border-indigo-200 p-4">
                                <div class="text-xs font-semibold uppercase tracking-wider text-indigo-600">Customer</div>
                                <div class="font-semibold text-slate-900 mt-1">{{ $editingCustomer->name }}</div>
                            </div>

                            <div class="rounded-2xl border border-slate-200 p-4">
                                <label class="block text-sm font-semibold text-slate-700 mb-2">Add Product</label>
                                <div class="flex flex-col sm:flex-row items-stretch sm:items-center gap-2">
                                    <select wire:model="editingNewProductId" class="flex-1 rounded-xl border border-slate-300 px-3.5 py-3 text-base sm:text-sm focus:border-indigo-500 focus:ring-2 focus:ring-indigo-500/20 transition bg-white">
                                        <option value="">Select product</option>
                                        @forelse($editingAvailableProducts ?? [] as $p)
                                            <option value="{{ $p->id }}">{{ $p->name }}</option>
                                        @empty
                                            <option value="">No more products available</option>
                                        @endforelse
                                    </select>
                                    <button wire:click="addProductToGroup" class="rounded-xl bg-indigo-600 px-4 py-3 text-sm font-semibold text-white shadow-sm hover:bg-indigo-700 active:scale-[0.98] transition whitespace-nowrap">+ Add</button>
                                </div>
                            </div>
                        </div>

                        <div class="rounded-2xl bg-slate-50 border border-slate-200 p-4">
                            <h3 class="text-sm font-semibold text-slate-900 mb-3 pb-2 border-b border-slate-200">Transaction Summary</h3>
                            <div class="space-y-2.5 text-sm">
                                <div class="flex justify-between gap-3">
                                    <span class="text-slate-600">Transaction Date</span>
                                    <span class="text-slate-900 font-semibold text-right">{{ $editingTransactionDate?->format('F j, Y') ?? '' }}</span>
                                </div>
                                <div class="flex justify-between gap-3">
                                    <span class="text-slate-600">Due Date</span>
                                    <span class="text-slate-900 font-semibold text-right">{{ $editingDueDate?->format('F j, Y') ?? '' }}</span>
                                </div>

                                @if($editingIsOverdue)
                                    <div class="flex justify-between gap-3 pt-2 border-t border-slate-200">
                                        <span class="text-slate-600">Overdue By</span>
                                        <span class="text-rose-700 font-bold text-right">
                                            {{ $editingDaysOverdue }} day{{ $editingDaysOverdue != 1 ? 's' : '' }}
                                            <span class="block text-xs font-normal text-rose-600">
                                                ({{ $editingWeeksOverdue }} week{{ $editingWeeksOverdue != 1 ? 's' : '' }} overdue)
                                            </span>
                                        </span>
                                    </div>
                                @elseif($editingDaysUntilDue > 0)
                                    <div class="flex justify-between gap-3 pt-2 border-t border-slate-200">
                                        <span class="text-slate-600">Due In</span>
                                        <span class="text-amber-700 font-semibold text-right">
                                            {{ $editingDaysUntilDue }} day{{ $editingDaysUntilDue != 1 ? 's' : '' }}
                                        </span>
                                    </div>
                                @elseif($editingDueDate)
                                    <div class="flex justify-between gap-3 pt-2 border-t border-slate-200">
                                        <span class="text-slate-600">Due</span>
                                        <span class="text-amber-700 font-semibold text-right">Today</span>
                                    </div>
                                @endif

                                <div class="flex justify-between gap-3">
                                    <span class="text-slate-600">
                                        Surcharge
                                        <span class="block text-xs text-slate-500">₱{{ number_format($editingSurchargePerUnit, 2) }} per unit</span>
                                    </span>
                                    <span class="text-slate-900 font-semibold text-right">₱{{ number_format($this->editingTotalSurcharge, 2) }}</span>
                                </div>

                                <div class="flex justify-between gap-3">
                                    <span class="text-slate-600">Interest Rate</span>
                                    <span class="text-slate-900 font-semibold text-right">{{ number_format($editingInterestRate, 2) }}%</span>
                                </div>

                                @if($editingIsOverdue && $this->editingTotalInterest > 0)
                                    <div class="flex justify-between gap-3 pt-2 border-t border-slate-200">
                                        <span class="text-slate-600">
                                            Overdue Interest
                                            <span class="block text-xs text-slate-500">
                                                {{ $editingWeeksOverdue }} week{{ $editingWeeksOverdue != 1 ? 's' : '' }} @ {{ number_format($editingInterestRate, 2) }}%
                                            </span>
                                        </span>
                                        <span class="text-rose-700 font-semibold text-right">
                                            + ₱{{ number_format($this->editingTotalInterest, 2) }}
                                        </span>
                                    </div>
                                @endif
                            </div>
                        </div>
                    </div>

                    <div class="mt-5 sm:mt-6 rounded-2xl border border-slate-200 overflow-hidden">
                        <div class="overflow-x-auto max-h-[320px] overflow-y-auto -mx-1 px-1">
                            <table class="w-full text-sm min-w-[600px]">
                                <thead class="bg-slate-50 sticky top-0 border-b border-slate-100">
                                    <tr class="text-left">
                                        <th class="p-3 text-xs font-semibold uppercase tracking-wider text-slate-500">Product</th>
                                        <th class="p-3 text-xs font-semibold uppercase tracking-wider text-slate-500 whitespace-nowrap">Unit</th>
                                        <th class="p-3 text-xs font-semibold uppercase tracking-wider text-slate-500 whitespace-nowrap">Qty</th>
                                        <th class="p-3 text-xs font-semibold uppercase tracking-wider text-slate-500 whitespace-nowrap">Price</th>
                                        <th class="p-3 text-xs font-semibold uppercase tracking-wider text-slate-500 whitespace-nowrap">Total</th>
                                        <th class="p-3"></th>
                                    </tr>
                                </thead>
                                <tbody class="divide-y divide-slate-100">
                                    @foreach($editingSelectedProducts as $index => $line)
                                        <tr class="{{ $line['is_paid'] ? 'bg-slate-50 text-slate-400' : '' }}">
                                            <td class="p-3 font-medium {{ $line['is_paid'] ? 'text-slate-500' : 'text-slate-900' }}">{{ $line['product_name'] }}</td>
                                            <td class="p-3 text-slate-600 whitespace-nowrap">{{ $line['unit_override'] }}</td>
                                            <td class="p-3">
                                                @if($line['is_paid'])
                                                    <span class="block px-2 py-1 bg-slate-200 rounded-lg text-center text-slate-600">{{ number_format($line['quantity'], 2) }}</span>
                                                @else
                                                    <input type="number" inputmode="decimal" step="0.01" min="0"
                                                           oninput="this.value = this.value.replace(/[^0-9.]/g, '')"
                                                           wire:model.lazy="editingSelectedProducts.{{ $index }}.quantity"
                                                           wire:change="updateEditingLine({{ $index }}, 'quantity', $event.target.value)"
                                                           class="w-20 rounded-xl border border-slate-300 px-2 py-2 text-base sm:text-sm focus:border-indigo-500 focus:ring-2 focus:ring-indigo-500/20 transition" />
                                                @endif
                                            </td>
                                            <td class="p-3">
                                                {{-- Price is locked for the life of a pending/overdue transaction --}}
                                                <span class="block px-2 py-1 bg-slate-100 rounded-lg text-center text-slate-600 whitespace-nowrap">
                                                    ₱{{ number_format($line['price'], 2) }}
                                                </span>
                                            </td>
                                            <td class="p-3 font-semibold {{ $line['is_paid'] ? 'text-slate-500' : 'text-slate-900' }} whitespace-nowrap">₱{{ number_format($line['total'], 2) }}</td>
                                            <td class="p-3">
                                                @if(!$line['is_paid'])
                                                    <button wire:click="removeEditingLine({{ $index }})" class="text-rose-600 hover:text-rose-800 text-xs font-semibold transition whitespace-nowrap">✕ Remove</button>
                                                @endif
                                                @if($line['is_paid'])
                                                    <span class="inline-block px-2 py-0.5 bg-emerald-100 text-emerald-700 rounded-full text-xs font-bold">PAID</span>
                                                @endif
                                            </td>
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                    </div>

                    <div class="mt-4 pt-4 border-t border-slate-100 flex flex-col sm:flex-row sm:justify-between sm:items-center gap-3">
                        <div class="text-lg font-bold text-slate-900">
                            Total: ₱{{ number_format($this->editingGrandTotal, 2) }}
                        </div>
                        <div class="flex flex-col-reverse sm:flex-row gap-2 sm:gap-3">
                            <button wire:click="$set('showEditGroupModal', false)" class="w-full sm:w-auto rounded-xl border border-slate-300 bg-white px-4 py-3 text-sm font-semibold text-slate-700 active:bg-slate-100 transition">Back</button>
                            <button wire:click="saveGroupEdits" class="w-full sm:w-auto rounded-xl bg-emerald-600 px-4 py-3 text-sm font-semibold text-white shadow-sm hover:bg-emerald-700 active:scale-[0.98] transition">Save Changes</button>
                        </div>
                    </div>
                </div>
            </div>
        @else
            <div class="bg-white border border-slate-200 p-12 text-center text-slate-500">
                Loading transaction details...
            </div>
        @endif
    </x-modal>

    {{-- Group Details Modal --}}
    <x-modal wire:model="showGroupDetailsModal" maxWidth="4xl">
        <div class="bg-white overflow-hidden">
            <div class="px-5 sm:px-6 py-4 border-b border-slate-100 flex items-center justify-between sticky top-0 bg-white z-10">
                <h2 class="text-base font-semibold text-slate-900">Products in this Transaction</h2>
                <button type="button" class="text-slate-400 hover:text-slate-600 transition p-1" wire:click="closeGroupDetails" aria-label="Close">
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12" /></svg>
                </button>
            </div>
            <div class="p-4 sm:p-6 overflow-x-auto">
                <table class="w-full text-sm min-w-[600px]">
                    <thead class="bg-slate-50 border-b border-slate-100">
                        <tr>
                            <th class="p-3 text-left text-xs font-semibold uppercase tracking-wider text-slate-500">Product</th>
                            <th class="p-3 text-left text-xs font-semibold uppercase tracking-wider text-slate-500 whitespace-nowrap">Qty</th>
                            <th class="p-3 text-left text-xs font-semibold uppercase tracking-wider text-slate-500 whitespace-nowrap">Price</th>
                            <th class="p-3 text-left text-xs font-semibold uppercase tracking-wider text-slate-500 whitespace-nowrap">Total</th>
                            <th class="p-3 text-left text-xs font-semibold uppercase tracking-wider text-slate-500 whitespace-nowrap">Status</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-100">
                        @foreach($groupDetailsTransactions ?? [] as $tx)
                            <tr>
                                <td class="p-3 font-medium text-slate-900">{{ $tx->display_product_name }}</td>
                                <td class="p-3 text-slate-600 whitespace-nowrap">{{ number_format($tx->quantity, 2) }}</td>
                                <td class="p-3 text-slate-600 whitespace-nowrap">₱{{ number_format($tx->base_total / max($tx->quantity, 1), 2) }}</td>
                                <td class="p-3 font-semibold text-slate-900 whitespace-nowrap">₱{{ number_format($tx->total_amount, 2) }}</td>
                                <td class="p-3">
                                    <span class="inline-flex items-center px-2.5 py-1 rounded-full text-xs font-semibold
                                        @if($tx->status === 'PAID') bg-emerald-100 text-emerald-700
                                        @elseif($tx->status === 'OVERDUE') bg-rose-100 text-rose-700
                                        @else bg-amber-100 text-amber-700 @endif">
                                        {{ $tx->status }}
                                    </span>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>
    </x-modal>

    {{-- Terms and Conditions Modal --}}
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