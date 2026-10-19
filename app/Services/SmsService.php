<?php

namespace App\Services;

use Illuminate\Support\Facades\Log;

class SmsService
{
    public function send(string $phoneNumber, string $message): bool
    {
        // Replace with actual SMS provider integration
        Log::info("SMS to {$phoneNumber}: {$message}");
        // Simulate success
        return true;
    }
}
