<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Payment extends Model
{
    use HasFactory;

    protected $fillable = [
        'transactions_id',
        'customer_id',
        'total_amount',
        'cash_given',
        'payment_date',
        'change',
        'payment_method',
        'reference_number',
    ];

    protected $casts = [
        'total_amount' => 'decimal:15.2',
        'cash_given' => 'decimal:15.2',
        'change' => 'decimal:15.2',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
        'payment_date' => 'datetime',
    ];

    public function customer(): BelongsTo
    {
        return $this->belongsTo(Customer::class, 'customer_id');
    }

    public function transaction(): BelongsTo
    {
        // transactions.payment_id -> payments.id
        return $this->belongsTo(Transaction::class, 'transactions_id');
    }

    public function histories(): HasMany
    {
        return $this->hasMany(TransactionHistory::class, 'payments_id');
    }
}
