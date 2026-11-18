<?php

namespace Database\Seeders;

use App\Models\Customer;
use App\Models\Payment;
use App\Models\Product;
use App\Models\Transaction;
use App\Models\TransactionHistory;
use App\Services\TransactionNumberGenerator;
use Carbon\Carbon;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class TransactionSeeder extends Seeder
{
    /** Store settings — matches admin panel */
    private float $surchargePerUnit = 5.00;
    private float $interestRate     = 5.00;
    private int   $durationDays     = 7;
    private float $maxCreditLimit   = 500.00;

    public function run(): void
    {
        $today = Carbon::today();

        // ── Clean existing transaction data ──────────────────────
        TransactionHistory::query()->delete();
        Transaction::query()->delete();
        Payment::query()->delete();
        DB::table('transaction_number_sequences')->delete();

        // ── Ensure base data exists ──────────────────────────────
        if (Product::count() === 0) {
            $this->seedProducts();
        }
        if (Customer::count() === 0) {
            $this->seedCustomers();
        }

        $customers = Customer::orderBy('id')->get();
        $products  = Product::where('is_active', true)->orderBy('id')->get();

        if ($customers->isEmpty() || $products->isEmpty()) {
            $this->command->error('Customers or products missing — aborting.');
            return;
        }

        $generator = app(TransactionNumberGenerator::class);

        // ═══════════════════════════════════════════════════════
        // 1) PAID TRANSACTIONS  (with payment + history)
        //    [days_ago_tx, days_before_due_paid, method, ref]
        // ═══════════════════════════════════════════════════════
        $paidScenarios = [
            [32, 3, 'cash',  null],
            [26, 2, 'gcash', 'GC-1001-2025'],
            [22, 0, 'cash',  null],
            [18, 1, 'cash',  null],
            [15, 4, 'gcash', 'GC-1002-2025'],
            [10, 2, 'cash',  null],
            [6, 0, 'cash',  null],
        ];

        foreach ($paidScenarios as $i => [$daysAgo, $paidBefore, $method, $ref]) {
            $customer  = $customers[$i % $customers->count()];
            $itemCount = 1 + ($i % 2); // 1 or 2 products
            $items     = $this->pickProducts($products, $i, $itemCount);

            $txDate  = $today->copy()->subDays($daysAgo);
            $dueDate = $txDate->copy()->addDays($this->durationDays);
            $payDate = $dueDate->copy()->subDays($paidBefore);

            $this->seedPaidGroup(
                $customer,
                $items,
                $txDate,
                $dueDate,
                $payDate,
                $method,
                $ref,
                $generator
            );
        }

        // ═══════════════════════════════════════════════════════
        // 2) PENDING TRANSACTIONS  (due in future)
        //    [days_ago_tx, item_count]
        // ═══════════════════════════════════════════════════════
        $pendingScenarios = [
            [5, 2],
            [3, 1],
            [2, 3],
            [1, 1],
            [0, 2], // created today → due in 7 days
        ];

        foreach ($pendingScenarios as $i => [$daysAgo, $itemCount]) {
            $customer = $customers[($i + 2) % $customers->count()];
            $items    = $this->pickProducts($products, $i + 7, $itemCount);

            $txDate  = $today->copy()->subDays($daysAgo);
            $dueDate = $txDate->copy()->addDays($this->durationDays);

            $this->seedPendingGroup(
                $customer,
                $items,
                $txDate,
                $dueDate,
                $generator
            );
        }

        // ═══════════════════════════════════════════════════════
        // 3) OVERDUE TRANSACTIONS
        //    [days_ago_tx, item_count]  (due = txDate + 7)
        // ═══════════════════════════════════════════════════════
        $overdueScenarios = [
            [12, 1], // ~5 days overdue
            [18, 2], // ~11 days overdue
            [25, 1], // ~18 days overdue
            [40, 2], // ~33 days overdue
        ];

        foreach ($overdueScenarios as $i => [$daysAgo, $itemCount]) {
            $customer = $customers[($i + 1) % $customers->count()];
            $items    = $this->pickProducts($products, $i + 15, $itemCount);

            $txDate  = $today->copy()->subDays($daysAgo);
            $dueDate = $txDate->copy()->addDays($this->durationDays);

            $this->seedOverdueGroup(
                $customer,
                $items,
                $txDate,
                $dueDate,
                $generator
            );
        }

        // ── Recalculate balances & risk levels ───────────────────
        foreach ($customers as $customer) {
            $balance = (float) $customer->transactions()
                ->where('status', '!=', 'PAID')
                ->sum('total_amount');

            $customer->total_balance = round($balance, 2);
            $customer->save();
            $customer->updateRiskLevel();
        }

        $this->command->info('✔  Transaction seed complete.');
        $this->command->info('   Paid:    ' . count($paidScenarios) . ' groups');
        $this->command->info('   Pending: ' . count($pendingScenarios) . ' groups');
        $this->command->info('   Overdue: ' . count($overdueScenarios) . ' groups');
    }

    // ═══════════════════════════════════════════════════════════
    // GROUP SEEDERS
    // ═══════════════════════════════════════════════════════════

    private function seedPaidGroup(
        Customer $customer,
        array $products,
        Carbon $txDate,
        Carbon $dueDate,
        Carbon $payDate,
        string $paymentMethod,
        ?string $referenceNumber,
        TransactionNumberGenerator $generator
    ): void {
        $groupId  = (string) Str::uuid();
        $txNumber = $generator->generate();

        // Build lines first so we know the grand total
        $lines      = [];
        $grandTotal = 0.0;

        foreach ($products as $product) {
            $qty   = 2.0;
            $base  = round((float) $product->price * $qty, 2);
            $sur   = round($this->surchargePerUnit * $qty, 2);
            $total = round($base + $sur, 2);

            $lines[] = [
                'product'         => $product,
                'quantity'        => $qty,
                'base_total'      => $base,
                'surcharge_total' => $sur,
                'line_total'      => $total,
            ];
            $grandTotal += $total;
        }
        $grandTotal = round($grandTotal, 2);

        $cashGiven = round($grandTotal + 20, 2);
        $change    = 20.0;

        // 1) Create transactions (status updated to PAID after payment)
        $transactions = [];
        foreach ($lines as $line) {
            $transactions[] = Transaction::create([
                'products_id'               => $line['product']->id,
                'customer_id'               => $customer->id,
                'quantity'                  => $line['quantity'],
                'base_total'                => $line['base_total'],
                'surcharge_total'           => $line['surcharge_total'],
                'interest_rate'             => 0, // paid on/before due date
                'total_amount'              => $line['line_total'],
                'transaction_date'          => $txDate->toDateString(),
                'due_date'                  => $dueDate->toDateString(),
                'status'                    => 'PENDING', // temp status
                'notes'                     => '',
                'product_name'              => $line['product']->name,
                'product_unit'              => $line['product']->unit,
                'surcharge_at_creation'     => $this->surchargePerUnit,
                'interest_rate_at_creation' => $this->interestRate,
                'duration_days_at_creation' => $this->durationDays,
                'group_id'                  => $groupId,
                'transaction_number'        => $txNumber,
            ]);
        }

        // 2) Create payment referencing the first transaction
        $payment = Payment::create([
            'transactions_id'  => $transactions[0]->id,
            'customer_id'      => $customer->id,
            'total_amount'     => $grandTotal,
            'cash_given'       => $cashGiven,
            'change'           => $change,
            'payment_date'     => $payDate->copy()->setTime(10, 30),
            'payment_method'   => $paymentMethod,
            'reference_number' => $paymentMethod === 'gcash' ? $referenceNumber : null,
        ]);

        // 3) Mark transactions PAID + write history snapshot
        foreach ($transactions as $idx => $tx) {
            $tx->update([
                'status'     => 'PAID',
                'payment_id' => $payment->id,
            ]);

            $line = $lines[$idx];

            TransactionHistory::create([
                'customer_id'      => $customer->id,
                'transactions_id'  => $tx->id,
                'payments_id'      => $payment->id,
                'customer_name'    => $customer->name,
                'product_name'     => $line['product']->name,
                'transaction_date' => $txDate->toDateString(),
                'due_date'         => $dueDate->toDateString(),
                'payment_date'     => $payDate->copy()->setTime(10, 30),
                'surcharge_total'  => $line['surcharge_total'],
                'interest_rate'    => 0,
                'duration_days'    => $this->durationDays,
                'total_amount'     => $line['line_total'],
                'cash_given'       => $cashGiven,
                'change'           => $change,
                'status'           => 'PAID',
                'payment_method'   => $paymentMethod,
                'reference_number' => $paymentMethod === 'gcash' ? $referenceNumber : null,
            ]);
        }
    }

    private function seedPendingGroup(
        Customer $customer,
        array $products,
        Carbon $txDate,
        Carbon $dueDate,
        TransactionNumberGenerator $generator
    ): void {
        $groupId  = (string) Str::uuid();
        $txNumber = $generator->generate();

        foreach ($products as $product) {
            $qty  = 2.0;
            $base = round((float) $product->price * $qty, 2);
            $sur  = round($this->surchargePerUnit * $qty, 2);

            Transaction::create([
                'products_id'               => $product->id,
                'customer_id'               => $customer->id,
                'quantity'                  => $qty,
                'base_total'                => $base,
                'surcharge_total'           => $sur,
                'interest_rate'             => $this->interestRate,
                'total_amount'              => round($base + $sur, 2),
                'transaction_date'          => $txDate->toDateString(),
                'due_date'                  => $dueDate->toDateString(),
                'status'                    => 'PENDING',
                'notes'                     => '',
                'product_name'              => $product->name,
                'product_unit'              => $product->unit,
                'surcharge_at_creation'     => $this->surchargePerUnit,
                'interest_rate_at_creation' => $this->interestRate,
                'duration_days_at_creation' => $this->durationDays,
                'group_id'                  => $groupId,
                'transaction_number'        => $txNumber,
            ]);
        }
    }

    private function seedOverdueGroup(
        Customer $customer,
        array $products,
        Carbon $txDate,
        Carbon $dueDate,
        TransactionNumberGenerator $generator
    ): void {
        $groupId  = (string) Str::uuid();
        $txNumber = $generator->generate();

        foreach ($products as $product) {
            $qty  = 2.0;
            $base = round((float) $product->price * $qty, 2);
            $sur  = round($this->surchargePerUnit * $qty, 2);

            // Note: `total_amount` stays = base + surcharge. The
            // `getCurrentTotalAttribute()` accessor dynamically adds
            // accrued weekly interest at read time — same behavior as
            // production.
            Transaction::create([
                'products_id'               => $product->id,
                'customer_id'               => $customer->id,
                'quantity'                  => $qty,
                'base_total'                => $base,
                'surcharge_total'           => $sur,
                'interest_rate'             => $this->interestRate,
                'total_amount'              => round($base + $sur, 2),
                'transaction_date'          => $txDate->toDateString(),
                'due_date'                  => $dueDate->toDateString(),
                'status'                    => 'OVERDUE',
                'notes'                     => '',
                'product_name'              => $product->name,
                'product_unit'              => $product->unit,
                'surcharge_at_creation'     => $this->surchargePerUnit,
                'interest_rate_at_creation' => $this->interestRate,
                'duration_days_at_creation' => $this->durationDays,
                'group_id'                  => $groupId,
                'transaction_number'        => $txNumber,
            ]);
        }
    }

    // ═══════════════════════════════════════════════════════════
    // HELPERS
    // ═══════════════════════════════════════════════════════════

    private function pickProducts($products, int $offset, int $count): array
    {
        $picked = [];
        for ($i = 0; $i < $count; $i++) {
            $picked[] = $products[($offset + $i) % $products->count()];
        }
        return $picked;
    }

    // ═══════════════════════════════════════════════════════════
    // FALLBACK SEEDERS (only if base tables are empty)
    // ═══════════════════════════════════════════════════════════

    private function seedProducts(): void
    {
        $products = [
            ['name' => 'Skyflakes Crackers', 'unit' => 'Pieces', 'price' => 12.00],
            ['name' => 'Pancit Canton',      'unit' => 'Pieces', 'price' => 18.00],
            ['name' => 'Coke in Can',        'unit' => 'Pieces', 'price' => 35.00],
            ['name' => 'Bear Brand 33g',     'unit' => 'Pieces', 'price' => 25.00],
            ['name' => 'Rice (Sinandomeng)', 'unit' => 'Kilos',  'price' => 55.00],
            ['name' => 'Sugar (White)',      'unit' => 'Kilos',  'price' => 65.00],
            ['name' => 'Cooking Oil 500ml',  'unit' => 'Pieces', 'price' => 45.00],
            ['name' => 'Eggs (Medium)',      'unit' => 'Pieces', 'price' =>  8.00],
            ['name' => 'Sardinas 155g',      'unit' => 'Pieces', 'price' => 25.00],
            ['name' => 'Piattos Cheese',     'unit' => 'Pieces', 'price' => 35.00],
        ];

        foreach ($products as $p) {
            Product::create(array_merge($p, ['is_active' => true]));
        }
    }

    private function seedCustomers(): void
    {
        $customers = [
            ['name' => 'Maria Santos',     'contact_number' => '639171234567', 'address' => 'Purok 1, Brgy. San Jose'],
            ['name' => 'Juan Dela Cruz',   'contact_number' => '639181234567', 'address' => 'Purok 2, Brgy. San Jose'],
            ['name' => 'Ana Reyes',        'contact_number' => '639191234567', 'address' => 'Purok 3, Brgy. San Jose'],
            ['name' => 'Pedro Bautista',   'contact_number' => '639201234567', 'address' => 'Purok 4, Brgy. San Jose'],
            ['name' => 'Liza Mercado',     'contact_number' => '639211234567', 'address' => 'Purok 5, Brgy. San Jose'],
            ['name' => 'Ramon Villanueva', 'contact_number' => '639221234567', 'address' => 'Purok 6, Brgy. San Jose'],
        ];

        foreach ($customers as $c) {
            Customer::create(array_merge($c, [
                'is_active'               => true,
                'credit_limit'            => $this->maxCreditLimit,
                'total_balance'           => 0.00,
                'risk_level'              => 'LOW',
                'days_since_last_payment' => 0,
                'accepted_terms_at'       => now(),
            ]));
        }
    }
}
