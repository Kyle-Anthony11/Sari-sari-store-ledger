<?php

namespace App\Http\Middleware;

use Closure;
use App\Services\AuthService;
use App\Models\StoreCredential;

class EnsureStoreEmailIsVerified
{
    public function handle($request, Closure $next)
    {
        $credentialId = AuthService::credentialId();
        if (!$credentialId) {
            return redirect()->route('login');
        }

        $credential = StoreCredential::find($credentialId);
        if (!$credential) {
            AuthService::logout();
            return redirect()->route('login');
        }

        // If email is not verified, redirect to the verification notice page
        if (!$credential->hasVerifiedEmail()) {
            return redirect()->route('verify.notice');
        }

        return $next($request);
    }
}
