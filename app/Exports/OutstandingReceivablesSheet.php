<?php

namespace App\Exports;

use App\Models\Customer;
use App\Models\Transaction;
use Carbon\Carbon;
use Maatwebsite\Excel\Concerns\FromArray;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithTitle;

class OutstandingReceivablesSheet implements FromArray, WithHeadings, WithTitle
{
    protected string $dateFilter;

    public function __construct(string $dateFilter)
    {
        $this->dateFilter = $dateFilter;
    }

    protected function getDateRange(): array
    {
        $end = Carbon::today()->endOfDay();
        $start = match ($this->dateFilter) {
            'today'       => Carbon::today()->startOfDay(),
            'last_7_days' => Carbon::today()->subDays(6)->startOfDay(),
            'last_30_days' => Carbon::today()->subDays(29)->startOfDay(),
            default       => Carbon::create(1900, 1, 1)->startOfDay(),
        };
        return [$start, $end];
    }

    public function array(): array
    {
        [$start, $end] = $this->getDateRange();

        $customers = Customer::whereHas('transactions', function ($q) use ($start, $end) {
            $q->where('status', '!=', 'PAID')
                ->whereBetween('transaction_date', [$start, $end]);
        })
            ->with(['transactions' => function ($q) use ($start, $end) {
                $q->where('status', '!=', 'PAID')
                    ->whereBetween('transaction_date', [$start, $end]);
            }])
            ->get()
            ->map(function ($customer) {
                $outstanding = $customer->transactions->sum(fn($tx) => $tx->current_total);
                $customer->outstanding_balance = $outstanding;
                return $customer;
            })
            ->filter(fn($c) => $c->outstanding_balance > 0)
            ->sortByDesc('outstanding_balance');

        $rows = [];
        foreach ($customers as $customer) {
            $rows[] = [
                $customer->name,
                number_format($customer->outstanding_balance, 2),
                number_format($customer->credit_limit ?? 0, 2),
                $customer->risk_level ?? 'LOW',
                $customer->days_since_last_payment ?? 0,
            ];
        }
        return $rows;
    }

    public function headings(): array
    {
        return ['Customer Name', 'Outstanding Balance (₱)', 'Credit Limit (₱)', 'Risk Level', 'Days Since Last Payment'];
    }

    public function title(): string
    {
        return 'Outstanding Receivables';
    }
}
