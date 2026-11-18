<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class PasswordResetOtp extends Model
{
    use HasFactory;

    protected $fillable = [
        'store_credential_id',
        'otp',
        'expires_at',
        'used_at',
    ];

    protected $casts = [
        'expires_at' => 'datetime',
        'used_at' => 'datetime',
    ];

    public function credential(): BelongsTo
    {
        return $this->belongsTo(StoreCredential::class, 'store_credential_id');
    }

    public function isValid(): bool
    {
        return $this->used_at === null && $this->expires_at->isFuture();
    }
}
