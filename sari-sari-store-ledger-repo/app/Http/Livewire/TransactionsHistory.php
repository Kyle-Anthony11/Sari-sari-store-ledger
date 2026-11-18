<?php

namespace App\Http\Livewire;

use App\Models\Customer;
use App\Models\TransactionHistory;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Builder;
use App\Services\AuthService;
use Livewire\Component;
use Livewire\WithPagination;
use Symfony\Component\HttpFoundation\StreamedResponse;

class TransactionsHistory extends Component
{
    use WithPagination;
    protected $paginationTheme = 'simple-tailwind-custom';

    public $customers;

    public bool $isDeleteAllModalOpen = false;
    public $receiptData = [];

    // Search filters
    public string $searchCustomerName = '';
    public string $searchItemName = '';
    public string $searchDate = '';

    // Date range filter (now includes 'all' as default)
    public string $dateFilter = 'all'; // all, today, last_7_days, last_30_days

    public bool $isDetailsModalOpen = false;
    public $selectedPayment = null;

    // Export modal properties
    public bool $showExportModal = false;
    public string $exportTimeframe = 'all';

    public bool $showGroupDetailsModal = false;

    public string $searchTransactionNumber = '';
    public $groupDetailsHistory = null;

    protected $queryString = [
        'searchCustomerName' => ['except' => ''],
        'searchItemName' => ['except' => ''],
        'searchDate' => ['except' => ''],
        'searchTransactionNumber' => ['except' => ''],
        'dateFilter' => ['except' => 'all'],
    ];

    public function mount(): void
    {
        $this->customers = Customer::query()->where('is_active', true)->get();
    }

    public function updatingDateFilter(): void
    {
        $this->resetPage();
    }

    /**
     * Get start and end dates based on the selected filter (for list display).
     * When filter is 'all', return a very wide range or handle null differently.
     */
    protected function getDateRange(): array
    {
        if ($this->dateFilter === 'all') {
            // Return a wide range that essentially includes all records
            return [Carbon::create(1900, 1, 1)->startOfDay(), Carbon::now()->addYear()->endOfDay()];
        }
        $end = Carbon::today()->endOfDay();
        $start = match ($this->dateFilter) {
            'today'       => Carbon::today()->startOfDay(),
            'last_7_days' => Carbon::today()->subDays(6)->startOfDay(),
            'last_30_days' => Carbon::today()->subDays(29)->startOfDay(),
            default       => Carbon::create(1900, 1, 1)->startOfDay(),
        };
        return [$start, $end];
    }

    public function viewGroupDetails($paymentId)
    {
        $histories = TransactionHistory::where('payments_id', $paymentId)
            ->with(['customer', 'transaction', 'payment'])
            ->get();

        if ($histories->isEmpty()) {
            $this->dispatch('showAlert', 'No details found.');
            return;
        }

        $store = AuthService::user();
        $ownerName = trim(($store->owner_first_name ?? '') . ' ' . ($store->owner_middle_name ?? '') . ' ' . ($store->owner_last_name ?? ''));
        $customerName = $histories->first()->customer_name ?? 'Deleted Customer';

        $payment = $histories->first()->payment;

        $cashGiven = $payment?->cash_given ?? $histories->first()->cash_given ?? 0;
        $change    = $payment?->change     ?? $histories->first()->change     ?? 0;
        $total     = $histories->sum('total_amount');
        $paymentDate = $payment?->payment_date
            ? Carbon::parse($payment->payment_date)->format('F d, Y h:i A')
            : now()->format('F d, Y h:i A');

        // Pull payment method + reference number from the payment row
        $paymentMethod   = $payment?->payment_method   ?? $histories->first()->payment_method   ?? 'cash';
        $referenceNumber = $payment?->reference_number ?? $histories->first()->reference_number ?? null;
        $transactionNumber = $histories->first()->transaction?->transaction_number ?? null;

        $isGcash = strtolower($paymentMethod) === 'gcash';

        $items = [];
        foreach ($histories as $h) {
            $items[] = [
                'name' => $h->product_name ?? 'Product',
                'quantity' => $h->transaction?->quantity ?? 0,
                'unit_price' => ($h->transaction && $h->transaction->quantity > 0)
                    ? ($h->transaction->base_total / $h->transaction->quantity)
                    : 0,
                'total' => $h->total_amount,
            ];
        }

        $this->receiptData = [
            'store_name'     => $store->store_name ?? 'Store',
            'owner_name'     => $ownerName,
            'customer_name'  => $customerName,
            'items'          => $items,
            'total_amount'   => $total,
            'cash_given'     => $cashGiven,
            'change'         => $change,
            'payment_date'   => $paymentDate,
            'payment_method' => $isGcash ? 'GCash' : 'Cash',
            'showBack'       => true,
            'backAction'     => 'closeGroupDetails',
        ];

        if ($transactionNumber) {
            $this->receiptData['transaction_number'] = $transactionNumber;
        }

        if ($isGcash && !empty($referenceNumber)) {
            $this->receiptData['reference_number'] = $referenceNumber;
        }

        $this->showGroupDetailsModal = true;
    }

    public function closeGroupDetails()
    {
        $this->showGroupDetailsModal = false;
        $this->groupDetailsHistory = null;
    }

    /**
     * Get date range for a given timeframe (used by export modal).
     */
    private function getDateRangeForTimeframe(string $timeframe): array
    {
        if ($timeframe === 'all') {
            return [Carbon::create(1900, 1, 1)->startOfDay(), Carbon::now()->addYear()->endOfDay()];
        }
        $end = Carbon::today()->endOfDay();
        $start = match ($timeframe) {
            'today'       => Carbon::today()->startOfDay(),
            'last_7_days' => Carbon::today()->subDays(6)->startOfDay(),
            'last_30_days' => Carbon::today()->subDays(29)->startOfDay(),
            default       => Carbon::create(1900, 1, 1)->startOfDay(),
        };
        return [$start, $end];
    }

    /**
     * Base query for transaction history, respecting the page date filter.
     */
    protected function historyQuery(): Builder
    {
        $query = TransactionHistory::query()
            ->with(['customer', 'transaction.product', 'payment']);

        // Use snapshot columns directly
        if ($this->dateFilter !== 'all') {
            [$start, $end] = $this->getDateRange();
            $query->whereBetween('transaction_date', [$start, $end]);
        }

        if (trim($this->searchDate) !== '') {
            $query->whereDate('transaction_date', $this->searchDate);
        }

        if (trim($this->searchCustomerName) !== '') {
            $query->where('customer_name', 'like', '%' . trim($this->searchCustomerName) . '%');
        }

        if (trim($this->searchItemName) !== '') {
            $query->where('product_name', 'like', '%' . trim($this->searchItemName) . '%');
        }

        if (trim($this->searchTransactionNumber) !== '') {
            $term = trim($this->searchTransactionNumber);
            $query->whereHas('transaction', function ($q) use ($term) {
                $q->where('transaction_number', 'like', '%' . $term . '%');
            });
        }

        return $query->orderByDesc('created_at');
    }

    /**
     * Open the export modal with timeframe selection.
     */
    public function openExportModal(): void
    {
        $this->exportTimeframe = 'all';
        $this->showExportModal = true;
    }

    /**
     * Export the filtered transaction history as a CSV, using the selected export timeframe.
     */
    public function exportWithTimeframe(): StreamedResponse
    {
        $this->showExportModal = false;

        // Build query based on $this->exportTimeframe (independent of page filter)
        [$start, $end] = $this->getDateRangeForTimeframe($this->exportTimeframe);
        $query = TransactionHistory::query()
            ->with(['customer', 'transaction', 'payment']);

        if ($this->exportTimeframe !== 'all') {
            $query->whereHas('transaction', function (Builder $q) use ($start, $end) {
                $q->whereBetween('transaction_date', [$start, $end]);
            });
        } else {
            $query->whereHas('transaction');
        }

        // Apply the same search filters as the main list (customer, item, exact date)
        if (trim($this->searchCustomerName) !== '') {
            $query->whereHas('customer', function (Builder $q) {
                $q->where('name', 'like', '%' . $this->searchCustomerName . '%');
            });
        }
        if (trim($this->searchItemName) !== '') {
            $query->whereHas('transaction.product', function (Builder $q) {
                $q->where('name', 'like', '%' . $this->searchItemName . '%');
            });
        }
        if (trim($this->searchTransactionNumber) !== '') {
            $term = trim($this->searchTransactionNumber);
            $query->whereHas('transaction', function ($q) use ($term) {
                $q->where('transaction_number', 'like', '%' . $term . '%');
            });
        }
        if (trim($this->searchDate) !== '') {
            $query->whereHas('transaction', function (Builder $q) {
                $q->whereDate('transaction_date', $this->searchDate);
            });
        }

        $histories = $query->orderByDesc('created_at')->get();

        $fileName = 'transaction_history_' . $this->exportTimeframe . '_' . now()->format('Y-m-d_His') . '.csv';

        return response()->streamDownload(function () use ($histories) {
            $handle = fopen('php://output', 'w');
            // Add UTF-8 BOM for Excel compatibility
            fputs($handle, "\xEF\xBB\xBF");

            // CSV headers
            fputcsv($handle, [
                'Customer Name',
                'Product Name',
                'Transaction Date',
                'Due Date',
                'Payment Date',
                'Payment Method',
                'Reference Number',
                'Total Amount (₱)',
                'Cash Given / Payment Sent (₱)',
                'Change (₱)',
                'Surcharge (₱)',
                'Interest Rate (%)',
                'Duration (days)',
                'Status',
                'Notes',
            ]);

            foreach ($histories as $history) {
                $transaction = $history->transaction;
                $payment = $history->payment;
                $customer = $history->customer;

                if (!$transaction) {
                    continue;
                }

                // Use the snapshot-aware accessor for product name
                $productName = $transaction->display_product_name;

                fputcsv($handle, [
                    $customer?->name ?? 'N/A',
                    $productName,
                    $transaction->transaction_date ? $transaction->transaction_date->format('Y-m-d') : '',
                    $transaction->due_date ? $transaction->due_date->format('Y-m-d') : '',
                    $payment?->payment_date ? Carbon::parse($payment->payment_date)->format('Y-m-d') : '',
                    $payment?->payment_method ?? '',
                    $payment?->reference_number ?? '',
                    number_format($transaction->total_amount, 2),
                    number_format($payment?->cash_given ?? 0, 2),
                    number_format($payment?->change ?? 0, 2),
                    number_format($transaction->surcharge_total ?? 0, 2),
                    number_format($transaction->interest_rate ?? 0, 2),
                    $transaction->duration_days,
                    $transaction->status ?? '',
                    $transaction->notes ?? '',
                ]);
            }

            fclose($handle);
        }, $fileName, [
            'Content-Type' => 'text/csv',
        ]);
    }

    public function openDetails(int $transactionHistoryId): void
    {
        $this->selectedPayment = TransactionHistory::with([
            'customer',
            'transaction.product',
            'payment',
        ])->find($transactionHistoryId);

        $this->isDetailsModalOpen = true;
    }

    public function closeDetails(): void
    {
        $this->isDetailsModalOpen = false;
        $this->selectedPayment = null;
    }

    public function confirmDeleteAll(): void
    {
        $this->isDeleteAllModalOpen = true;
        $this->closeDetails();
    }

    public function cancelDeleteAll(): void
    {
        $this->isDeleteAllModalOpen = false;
    }

    public function deleteAll(): void
    {
        TransactionHistory::query()->delete();
        $this->isDeleteAllModalOpen = false;
        $this->closeDetails();
        $this->resetPage();
        $this->dispatch('showAlert', 'All transaction history has been deleted.');
    }

    public function render()
    {
        // Base query (without pagination) – fetch all matching records
        $query = TransactionHistory::query()
            ->with(['customer', 'transaction.product', 'payment'])
            ->where('status', 'PAID') // only paid transactions
            ->whereNotNull('payments_id'); // only those with payment link

        // Apply filters (same as before)
        if ($this->dateFilter !== 'all') {
            [$start, $end] = $this->getDateRange();
            $query->whereBetween('transaction_date', [$start, $end]);
        }

        if (trim($this->searchDate) !== '') {
            $query->whereDate('transaction_date', $this->searchDate);
        }

        if (trim($this->searchCustomerName) !== '') {
            $query->where('customer_name', 'like', '%' . trim($this->searchCustomerName) . '%');
        }

        if (trim($this->searchItemName) !== '') {
            $query->where('product_name', 'like', '%' . trim($this->searchItemName) . '%');
        }

        if (trim($this->searchTransactionNumber) !== '') {
            $term = trim($this->searchTransactionNumber);
            $query->whereHas('transaction', function ($q) use ($term) {
                $q->where('transaction_number', 'like', '%' . $term . '%');
            });
        }

        $histories = $query->orderByDesc('payment_date')->get();

        // Group by payments_id
        $groups = $histories->groupBy('payments_id')->map(function ($items) {
            $first = $items->first();
            $totalAmount = $items->sum('total_amount');
            $productNames = $items
                ->take(3)
                ->pluck('product_name')
                ->implode(', ');

            if ($items->count() > 3) {
                $productNames .= ', ...';
            }
            $transactionNumber = $first->transaction->transaction_number ?? '—';
            return (object) [
                'payment_id'      => $first->payments_id,
                'transaction_number' => $transactionNumber,
                'customer_name'   => $first->customer_name ?? 'Deleted Customer',
                'payment_date'    => $first->payment_date, // datetime
                'total_amount'    => $totalAmount,
                'product_names'   => $productNames,
                'items_count'     => $items->count(),
                'history_items'   => $items, // for details
            ];
        })->values();

        // Manual pagination
        $currentPage = \Illuminate\Pagination\Paginator::resolveCurrentPage();
        $perPage = 5;
        $currentItems = $groups->slice(($currentPage - 1) * $perPage, $perPage)->values();
        $paginatedGroups = new \Illuminate\Pagination\LengthAwarePaginator(
            $currentItems,
            $groups->count(),
            $perPage,
            $currentPage,
            ['path' => \Illuminate\Pagination\Paginator::resolveCurrentPath()]
        );

        return view('livewire.transactions-history', ['groups' => $paginatedGroups]);
    }
}
