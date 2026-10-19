<?php

namespace App\Console\Commands;

use App\Models\Customer;
use Illuminate\Console\Command;

class UpdateCustomerRiskLevels extends Command
{
    protected $signature = 'customers:update-risk-levels';
    protected $description = 'Recalculate risk levels for all customers';

    public function handle()
    {
        $customers = Customer::all();
        $count = 0;

        foreach ($customers as $customer) {
            $customer->updateRiskLevel();
            $count++;
        }

        $this->info("Recalculated risk levels for {$count} customers.");
        return 0;
    }
}
