<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class TransactionHistory extends Model
{
    use HasFactory;

    protected $table = 'transaction_history';

    public array $receiptData = [];

    protected $fillable = [
        'customer_id',
        'transactions_id',
        'payments_id',
        // Snapshot fields
        'customer_name',
        'product_name',
        'transaction_date',
        'due_date',
        'payment_date',
        'surcharge_total',
        'interest_rate',
        'duration_days',
        'total_amount',
        'cash_given',
        'change',
        'status',
        'payment_method',
        'reference_number',
    ];

    protected $casts = [
        'transaction_date' => 'date',
        'due_date' => 'date',
        'payment_date' => 'datetime',
        'surcharge_total' => 'decimal:2',
        'interest_rate' => 'decimal:2',
        'duration_days' => 'integer',
        'total_amount' => 'decimal:2',
        'cash_given' => 'decimal:2',
        'change' => 'decimal:2',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
    ];

    public function customer(): BelongsTo
    {
        return $this->belongsTo(Customer::class, 'customer_id');
    }

    public function transaction(): BelongsTo
    {
        return $this->belongsTo(Transaction::class, 'transactions_id');
    }

    public function payment(): BelongsTo
    {
        return $this->belongsTo(Payment::class, 'payments_id');
    }
}
