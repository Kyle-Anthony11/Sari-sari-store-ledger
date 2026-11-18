<div class="space-y-4 sm:space-y-6">
    <div class="flex flex-col gap-3">
        <div class="flex items-start justify-between gap-3">
            <div>
            </div>
            <button wire:click="openExportModal"
                    class="inline-flex items-center justify-center gap-1.5 rounded-xl bg-emerald-600 px-3.5 py-2.5 text-xs sm:text-sm font-semibold text-white shadow-sm hover:bg-emerald-700 active:scale-[0.98] transition flex-shrink-0">
                <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-4l-4 4m0 0l-4-4m4 4V4" /></svg>
                Export
            </button>
        </div>
        <select wire:model.live="dateFilter" class="w-full sm:w-64 rounded-xl border border-slate-300 bg-white px-3.5 py-3 text-base sm:text-sm shadow-sm focus:border-indigo-500 focus:ring-2 focus:ring-indigo-500/20 transition">
            <option value="all">All Time</option>
            <option value="today">Today</option>
            <option value="last_7_days">Last 7 Days</option>
            <option value="last_30_days">Last 30 Days</option>
        </select>
    </div>

    @if(session()->has('message'))
        <div class="rounded-2xl border border-emerald-200 bg-emerald-50 px-4 py-3 text-sm text-emerald-800">{{ session('message') }}</div>
    @endif

    <div class="bg-white border border-slate-200 rounded-2xl shadow-sm overflow-hidden">
        {{-- Filters --}}
        <div class="p-4 sm:p-5 border-b border-slate-100">
            <div class="grid grid-cols-1 md:grid-cols-4 gap-3 sm:gap-4">
                <div>
                    <label class="block text-sm font-semibold text-slate-700 mb-1.5">Customer</label>
                    <input type="text" class="w-full rounded-xl border border-slate-300 px-3.5 py-3 text-base sm:text-sm focus:border-indigo-500 focus:ring-2 focus:ring-indigo-500/20 transition" placeholder="Search customer" wire:model.live="searchCustomerName" autocomplete="new-password" />
                </div>
                <div>
                    <label class="block text-sm font-semibold text-slate-700 mb-1.5">Transaction #</label>
                    <input type="text" class="w-full rounded-xl border border-slate-300 px-3.5 py-3 text-base sm:text-sm focus:border-indigo-500 focus:ring-2 focus:ring-indigo-500/20 transition font-mono" placeholder="e.g. 2026091200001" wire:model.live="searchTransactionNumber" autocomplete="new-password" />
                </div>
                <div>
                    <label class="block text-sm font-semibold text-slate-700 mb-1.5">Item</label>
                    <input type="text" class="w-full rounded-xl border border-slate-300 px-3.5 py-3 text-base sm:text-sm focus:border-indigo-500 focus:ring-2 focus:ring-indigo-500/20 transition" placeholder="Search item" wire:model.live="searchItemName" autocomplete="new-password" />
                </div>
                <div>
                    <label class="block text-sm font-semibold text-slate-700 mb-1.5">Date</label>
                    <input type="date" class="w-full rounded-xl border border-slate-300 px-3.5 py-3 text-base sm:text-sm focus:border-indigo-500 focus:ring-2 focus:ring-indigo-500/20 transition" wire:model.live="searchDate" />
                </div>
            </div>
        </div>

        {{-- Mobile card list --}}
        <div class="md:hidden divide-y divide-slate-100">
            @forelse($groups as $group)
                <div class="p-4 space-y-3 active:bg-slate-50/60 transition-colors">
                    <div class="flex items-start justify-between gap-3">
                        <div class="min-w-0 flex-1">
                            <div class="font-semibold text-slate-900 truncate">{{ $group->customer_name }}</div>
                            <div class="text-xs text-slate-500 mt-0.5">
                                {{ $group->payment_date ? \Carbon\Carbon::parse($group->payment_date)->format('M d, Y h:i A') : '—' }}
                            </div>
                        </div>
                        <span class="inline-flex items-center px-2.5 py-1 rounded-full text-xs font-semibold bg-slate-100 text-slate-700 flex-shrink-0">
                            {{ $group->items_count }} item{{ $group->items_count > 1 ? 's' : '' }}
                        </span>
                    </div>
                    <div class="text-xs space-y-1.5">
                        <div class="font-mono text-slate-700 bg-slate-100 px-2 py-1 rounded inline-block">{{ $group->transaction_number ?? '—' }}</div>
                        <div class="text-slate-600 truncate">{{ $group->product_names }}</div>
                    </div>
                    <div class="flex items-center justify-between pt-1 border-t border-slate-100">
                        <div class="text-base font-bold text-slate-900">₱{{ number_format($group->total_amount, 2) }}</div>
                        <button type="button" class="rounded-xl border border-slate-300 bg-white px-3.5 py-2 text-xs font-semibold text-slate-700 active:bg-slate-100 transition" wire:click="viewGroupDetails({{ $group->payment_id }})">Details</button>
                    </div>
                </div>
            @empty
                <div class="py-12 text-center text-sm text-slate-500">No paid transactions found.</div>
            @endforelse
        </div>

        {{-- Desktop table --}}
        <div class="hidden md:block overflow-x-auto">
            <table class="w-full text-sm">
                <thead class="bg-slate-50/80 border-b border-slate-100">
                    <tr class="text-left">
                        <th class="py-3 px-5 text-xs font-semibold uppercase tracking-wider text-slate-500">Payment Date</th>
                        <th class="py-3 px-5 text-xs font-semibold uppercase tracking-wider text-slate-500">Transaction #</th>
                        <th class="py-3 px-5 text-xs font-semibold uppercase tracking-wider text-slate-500">Customer</th>
                        <th class="py-3 px-5 text-xs font-semibold uppercase tracking-wider text-slate-500">Products</th>
                        <th class="py-3 px-5 text-xs font-semibold uppercase tracking-wider text-slate-500">Total Paid</th>
                        <th class="py-3 px-5 text-xs font-semibold uppercase tracking-wider text-slate-500">Items</th>
                        <th class="py-3 px-5 text-xs font-semibold uppercase tracking-wider text-slate-500 text-right">Actions</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100">
                    @forelse($groups as $group)
                        <tr class="hover:bg-slate-50/70 transition-colors">
                            <td class="py-3 px-5 text-slate-600">{{ $group->payment_date ? \Carbon\Carbon::parse($group->payment_date)->format('M d, Y h:i A') : '—' }}</td>
                            <td class="py-3 px-5">
                                <span class="font-mono text-xs font-semibold text-slate-700 bg-slate-100 px-2 py-1 rounded-md">{{ $group->transaction_number ?? '—' }}</span>
                            </td>
                            <td class="py-3 px-5 font-medium text-slate-900">{{ $group->customer_name }}</td>
                            <td class="py-3 px-5 text-slate-600 max-w-xs truncate">{{ $group->product_names }}</td>
                            <td class="py-3 px-5 font-semibold text-slate-900">₱{{ number_format($group->total_amount, 2) }}</td>
                            <td class="py-3 px-5">
                                <span class="inline-flex items-center px-2.5 py-1 rounded-full text-xs font-semibold bg-slate-100 text-slate-700">{{ $group->items_count }}</span>
                            </td>
                            <td class="py-3 px-5 text-right">
                                <button type="button" class="rounded-lg border border-slate-300 bg-white px-3 py-1.5 text-xs font-semibold text-slate-700 hover:bg-slate-50 transition" wire:click="viewGroupDetails({{ $group->payment_id }})">Details</button>
                            </td>
                        </tr>
                    @empty
                        <tr><td colspan="7" class="py-12 text-center text-sm text-slate-500">No paid transactions found.</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        @if($groups->hasPages())
            <div class="px-4 sm:px-5 py-4 border-t border-slate-100 flex justify-center">{{ $groups->links() }}</div>
        @endif
    </div>

    {{-- Details Modal --}}
    <x-modal wire:model="isDetailsModalOpen" maxWidth="2xl">
        <div class="bg-white overflow-hidden">
            <div class="px-5 sm:px-6 py-4 border-b border-slate-100 flex items-center justify-between sticky top-0 bg-white z-10">
                <h2 class="text-base font-semibold text-slate-900">Payment Details</h2>
                <button type="button" class="text-slate-400 hover:text-slate-600 transition p-1" wire:click="closeDetails" aria-label="Close">
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12" /></svg>
                </button>
            </div>
            @if($selectedPayment)
                @php
                    $isGcash = strtolower($selectedPayment->payment?->payment_method ?? $selectedPayment->payment_method ?? 'cash') === 'gcash';
                @endphp
                <div class="p-5 sm:p-6 grid grid-cols-1 sm:grid-cols-2 gap-3 text-sm">
                    <div class="rounded-xl bg-slate-50 border border-slate-200 p-3">
                        <div class="text-xs font-semibold uppercase tracking-wider text-slate-500">Customer</div>
                        <div class="text-slate-900 font-medium mt-1">{{ $selectedPayment->customer?->name ?? $selectedPayment->customer_name ?? 'Deleted Customer' }}</div>
                    </div>
                    <div class="rounded-xl bg-slate-50 border border-slate-200 p-3">
                        <div class="text-xs font-semibold uppercase tracking-wider text-slate-500">Product</div>
                        <div class="text-slate-900 font-medium mt-1">{{ optional($selectedPayment->transaction)->display_product_name ?? $selectedPayment->product_name ?? 'N/A' }}</div>
                    </div>
                    <div class="rounded-xl bg-slate-50 border border-slate-200 p-3">
                        <div class="text-xs font-semibold uppercase tracking-wider text-slate-500">Transaction Date</div>
                        <div class="text-slate-900 font-medium mt-1">{{ optional($selectedPayment->transaction)->transaction_date?->format('F j, Y') ?? '—' }}</div>
                    </div>
                    <div class="rounded-xl bg-slate-50 border border-slate-200 p-3">
                        <div class="text-xs font-semibold uppercase tracking-wider text-slate-500">Due Date</div>
                        <div class="text-slate-900 font-medium mt-1">{{ optional($selectedPayment->transaction)->due_date?->format('F j, Y') ?? '—' }}</div>
                    </div>
                    <div class="rounded-xl bg-slate-50 border border-slate-200 p-3">
                        <div class="text-xs font-semibold uppercase tracking-wider text-slate-500">Payment Date</div>
                        <div class="text-slate-900 font-medium mt-1">{{ optional($selectedPayment->payment)->payment_date ? \Carbon\Carbon::parse($selectedPayment->payment->payment_date)->format('F j, Y') : '—' }}</div>
                    </div>
                    <div class="rounded-xl bg-slate-50 border border-slate-200 p-3">
                        <div class="text-xs font-semibold uppercase tracking-wider text-slate-500">Payment Method</div>
                        <div class="text-slate-900 font-medium mt-1">{{ $isGcash ? 'GCash' : 'Cash' }}</div>
                    </div>
                    @if($isGcash)
                        <div class="rounded-xl bg-slate-50 border border-slate-200 p-3">
                            <div class="text-xs font-semibold uppercase tracking-wider text-slate-500">Reference Number</div>
                            <div class="text-slate-900 font-medium mt-1">
                                {{ $selectedPayment->payment?->reference_number ?? $selectedPayment->reference_number ?? '—' }}
                            </div>
                        </div>
                    @endif
                    <div class="rounded-xl bg-slate-50 border border-slate-200 p-3">
                        <div class="text-xs font-semibold uppercase tracking-wider text-slate-500">Surcharge</div>
                        <div class="text-slate-900 font-medium mt-1">₱{{ number_format($selectedPayment->transaction?->surcharge_total ?? $selectedPayment->surcharge_total ?? 0, 2) }}</div>
                    </div>
                    <div class="rounded-xl bg-slate-50 border border-slate-200 p-3">
                        <div class="text-xs font-semibold uppercase tracking-wider text-slate-500">Interest Rate</div>
                        <div class="text-slate-900 font-medium mt-1">{{ number_format($selectedPayment->transaction?->interest_rate ?? $selectedPayment->interest_rate ?? 0, 2) }}%</div>
                    </div>
                    <div class="rounded-xl bg-slate-50 border border-slate-200 p-3">
                        <div class="text-xs font-semibold uppercase tracking-wider text-slate-500">Duration</div>
                        <div class="text-slate-900 font-medium mt-1">{{ $selectedPayment->transaction?->duration_days ?? $selectedPayment->duration_days ?? 0 }} days</div>
                    </div>
                    <div class="rounded-xl bg-emerald-50 border border-emerald-200 p-3">
                        <div class="text-xs font-semibold uppercase tracking-wider text-emerald-600">Total Amount</div>
                        <div class="text-emerald-700 font-bold text-lg mt-1">₱{{ number_format($selectedPayment->transaction?->total_amount ?? $selectedPayment->total_amount ?? 0, 2) }}</div>
                    </div>
                    <div class="rounded-xl bg-slate-50 border border-slate-200 p-3">
                        <div class="text-xs font-semibold uppercase tracking-wider text-slate-500">
                            {{ $isGcash ? 'Payment sent' : 'Cash Given' }}
                        </div>
                        <div class="text-slate-900 font-medium mt-1">₱{{ number_format($selectedPayment->payment?->cash_given ?? $selectedPayment->cash_given ?? 0, 2) }}</div>
                    </div>
                    <div class="rounded-xl bg-slate-50 border border-slate-200 p-3">
                        <div class="text-xs font-semibold uppercase tracking-wider text-slate-500">Change</div>
                        <div class="text-slate-900 font-medium mt-1">₱{{ number_format($selectedPayment->payment?->change ?? $selectedPayment->change ?? 0, 2) }}</div>
                    </div>
                    <div class="rounded-xl bg-slate-50 border border-slate-200 p-3">
                        <div class="text-xs font-semibold uppercase tracking-wider text-slate-500">Status</div>
                        <div class="mt-1">
                            <span class="inline-flex items-center px-2.5 py-1 rounded-full text-xs font-semibold bg-emerald-100 text-emerald-700">{{ $selectedPayment->transaction?->status ?? $selectedPayment->status ?? 'PAID' }}</span>
                        </div>
                    </div>
                </div>
            @else
                <div class="p-12 text-center text-sm text-slate-500">No payment data available.</div>
            @endif
        </div>
    </x-modal>

    {{-- Delete All Modal --}}
    <x-modal wire:model="isDeleteAllModalOpen" maxWidth="md">
        <div class="p-5 sm:p-6">
            <div class="flex items-start gap-4">
                <div class="flex-shrink-0 w-11 h-11 rounded-full bg-rose-100 flex items-center justify-center">
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 text-rose-600" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z" /></svg>
                </div>
                <div>
                    <h2 class="text-base font-semibold text-slate-900">Delete All Transaction History?</h2>
                    <p class="text-sm text-slate-500 mt-1">This cannot be undone.</p>
                </div>
            </div>
            <div class="flex flex-col-reverse sm:flex-row sm:justify-end gap-2 sm:gap-3 mt-6">
                <button type="button" class="w-full sm:w-auto rounded-xl border border-slate-300 bg-white px-4 py-3 text-sm font-semibold text-slate-700 active:bg-slate-100 transition" wire:click="cancelDeleteAll">Cancel</button>
                <button type="button" class="w-full sm:w-auto rounded-xl bg-rose-600 px-4 py-3 text-sm font-semibold text-white shadow-sm hover:bg-rose-700 active:scale-[0.98] transition" wire:click="deleteAll">Delete All</button>
            </div>
        </div>
    </x-modal>

    {{-- Group Details / Receipt Modal --}}
    <x-modal wire:model="showGroupDetailsModal" maxWidth="2xl">
        <div class="p-4 sm:p-6">
            @if(!empty($receiptData))
                @include('components.receipt', ['receiptData' => $receiptData])
            @else
                <p class="text-sm text-slate-500">No data.</p>
            @endif
        </div>
    </x-modal>

    {{-- Export Timeframe Modal --}}
    @if($showExportModal)
    <div class="fixed inset-0 z-50 flex items-end sm:items-center justify-center sm:p-4">
        <div class="fixed inset-0 bg-slate-900/50 backdrop-blur-sm" wire:click="$set('showExportModal', false)"></div>
        <div class="relative w-full sm:max-w-md bg-white rounded-t-3xl sm:rounded-2xl shadow-2xl border border-slate-200 overflow-hidden"
             style="padding-bottom: env(safe-area-inset-bottom);">
            <div class="w-12 h-1.5 bg-slate-200 rounded-full mx-auto mt-3 sm:hidden"></div>
            <div class="px-5 sm:px-6 py-4 border-b border-slate-100">
                <h2 class="text-base font-semibold text-slate-900">Export Transaction History</h2>
                <p class="text-xs text-slate-500 mt-0.5">Select timeframe for export</p>
            </div>
            <div class="p-5 sm:p-6 space-y-3">
                <label class="flex items-center gap-3 p-4 rounded-xl border border-slate-200 active:bg-slate-50 cursor-pointer transition">
                    <input type="radio" value="all" wire:model="exportTimeframe" class="rounded-full text-indigo-600 focus:ring-indigo-500 w-5 h-5" />
                    <span class="text-sm font-medium text-slate-700">All Time</span>
                </label>
                <label class="flex items-center gap-3 p-4 rounded-xl border border-slate-200 active:bg-slate-50 cursor-pointer transition">
                    <input type="radio" value="today" wire:model="exportTimeframe" class="rounded-full text-indigo-600 focus:ring-indigo-500 w-5 h-5" />
                    <span class="text-sm font-medium text-slate-700">Today</span>
                </label>
                <label class="flex items-center gap-3 p-4 rounded-xl border border-slate-200 active:bg-slate-50 cursor-pointer transition">
                    <input type="radio" value="last_7_days" wire:model="exportTimeframe" class="rounded-full text-indigo-600 focus:ring-indigo-500 w-5 h-5" />
                    <span class="text-sm font-medium text-slate-700">Last 7 Days</span>
                </label>
                <label class="flex items-center gap-3 p-4 rounded-xl border border-slate-200 active:bg-slate-50 cursor-pointer transition">
                    <input type="radio" value="last_30_days" wire:model="exportTimeframe" class="rounded-full text-indigo-600 focus:ring-indigo-500 w-5 h-5" />
                    <span class="text-sm font-medium text-slate-700">Last 30 Days</span>
                </label>
            </div>
            <div class="px-5 sm:px-6 py-4 bg-slate-50 border-t border-slate-100 flex flex-col-reverse sm:flex-row sm:justify-end gap-2 sm:gap-3">
                <button type="button" wire:click="$set('showExportModal', false)" class="w-full sm:w-auto rounded-xl border border-slate-300 bg-white px-4 py-3 text-sm font-semibold text-slate-700 active:bg-slate-100 transition">Cancel</button>
                <button type="button" wire:click="exportWithTimeframe" class="w-full sm:w-auto rounded-xl bg-indigo-600 px-4 py-3 text-sm font-semibold text-white shadow-sm hover:bg-indigo-700 active:scale-[0.98] transition">Export</button>
            </div>
        </div>
    </div>
    @endif
</div>