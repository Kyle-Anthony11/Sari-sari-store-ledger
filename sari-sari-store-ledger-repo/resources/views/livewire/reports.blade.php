<div class="space-y-4 sm:space-y-6">
    <div class="flex flex-col gap-3">
        <div>
            <h1 class="text-xl sm:text-2xl font-bold text-slate-900 tracking-tight"></h1>
            <p class="text-xs sm:text-sm text-slate-500 mt-1"></p>
        </div>
        <button wire:click="openExportModal"
                class="inline-flex items-center justify-center gap-2 rounded-xl bg-emerald-600 px-4 py-2.5 text-sm font-semibold text-white shadow-sm hover:bg-emerald-700 hover:shadow transition-all w-full sm:w-auto sm:self-end">
            <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-4l-4 4m0 0l-4-4m4 4V4" /></svg>
            Export
        </button>
    </div>

    {{-- Balance Due --}}
    <div class="bg-white border border-slate-200 rounded-2xl shadow-sm overflow-hidden">
        <div class="px-4 sm:px-5 py-4 border-b border-slate-100 flex flex-col sm:flex-row sm:items-center sm:justify-between gap-3">
            <div>
                <h2 class="text-base font-bold text-slate-900">Balance Due by Customer</h2>
                <p class="text-xs text-slate-500 mt-0.5">Outstanding receivables per customer</p>
            </div>
            <select wire:model.live="outstandingDateFilter" class="w-full sm:w-auto rounded-xl border border-slate-300 bg-white px-3.5 py-2.5 text-sm shadow-sm focus:border-indigo-500 focus:ring-2 focus:ring-indigo-500/20 transition">
                <option value="all">All Time</option>
                <option value="today">Today</option>
                <option value="last_7_days">Last 7 Days</option>
                <option value="last_30_days">Last 30 Days</option>
            </select>
        </div>

        {{-- Mobile cards --}}
        <div class="md:hidden divide-y divide-slate-100">
            @forelse($customers as $customer)
                <div class="p-4 space-y-2">
                    <div class="flex items-start justify-between gap-3">
                        <div class="font-semibold text-slate-900 truncate min-w-0">{{ $customer->name }}</div>
                        <span class="inline-flex items-center px-2.5 py-1 rounded-full text-xs font-semibold flex-shrink-0
                            @if($customer->risk_level === 'HIGH') bg-rose-100 text-rose-700
                            @elseif($customer->risk_level === 'MEDIUM') bg-amber-100 text-amber-700
                            @else bg-emerald-100 text-emerald-700 @endif">
                            {{ $customer->risk_level ?? 'LOW' }}
                        </span>
                    </div>
                    <div class="grid grid-cols-3 gap-2 text-xs">
                        <div>
                            <div class="text-slate-500">Balance</div>
                            <div class="font-semibold text-slate-900 mt-0.5">₱{{ number_format($customer->total_balance, 2) }}</div>
                        </div>
                        <div>
                            <div class="text-slate-500">Limit</div>
                            <div class="font-semibold text-slate-900 mt-0.5">₱{{ number_format($customer->credit_limit ?? 0, 2) }}</div>
                        </div>
                        <div>
                            <div class="text-slate-500">Days Since</div>
                            <div class="font-semibold text-slate-900 mt-0.5">{{ $customer->days_since_last_payment ?? 0 }}</div>
                        </div>
                    </div>
                </div>
            @empty
                <div class="py-12 text-center text-sm text-slate-500">No outstanding balances found.</div>
            @endforelse
        </div>

        {{-- Desktop table --}}
        <div class="hidden md:block overflow-x-auto">
            <table class="w-full text-sm">
                <thead class="bg-slate-50/80 border-b border-slate-100">
                    <tr class="text-left">
                        <th class="py-3 px-5 text-xs font-semibold uppercase tracking-wider text-slate-500">Customer</th>
                        <th class="py-3 px-5 text-xs font-semibold uppercase tracking-wider text-slate-500">Total Balance</th>
                        <th class="py-3 px-5 text-xs font-semibold uppercase tracking-wider text-slate-500">Credit Limit</th>
                        <th class="py-3 px-5 text-xs font-semibold uppercase tracking-wider text-slate-500">Risk</th>
                        <th class="py-3 px-5 text-xs font-semibold uppercase tracking-wider text-slate-500">Days Since Last Payment</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100">
                    @forelse($customers as $customer)
                        <tr class="hover:bg-slate-50/70 transition-colors">
                            <td class="py-3 px-5 font-medium text-slate-900">{{ $customer->name }}</td>
                            <td class="py-3 px-5 font-semibold text-slate-900">₱{{ number_format($customer->total_balance, 2) }}</td>
                            <td class="py-3 px-5 text-slate-600">₱{{ number_format($customer->credit_limit ?? 0, 2) }}</td>
                            <td class="py-3 px-5">
                                <span class="inline-flex items-center px-2.5 py-1 rounded-full text-xs font-semibold
                                    @if($customer->risk_level === 'HIGH') bg-rose-100 text-rose-700
                                    @elseif($customer->risk_level === 'MEDIUM') bg-amber-100 text-amber-700
                                    @else bg-emerald-100 text-emerald-700 @endif">
                                    {{ $customer->risk_level ?? 'LOW' }}
                                </span>
                            </td>
                            <td class="py-3 px-5 text-slate-600">{{ $customer->days_since_last_payment ?? 0 }}</td>
                        </tr>
                    @empty
                        <tr><td colspan="5" class="py-12 text-center text-sm text-slate-500">No outstanding balances found.</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        @if($customers->hasPages())
            <div class="px-4 sm:px-5 py-4 border-t border-slate-100 flex justify-center">{{ $customers->links() }}</div>
        @endif
    </div>

    {{-- Aging --}}
    <div class="bg-white border border-slate-200 rounded-2xl shadow-sm overflow-hidden">
        <div class="px-4 sm:px-5 py-4 border-b border-slate-100 flex flex-col sm:flex-row sm:items-center sm:justify-between gap-3">
            <div>
                <h2 class="text-base font-bold text-slate-900">Aging of Receivables</h2>
                <p class="text-xs text-slate-500 mt-0.5">Overdue transactions by time bucket</p>
            </div>
            <select wire:model.live="agingDateFilter" class="w-full sm:w-auto rounded-xl border border-slate-300 bg-white px-3.5 py-2.5 text-sm shadow-sm focus:border-indigo-500 focus:ring-2 focus:ring-indigo-500/20 transition">
                <option value="all">All Time</option>
                <option value="today">Today</option>
                <option value="last_7_days">Last 7 Days</option>
                <option value="last_30_days">Last 30 Days</option>
            </select>
        </div>
        <div class="overflow-x-auto">
            <table class="w-full text-sm">
                <thead class="bg-slate-50/80 border-b border-slate-100">
                    <tr class="text-left">
                        <th class="py-3 px-4 sm:px-5 text-xs font-semibold uppercase tracking-wider text-slate-500">Overdue Bucket</th>
                        <th class="py-3 px-4 sm:px-5 text-xs font-semibold uppercase tracking-wider text-slate-500 text-center">Txns</th>
                        <th class="py-3 px-4 sm:px-5 text-xs font-semibold uppercase tracking-wider text-slate-500 text-right">Total Amount</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100">
                    @foreach($agingBuckets as $bucket => $data)
                        <tr class="hover:bg-slate-50/70 transition-colors">
                            <td class="py-3 px-4 sm:px-5">
                                <span class="inline-flex items-center px-2.5 py-1 rounded-full text-xs font-semibold
                                    @if($bucket === '0-30 days') bg-amber-100 text-amber-700
                                    @elseif($bucket === '31-60 days') bg-orange-100 text-orange-700
                                    @else bg-rose-100 text-rose-700 @endif">
                                    {{ $bucket }}
                                </span>
                            </td>
                            <td class="py-3 px-4 sm:px-5 text-slate-700 text-center">{{ $data['count'] }}</td>
                            <td class="py-3 px-4 sm:px-5 font-semibold text-slate-900 text-right">₱{{ number_format($data['total'], 2) }}</td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    </div>

    {{-- Export Modal --}}
    @if($showExportModal)
        <div class="fixed inset-0 z-50 flex items-center justify-center p-4">
            <div class="fixed inset-0 bg-slate-900/50 backdrop-blur-sm" wire:click="closeExportModal"></div>

            <div class="relative w-full max-w-md bg-white rounded-2xl shadow-2xl border border-slate-200 overflow-hidden">
                <div class="px-5 py-4 border-b border-slate-100">
                    <h2 class="text-base font-semibold text-slate-900">Export Reports</h2>
                    <p class="text-xs text-slate-500 mt-0.5">Choose export format</p>
                </div>

                <div class="p-5 space-y-3">
                    <button wire:click="exportCsv"
                            class="w-full rounded-xl bg-emerald-600 px-4 py-3 text-sm font-semibold text-white hover:bg-emerald-700 transition">
                        Export as CSV
                    </button>

                    <button wire:click="exportPdf"
                            class="w-full rounded-xl bg-indigo-600 px-4 py-3 text-sm font-semibold text-white hover:bg-indigo-700 transition">
                        Export as PDF
                    </button>
                </div>

                <div class="px-5 py-4 bg-slate-50 border-t border-slate-100 flex justify-end">
                    <button wire:click="closeExportModal"
                            class="rounded-xl border border-slate-300 bg-white px-4 py-2 text-sm font-semibold text-slate-700 hover:bg-slate-50 transition">
                        Cancel
                    </button>
                </div>
            </div>
        </div>
    @endif
</div>