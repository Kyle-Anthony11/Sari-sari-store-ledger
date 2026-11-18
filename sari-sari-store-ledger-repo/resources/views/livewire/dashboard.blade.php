<div>
    {{-- Header --}}
    <div class="flex flex-col gap-3 mb-4 sm:mb-6">
        <div>
        </div>
        <div class="flex flex-col sm:flex-row gap-2">
            <select wire:model.live="dateFilter" class="w-full sm:w-auto rounded-xl border border-slate-300 bg-white px-3.5 py-3 text-sm shadow-sm focus:border-indigo-500 focus:ring-2 focus:ring-indigo-500/20 transition">
                <option value="all">All Time</option>
                <option value="today">Today</option>
                <option value="this_week">This Week</option>
                <option value="this_month">This Month</option>
                <option value="past_months">Past Months</option>
            </select>
            @if($dateFilter === 'past_months')
                @php
                    $months = [
                        '01'=>'January','02'=>'February','03'=>'March','04'=>'April','05'=>'May','06'=>'June',
                        '07'=>'July','08'=>'August','09'=>'September','10'=>'October','11'=>'November','12'=>'December'
                    ];
                @endphp
                <select wire:model.live="monthFilter" class="w-full sm:w-auto rounded-xl border border-slate-300 bg-white px-3.5 py-3 text-sm shadow-sm focus:border-indigo-500 focus:ring-2 focus:ring-indigo-500/20 transition">
                    @foreach($months as $value => $label)
                        <option value="{{ $value }}">{{ $label }}</option>
                    @endforeach
                </select>
                <select wire:model.live="yearFilter" class="w-full sm:w-auto rounded-xl border border-slate-300 bg-white px-3.5 py-3 text-sm shadow-sm focus:border-indigo-500 focus:ring-2 focus:ring-indigo-500/20 transition">
                    @foreach(range(2015, date('Y')) as $year)
                        <option value="{{ $year }}">{{ $year }}</option>
                    @endforeach
                </select>
            @endif
        </div>
    </div>

    {{-- Metric Cards --}}
    <div class="grid grid-cols-2 lg:grid-cols-4 gap-2.5 sm:gap-4">

        {{-- Total Receivables --}}
        <div class="relative" x-data="{ show: false }" @mouseenter="show = true" @mouseleave="show = false">
            <div class="bg-white border border-slate-200 rounded-2xl shadow-sm p-3.5 sm:p-5 cursor-help">
                <div class="flex items-center justify-between">
                    <div class="text-[10px] sm:text-xs font-semibold uppercase tracking-wider text-slate-500 leading-tight">Total<br class="sm:hidden">Receivables</div>
                    <div class="w-7 h-7 sm:w-8 sm:h-8 rounded-lg bg-blue-50 flex items-center justify-center flex-shrink-0">
                        <svg xmlns="http://www.w3.org/2000/svg" class="h-3.5 w-3.5 sm:h-4 sm:w-4 text-blue-600" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                        </svg>
                    </div>
                </div>
                <div class="text-base sm:text-2xl font-bold text-slate-900 mt-2 sm:mt-3 truncate">₱{{ number_format($totalReceivables ?? 0, 2) }}</div>
            </div>
            <div x-show="show" x-cloak x-transition
                 class="absolute z-30 top-full left-0 right-0 mt-2 bg-white border border-slate-200 rounded-xl shadow-xl p-3 max-h-64 overflow-y-auto">
                <div class="text-xs font-semibold uppercase tracking-wider text-slate-500 mb-2">Customers with Outstanding</div>
                @forelse($receivablesBreakdown as $item)
                    <div class="flex justify-between py-1.5 text-xs border-b border-slate-100 last:border-0">
                        <span class="text-slate-600 truncate pr-2">{{ $item['name'] }}</span>
                        <span class="font-semibold text-slate-900 whitespace-nowrap">₱{{ number_format($item['amount'], 2) }}</span>
                    </div>
                @empty
                    <p class="text-xs text-slate-500 text-center py-2">No data</p>
                @endforelse
            </div>
        </div>

        {{-- Overdue Amount --}}
        <div class="relative" x-data="{ show: false }" @mouseenter="show = true" @mouseleave="show = false">
            <div class="bg-white border border-slate-200 rounded-2xl shadow-sm p-3.5 sm:p-5 cursor-help">
                <div class="flex items-center justify-between">
                    <div class="text-[10px] sm:text-xs font-semibold uppercase tracking-wider text-slate-500 leading-tight">Overdue<br class="sm:hidden">Amount</div>
                    <div class="w-7 h-7 sm:w-8 sm:h-8 rounded-lg bg-rose-50 flex items-center justify-center flex-shrink-0">
                        <svg xmlns="http://www.w3.org/2000/svg" class="h-3.5 w-3.5 sm:h-4 sm:w-4 text-rose-600" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                        </svg>
                    </div>
                </div>
                <div class="text-base sm:text-2xl font-bold text-rose-600 mt-2 sm:mt-3 truncate">₱{{ number_format($overdueAccounts ?? 0, 2) }}</div>
            </div>
            <div x-show="show" x-cloak x-transition
                 class="absolute z-30 top-full left-0 right-0 mt-2 bg-white border border-slate-200 rounded-xl shadow-xl p-3 max-h-64 overflow-y-auto">
                <div class="text-xs font-semibold uppercase tracking-wider text-slate-500 mb-2">Customers with Overdue</div>
                @forelse($overdueBreakdown as $item)
                    <div class="flex justify-between py-1.5 text-xs border-b border-slate-100 last:border-0">
                        <span class="text-slate-600 truncate pr-2">{{ $item['name'] }}</span>
                        <span class="font-semibold text-rose-700 whitespace-nowrap">₱{{ number_format($item['amount'], 2) }}</span>
                    </div>
                @empty
                    <p class="text-xs text-slate-500 text-center py-2">No data</p>
                @endforelse
            </div>
        </div>

        {{-- Total Paid --}}
        <div class="relative" x-data="{ show: false }" @mouseenter="show = true" @mouseleave="show = false">
            <div class="bg-white border border-slate-200 rounded-2xl shadow-sm p-3.5 sm:p-5 cursor-help">
                <div class="flex items-center justify-between">
                    <div class="text-[10px] sm:text-xs font-semibold uppercase tracking-wider text-slate-500 leading-tight">Total<br class="sm:hidden">Paid</div>
                    <div class="w-7 h-7 sm:w-8 sm:h-8 rounded-lg bg-emerald-50 flex items-center justify-center flex-shrink-0">
                        <svg xmlns="http://www.w3.org/2000/svg" class="h-3.5 w-3.5 sm:h-4 sm:w-4 text-emerald-600" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" />
                        </svg>
                    </div>
                </div>
                <div class="text-base sm:text-2xl font-bold text-emerald-600 mt-2 sm:mt-3 truncate">₱{{ number_format($totalPaid ?? 0, 2) }}</div>
            </div>
            <div x-show="show" x-cloak x-transition
                 class="absolute z-30 top-full left-0 right-0 mt-2 bg-white border border-slate-200 rounded-xl shadow-xl p-3 max-h-64 overflow-y-auto">
                <div class="text-xs font-semibold uppercase tracking-wider text-slate-500 mb-2">Payments Received</div>
                @forelse($paidBreakdown as $item)
                    <div class="flex justify-between py-1.5 text-xs border-b border-slate-100 last:border-0">
                        <span class="text-slate-600 truncate pr-2">{{ $item['name'] }}</span>
                        <span class="font-semibold text-emerald-700 whitespace-nowrap">₱{{ number_format($item['amount'], 2) }}</span>
                    </div>
                @empty
                    <p class="text-xs text-slate-500 text-center py-2">No data</p>
                @endforelse
            </div>
        </div>

        {{-- High Risk Customers --}}
        <div class="relative" x-data="{ show: false }" @mouseenter="show = true" @mouseleave="show = false">
            <div class="bg-white border border-slate-200 rounded-2xl shadow-sm p-3.5 sm:p-5 cursor-help">
                <div class="flex items-center justify-between">
                    <div class="text-[10px] sm:text-xs font-semibold uppercase tracking-wider text-slate-500 leading-tight">High Risk<br class="sm:hidden">Customers</div>
                    <div class="w-7 h-7 sm:w-8 sm:h-8 rounded-lg bg-amber-50 flex items-center justify-center flex-shrink-0">
                        <svg xmlns="http://www.w3.org/2000/svg" class="h-3.5 w-3.5 sm:h-4 sm:w-4 text-amber-600" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0z" />
                        </svg>
                    </div>
                </div>
                <div class="text-base sm:text-2xl font-bold text-slate-900 mt-2 sm:mt-3">{{ $highRiskCustomers ?? 0 }}</div>
            </div>
            <div x-show="show" x-cloak x-transition
                 class="absolute z-30 top-full left-0 right-0 mt-2 bg-white border border-slate-200 rounded-xl shadow-xl p-3 max-h-64 overflow-y-auto">
                <div class="text-xs font-semibold uppercase tracking-wider text-slate-500 mb-2">High Risk Customers</div>
                @forelse($highRiskCustomersList as $c)
                    <div class="py-1.5 text-xs border-b border-slate-100 last:border-0">
                        <span class="text-slate-700 font-medium">{{ $c['name'] }}</span>
                    </div>
                @empty
                    <p class="text-xs text-slate-500 text-center py-2">No high risk customers</p>
                @endforelse
            </div>
        </div>

    </div>

    {{-- Monthly & Risk Charts --}}
    <div class="mt-4 sm:mt-6 grid grid-cols-1 lg:grid-cols-2 gap-4 sm:gap-6">

        {{-- Bar Chart (Monthly ⇄ Weekly drill-down) --}}
        <div class="bg-white border border-slate-200 rounded-2xl shadow-sm p-4 sm:p-6">
            <div class="flex items-center justify-between gap-2 mb-3 sm:mb-4">
                <div class="flex items-center gap-2 min-w-0">
                    <h2 class="text-sm sm:text-base font-semibold text-slate-900 truncate">
                        @if(($monthlyChartData['mode'] ?? 'monthly') === 'weekly')
                            Weekly — {{ $monthlyChartData['monthLabel'] ?? '' }}
                        @else
                            Monthly Overview
                        @endif
                    </h2>
                </div>
                <span class="text-[10px] sm:text-xs font-semibold px-2.5 py-1 rounded-full flex-shrink-0
                    {{ ($monthlyChartData['mode'] ?? 'monthly') === 'weekly' ? 'bg-amber-50 text-amber-700' : 'bg-indigo-50 text-indigo-700' }}">
                    @if(($monthlyChartData['mode'] ?? 'monthly') === 'weekly')
                        Weekly
                    @else
                        Click a bar
                    @endif
                </span>
            </div>

            <div class="relative h-64 sm:h-72">
                {{-- Back arrow overlay — visible only in weekly (drilled) view --}}
                @if($chartDrillMonth !== null)
                    <button type="button"
                            wire:click="resetChartDrill"
                            title="Back to monthly view"
                            aria-label="Back to monthly view"
                            class="absolute top-2 left-2 z-20 inline-flex items-center justify-center w-9 h-9 sm:w-10 sm:h-10 rounded-full bg-white border border-slate-200 shadow-md hover:bg-slate-50 hover:border-slate-300 active:scale-95 transition">
                        <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4 text-slate-700" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M10 19l-7-7m0 0l7-7m-7 7h18" />
                        </svg>
                    </button>
                @endif

                <div class="h-full w-full" wire:ignore>
                    <canvas id="monthlyChart"></canvas>
                </div>
            </div>
        </div>

        {{-- Customer Risk Pie Chart --}}
        <div class="bg-white border border-slate-200 rounded-2xl shadow-sm p-4 sm:p-6">
            <div class="flex items-center justify-between mb-3 sm:mb-4">
                <h2 class="text-sm sm:text-base font-semibold text-slate-900">Customer Risk Distribution</h2>
                <span class="text-[10px] sm:text-xs font-semibold px-2.5 py-1 rounded-full bg-rose-50 text-rose-700">
                    Risk
                </span>
            </div>
            <div class="h-64 sm:h-72" wire:ignore>
                <canvas id="riskChart"></canvas>
            </div>
        </div>

    </div>

    {{-- Next Expected Payment --}}
    <div class="mt-4 sm:mt-6 bg-gradient-to-br from-indigo-500 to-blue-600 rounded-2xl shadow-lg shadow-indigo-500/20 p-4 sm:p-6 text-white relative overflow-hidden">
        <div class="absolute top-0 right-0 w-40 sm:w-64 h-40 sm:h-64 bg-white/5 rounded-full -mr-20 sm:-mr-32 -mt-20 sm:-mt-32"></div>
        <div class="relative">
            <div class="flex items-center gap-2 text-[10px] sm:text-xs font-semibold uppercase tracking-wider text-indigo-100">
                <svg xmlns="http://www.w3.org/2000/svg" class="h-3.5 w-3.5 sm:h-4 sm:w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z" />
                </svg>
                Next Expected Payment
            </div>
            <div class="mt-3 flex flex-col gap-3">
                <div class="min-w-0">
                    <div class="text-lg sm:text-2xl font-bold">{{ $nextExpectedPayment['label'] ?? 'Today' }}</div>
                    @if(!empty($nextExpectedPayment['topCustomerNames']))
                        <div class="text-xs sm:text-sm text-indigo-100 mt-1 truncate">
                            {{ implode(', ', $nextExpectedPayment['topCustomerNames']) }}
                            @if(($nextExpectedPayment['othersCount'] ?? 0) > 0)
                                <button type="button"
                                        wire:click="$set('isNextExpectedModalOpen', true)"
                                        class="text-indigo-100 hover:text-white underline decoration-indigo-200 hover:decoration-white underline-offset-2 font-semibold transition">
                                    + {{ $nextExpectedPayment['othersCount'] }} more
                                </button>
                            @endif
                        </div>
                    @endif
                </div>
                <div class="flex items-end justify-between sm:block sm:text-right pt-2 border-t border-white/10 sm:border-0 sm:pt-0">
                    <div class="text-[10px] sm:text-xs text-indigo-100">Estimated</div>
                    <div class="text-xl sm:text-3xl font-bold">₱{{ number_format($nextExpectedPayment['amountEstimate'] ?? 0, 2) }}</div>
                </div>
            </div>
        </div>
    </div>

    {{-- Risk Watchlist & Recent Transactions --}}
    <div class="mt-4 sm:mt-6 grid grid-cols-1 lg:grid-cols-2 gap-4 sm:gap-6">
        <div class="bg-white border border-slate-200 rounded-2xl shadow-sm p-4 sm:p-6">
            <div class="flex items-center justify-between mb-3 sm:mb-4">
                <h2 class="text-sm sm:text-base font-semibold text-slate-900">Risk Watchlist</h2>
                <span class="text-[10px] sm:text-xs font-semibold px-2.5 py-1 rounded-full bg-rose-50 text-rose-700">Top {{ count($riskWatchlist ?? []) }}</span>
            </div>
            <ul class="space-y-2 sm:space-y-3">
                @forelse(($riskWatchlist ?? []) as $r)
                    <li class="flex items-center justify-between gap-3 p-3 rounded-xl bg-slate-50 active:bg-slate-100 transition-colors">
                        <div class="flex items-center gap-3 min-w-0 flex-1">
                            <div class="w-9 h-9 rounded-full bg-gradient-to-br from-indigo-500 to-blue-600 flex items-center justify-center flex-shrink-0">
                                <span class="text-xs font-bold text-white">{{ strtoupper(substr($r['name'] ?? '?', 0, 1)) }}</span>
                            </div>
                            <div class="min-w-0 flex-1">
                                <div class="font-medium text-slate-900 text-sm truncate">{{ $r['name'] ?? '' }}</div>
                                @if(!empty($r['topReasons']))
                                    <div class="text-xs text-slate-500 truncate">{{ $r['topReasons'][0] ?? '' }}</div>
                                @endif
                            </div>
                        </div>
                        <span class="text-xs font-semibold px-2.5 py-1 rounded-full bg-slate-200 text-slate-700 flex-shrink-0">{{ $r['riskScore'] ?? 0 }}</span>
                    </li>
                @empty
                    <li class="text-sm text-slate-500 text-center py-8">No risk indicators yet. All customers appear low-risk.</li>
                @endforelse
            </ul>
        </div>

        <div class="bg-white border border-slate-200 rounded-2xl shadow-sm p-4 sm:p-6">
            <div class="flex items-center justify-between mb-3 sm:mb-4">
                <h2 class="text-sm sm:text-base font-semibold text-slate-900">Recent Transactions</h2>
                <span class="text-[10px] sm:text-xs font-semibold px-2.5 py-1 rounded-full bg-blue-50 text-blue-700">Latest</span>
            </div>
            <ul class="space-y-2 sm:space-y-3">
                @forelse(($recentTransactions ?? []) as $t)
                    <li class="flex items-center justify-between gap-3 p-3 rounded-xl bg-slate-50 active:bg-slate-100 transition-colors">
                        <div class="flex items-center gap-3 min-w-0 flex-1">
                            <div class="w-9 h-9 rounded-full bg-gradient-to-br from-slate-400 to-slate-600 flex items-center justify-center flex-shrink-0">
                                <span class="text-xs font-bold text-white">{{ strtoupper(substr($t->customer?->name ?? '?', 0, 1)) }}</span>
                            </div>
                            <div class="min-w-0 flex-1">
                                <div class="font-medium text-slate-900 text-sm truncate">{{ $t->customer?->name ?? 'Unknown' }}</div>
                                <div class="text-xs text-slate-500 truncate">{{ $t->item_name ?? 'Item' }}</div>
                            </div>
                        </div>
                        <div class="text-right flex-shrink-0">
                            <div class="text-sm font-semibold text-slate-900">₱{{ number_format($t->total_amount ?? 0, 2) }}</div>
                            <span class="inline-block text-[10px] sm:text-xs font-semibold px-2 py-0.5 rounded-full
                                @if(($t->status ?? '') === 'PAID') bg-emerald-100 text-emerald-700
                                @elseif(($t->status ?? '') === 'OVERDUE') bg-rose-100 text-rose-700
                                @else bg-amber-100 text-amber-700 @endif">
                                {{ $t->status ?? '' }}
                            </span>
                        </div>
                    </li>
                @empty
                    <li class="text-sm text-slate-500 text-center py-8">No recent transactions.</li>
                @endforelse
            </ul>
        </div>
    </div>

    {{-- Next Expected Payment Details Modal --}}
    @if($isNextExpectedModalOpen)
    <div class="fixed inset-0 z-[60] flex items-end sm:items-center justify-center sm:p-4">
        <div class="fixed inset-0 bg-slate-900/50 backdrop-blur-sm" wire:click="$set('isNextExpectedModalOpen', false)"></div>
        <div class="relative w-full sm:max-w-md bg-white rounded-t-3xl sm:rounded-2xl shadow-2xl border border-slate-200 overflow-hidden flex flex-col max-h-[85dvh]"
             style="padding-bottom: env(safe-area-inset-bottom);">

            <div class="w-12 h-1.5 bg-slate-200 rounded-full mx-auto mt-3 sm:hidden"></div>

            <div class="px-5 sm:px-6 py-4 border-b border-slate-100 flex items-center justify-between flex-shrink-0">
                <div class="min-w-0">
                    <h2 class="text-base font-semibold text-slate-900">Upcoming Payments</h2>
                    <p class="text-xs text-slate-500 mt-0.5 truncate">{{ $nextExpectedPayment['label'] ?? '' }}</p>
                </div>
                <button type="button" wire:click="$set('isNextExpectedModalOpen', false)"
                        class="text-slate-400 hover:text-slate-600 transition p-1 flex-shrink-0" aria-label="Close">
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12" />
                    </svg>
                </button>
            </div>

            <div class="p-5 overflow-y-auto flex-1">
                @php $detailTotal = 0; @endphp
                <ul class="space-y-2">
                    @forelse($nextExpectedPayment['details'] ?? [] as $detail)
                        @php $detailTotal += $detail['amount']; @endphp
                        <li class="flex items-center justify-between gap-3 p-3 rounded-xl bg-slate-50">
                            <span class="text-sm font-medium text-slate-900 truncate">{{ $detail['name'] }}</span>
                            <span class="text-sm font-semibold text-slate-900 whitespace-nowrap">₱{{ number_format($detail['amount'], 2) }}</span>
                        </li>
                    @empty
                        <li class="text-sm text-slate-500 text-center py-4">No upcoming payments.</li>
                    @endforelse
                </ul>
            </div>

            <div class="px-5 sm:px-6 py-4 bg-slate-50 border-t border-slate-100 flex items-center justify-between flex-shrink-0">
                <span class="text-sm font-semibold text-slate-700">Total</span>
                <span class="text-lg font-bold text-slate-900">₱{{ number_format($detailTotal, 2) }}</span>
            </div>
        </div>
    </div>
    @endif

    {{-- Charts: Chart.js + Livewire wiring --}}
    @once
        <script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.0/dist/chart.umd.min.js"></script>
    @endonce

        @script
    <script>
    (function () {
        // ── Bootstrap guard: only run once per canvas lifetime ───────────
        const monthlyCanvas = document.getElementById('monthlyChart');
        const riskCanvas    = document.getElementById('riskChart');

        if (!monthlyCanvas || !riskCanvas) return;

        // Setting a data attribute on a <canvas> is safe — DOM nodes are
        // never reactively instrumented by Alpine/Livewire.
        if (monthlyCanvas.dataset.dashboardChartsInit === '1') return;
        monthlyCanvas.dataset.dashboardChartsInit = '1';

        const PESO = (v) => '₱' + Number(v || 0).toLocaleString('en-PH', {
            minimumFractionDigits: 2,
            maximumFractionDigits: 2,
        });

        // ── Plain, non-reactive closure state ────────────────────────────
        // NEVER put $wire or any Livewire proxy onto window or into Chart.js.
        let wireRef = $wire;
        const state = { drilled: false };

        // ── Cursor plugin: pointer only when we can drill ────────────────
        const drillCursorPlugin = {
            id: 'drillCursor',
            beforeEvent(chart, args) {
                const e = args.event;
                if (!e || !e.native) return;
                if (state.drilled) {
                    e.native.target.style.cursor = 'default';
                    return;
                }
                const els = chart.getElementsAtEventForMode(
                    e, 'nearest', { intersect: true }, true
                );
                e.native.target.style.cursor = els.length ? 'pointer' : 'default';
            },
        };

        const buildMonthly = (canvas, data) => new Chart(canvas, {
            type: 'bar',
            data: {
                labels: data.labels,
                datasets: [
                    { label: 'Total Paid',        data: data.paid,        backgroundColor: 'rgba(16,185,129,0.75)', borderColor: 'rgb(16,185,129)', borderWidth: 1, borderRadius: 4 },
                    { label: 'Total Receivables', data: data.receivables, backgroundColor: 'rgba(59,130,246,0.75)', borderColor: 'rgb(59,130,246)', borderWidth: 1, borderRadius: 4 },
                    { label: 'Total Overdues',    data: data.overdues,    backgroundColor: 'rgba(244,63,94,0.75)',  borderColor: 'rgb(244,63,94)',  borderWidth: 1, borderRadius: 4 },
                ],
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                plugins: {
                    legend: { position: 'bottom', labels: { boxWidth: 10, padding: 8, font: { size: 11 } } },
                    tooltip: { callbacks: { label: (c) => `${c.dataset.label}: ${PESO(c.parsed.y)}` } },
                },
                scales: {
                    x: { grid: { display: false }, ticks: { font: { size: 10 } } },
                    y: {
                        beginAtZero: true,
                        ticks: { font: { size: 10 }, callback: (v) => '₱' + Number(v).toLocaleString('en-PH') },
                    },
                },
            },
            plugins: [drillCursorPlugin],
        });

        const buildRisk = (canvas, data) => new Chart(canvas, {
            type: 'doughnut',
            data: {
                labels: ['Low', 'Medium', 'High'],
                datasets: [{
                    data: [data.low, data.medium, data.high],
                    backgroundColor: ['rgba(16,185,129,0.85)', 'rgba(245,158,11,0.85)', 'rgba(244,63,94,0.85)'],
                    borderColor:     ['rgb(16,185,129)',        'rgb(245,158,11)',        'rgb(244,63,94)'],
                    borderWidth: 1,
                }],
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                cutout: '60%',
                plugins: {
                    legend: { position: 'bottom', labels: { boxWidth: 10, padding: 8, font: { size: 11 } } },
                    tooltip: {
                        callbacks: {
                            label: (c) => {
                                const total = c.dataset.data.reduce((a, b) => a + b, 0);
                                const pct   = total > 0 ? ((c.parsed / total) * 100).toFixed(1) : '0.0';
                                return `${c.label}: ${c.parsed} (${pct}%)`;
                            },
                        },
                    },
                },
            },
        });

        const initialMonthly = @json($monthlyChartData);
        const initialRisk    = @json($riskChartData);

        const monthlyChart = buildMonthly(monthlyCanvas, initialMonthly);
        const riskChart    = buildRisk(riskCanvas, initialRisk);

        state.drilled = (initialMonthly.mode === 'weekly');

        // ── Direct DOM click → drill into that month ─────────────────────
        monthlyCanvas.addEventListener('click', function (evt) {
            if (state.drilled) return;

            const points = monthlyChart.getElementsAtEventForMode(
                evt, 'nearest', { intersect: true }, true
            );
            if (!points || points.length === 0) return;

            const monthIndex = points[0].index;
            if (typeof monthIndex !== 'number') return;
            if (monthIndex < 0 || monthIndex > 11) return;

            wireRef.drillIntoMonth(monthIndex);
        });

        // ── Keep chart + drilled flag in sync with the server ────────────
        $wire.on('chartDataUpdated', () => {
            // Refresh the component reference for future clicks.
            wireRef = $wire;

            // 🔑 Unwrap Livewire reactive proxies into plain objects.
            // Passing $wire.* directly into Chart.js triggers Alpine's
            // reactivity instrumentation and causes infinite recursion.
            const monthly = JSON.parse(JSON.stringify($wire.monthlyChartData || {}));
            const risk    = JSON.parse(JSON.stringify($wire.riskChartData || {}));

            if (monthly.labels && monthly.labels.length) {
                monthlyChart.data.labels           = monthly.labels;
                monthlyChart.data.datasets[0].data = monthly.paid;
                monthlyChart.data.datasets[1].data = monthly.receivables;
                monthlyChart.data.datasets[2].data = monthly.overdues;

                state.drilled = (monthly.mode === 'weekly');
                monthlyChart.update();
            }

            if (typeof risk.low === 'number') {
                riskChart.data.datasets[0].data = [risk.low, risk.medium, risk.high];
                riskChart.update();
            }
        });
    })();
    </script>
    @endscript
</div>