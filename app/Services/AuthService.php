<?php

namespace App\Services;

use App\Models\StoreCredential;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Session;

class AuthService
{
    public static function login(string $email, string $password, string $pin): bool
    {
        $credential = StoreCredential::where('email', $email)->first();

        if (
            !$credential ||
            !Hash::check($password, $credential->password) ||
            !Hash::check($pin, $credential->pin)
        ) {
            return false;
        }

        // Check if email is verified
        if (is_null($credential->email_verified_at)) {
            return false; // or throw an exception; we'll handle in Login component
        }

        Session::put('store_credential_id', $credential->id);
        Session::put('store_profile_id', $credential->store_profile_id);
        Session::regenerate();

        return true;
    }

    public static function logout(): void
    {
        Session::forget(['store_credential_id', 'store_profile_id']);
        Session::regenerate();
    }

    public static function check(): bool
    {
        return Session::has('store_credential_id');
    }

    public static function id(): ?int
    {
        return Session::get('store_profile_id');
    }

    public static function credentialId(): ?int
    {
        return Session::get('store_credential_id');
    }

    public static function user(): ?\App\Models\StoreProfile
    {
        $profileId = self::id();
        if (!$profileId) return null;

        return \App\Models\StoreProfile::with('credentials')->find($profileId);
    }
}
