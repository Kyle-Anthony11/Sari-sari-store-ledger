<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class StoreSetting extends Model
{
    use HasFactory;

    protected $fillable = [
        'store_profile_id',
        'surcharge',
        'interest_rate',
        'transaction_duration_days',
        'max_credit_limit',
        'min_credit_limit',
    ];

    protected $casts = [
        'surcharge' => 'decimal:2',
        'interest_rate' => 'decimal:2',
        'transaction_duration_days' => 'integer',
        'max_credit_limit' => 'decimal:2',
        'min_credit_limit' => 'decimal:2',
    ];

    public function storeProfile(): BelongsTo
    {
        return $this->belongsTo(StoreProfile::class);
    }
}
