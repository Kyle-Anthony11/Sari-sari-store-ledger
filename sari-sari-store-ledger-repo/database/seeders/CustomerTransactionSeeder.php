<?php

namespace Database\Seeders;

use App\Models\Customer;
use App\Models\Product;
use App\Models\Transaction;
use App\Models\Payment;
use App\Models\TransactionHistory;
use App\Models\StoreProfile;
use App\Models\StoreSetting;
use Carbon\Carbon;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class CustomerTransactionSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // 1. Ensure a store profile exists (do NOT create one)
        $store = StoreProfile::first();
        if (!$store) {
            $this->command->warn('No store profile found. Skipping seed to avoid admin data creation.');
            return;
        }

        // 2. Ensure store settings match the required values
        $settings = StoreSetting::firstOrCreate(
            ['store_profile_id' => $store->id],
            [
                'surcharge' => 5.00,
                'interest_rate' => 5.00,
                'transaction_duration_days' => 7,
                'max_credit_limit' => 500.00,
            ]
        );
        // Force update to match the given rules
        $settings->update([
            'surcharge' => 5.00,
            'interest_rate' => 5.00,
            'transaction_duration_days' => 7,
            'max_credit_limit' => 500.00,
        ]);

        $surchargePerUnit = (float) $settings->surcharge;
        $interestRate = (float) $settings->interest_rate;
        $durationDays = (int) $settings->transaction_duration_days;
        $globalMaxLimit = (float) $settings->max_credit_limit; // 500

        // 3. Create products (if none exist)
        $products = [
            ['name' => 'Rice (1kg)', 'unit' => 'Kilos', 'price' => 52.00],
            ['name' => 'Cooking Oil (1L)', 'unit' => 'Pieces', 'price' => 85.00],
            ['name' => 'Sugar (1kg)', 'unit' => 'Kilos', 'price' => 70.00],
            ['name' => 'Milk (1L)', 'unit' => 'Pieces', 'price' => 95.00],
            ['name' => 'Canned Sardines', 'unit' => 'Pieces', 'price' => 25.00],
            ['name' => 'Instant Noodles', 'unit' => 'Pieces', 'price' => 15.00],
            ['name' => 'Bread Loaf', 'unit' => 'Pieces', 'price' => 55.00],
            ['name' => 'Eggs (per tray)', 'unit' => 'Pieces', 'price' => 180.00],
        ];

        foreach ($products as $prod) {
            Product::firstOrCreate(
                ['name' => $prod['name'], 'unit' => $prod['unit']],
                ['price' => $prod['price'], 'is_active' => true]
            );
        }

        // Fetch all active products
        $allProducts = Product::where('is_active', true)->get();

        // 4. Create customers with credit limits up to 500
        $customersData = [
            ['name' => 'Maria Santos', 'contact' => '09123456789', 'address' => '123 Rizal St.', 'limit' => 500],
            ['name' => 'Juan Dela Cruz', 'contact' => '09234567890', 'address' => '456 Mabini Ave.', 'limit' => 450],
            ['name' => 'Ana Reyes', 'contact' => '09345678901', 'address' => '789 Bonifacio Dr.', 'limit' => 300],
            ['name' => 'Pedro Lopez', 'contact' => '09456789012', 'address' => '101 Luna St.', 'limit' => 500],
            ['name' => 'Rosa Martinez', 'contact' => '09567890123', 'address' => '202 Rizal St.', 'limit' => 200],
            ['name' => 'Carlos Gomez', 'contact' => '09678901234', 'address' => '303 Mabini Ave.', 'limit' => 480],
            ['name' => 'Linda Flores', 'contact' => '09789012345', 'address' => '404 Bonifacio Dr.', 'limit' => 350],
        ];

        $customers = [];
        foreach ($customersData as $cData) {
            $customer = Customer::firstOrCreate(
                ['name' => $cData['name']],
                [
                    'contact_number' => $cData['contact'],
                    'address' => $cData['address'],
                    'credit_limit' => min($cData['limit'], $globalMaxLimit),
                    'is_active' => true,
                    'total_balance' => 0,
                    'risk_level' => 'LOW',
                    'days_since_last_payment' => 0,
                ]
            );
            $customers[] = $customer;
        }

        // 5. Generate transactions for the last 3 months
        $startDate = Carbon::today()->subMonths(3);
        $endDate = Carbon::today();

        DB::transaction(function () use ($customers, $allProducts, $startDate, $endDate, $surchargePerUnit, $interestRate, $durationDays, $globalMaxLimit) {
            foreach ($customers as $customer) {
                // Number of transactions per customer (3 to 8)
                $numTransactions = rand(3, 8);
                $transactionDates = [];

                for ($i = 0; $i < $numTransactions; $i++) {
                    // Random transaction date within the last 3 months
                    $transactionDate = Carbon::createFromTimestamp(rand($startDate->timestamp, $endDate->timestamp))->startOfDay();
                    // Avoid duplicate dates for simplicity
                    if (in_array($transactionDate->toDateString(), $transactionDates)) {
                        continue;
                    }
                    $transactionDates[] = $transactionDate->toDateString();

                    // Random product
                    $product = $allProducts->random();
                    $unitPrice = (float) $product->price;

                    // Random quantity (pieces: 1-12, kilos: 0.5 - 5.0)
                    $quantity = $product->unit === 'Kilos'
                        ? round(rand(5, 50) / 10, 1)  // 0.5 to 5.0
                        : rand(1, 12);

                    $baseTotal = $unitPrice * $quantity;
                    $surchargeTotal = $surchargePerUnit * $quantity;
                    $transactionTotal = $baseTotal + $surchargeTotal;

                    // Check credit limit before creating transaction
                    $currentBalance = (float) $customer->total_balance;
                    $newBalance = $currentBalance + $transactionTotal;
                    $effectiveLimit = min($customer->credit_limit, $globalMaxLimit);
                    if ($effectiveLimit > 0 && $newBalance > $effectiveLimit) {
                        // Skip this transaction to avoid exceeding limit
                        continue;
                    }

                    $dueDate = (clone $transactionDate)->addDays($durationDays);
                    $today = Carbon::today();

                    // Determine status and payment details
                    $status = 'PENDING';
                    $paymentDate = null;
                    $paidAmount = null;

                    if ($dueDate->lt($today)) {
                        // 60% chance to be OVERDUE, 40% chance to be PAID (late payment)
                        if (rand(1, 100) <= 60) {
                            $status = 'OVERDUE';
                        } else {
                            $status = 'PAID';
                            // Late payment: payment date after due date (random up to today)
                            $paymentDate = Carbon::createFromTimestamp(rand($dueDate->timestamp, $today->timestamp))->startOfDay();
                            $daysOverdue = $paymentDate->diffInDays($dueDate);
                            $periods = (int) ceil($daysOverdue / 7);
                            $interest = $baseTotal * ($interestRate / 100) * $periods;
                            $paidAmount = $transactionTotal + $interest;
                        }
                    } else {
                        // Not yet overdue: 50% chance to be PAID on time, 50% PENDING
                        if (rand(1, 100) <= 50) {
                            $status = 'PAID';
                            // On‑time payment: between transaction date and due date
                            $maxDate = $dueDate->copy()->subDay();
                            if ($maxDate->gte($transactionDate)) {
                                $paymentDate = Carbon::createFromTimestamp(rand($transactionDate->timestamp, $maxDate->timestamp))->startOfDay();
                            } else {
                                $paymentDate = clone $transactionDate;
                            }
                            $paidAmount = $transactionTotal;
                        } else {
                            $status = 'PENDING';
                        }
                    }

                    // Create the transaction
                    $transaction = Transaction::create([
                        'products_id' => $product->id,
                        'customer_id' => $customer->id,
                        'quantity' => $quantity,
                        'base_total' => $baseTotal,
                        'surcharge_total' => $surchargeTotal,
                        'interest_rate' => $interestRate,
                        'total_amount' => $transactionTotal,
                        'transaction_date' => $transactionDate,
                        'due_date' => $dueDate,
                        'status' => $status,
                        'notes' => 'Seeded transaction',
                    ]);

                    // Update customer balance (add the original debt)
                    $customer->increment('total_balance', $transactionTotal);

                    // If PAID, create payment record and adjust balance
                    if ($status === 'PAID' && $paymentDate && $paidAmount !== null) {
                        $payment = Payment::create([
                            'transactions_id' => $transaction->id,
                            'customer_id' => $customer->id,
                            'total_amount' => $paidAmount,
                            'cash_given' => $paidAmount,
                            'change' => 0,
                            'payment_date' => $paymentDate,
                            'reference_number' => 'PAY-' . Str::uuid(),
                            'notes' => 'Seeded payment',
                        ]);

                        $transaction->update(['payment_id' => $payment->id]);

                        TransactionHistory::create([
                            'customer_id' => $customer->id,
                            'transactions_id' => $transaction->id,
                            'payments_id' => $payment->id,
                        ]);

                        // Subtract the paid amount (including any interest) from balance
                        $customer->decrement('total_balance', $paidAmount);
                    }
                }

                // After all transactions, recalculate risk level and days since last payment
                $customer->updateRiskLevel();
            }
        });

        $this->command->info('Customer and transaction data seeded successfully for the last 3 months.');
    }
}
