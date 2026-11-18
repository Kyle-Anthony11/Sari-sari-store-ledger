<?php

namespace App\Console\Commands;

use App\Models\Customer;
use App\Models\Transaction;
use Carbon\Carbon;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;

class UpdateOverdueTransactions extends Command
{
    protected $signature = 'transactions:update-overdue';
    protected $description = 'Mark transactions as OVERDUE when due date has passed and they are not yet paid';

    public function handle()
    {
        $today = Carbon::today();

        $transactions = Transaction::where('status', '!=', 'PAID')
            ->where('status', '!=', 'OVERDUE')
            ->whereDate('due_date', '<', $today)
            ->get();

        if ($transactions->isEmpty()) {
            $this->info('No transactions need updating.');
            return 0;
        }

        $updatedCount = 0;
        $customerIds = [];

        DB::transaction(function () use ($transactions, &$updatedCount, &$customerIds) {
            foreach ($transactions as $transaction) {
                if ($transaction->status === 'PAID') {
                    continue;
                }

                $transaction->status = 'OVERDUE';
                $transaction->save();
                $updatedCount++;

                if ($transaction->customer_id && !in_array($transaction->customer_id, $customerIds)) {
                    $customerIds[] = $transaction->customer_id;
                }
            }
        });

        // Recalculate risk levels for affected customers
        foreach ($customerIds as $customerId) {
            $customer = Customer::find($customerId);
            if ($customer) {
                $customer->updateRiskLevel();
            }
        }

        $this->info("Updated {$updatedCount} transaction(s) to OVERDUE and recalculated risks for " . count($customerIds) . " customer(s).");

        return 0;
    }
}
