<?php

namespace App\Services;

use App\Models\StoreCredential;
use Illuminate\Support\Facades\Log;
use Resend\Laravel\Facades\Resend;

class PasswordResetMailService
{
    /**
     * Send a password reset OTP using the Resend facade.
     */
    public static function send(StoreCredential $credential, string $otp): bool
    {
        try {
            Resend::emails()->send([
                'from'    => config('mail.from.address'),
                'to'      => $credential->email,
                'subject' => 'Your Password Reset Code',
                'html'    => view('emails.password-reset-otp-html', [
                    'otp'       => $otp,
                    'storeName' => $credential->profile?->store_name ?? 'Store',
                ])->render(),
            ]);

            return true;
        } catch (\Exception $e) {
            Log::error('Failed to send password reset OTP via Resend: ' . $e->getMessage());
            return false;
        }
    }
}
