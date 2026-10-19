<?php

namespace Database\Seeders;

use App\Models\Customer;
use App\Models\Payment;
use App\Models\Product;
use App\Models\StoreProfile;
use App\Models\Transaction;
use App\Models\TransactionHistory;
use Carbon\Carbon;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class DemoTransactionSeeder extends Seeder
{
    /** Marker used to identify rows this seeder owns. */
    protected const DEMO_NOTE_PREFIX = 'Demo seeded transaction';

    /** Paid history: covers today and the previous N days (N + 1 total). */
    protected int $paidDaysBack        = 30;
    protected int $paidGroupsPerDayMin = 2;
    protected int $paidGroupsPerDayMax = 4;

    /** Unpaid groups configuration. */
    protected array $unpaidPlan = [
        ['type' => 'pending',        'count' => 10],
        ['type' => 'almost_overdue', 'count' => 5],
        ['type' => 'overdue',        'count' => 5],
    ];

    /** Rolling counter used by nextDemoNumber(). */
    protected int $demoCounter = 0;

    // =====================================================================
    // ENTRY POINT
    // =====================================================================

    public function run(): void
    {
        // ---------- 0) Profile & admin-defined rules -----------------------
        $profile = StoreProfile::first();
        if (! $profile) {
            $this->command->error('No store profile found. Please complete the store setup first.');
            return;
        }

        $settings = $profile->settings;

        $surchargePerUnit = (float) ($settings?->surcharge ?? 0);
        $interestRate     = (float) ($settings?->interest_rate ?? 0);
        $durationDays     = (int)   ($settings?->transaction_duration_days ?? 30);
        $globalMaxCredit  = (float) ($settings?->max_credit_limit ?? 0);

        // ---------- 1) Wipe previous demo data -----------------------------
        $this->cleanPreviousDemoData();

        // ---------- 2) Ensure customers / products -------------------------
        $customers = $this->ensureCustomers();
        $products  = $this->ensureProducts();

        // ---------- 3) Effective credit limit per customer -----------------
        //   Mirrors Transactions.php:
        //     effective = global max IF (global > 0 AND (individual == 0 OR global < individual))
        //     otherwise individual
        $limits = $this->buildEffectiveLimits($customers, $globalMaxCredit);

        // ---------- 4) Balance tracker -------------------------------------
        $balance = [];
        foreach ($customers as $c) {
            $balance[$c->id] = (float) $c->fresh()->total_balance;
        }

        // ---------- 5) Seed 1 month of PAID history (up to today) ----------
        $paidStats = $this->seedPaidHistory(
            $customers,
            $products,
            $surchargePerUnit,
            $interestRate,
            $durationDays
        );

        // ---------- 6) Seed UNPAID groups within limits --------------------
        $unpaidStats = $this->seedUnpaidGroups(
            $customers,
            $products,
            $limits,
            $balance,
            $surchargePerUnit,
            $interestRate,
            $durationDays
        );

        // ---------- 7) Persist tracked balances ----------------------------
        foreach ($balance as $customerId => $newBalance) {
            $c = Customer::find($customerId);
            if ($c) {
                $c->update(['total_balance' => $newBalance]);
            }
        }

        // ---------- 8) Recalculate risk levels -----------------------------
        $this->command->info('Recalculating customer risk levels…');
        foreach (Customer::all() as $c) {
            $c->updateRiskLevel();
        }

        // ---------- 9) Summary ---------------------------------------------
        $this->command->newLine();
        $this->command->info('✅ Demo data seeded successfully.');

        $this->command->line(sprintf(
            '   • PAID history (%s → today)     : %d group(s) / %d line(s)',
            Carbon::today()->subDays($this->paidDaysBack)->format('M d, Y'),
            $paidStats['groups'],
            $paidStats['lines']
        ));
        $this->command->line("   • PENDING (due in future)          : {$unpaidStats['pending']} group(s)");
        $this->command->line("   • Almost overdue (due in 3–4 days) : {$unpaidStats['almost_overdue']} group(s)");
        $this->command->line("   • OVERDUE (already past due)       : {$unpaidStats['overdue']} group(s)");

        $this->command->newLine();
        $this->command->line('   Rules applied (from Store Settings):');
        $this->command->line('     – Surcharge per unit  : ₱' . number_format($surchargePerUnit, 2));
        $this->command->line("     – Interest rate       : {$interestRate}% per week");
        $this->command->line("     – Duration            : {$durationDays} days");
        $this->command->line('     – Global max credit   : ₱' . number_format($globalMaxCredit, 2));

        $this->command->newLine();
        $this->command->line('   Customer effective credit limits:');
        foreach ($customers as $c) {
            $individual = (float) ($c->credit_limit ?? 0);
            $effective  = $limits[$c->id] ?? 0;
            $used       = (float) Customer::find($c->id)->total_balance;
            $this->command->line(sprintf(
                '     – %-20s individual ₱%-10s effective ₱%-10s used ₱%-10s',
                $c->name,
                number_format($individual, 2),
                number_format($effective, 2),
                number_format($used, 2)
            ));
        }
    }

    // =====================================================================
    // CLEANUP
    // =====================================================================

    protected function cleanPreviousDemoData(): void
    {
        $marker = self::DEMO_NOTE_PREFIX . '%';

        $demoTxs = Transaction::where('notes', 'LIKE', $marker)->get();
        if ($demoTxs->isEmpty()) {
            return;
        }

        $this->command->info("Cleaning {$demoTxs->count()} previous demo transaction line(s)…");

        $txIds      = $demoTxs->pluck('id')->all();
        $paymentIds = $demoTxs->pluck('payment_id')->filter()->unique()->values()->all();

        // Wipe any history rows referencing demo tx / payments
        TransactionHistory::whereIn('transactions_id', $txIds)->delete();
        if (! empty($paymentIds)) {
            TransactionHistory::whereIn('payments_id', $paymentIds)->delete();
        }

        // Roll back balances for unpaid demo transactions
        foreach ($demoTxs->where('status', '!=', 'PAID')->groupBy('customer_id') as $customerId => $rows) {
            if (! $customerId) {
                continue;
            }
            $customer = Customer::find($customerId);
            if ($customer) {
                $reduction  = (float) $rows->sum('total_amount');
                $newBalance = max(0, (float) $customer->total_balance - $reduction);
                $customer->update(['total_balance' => $newBalance]);
            }
        }

        // Delete payments then the transactions themselves
        if (! empty($paymentIds)) {
            Payment::whereIn('id', $paymentIds)->delete();
        }
        Transaction::whereIn('id', $txIds)->delete();
    }

    // =====================================================================
    // CUSTOMERS & PRODUCTS
    // =====================================================================

    protected function ensureCustomers()
    {
        $customers = Customer::where('is_active', true)->get();
        if ($customers->count() >= 3) {
            return $customers;
        }

        $this->command->info('Creating demo customers…');
        $seed = [
            ['name' => 'Juan Dela Cruz',  'credit' => 5000],
            ['name' => 'Maria Santos',    'credit' => 3000],
            ['name' => 'Pedro Reyes',     'credit' => 4000],
            ['name' => 'Ana Bautista',    'credit' => 2500],
            ['name' => 'Jose Ramos',      'credit' => 6000],
            ['name' => 'Liza Mendoza',    'credit' => 3500],
            ['name' => 'Mark Villanueva', 'credit' => 4500],
            ['name' => 'Grace Aquino',    'credit' => 2000],
        ];

        foreach ($seed as $c) {
            Customer::firstOrCreate(
                ['name' => $c['name']],
                [
                    'contact_number'    => '63' . str_pad((string) random_int(0, 999999999), 9, '0', STR_PAD_LEFT),
                    'address'           => 'Sample Address, Barangay ' . random_int(1, 20),
                    'credit_limit'      => $c['credit'],
                    'total_balance'     => 0,
                    'is_active'         => true,
                    'accepted_terms_at' => now(),
                ]
            );
        }

        return Customer::where('is_active', true)->get();
    }

    protected function ensureProducts()
    {
        $products = Product::where('is_active', true)->get();
        if ($products->count() >= 5) {
            return $products;
        }

        $this->command->info('Creating demo products…');
        $seed = [
            ['name' => 'Coke 1.5L',         'unit' => 'Pieces', 'price' => 89.00],
            ['name' => 'Pancit Canton',     'unit' => 'Pieces', 'price' => 18.50],
            ['name' => 'Bear Brand 300g',   'unit' => 'Pieces', 'price' => 125.00],
            ['name' => 'Rice (per Kilo)',   'unit' => 'Kilos',  'price' => 55.00],
            ['name' => 'Sugar (per Kilo)',  'unit' => 'Kilos',  'price' => 78.00],
            ['name' => 'Cooking Oil 1L',    'unit' => 'Pieces', 'price' => 95.00],
            ['name' => 'Sky Flakes',        'unit' => 'Pieces', 'price' => 12.00],
            ['name' => 'Safeguard Soap',    'unit' => 'Pieces', 'price' => 45.00],
            ['name' => 'Alaska Evap 370ml', 'unit' => 'Pieces', 'price' => 42.00],
            ['name' => '555 Sardines',      'unit' => 'Pieces', 'price' => 27.50],
        ];

        foreach ($seed as $p) {
            Product::firstOrCreate(
                ['name' => $p['name']],
                ['unit' => $p['unit'], 'price' => $p['price'], 'is_active' => true]
            );
        }

        return Product::where('is_active', true)->get();
    }

    // =====================================================================
    // EFFECTIVE LIMIT
    // =====================================================================

    protected function buildEffectiveLimits($customers, float $globalMax): array
    {
        $limits = [];
        foreach ($customers as $c) {
            $individual = (float) ($c->credit_limit ?? 0);

            if ($globalMax > 0 && ($individual == 0 || $globalMax < $individual)) {
                $limits[$c->id] = $globalMax;
            } else {
                $limits[$c->id] = $individual;
            }
        }
        return $limits;
    }

    // =====================================================================
    // PAID HISTORY — from (today − N days) THROUGH today
    // =====================================================================

    protected function seedPaidHistory($customers, $products, float $surcharge, float $interest, int $duration): array
    {
        $this->command->info(
            'Seeding PAID history from '
                . Carbon::today()->subDays($this->paidDaysBack)->format('M d, Y')
                . ' through today…'
        );

        $groups = 0;
        $lines  = 0;
        $today  = Carbon::today();

        // dayOffset goes from $this->paidDaysBack → 0 inclusive,
        // so the last iteration uses $today itself.
        for ($dayOffset = $this->paidDaysBack; $dayOffset >= 0; $dayOffset--) {
            $txDate    = $today->copy()->subDays($dayOffset);
            $groupsDay = random_int($this->paidGroupsPerDayMin, $this->paidGroupsPerDayMax);

            for ($g = 0; $g < $groupsDay; $g++) {
                $created = $this->createPaidGroup(
                    $customers,
                    $products,
                    $surcharge,
                    $interest,
                    $duration,
                    $txDate
                );
                if ($created > 0) {
                    $groups++;
                    $lines += $created;
                }
            }
        }

        return ['groups' => $groups, 'lines' => $lines];
    }

    protected function createPaidGroup($customers, $products, float $surcharge, float $interest, int $duration, Carbon $txDate): int
    {
        if ($customers->isEmpty() || $products->isEmpty()) {
            return 0;
        }

        $customer = $customers->random();
        $groupId  = (string) Str::uuid();

        // Due date = transaction date + admin-defined duration
        $dueDate = $txDate->copy()->addDays($duration);

        // Payment happens 0–3 days after the purchase,
        // but never in the future (clamp to now).
        $paymentDate = $txDate->copy()->addDays(random_int(0, 3));
        if ($paymentDate->greaterThan(Carbon::now())) {
            $paymentDate = Carbon::now();
        }

        // 70% cash / 30% GCash
        $method = random_int(1, 100) <= 70 ? 'cash' : 'gcash';
        $ref    = $method === 'gcash' ? strtoupper(Str::random(10)) : null;

        // Pick 1–3 distinct products
        $lineCount = min(random_int(1, 3), $products->count());
        $chosen    = $products->random($lineCount);
        if ($chosen instanceof Product) {
            $chosen = collect([$chosen]);
        }

        $totalDue  = 0.0;
        $createdTx = [];

        DB::transaction(function () use (
            $customer,
            $chosen,
            $groupId,
            $txDate,
            $dueDate,
            $surcharge,
            $interest,
            $duration,
            &$totalDue,
            &$createdTx
        ) {
            foreach ($chosen as $product) {
                $qty   = $this->randomQuantityForUnit($product->unit);
                $price = (float) $product->price;

                $lineBase  = round($price * $qty, 2);
                $lineSur   = round($surcharge * $qty, 2);
                $lineTotal = $lineBase + $lineSur;

                $createdTx[] = Transaction::create([
                    'products_id'                => $product->id,
                    'customer_id'                => $customer->id,
                    'quantity'                   => $qty,
                    'base_total'                 => $lineBase,
                    'surcharge_total'            => $lineSur,
                    'interest_rate'              => $interest,
                    'total_amount'               => $lineTotal,
                    'transaction_date'           => $txDate,
                    'due_date'                   => $dueDate,
                    'status'                     => 'PAID',
                    'notes'                      => self::DEMO_NOTE_PREFIX . ' (PAID)',
                    'product_name'               => $product->name,
                    'product_unit'               => $product->unit,
                    'surcharge_at_creation'      => $surcharge,
                    'interest_rate_at_creation'  => $interest,
                    'duration_days_at_creation'  => $duration,
                    'group_id'                   => $groupId,
                    'transaction_number'         => $this->nextDemoNumber(),
                ]);

                $totalDue += $lineTotal;
            }
        });

        if (empty($createdTx)) {
            return 0;
        }

        // Cash: round up to nearest 50; GCash: exact amount
        $cashGiven = $method === 'cash' ? $this->roundUpTo($totalDue, 50) : $totalDue;
        $change    = round($cashGiven - $totalDue, 2);

        $payment = Payment::create([
            'transactions_id'  => $createdTx[0]->id,
            'customer_id'      => $customer->id,
            'total_amount'     => $totalDue,
            'cash_given'       => $cashGiven,
            'change'           => $change,
            'payment_date'     => $paymentDate,
            'payment_method'   => $method,
            'reference_number' => $ref,
        ]);

        foreach ($createdTx as $tx) {
            $tx->update(['payment_id' => $payment->id]);

            TransactionHistory::create([
                'customer_id'      => $customer->id,
                'customer_name'    => $customer->name,
                'transactions_id'  => $tx->id,
                'payments_id'      => $payment->id,
                'product_name'     => $tx->product_name,
                'transaction_date' => $tx->transaction_date,
                'due_date'         => $tx->due_date,
                'payment_date'     => $paymentDate,
                'surcharge_total'  => $tx->surcharge_total,
                'interest_rate'    => $tx->interest_rate,
                'duration_days'    => $duration,
                'total_amount'     => $tx->total_amount,
                'cash_given'       => $cashGiven,
                'change'           => $change,
                'status'           => 'PAID',
                'payment_method'   => $method,
                'reference_number' => $ref,
            ]);
        }

        return count($createdTx);
    }

    // =====================================================================
    // UNPAID GROUPS — strictly respect effective credit limit
    // =====================================================================

    protected function seedUnpaidGroups(
        $customers,
        $products,
        array $limits,
        array &$balance,
        float $surcharge,
        float $interest,
        int $duration
    ): array {
        $this->command->info('Seeding UNPAID groups (respecting effective credit limits)…');

        $stats = ['pending' => 0, 'almost_overdue' => 0, 'overdue' => 0];

        foreach ($this->unpaidPlan as $spec) {
            for ($i = 0; $i < $spec['count']; $i++) {
                $ok = $this->createUnpaidGroup(
                    $customers,
                    $products,
                    $limits,
                    $balance,
                    $surcharge,
                    $interest,
                    $duration,
                    $spec['type']
                );

                if ($ok) {
                    $stats[$spec['type']]++;
                } else {
                    $this->command->warn("   ⚠ Could not fit 1 '{$spec['type']}' group within limits — skipped.");
                }
            }
        }

        return $stats;
    }

    protected function createUnpaidGroup(
        $customers,
        $products,
        array $limits,
        array &$balance,
        float $surcharge,
        float $interest,
        int $duration,
        string $type
    ): bool {
        if ($customers->isEmpty() || $products->isEmpty()) {
            return false;
        }

        // ---- Dates / status ----
        switch ($type) {
            case 'overdue':
                $dueDate = Carbon::today()->subDays(random_int(1, 15));
                $status  = 'OVERDUE';
                break;
            case 'almost_overdue':
                $dueDate = Carbon::today()->addDays(random_int(3, 4));
                $status  = 'PENDING';
                break;
            case 'pending':
            default:
                $dueDate = Carbon::today()->addDays(random_int(5, 20));
                $status  = 'PENDING';
                break;
        }

        $txDate = $dueDate->copy()->subDays($duration);
        if ($txDate->greaterThan(Carbon::today())) {
            $txDate = Carbon::today()->subDays(random_int(1, 5));
        }

        // ---- Choose a customer who actually has headroom ----
        $candidate = null;
        $headroom  = 0.0;

        foreach ($customers->shuffle() as $c) {
            $limit = $limits[$c->id] ?? 0;
            $used  = $balance[$c->id] ?? 0;

            // limit <= 0 means "no limit configured" → treat as unlimited
            if ($limit <= 0) {
                $candidate = $c;
                $headroom  = PHP_FLOAT_MAX;
                break;
            }

            $room = $limit - $used;
            if ($room > 0) {
                $candidate = $c;
                $headroom  = $room;
                break;
            }
        }

        if (! $candidate) {
            return false; // every customer is maxed out
        }

        // ---- Choose line items that fit within remaining headroom ----
        $lineCount = min(random_int(1, 3), $products->count());
        $chosen    = [];

        foreach ($products->shuffle() as $product) {
            if (count($chosen) >= $lineCount) {
                break;
            }

            $qty   = $this->randomQuantityForUnit($product->unit);
            $price = (float) $product->price;

            $lineBase  = round($price * $qty, 2);
            $lineSur   = round($surcharge * $qty, 2);
            $lineTotal = $lineBase + $lineSur;

            // Enforce the limit — skip products that would overflow headroom
            if ($lineTotal > $headroom) {
                continue;
            }

            $chosen[] = [
                'product'   => $product,
                'qty'       => $qty,
                'lineBase'  => $lineBase,
                'lineSur'   => $lineSur,
                'lineTotal' => $lineTotal,
            ];

            $headroom -= $lineTotal;
        }

        if (empty($chosen)) {
            return false; // nothing fit
        }

        // ---- Insert ----
        $groupId = (string) Str::uuid();

        DB::transaction(function () use (
            $candidate,
            $chosen,
            $groupId,
            $txDate,
            $dueDate,
            $status,
            $surcharge,
            $interest,
            $duration,
            &$balance
        ) {
            $added = 0.0;

            foreach ($chosen as $item) {
                $product = $item['product'];

                Transaction::create([
                    'products_id'                => $product->id,
                    'customer_id'                => $candidate->id,
                    'quantity'                   => $item['qty'],
                    'base_total'                 => $item['lineBase'],
                    'surcharge_total'            => $item['lineSur'],
                    'interest_rate'              => $interest,
                    'total_amount'               => $item['lineTotal'],
                    'transaction_date'           => $txDate,
                    'due_date'                   => $dueDate,
                    'status'                     => $status,
                    'notes'                      => self::DEMO_NOTE_PREFIX . " ({$status})",
                    'product_name'               => $product->name,
                    'product_unit'               => $product->unit,
                    'surcharge_at_creation'      => $surcharge,
                    'interest_rate_at_creation'  => $interest,
                    'duration_days_at_creation'  => $duration,
                    'group_id'                   => $groupId,
                    'transaction_number'         => $this->nextDemoNumber(),
                ]);

                $added += $item['lineTotal'];
            }

            $balance[$candidate->id] = ($balance[$candidate->id] ?? 0) + $added;
        });

        return true;
    }

    // =====================================================================
    // HELPERS
    // =====================================================================

    /**
     * Unique demo transaction number in a range the app never uses:
     *   YYYYMMDD + 5 digits (starting from a random slot in 80000–99999).
     */
    protected function nextDemoNumber(): string
    {
        $datePrefix = Carbon::today()->format('Ymd');

        if ($this->demoCounter === 0) {
            $this->demoCounter = random_int(80000, 99999);
        }

        for ($attempt = 0; $attempt < 20000; $attempt++) {
            $this->demoCounter++;
            if ($this->demoCounter > 99999) {
                $this->demoCounter = 80000;
            }

            $candidate = $datePrefix . str_pad((string) $this->demoCounter, 5, '0', STR_PAD_LEFT);

            if (! Transaction::where('transaction_number', $candidate)->exists()) {
                return $candidate;
            }
        }

        throw new \RuntimeException('Could not generate a unique demo transaction number.');
    }

    protected function randomQuantityForUnit(string $unit): float
    {
        return $unit === 'Kilos'
            ? round(random_int(5, 50) / 10, 2) // 0.5 – 5.0 kg
            : (float) random_int(1, 10);       // 1 – 10 pieces
    }

    protected function roundUpTo(float $value, int $increment): float
    {
        if ($increment <= 0) {
            return $value;
        }
        return ceil($value / $increment) * $increment;
    }
}
