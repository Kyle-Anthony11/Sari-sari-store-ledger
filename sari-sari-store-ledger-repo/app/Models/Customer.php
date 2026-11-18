<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Carbon;

class Customer extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'contact_number',
        'address',
        'is_active',
        'credit_limit',
        'total_balance',
        'risk_level',
        'days_since_last_payment',
        'accepted_terms_at',
        'terms_attachment_path',
    ];

    protected $casts = [
        'is_active' => 'boolean',
        'credit_limit' => 'decimal:15.2',
        'total_balance' => 'decimal:15.2',
        'risk_level' => 'string',
        'days_since_last_payment' => 'integer',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
        'accepted_terms_at' => 'datetime',
    ];

    public function transactions(): HasMany
    {
        return $this->hasMany(Transaction::class, 'customer_id');
    }

    public function payments(): HasMany
    {
        return $this->hasMany(Payment::class, 'customer_id');
    }

    public function updateRiskLevel(): void
    {
        // Latest payment date (if any)
        $latestPayment = $this->payments()
            ->orderByDesc('payment_date')
            ->first();

        $daysSinceLastPayment = 0;
        if ($latestPayment) {
            $daysSinceLastPayment = Carbon::parse($latestPayment->payment_date)->diffInDays(Carbon::today());
        } else {
            // No payments yet: treat as very old
            $daysSinceLastPayment = 365;
        }

        $overdueTotal = (float) $this->transactions()
            ->where('status', 'OVERDUE')
            ->sum('total_amount');

        $overdueCount = (int) $this->transactions()
            ->where('status', 'OVERDUE')
            ->count();

        $creditLimit = (float) ($this->credit_limit ?? 0);
        $totalBalance = (float) ($this->total_balance ?? 0);

        // Pressure ratio: if credit_limit is 0, avoid division by zero.
        $pressureRatio = $creditLimit > 0 ? ($totalBalance / $creditLimit) : ($totalBalance > 0 ? 10 : 0);

        // Simple scoring
        $score = 0;
        if ($daysSinceLastPayment >= 30) $score += 2;
        elseif ($daysSinceLastPayment >= 15) $score += 1;

        if ($overdueCount >= 1) $score += 2;
        if ($overdueTotal > 0) $score += 1;

        if ($pressureRatio >= 1.0) $score += 2;
        elseif ($pressureRatio >= 0.8) $score += 1;

        $riskLevel = match (true) {
            $score >= 6 => 'HIGH',
            $score >= 3 => 'MEDIUM',
            default => 'LOW',
        };

        $this->days_since_last_payment = $daysSinceLastPayment;
        $this->risk_level = $riskLevel;
        $this->save();
    }
}
