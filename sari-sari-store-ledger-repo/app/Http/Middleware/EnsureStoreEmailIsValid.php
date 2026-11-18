<?php

namespace App\Http\Middleware;

use Closure;
use App\Services\AuthService;
use App\Models\StoreCredential;

class EnsureStoreEmailIsValid
{
    public function handle($request, Closure $next)
    {
        $credentialId = AuthService::credentialId();
        if (!$credentialId) {
            return redirect()->route('login');
        }

        $credential = StoreCredential::find($credentialId);
        if (!$credential || empty($credential->email) || !filter_var($credential->email, FILTER_VALIDATE_EMAIL)) {
            // Redirect to store setup with a flag to update email
            return redirect()->route('store-setup', ['update' => 'email']);
        }

        return $next($request);
    }
}
