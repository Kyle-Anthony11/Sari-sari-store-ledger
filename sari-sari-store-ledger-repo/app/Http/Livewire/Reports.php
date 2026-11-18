<?php

namespace App\Http\Livewire;

use App\Models\Customer;
use App\Models\Transaction;
use Barryvdh\DomPDF\Facade\Pdf;
use Carbon\Carbon;
use Livewire\Component;
use Livewire\WithPagination;

class Reports extends Component
{
    use WithPagination;

    protected $paginationTheme = 'simple-tailwind-custom';

    // Separate filters for each section
    public string $outstandingDateFilter = 'all'; // 'all', 'today', 'last_7_days', 'last_30_days'
    public string $agingDateFilter = 'all';

    // Export modal
    public bool $showExportModal = false;

    protected $queryString = ['outstandingDateFilter', 'agingDateFilter'];

    public function updatedOutstandingDateFilter()
    {
        $this->resetPage();
    }

    public function updatedAgingDateFilter()
    {
        // No pagination for aging, but we keep it consistent
    }

    /**
     * Get start and end dates based on the selected filter.
     * Returns a wide range for 'all'.
     */
    protected function getDateRange(string $filter): array
    {
        if ($filter === 'all') {
            return [
                Carbon::create(1900, 1, 1)->startOfDay(),
                Carbon::now()->addYear()->endOfDay()
            ];
        }

        $end = Carbon::today()->endOfDay();
        $start = match ($filter) {
            'today'       => Carbon::today()->startOfDay(),
            'last_7_days' => Carbon::today()->subDays(6)->startOfDay(),
            'last_30_days' => Carbon::today()->subDays(29)->startOfDay(),
            default       => Carbon::create(1900, 1, 1)->startOfDay(),
        };
        return [$start, $end];
    }

    public function openExportModal()
    {
        $this->showExportModal = true;
    }

    public function closeExportModal()
    {
        $this->showExportModal = false;
    }

    public function exportCsv()
    {
        $this->showExportModal = false;

        return response()->streamDownload(function () {
            // Get ranges for each section
            [$outStart, $outEnd] = $this->getDateRange($this->outstandingDateFilter);
            [$ageStart, $ageEnd] = $this->getDateRange($this->agingDateFilter);

            // ---- Sheet 1: Outstanding Receivables ----
            $customers = Customer::whereHas('transactions', function ($q) use ($outStart, $outEnd) {
                $q->where('status', '!=', 'PAID')
                    ->whereBetween('transaction_date', [$outStart, $outEnd]);
            })
                ->with(['transactions' => function ($q) use ($outStart, $outEnd) {
                    $q->where('status', '!=', 'PAID')
                        ->whereBetween('transaction_date', [$outStart, $outEnd]);
                }])
                ->get()
                ->map(function ($customer) {
                    $outstanding = $customer->transactions->sum(fn($tx) => $tx->current_total);
                    $customer->outstanding_balance = $outstanding;
                    return $customer;
                })
                ->filter(fn($c) => $c->outstanding_balance > 0)
                ->sortByDesc('outstanding_balance');

            $output = fopen('php://output', 'w');
            fputcsv($output, ['Outstanding Receivables']);
            fputcsv($output, []);
            fputcsv($output, ['Customer Name', 'Outstanding Balance (₱)', 'Credit Limit (₱)', 'Risk Level', 'Days Since Last Payment']);
            foreach ($customers as $customer) {
                fputcsv($output, [
                    $customer->name,
                    round($customer->outstanding_balance, 2),
                    number_format($customer->credit_limit ?? 0, 2),
                    $customer->risk_level ?? 'LOW',
                    $customer->days_since_last_payment ?? 0,
                ]);
            }

            fputcsv($output, []);
            fputcsv($output, []);

            // ---- Sheet 2: Aging of Receivables ----
            $transactions = Transaction::where('status', '!=', 'PAID')
                ->whereNotNull('due_date')
                ->whereBetween('transaction_date', [$ageStart, $ageEnd])
                ->get();

            $today = Carbon::today();
            $buckets = [
                '0-30 days'  => ['count' => 0, 'total' => 0],
                '31-60 days' => ['count' => 0, 'total' => 0],
                '61+ days'   => ['count' => 0, 'total' => 0],
            ];

            foreach ($transactions as $transaction) {
                $overdueDays = max(0, $today->diffInDays(Carbon::parse($transaction->due_date), false));
                if ($overdueDays <= 30) {
                    $buckets['0-30 days']['count']++;
                    $buckets['0-30 days']['total'] += $transaction->current_total;
                } elseif ($overdueDays <= 60) {
                    $buckets['31-60 days']['count']++;
                    $buckets['31-60 days']['total'] += $transaction->current_total;
                } else {
                    $buckets['61+ days']['count']++;
                    $buckets['61+ days']['total'] += $transaction->current_total;
                }
            }

            fputcsv($output, ['Aging of Receivables']);
            fputcsv($output, []);
            fputcsv($output, ['Overdue Bucket', 'Number of Transactions', 'Total Amount (₱)']);
            foreach ($buckets as $bucket => $data) {
                fputcsv($output, [$bucket, $data['count'], number_format($data['total'], 2)]);
            }

            fclose($output);
        }, 'reports_' . $this->outstandingDateFilter . '_' . $this->agingDateFilter . '_' . now()->format('Y-m-d') . '.csv', [
            'Content-Type' => 'text/csv',
        ]);
    }

    public function exportPdf()
    {
        $this->showExportModal = false;

        [$outStart, $outEnd] = $this->getDateRange($this->outstandingDateFilter);
        [$ageStart, $ageEnd] = $this->getDateRange($this->agingDateFilter);

        $customers = Customer::whereHas('transactions', function ($q) use ($outStart, $outEnd) {
            $q->where('status', '!=', 'PAID')
                ->whereBetween('transaction_date', [$outStart, $outEnd]);
        })
            ->with(['transactions' => function ($q) use ($outStart, $outEnd) {
                $q->where('status', '!=', 'PAID')
                    ->whereBetween('transaction_date', [$outStart, $outEnd]);
            }])
            ->get()
            ->map(function ($customer) {
                $outstanding = $customer->transactions->sum(fn($tx) => $tx->current_total);
                $customer->outstanding_balance = $outstanding;
                return $customer;
            })
            ->filter(fn($c) => $c->outstanding_balance > 0)
            ->sortByDesc('outstanding_balance');

        $agingBuckets = $this->calculateAgingBuckets($ageStart, $ageEnd);

        $pdf = Pdf::loadView('livewire.reports-pdf', [
            'customers' => $customers,
            'agingBuckets' => $agingBuckets,
            'outstandingDateFilter' => $this->outstandingDateFilter,
            'agingDateFilter' => $this->agingDateFilter,
        ])->setPaper('a4', 'portrait');

        return response()->streamDownload(function () use ($pdf) {
            echo $pdf->output();
        }, 'reports_' . now()->format('Y-m-d') . '.pdf');
    }

    public function render()
    {
        // Outstanding receivables with its own filter
        [$outStart, $outEnd] = $this->getDateRange($this->outstandingDateFilter);

        $customers = Customer::whereHas('transactions', function ($q) use ($outStart, $outEnd) {
            $q->where('status', '!=', 'PAID')
                ->whereBetween('transaction_date', [$outStart, $outEnd]);
        })
            ->with(['transactions' => function ($q) use ($outStart, $outEnd) {
                $q->where('status', '!=', 'PAID')
                    ->whereBetween('transaction_date', [$outStart, $outEnd]);
            }])
            ->get()
            ->map(function ($customer) {
                $outstanding = $customer->transactions->sum(fn($tx) => $tx->current_total);
                $customer->outstanding_balance = $outstanding;
                return $customer;
            })
            ->filter(fn($c) => $c->outstanding_balance > 0)
            ->sortByDesc('outstanding_balance');

        // Paginate manually (5 per page)
        $currentPage = \Illuminate\Pagination\Paginator::resolveCurrentPage();
        $perPage = 5;
        $currentItems = $customers->slice(($currentPage - 1) * $perPage, $perPage)->values();
        $paginatedCustomers = new \Illuminate\Pagination\LengthAwarePaginator(
            $currentItems,
            $customers->count(),
            $perPage,
            $currentPage,
            ['path' => \Illuminate\Pagination\Paginator::resolveCurrentPath()]
        );

        // Aging buckets with its own filter
        [$ageStart, $ageEnd] = $this->getDateRange($this->agingDateFilter);
        $agingBuckets = $this->calculateAgingBuckets($ageStart, $ageEnd);

        return view('livewire.reports', [
            'customers' => $paginatedCustomers,
            'agingBuckets' => $agingBuckets,
        ]);
    }

    private function calculateAgingBuckets($start, $end): array
    {
        $today = Carbon::today();

        $transactions = Transaction::where('status', '!=', 'PAID')
            ->whereNotNull('due_date')
            ->whereBetween('transaction_date', [$start, $end])
            ->get();

        $buckets = [
            '0-30 days'  => ['count' => 0, 'total' => 0],
            '31-60 days' => ['count' => 0, 'total' => 0],
            '61+ days'   => ['count' => 0, 'total' => 0],
        ];

        foreach ($transactions as $transaction) {
            $dueDate = Carbon::parse($transaction->due_date);
            $overdueDays = max(0, $today->diffInDays($dueDate, false));

            if ($overdueDays <= 30) {
                $buckets['0-30 days']['count']++;
                $buckets['0-30 days']['total'] += $transaction->current_total;
            } elseif ($overdueDays <= 60) {
                $buckets['31-60 days']['count']++;
                $buckets['31-60 days']['total'] += $transaction->current_total;
            } else {
                $buckets['61+ days']['count']++;
                $buckets['61+ days']['total'] += $transaction->current_total;
            }
        }

        return $buckets;
    }
}
