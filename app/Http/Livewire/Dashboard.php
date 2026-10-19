<?php

namespace App\Http\Livewire;

use App\Models\Customer;
use App\Models\Transaction;
use App\Models\TransactionHistory;
use App\Models\Payment;
use App\Models\StoreProfile;
use Carbon\Carbon;
use Illuminate\Support\Facades\Schema;
use Livewire\Component;

class Dashboard extends Component
{
    public $totalReceivables = 0;
    public $overdueAccounts = 0;
    public $totalPaid = 0;
    public $highRiskCustomers = 0;

    public $recentTransactions = [];
    public $riskWatchlist = [];

    public $allRiskWatchlist = [];
    public $allRecentTransactions = [];

    public int $riskWatchlistPreviewCount = 2;
    public int $recentTransactionsPreviewCount = 5;

    public bool $isRiskWatchlistModalOpen = false;
    public bool $isRecentTransactionsModalOpen = false;
    public bool $isNextExpectedModalOpen = false;

    public $cashForecast = [
        'expected' => 0,
        'min' => 0,
        'max' => 0,
        'atRisk' => 0,
        'confidence' => 0,
    ];

    public $nextExpectedPayment = [
        'label' => 'Today',
        'topCustomerNames' => [],
        'othersCount' => 0,
        'amountEstimate' => 0,
        'details' => [],
    ];

    public $weeklyInsights = [];

    // ── Hover breakdown lists ──────────────────────────────────────────
    public array $receivablesBreakdown = [];
    public array $overdueBreakdown = [];
    public array $paidBreakdown = [];
    public array $highRiskCustomersList = [];

    public string $dateFilter = 'all';
    public string $monthFilter = '';
    public string $yearFilter = '';

    // ── Chart data ─────────────────────────────────────────────────────
    // When $chartDrillMonth is null: monthly view.
    // When $chartDrillMonth is 0-11: weekly view of that month.
    public ?int $chartDrillMonth = null;

    public array $monthlyChartData = [
        'mode'        => 'monthly',
        'year'        => null,
        'month'       => null,
        'monthLabel'  => null,
        'labels'      => [],
        'paid'        => [],
        'receivables' => [],
        'overdues'    => [],
    ];

    public array $riskChartData = [
        'low'    => 0,
        'medium' => 0,
        'high'   => 0,
    ];

    public function mount(): void
    {
        $this->monthFilter = Carbon::now()->format('m');
        $this->yearFilter = Carbon::now()->format('Y');
        $this->loadStats();
    }

    public function updatedDateFilter(): void
    {
        $this->chartDrillMonth = null;
        $this->loadStats();
    }

    public function updatedMonthFilter(): void
    {
        $this->chartDrillMonth = null;
        $this->loadStats();
    }

    public function updatedYearFilter(): void
    {
        $this->chartDrillMonth = null;
        $this->loadStats();
    }

    /**
     * Drill into a specific month (0-11) and switch the bar chart
     * to a weekly breakdown of that month.
     */
    public function drillIntoMonth(int $monthIndex): void
    {
        if ($monthIndex < 0 || $monthIndex > 11) {
            return;
        }

        $this->chartDrillMonth  = $monthIndex;
        $this->monthlyChartData = $this->computeMonthlyChartData();
        $this->dispatch('chartDataUpdated');
    }

    /**
     * Return to the filter-aware monthly view.
     */
    public function resetChartDrill(): void
    {
        $this->chartDrillMonth  = null;
        $this->monthlyChartData = $this->computeMonthlyChartData();
        $this->dispatch('chartDataUpdated');
    }

    protected function getDateRange(): array
    {
        $start = match ($this->dateFilter) {
            'today'       => Carbon::today()->startOfDay(),
            'this_week'   => Carbon::now()->startOfWeek(),
            'this_month'  => Carbon::now()->startOfMonth(),
            'past_months' => Carbon::create((int)$this->yearFilter, (int)$this->monthFilter, 1)->startOfMonth(),
            default       => Carbon::create(1900, 1, 1)->startOfDay(),
        };

        $end = match ($this->dateFilter) {
            'today'       => Carbon::today()->endOfDay(),
            'this_week'   => Carbon::now()->endOfWeek(),
            'this_month'  => Carbon::now()->endOfMonth(),
            'past_months' => Carbon::create((int)$this->yearFilter, (int)$this->monthFilter, 1)->endOfMonth(),
            default       => Carbon::now()->endOfDay(),
        };

        return [$start, $end];
    }

    public function loadStats(): void
    {
        [$start, $end] = $this->getDateRange();

        $hasCustomers = Schema::hasTable('customers');
        $hasTransactions = Schema::hasTable('transactions');

        if (! $hasCustomers || ! $hasTransactions) {
            $this->setEmptyStats();
            return;
        }

        $transactionsQuery = Transaction::query()->whereBetween('transaction_date', [$start, $end]);

        $this->totalReceivables = (clone $transactionsQuery)->where('status', '!=', 'PAID')->sum('total_amount');
        $this->overdueAccounts  = (clone $transactionsQuery)->where('status', 'OVERDUE')->sum('total_amount');
        $this->totalPaid        = Payment::whereBetween('payment_date', [$start, $end])->sum('total_amount');

        $customerHasRisk = Schema::hasColumn('customers', 'risk_level');
        if ($customerHasRisk) {
            $this->highRiskCustomers = Customer::where('risk_level', 'HIGH')
                ->whereHas('transactions', fn($q) => $q->whereBetween('transaction_date', [$start, $end]))
                ->count();
        } else {
            $this->highRiskCustomers = 0;
        }

        // === FIX: Preload overdue counts and sums to avoid N+1 ===
        $customers = Customer::with([
            'transactions' => fn($q) =>
            $q->whereBetween('transaction_date', [$start, $end])
        ])
            ->withCount(['transactions as overdue_count' => function ($q) use ($start, $end) {
                $q->where('status', 'OVERDUE')->whereBetween('transaction_date', [$start, $end]);
            }])
            ->withSum(['transactions as overdue_total' => function ($q) use ($start, $end) {
                $q->where('status', 'OVERDUE')->whereBetween('transaction_date', [$start, $end]);
            }], 'total_amount')
            ->whereHas('transactions', fn($q) => $q->whereBetween('transaction_date', [$start, $end]))
            ->get();

        $this->allRiskWatchlist = $this->buildRiskWatchlist($customers);
        $this->riskWatchlist = array_slice($this->allRiskWatchlist, 0, $this->riskWatchlistPreviewCount);

        // Recent paid transactions from history
        $allHistories = TransactionHistory::with('transaction')
            ->whereBetween('payment_date', [$start, $end])
            ->latest('payment_date')
            ->take(30)
            ->get();

        $this->allRecentTransactions = $allHistories->map(function ($h) {
            return $this->mapHistoryToRecent($h);
        })->all();
        $this->recentTransactions = array_slice($this->allRecentTransactions, 0, $this->recentTransactionsPreviewCount);

        $this->cashForecast = $this->compute7DayCashForecast($customers, []);
        $this->nextExpectedPayment = $this->computeNextExpectedPayment();
        $this->weeklyInsights = $this->computeWeeklyInsights($customers);

        // ── Hover breakdown lists ─────────────────────────────────────────
        $this->receivablesBreakdown = $customers->map(function ($c) {
            $total = $c->transactions->where('status', '!=', 'PAID')->sum('total_amount');
            return ['name' => $c->name, 'amount' => (float) $total];
        })->filter(fn($c) => $c['amount'] > 0)->sortByDesc('amount')->values()->all();

        $this->overdueBreakdown = $customers->map(function ($c) {
            $total = $c->transactions->where('status', 'OVERDUE')->sum('total_amount');
            return ['name' => $c->name, 'amount' => (float) $total];
        })->filter(fn($c) => $c['amount'] > 0)->sortByDesc('amount')->values()->all();

        $this->paidBreakdown = Payment::whereBetween('payment_date', [$start, $end])
            ->with('customer')
            ->get()
            ->groupBy('customer_id')
            ->map(function ($payments) {
                $first = $payments->first();
                return [
                    'name'   => $first->customer?->name ?? 'Deleted Customer',
                    'amount' => (float) $payments->sum('total_amount'),
                ];
            })
            ->filter(fn($c) => $c['amount'] > 0)
            ->sortByDesc('amount')
            ->values()
            ->all();

        if ($customerHasRisk) {
            $this->highRiskCustomersList = $customers
                ->where('risk_level', 'HIGH')
                ->map(fn($c) => ['name' => $c->name])
                ->values()
                ->all();
        } else {
            $this->highRiskCustomersList = [];
        }

        // ── Chart data (respects the active date filter) ──────────────────────
        $this->monthlyChartData = $this->computeMonthlyChartData();
        $this->riskChartData    = $this->computeRiskChartData();
        $this->dispatch('chartDataUpdated');
    }

    private function mapHistoryToRecent($history)
    {
        return (object) [
            'id' => $history->id,
            'customer' => (object)['name' => $history->customer_name ?? 'Deleted Customer'],
            'item_name' => $history->product_name ?? 'Unknown item',
            'quantity' => $history->transaction?->quantity ?? 0,
            'total_amount' => $history->total_amount,
            'transaction_date' => $history->transaction_date,
            'due_date' => $history->due_date,
            'status' => $history->status,
            'notes' => $history->transaction?->notes ?? '',
            'warningCreditLimit' => false,
            'repaymentProbability' => 0.5,
            'warningProbability' => false,
        ];
    }

    private function setEmptyStats(): void
    {
        $this->totalReceivables = 0;
        $this->overdueAccounts = 0;
        $this->highRiskCustomers = 0;
        $this->recentTransactions = [];
        $this->riskWatchlist = [];
        $this->allRiskWatchlist = [];
        $this->allRecentTransactions = [];
        $this->receivablesBreakdown = [];
        $this->overdueBreakdown = [];
        $this->paidBreakdown = [];
        $this->highRiskCustomersList = [];
        $this->cashForecast = [
            'expected' => 0,
            'min' => 0,
            'max' => 0,
            'atRisk' => 0,
            'confidence' => 0,
        ];
        $this->nextExpectedPayment = [
            'label' => 'Today',
            'topCustomerNames' => [],
            'othersCount' => 0,
            'amountEstimate' => 0,
            'details' => [],
        ];
        $this->weeklyInsights = [];

        $this->chartDrillMonth = null;
        $this->monthlyChartData = [
            'mode'        => 'monthly',
            'year'        => (int) Carbon::now()->format('Y'),
            'month'       => null,
            'monthLabel'  => null,
            'labels'      => ['Jan', 'Feb', 'Mar', 'Apr', 'May', 'Jun', 'Jul', 'Aug', 'Sep', 'Oct', 'Nov', 'Dec'],
            'paid'        => array_fill(0, 12, 0.0),
            'receivables' => array_fill(0, 12, 0.0),
            'overdues'    => array_fill(0, 12, 0.0),
        ];
        $this->riskChartData = ['low' => 0, 'medium' => 0, 'high' => 0];
    }

    private function buildRiskWatchlist($customers): array
    {
        if ($customers->isEmpty()) {
            return [];
        }

        $watch = $customers->map(function ($c) {
            $avgLag = (int) ($c->days_since_last_payment ?? 0);
            $overdueCount = (int) ($c->overdue_count ?? 0);

            $prevLag = max(0, $avgLag - min(25, $overdueCount * 3));
            $jump = $avgLag - $prevLag;

            $trend = 'green';
            if ($jump >= 8 || $avgLag >= 30) {
                $trend = 'red';
            } elseif ($jump >= 3 || $avgLag >= 20) {
                $trend = 'amber';
            }

            $reasons = [];
            if ($avgLag >= 10) {
                $reasons[] = "Delay rising ({$avgLag} days)";
            }
            if ($overdueCount) {
                $reasons[] = "{$overdueCount} overdue transaction(s)";
            }
            if (($c->risk_level ?? '') === 'HIGH') {
                $reasons[] = 'High risk flag';
            }

            if (empty($reasons)) {
                $reasons[] = 'Active customer (monitor normally)';
            }

            $riskScore = ($avgLag * 2) + ($overdueCount * 10) + max(0, $jump) * 1.5;

            return [
                'customerId'     => $c->id,
                'name'           => $c->name,
                'avgLagDays'     => $avgLag,
                'avgLagPrevDays' => $prevLag,
                'lagJump'        => $jump,
                'trend'          => $trend,
                'riskScore'      => $riskScore,
                'atRisk'         => $trend !== 'green',
                'topReasons'     => array_slice($reasons, 0, 3),
            ];
        });

        return $watch
            ->sortByDesc('riskScore')
            ->take(8)
            ->values()
            ->all();
    }

    private function compute7DayCashForecast($customers, $allTransactions): array
    {
        $expected = 0.0;
        $min = 0.0;
        $max = 0.0;
        $atRisk = 0.0;

        foreach ($customers as $c) {
            $balance = (float) ($c->total_balance ?? 0);
            $delayDays = (int) ($c->days_since_last_payment ?? 0);

            $p = 0.90 - min(0.70, $delayDays / 20);
            $p = max(0.05, min(0.95, $p));

            $riskPortion = $p < 0.50;
            $riskFactor = $riskPortion ? (0.55 + min(0.40, ($delayDays / 120))) : 0.0;

            $expected += $balance * ($p * 0.10);
            $min += $balance * (max(0.08, $p - 0.15) * 0.08);
            $max += $balance * (min(0.95, $p + 0.15) * 0.12);

            if ($riskFactor > 0) {
                $atRisk += $balance * ($riskFactor * 0.08);
            }
        }

        $confidence = 0.55;
        if (count($customers) > 0) {
            $overdueRatio = $this->totalReceivables > 0
                ? ((float) $this->overdueAccounts / (float) $this->totalReceivables)
                : 0;
            $confidence = max(0.25, min(0.85, 0.70 - $overdueRatio));
        }

        return [
            'expected' => $expected,
            'min' => $min,
            'max' => $max,
            'atRisk' => $atRisk,
            'confidence' => $confidence,
        ];
    }

    /**
     * Compute the next expected payment window.
     *
     * IMPORTANT: This intentionally ignores the dashboard's dateFilter.
     * Due dates are future events tied to when payment is expected,
     * not to when the transaction was created. Filtering by
     * transaction_date would wrongly hide upcoming dues from older
     * transactions. We therefore query ALL unpaid transactions with a
     * due date, group them by date, and pick the earliest group.
     */
    private function computeNextExpectedPayment(): array
    {
        $today = Carbon::today();

        $upcoming = Transaction::query()
            ->where('status', '!=', 'PAID')
            ->whereNotNull('due_date')
            ->with('customer')
            ->get()
            ->groupBy(fn($tx) => Carbon::parse($tx->due_date)->toDateString())
            ->map(function ($items) {
                return [
                    'dueDate'     => Carbon::parse($items->first()->due_date),
                    'totalAmount' => $items->sum(fn($tx) => (float) $tx->current_total),
                    'customers'   => $items->map(fn($tx) => [
                        'name'   => $tx->customer?->name ?? 'Deleted Customer',
                        'amount' => (float) $tx->current_total,
                    ])->values()->all(),
                ];
            })
            ->sortBy(fn($g) => $g['dueDate'])
            ->first();

        if (! $upcoming) {
            return [
                'label' => 'Today',
                'topCustomerNames' => [],
                'othersCount' => 0,
                'amountEstimate' => 0,
                'details' => [],
            ];
        }

        $daysDiff = $today->diffInDays($upcoming['dueDate'], false);
        $label = match (true) {
            $daysDiff < 0   => 'Past due',
            $daysDiff === 0 => 'Today',
            $daysDiff === 1 => 'Tomorrow',
            $daysDiff <= 7  => "Due in {$daysDiff} days",
            default         => $upcoming['dueDate']->format('F j, Y'),
        };

        $details = collect($upcoming['customers'])->sortByDesc('amount')->values();

        return [
            'label' => $label,
            'topCustomerNames' => [$details->first()['name']],
            'othersCount' => max(0, $details->count() - 1),
            'amountEstimate' => (int) round($upcoming['totalAmount']),
            'details' => $details->all(),
        ];
    }

    private function computeWeeklyInsights($customers): array
    {
        $wellBehaved = $customers->filter(function ($c) {
            $lag = (int) ($c->days_since_last_payment ?? 0);
            $overdueCount = (int) ($c->overdue_count ?? 0);
            return $lag <= 12 && $overdueCount === 0 && ($c->risk_level ?? '') !== 'HIGH';
        });

        $atRisk = $customers->filter(function ($c) {
            $lag = (int) ($c->days_since_last_payment ?? 0);
            return $lag >= 20 || ($c->risk_level ?? '') === 'HIGH';
        });

        $insights = [];

        if ($wellBehaved->count() >= 2) {
            $insights[] = [
                'tone' => 'emerald',
                'title' => 'Consider raising limits (safe group)',
                'body' => 'Multiple customers show low lag and no recent overdue behavior. Raising credit limits can speed collection cycles.',
            ];
        } else {
            $insights[] = [
                'tone' => 'amber',
                'title' => 'Hold credit while monitoring lag',
                'body' => 'Not enough well-behaved signals this week. Focus on follow-ups for changing customer behavior.',
            ];
        }

        $expected = (float) ($this->cashForecast['expected'] ?? 0);
        $overdue = (float) ($this->overdueAccounts ?? 0);

        if ($expected > $overdue) {
            $insights[] = [
                'tone' => 'amber',
                'title' => 'Order stock ahead of predicted collection surge',
                'body' => 'Expected 7-day collections outweigh overdue risk. Plan inventory to meet near-term demand.',
            ];
        } else {
            $insights[] = [
                'tone' => 'red',
                'title' => 'Reduce exposure; prioritize reminders',
                'body' => 'Forecast confidence is lowered by overdue concentration. Focus on high-risk reminders before expanding inventory.',
            ];
        }

        if ($atRisk->count() >= 3) {
            $insights[] = [
                'tone' => 'red',
                'title' => 'Run a targeted reminder wave',
                'body' => 'A cluster of customers shows rising delays. Send reminders to the risk watchlist first.',
            ];
        }

        return array_slice($insights, 0, 3);
    }

    public function sendReminder($customerId): void
    {
        $customer = Customer::find($customerId);
        $name = $customer?->name ?? 'customer';
        $this->dispatch('showAlert', "Reminder queued for {$name}. (Heuristic demo action)");
    }

    public function render()
    {
        return view('livewire.dashboard');
    }

    // ── Chart computation helpers ───────────────────────────────────────

    /**
     * Entry point for both monthly and weekly bar data.
     * If $chartDrillMonth is set, computes weekly bars for that month.
     */
    private function computeMonthlyChartData(): array
    {
        [$start, $end] = $this->getDateRange();

        $targetYear = $this->dateFilter === 'past_months'
            ? (int) $this->yearFilter
            : (int) Carbon::now()->format('Y');

        if ($this->chartDrillMonth === null) {
            return $this->computeMonthlyBars($start, $end, $targetYear);
        }

        return $this->computeWeeklyBars($targetYear, $this->chartDrillMonth);
    }

    /**
     * The original 12-month bar data.
     */
    private function computeMonthlyBars($start, $end, int $targetYear): array
    {
        $labels      = ['Jan', 'Feb', 'Mar', 'Apr', 'May', 'Jun', 'Jul', 'Aug', 'Sep', 'Oct', 'Nov', 'Dec'];
        $paid        = array_fill(0, 12, 0.0);
        $receivables = array_fill(0, 12, 0.0);
        $overdues    = array_fill(0, 12, 0.0);

        // Paid — from Payment rows
        Payment::whereBetween('payment_date', [$start, $end])
            ->get()
            ->each(function ($p) use (&$paid, $targetYear) {
                $when = Carbon::parse($p->payment_date);
                if ((int) $when->format('Y') !== $targetYear) return;
                $m = (int) $when->format('n') - 1;
                $paid[$m] += (float) $p->total_amount;
            });

        // Receivables + Overdues — from Transaction rows
        Transaction::whereBetween('transaction_date', [$start, $end])
            ->get()
            ->each(function ($t) use (&$receivables, &$overdues, $targetYear) {
                $when = Carbon::parse($t->transaction_date);
                if ((int) $when->format('Y') !== $targetYear) return;
                $m = (int) $when->format('n') - 1;

                // Receivables = every unpaid transaction (PENDING + OVERDUE).
                // This matches the "Total Receivables" card, which uses
                // ->where('status', '!=', 'PAID')->sum('total_amount').
                if ($t->status !== 'PAID') {
                    $receivables[$m] += (float) $t->total_amount;
                }

                // Overdue is a subset of the above — shown as its own bar.
                if ($t->status === 'OVERDUE') {
                    $overdues[$m] += (float) $t->total_amount;
                }
            });

        return [
            'mode'        => 'monthly',
            'year'        => $targetYear,
            'month'       => null,
            'monthLabel'  => null,
            'labels'      => $labels,
            'paid'        => $paid,
            'receivables' => $receivables,
            'overdues'    => $overdues,
        ];
    }

    /**
     * Weekly breakdown of a single month.
     * Week 1 = days 1-7, Week 2 = 8-14, Week 3 = 15-21,
     * Week 4 = 22-28, Week 5 = 29-end (only if the month has >28 days).
     *
     * NOTE: When drilled in, the weekly view intentionally shows the
     * FULL month regardless of the outer date filter — the user
     * explicitly asked for a complete monthly breakdown.
     */
    private function computeWeeklyBars(int $targetYear, int $monthIndex): array
    {
        $month = $monthIndex + 1;

        $monthStart = Carbon::create($targetYear, $month, 1)->startOfDay();
        $monthEnd   = Carbon::create($targetYear, $month, 1)->endOfMonth();
        $lastDay    = (int) $monthEnd->format('j');

        // Build the week buckets
        $weeks  = [];
        $day    = 1;
        $weekNo = 1;
        while ($day <= $lastDay) {
            $wStart = $day;
            $wEnd   = min($day + 6, $lastDay);

            $weeks[] = [
                'index' => $weekNo,
                'label' => $wStart . '–' . $wEnd,
                'start' => Carbon::create($targetYear, $month, $wStart)->startOfDay(),
                'end'   => Carbon::create($targetYear, $month, $wEnd)->endOfDay(),
            ];

            $day = $wEnd + 1;
            $weekNo++;
        }

        $count       = count($weeks);
        $labels      = array_map(fn($w) => $w['label'], $weeks);
        $paid        = array_fill(0, $count, 0.0);
        $receivables = array_fill(0, $count, 0.0);
        $overdues    = array_fill(0, $count, 0.0);

        // Payments inside this month
        Payment::whereBetween('payment_date', [$monthStart, $monthEnd])
            ->get()
            ->each(function ($p) use (&$paid, $weeks) {
                $when = Carbon::parse($p->payment_date);
                foreach ($weeks as $i => $w) {
                    if ($when->between($w['start'], $w['end'])) {
                        $paid[$i] += (float) $p->total_amount;
                        break;
                    }
                }
            });

        // Transactions inside this month
        Transaction::whereBetween('transaction_date', [$monthStart, $monthEnd])
            ->get()
            ->each(function ($t) use (&$receivables, &$overdues, $weeks) {
                $when = Carbon::parse($t->transaction_date);
                foreach ($weeks as $i => $w) {
                    if ($when->between($w['start'], $w['end'])) {
                        // Receivables = every unpaid transaction (PENDING + OVERDUE).
                        if ($t->status !== 'PAID') {
                            $receivables[$i] += (float) $t->total_amount;
                        }

                        // Overdue is a subset of the above.
                        if ($t->status === 'OVERDUE') {
                            $overdues[$i] += (float) $t->total_amount;
                        }
                        break;
                    }
                }
            });

        return [
            'mode'        => 'weekly',
            'year'        => $targetYear,
            'month'       => $month,
            'monthLabel'  => Carbon::create($targetYear, $month, 1)->format('F Y'),
            'labels'      => $labels,
            'paid'        => $paid,
            'receivables' => $receivables,
            'overdues'    => $overdues,
        ];
    }

    /**
     * Compute the LOW / MEDIUM / HIGH distribution of customers,
     * accurately and in a filter-aware way.
     *
     * Strategy:
     *  - Consider ALL active customers that had ANY transaction
     *    in the selected date range (so the distribution respects the filter).
     *  - Re-derive each customer's risk on the fly (not the stored field),
     *    using the exact same scoring formula as Customer::updateRiskLevel()
     *    so the result matches the rest of the app.
     *  - Use the filter range to compute overdue counts, so the
     *    distribution genuinely shifts when the filter changes.
     */
    private function computeRiskChartData(): array
    {
        [$start, $end] = $this->getDateRange();
        $today = Carbon::today();

        // Every active customer that had at least one transaction in range.
        $customers = Customer::where('is_active', true)
            ->whereHas('transactions', fn($q) => $q->whereBetween('transaction_date', [$start, $end]))
            ->get(['id', 'credit_limit', 'total_balance']);

        if ($customers->isEmpty()) {
            return ['low' => 0, 'medium' => 0, 'high' => 0];
        }

        $ids = $customers->pluck('id')->all();

        // Overdue aggregates limited to the filter range.
        $overdueAgg = Transaction::whereIn('customer_id', $ids)
            ->where('status', 'OVERDUE')
            ->whereBetween('transaction_date', [$start, $end])
            ->selectRaw('customer_id, COUNT(*) as cnt, COALESCE(SUM(total_amount), 0) as total')
            ->groupBy('customer_id')
            ->get()
            ->keyBy('customer_id');

        // Latest payment per customer (unfiltered — a real, customer-level metric).
        $lastPaymentAgg = Payment::whereIn('customer_id', $ids)
            ->selectRaw('customer_id, MAX(payment_date) as last_payment')
            ->groupBy('customer_id')
            ->get()
            ->keyBy('customer_id');

        $low = 0;
        $medium = 0;
        $high = 0;

        foreach ($customers as $c) {
            $payRow = $lastPaymentAgg->get($c->id);
            $daysSince = 365;
            if ($payRow && $payRow->last_payment) {
                $daysSince = (int) Carbon::parse($payRow->last_payment)->diffInDays($today);
            }

            $ovRow   = $overdueAgg->get($c->id);
            $ovCount = $ovRow ? (int) $ovRow->cnt    : 0;
            $ovTotal = $ovRow ? (float) $ovRow->total : 0.0;

            $creditLimit  = (float) ($c->credit_limit  ?? 0);
            $totalBalance = (float) ($c->total_balance ?? 0);

            $pressureRatio = $creditLimit > 0
                ? ($totalBalance / $creditLimit)
                : ($totalBalance > 0 ? 10 : 0);

            $score = 0;
            if ($daysSince >= 30)          $score += 2;
            elseif ($daysSince >= 15)      $score += 1;

            if ($ovCount >= 1)             $score += 2;
            if ($ovTotal > 0)              $score += 1;

            if ($pressureRatio >= 1.0)     $score += 2;
            elseif ($pressureRatio >= 0.8) $score += 1;

            if ($score >= 6)      $high++;
            elseif ($score >= 3)  $medium++;
            else                  $low++;
        }

        return ['low' => $low, 'medium' => $medium, 'high' => $high];
    }
}
