<?php

namespace App\Models;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Transaction extends Model
{
    use HasFactory;

    protected $fillable = [
        'products_id',
        'reference_number',
        'customer_id',
        'quantity',
        'total_amount',
        'base_total',
        'surcharge_total',
        'interest_rate',
        'transaction_date',
        'due_date',
        'status',
        'payment_id',
        'notes',
        'product_name',
        'product_unit',
        'surcharge_at_creation',
        'interest_rate_at_creation',
        'duration_days_at_creation',
        'group_id',
        'transaction_number',
    ];

    protected $casts = [
        'quantity' => 'decimal:15.2',
        'total_amount' => 'decimal:15.2',
        'base_total' => 'decimal:15.2',
        'surcharge_total' => 'decimal:15.2',
        'interest_rate' => 'decimal:5.2',
        'transaction_date' => 'date',
        'due_date' => 'date',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
        'surcharge_at_creation' => 'decimal:15.2',
        'interest_rate_at_creation' => 'decimal:5.2',
        'duration_days_at_creation' => 'integer',
        'group_id' => 'string',
    ];

    /**
     * Number of full/partial 7-day periods the transaction is overdue.
     * Returns 0 if not overdue yet.
     */
    public function getWeeksOverdueAttribute(): int
    {
        if (! $this->due_date) {
            return 0;
        }

        $today = Carbon::today();
        $due   = Carbon::parse($this->due_date);

        if (! $today->greaterThan($due)) {
            return 0;
        }

        return (int) ceil($due->diffInDays($today) / 7);
    }

    /**
     * Interest amount accrued on this transaction (snapshot rate).
     * Always 0 for PAID transactions.
     */
    public function getInterestAmountAttribute(): float
    {
        if ($this->status === 'PAID') {
            return 0.0;
        }

        // Use snapshot rate to stay in sync with the Edit Transaction modal.
        $rate = (float) ($this->interest_rate_at_creation ?? $this->interest_rate ?? 0);

        if ($rate <= 0) {
            return 0.0;
        }

        $weeks = $this->weeks_overdue;
        if ($weeks <= 0) {
            return 0.0;
        }

        return round((float) $this->base_total * ($rate / 100) * $weeks, 2);
    }

    /**
     * Current amount owed for this transaction line:
     *   base_total + surcharge_total + interest_amount
     * For PAID transactions it just returns the frozen total_amount.
     */
    public function getCurrentTotalAttribute(): float
    {
        if ($this->status === 'PAID') {
            return (float) $this->total_amount;
        }

        return round(
            (float) $this->base_total
            + (float) $this->surcharge_total
            + $this->interest_amount,
            2
        );
    }

    public function getDisplayProductNameAttribute(): string
    {
        if ($this->status === 'PAID') {
            return $this->product_name ?? 'Deleted Product';
        }
        return $this->product?->name ?? 'N/A';
    }

    public function getDisplayProductUnitAttribute(): string
    {
        if ($this->status === 'PAID') {
            return $this->product_unit ?? '';
        }
        return $this->product?->unit ?? '';
    }

    // Get transaction duration (days between transaction_date and due_date)
    public function getDurationDaysAttribute(): int
    {
        if (!$this->transaction_date || !$this->due_date) {
            return 0;
        }
        return $this->transaction_date->diffInDays($this->due_date);
    }

    // Relations
    public function customer(): BelongsTo
    {
        return $this->belongsTo(Customer::class, 'customer_id');
    }

    public function product(): BelongsTo
    {
        return $this->belongsTo(Product::class, 'products_id');
    }

    public function payment(): BelongsTo
    {
        return $this->belongsTo(Payment::class, 'payment_id');
    }

    public function histories(): HasMany
    {
        return $this->hasMany(TransactionHistory::class, 'transactions_id');
    }
}