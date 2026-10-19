<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Notifications\Notifiable;
use Illuminate\Support\Facades\Mail;
use App\Mail\VerifyStoreEmail;

class StoreCredential extends Model implements MustVerifyEmail
{
    
    use HasFactory, Notifiable;

    protected $fillable = [
        'store_profile_id',
        'email',
        'password',
        'pin',
        'email_verified_at',
        'verification_token',
    ];

    protected $hidden = [
        'password',
        'pin',
        'verification_token',
    ];

    protected $casts = [
        'email_verified_at' => 'datetime',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
    ];

    public function profile(): BelongsTo
    {
        return $this->belongsTo(StoreProfile::class, 'store_profile_id');
    }

    // ---- MustVerifyEmail implementation ----

    public function hasVerifiedEmail(): bool
    {
        return ! is_null($this->email_verified_at);
    }

    public function markEmailAsVerified(): bool
    {
        return $this->forceFill([
            'email_verified_at' => $this->freshTimestamp(),
            'verification_token' => null,
        ])->save();
    }

    public function markEmailAsUnverified(): bool
    {
        return $this->forceFill([
            'email_verified_at' => null,
            'verification_token' => null,
        ])->save();
    }

    public function sendEmailVerificationNotification(): void
    {
        $token = $this->generateVerificationToken();
        $verificationUrl = route('verify-email', ['token' => $token]);
        Mail::to($this->email)->send(new VerifyStoreEmail($this, $verificationUrl));
    }

    public function getEmailForVerification(): string
    {
        return $this->email;
    }

    // ---- Helper ----

    public function generateVerificationToken(): string
    {
        $token = \Illuminate\Support\Str::random(64);
        $this->verification_token = $token;
        $this->save();
        return $token;
    }
}
