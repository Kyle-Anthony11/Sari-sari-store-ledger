<?php

namespace App\Services;

use App\Models\StoreCredential;
use Resend\Laravel\Facades\Resend;
use Illuminate\Support\Facades\Log;

class VerificationMailService
{
    /**
     * Send a verification email using the Resend facade.
     *
     * @param StoreCredential $credential
     * @param string $verificationUrl
     * @return bool
     */
    public static function send(StoreCredential $credential, string $verificationUrl): bool
    {
        try {
            Resend::emails()->send([
                'from'    => config('mail.from.address'),
                'to'      => $credential->email,
                'subject' => 'Verify Your Store Account Email',
                'html'    => view('emails.verify-store-email-html', [
                    'verificationUrl' => $verificationUrl,
                    'storeName'       => $credential->profile?->store_name ?? 'Store',
                ])->render(),
            ]);

            return true;
        } catch (\Exception $e) {
            Log::error('Failed to send verification email via Resend: ' . $e->getMessage());
            return false;
        }
    }
}
