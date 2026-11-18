<?php

namespace App\Exports;

use App\Models\Transaction;
use Carbon\Carbon;
use Maatwebsite\Excel\Concerns\FromArray;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithTitle;

class AgingReceivablesSheet implements FromArray, WithHeadings, WithTitle
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

        $rows = [];
        foreach ($buckets as $bucket => $data) {
            $rows[] = [$bucket, $data['count'], number_format($data['total'], 2)];
        }
        return $rows;
    }

    public function headings(): array
    {
        return ['Overdue Bucket', 'Number of Transactions', 'Total Amount (₱)'];
    }

    public function title(): string
    {
        return 'Aging of Receivables';
    }
}
