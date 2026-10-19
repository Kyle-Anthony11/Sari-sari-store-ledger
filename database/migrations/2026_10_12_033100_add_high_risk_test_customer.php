<?php

use App\Models\Customer;
use App\Models\Product;
use App\Models\Transaction;
use Carbon\Carbon;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Str;

return new class extends Migration
{
    public function up(): void
    {
        // Idempotent — skip if already seeded
        if (Customer::where('name', 'High Risk Test Customer')->exists()) {
            return;
        }

        // 1. Product — reuse an active one, or create a fresh one
        $product = Product::where('is_active', true)->first()
            ?? Product::create([
                'name'      => 'Test Product',
                'unit'      => 'Pieces',
                'price'     => 900.00,
                'is_active' => true,
            ]);

        // 2. Customer with a real credit limit
        $customer = Customer::create([
            'name'                  => 'High Risk Test Customer',
            'contact_number'        => '+639171234567',
            'address'               => 'Test Address',
            'is_active'             => true,
            'credit_limit'          => 1000.00,
            'total_balance'         => 0,
            'accepted_terms_at'     => now(),
            'terms_attachment_path' => null,
        ]);

        // 3. An OVERDUE transaction whose total == the credit limit
        //    (base_total + surcharge_total = 900 + 100 = 1000 → ratio = 1.0)
        Transaction::create([
            'products_id'                => $product->id,
            'customer_id'                => $customer->id,
            'quantity'                   => 1,
            'base_total'                 => 900.00,
            'surcharge_total'            => 100.00,
            'interest_rate'              => 5.00,
            'total_amount'               => 1000.00,
            'transaction_date'           => Carbon::today()->subDays(60),
            'due_date'                   => Carbon::today()->subDays(30),
            'status'                     => 'OVERDUE',
            'product_name'               => $product->name,
            'product_unit'               => $product->unit,
            'surcharge_at_creation'      => 100.00,
            'interest_rate_at_creation'  => 5.00,
            'duration_days_at_creation'  => 30,
            'group_id'                   => (string) Str::uuid(),
        ]);

        // 4. Reflect the outstanding balance on the customer
        $customer->update(['total_balance' => 1000.00]);

        // 5. Let the app compute the risk level using its own formula.
        //    Expected score:
        //      +2  no payments (days_since_last_payment = 365)
        //      +2  1 overdue transaction
        //      +1  overdue total > 0
        //      +2  balance / credit_limit = 1.0
        //      ---
        //       7  →  HIGH
        $customer->updateRiskLevel();
    }

    public function down(): void
    {
        $customer = Customer::where('name', 'High Risk Test Customer')->first();

        if ($customer) {
            Transaction::where('customer_id', $customer->id)->delete();
            $customer->delete();
        }
    }
};
