<?php

namespace App\Http\Livewire;

use App\Models\PasswordResetOtp;
use App\Models\StoreCredential;
use App\Services\AuthService;
use App\Services\PasswordResetMailService;
use Illuminate\Support\Facades\Hash;
use Livewire\Component;

class ForgotPassword extends Component
{
    /**
     * 1 = idle / no admin
     * 2 = enter code
     * 3 = enter new password
     */
    public int $step = 1;

    public string $email = '';
    public string $otp = '';
    public string $new_password = '';
    public string $new_password_confirmation = '';
    public string $error = '';
    public string $info = '';
    public ?int $credentialId = null;
    public ?int $otpSentAt = null;

    public function mount()
    {
        if (AuthService::check()) {
            return redirect()->route('dashboard');
        }

        $credential = StoreCredential::first();
        if (!$credential || empty($credential->email)) {
            $this->error = 'No admin account found. Please complete the store setup first.';
            $this->step  = 1;
            return;
        }

        $this->credentialId = $credential->id;
        $this->email        = $credential->email;
        $this->step         = 2;

        // Reuse a recent OTP if one was just sent (prevents spam on refresh)
        $recent = PasswordResetOtp::where('store_credential_id', $credential->id)
            ->whereNull('used_at')
            ->where('expires_at', '>', now())
            ->latest()
            ->first();

        if ($recent && $recent->created_at->gt(now()->subSeconds(60))) {
            $this->otpSentAt = $recent->created_at->timestamp;
            $this->info      = 'A code was recently sent to ' . $this->maskEmail($credential->email)
                . '. It expires in 30 minutes.';
        } else {
            $this->issueOtp($credential);
        }
    }

    protected function issueOtp(StoreCredential $credential): void
    {
        // Invalidate any pending OTPs for this credential
        PasswordResetOtp::where('store_credential_id', $credential->id)
            ->whereNull('used_at')
            ->update(['used_at' => now()]);

        // Generate a 6-digit code (zero-padded)
        $plainOtp = str_pad((string) random_int(0, 999999), 6, '0', STR_PAD_LEFT);

        PasswordResetOtp::create([
            'store_credential_id' => $credential->id,
            'otp'                 => Hash::make($plainOtp),
            'expires_at'          => now()->addMinutes(30),
        ]);

        $this->otpSentAt = now()->timestamp;

        $sent = PasswordResetMailService::send($credential, $plainOtp);
        if ($sent) {
            $this->error = '';
            $this->info  = 'A 6-digit code has been sent to '
                . $this->maskEmail($credential->email)
                . '. It expires in 30 minutes.';
        } else {
            $this->info  = '';
            $this->error = 'We could not send the code right now. Please try again in a moment.';
        }
    }

    protected function maskEmail(string $email): string
    {
        if (!str_contains($email, '@')) {
            return $email;
        }

        [$user, $domain] = explode('@', $email, 2);
        $len = strlen($user);

        if ($len <= 2) {
            return $user[0] . str_repeat('*', max(1, $len - 1)) . '@' . $domain;
        }

        return substr($user, 0, 2) . str_repeat('*', max(1, $len - 2)) . '@' . $domain;
    }

    public function resendOtp()
    {
        if (!$this->credentialId) {
            return;
        }

        $credential = StoreCredential::find($this->credentialId);
        if (!$credential) {
            $this->error = 'Session expired. Please start again.';
            $this->step  = 1;
            return;
        }

        $latest = PasswordResetOtp::where('store_credential_id', $credential->id)
            ->latest()
            ->first();

        // Enforce 60-second cooldown
        if ($latest && $latest->created_at->gt(now()->subSeconds(60))) {
            $this->error = 'Please wait before requesting a new code.';
            return;
        }

        $this->issueOtp($credential);
    }

    public function verifyOtp()
    {
        $this->validate([
            'otp' => 'required|digits:6',
        ], [
            'otp.digits' => 'The code must be exactly 6 digits.',
        ]);

        if (!$this->credentialId) {
            $this->error = 'Session expired. Please start again.';
            $this->step  = 1;
            return;
        }

        $otpRecord = PasswordResetOtp::where('store_credential_id', $this->credentialId)
            ->whereNull('used_at')
            ->latest()
            ->first();

        if (!$otpRecord) {
            $this->error = 'No active code found. Please request a new one.';
            return;
        }

        if ($otpRecord->expires_at->isPast()) {
            $this->error = 'This code has expired. Please request a new one.';
            return;
        }

        if (!Hash::check($this->otp, $otpRecord->otp)) {
            $this->error = 'Invalid code. Please try again.';
            return;
        }

        // Mark as used
        $otpRecord->update(['used_at' => now()]);

        $this->step  = 3;
        $this->error = '';
        $this->info  = '';
    }

    public function updatePassword()
    {
        $this->validate([
            'new_password' => [
                'required',
                'string',
                'min:6',
                'confirmed',
                'regex:/^(?=.*[A-Z])(?=.*\d)(?=.*[\W_]).+$/',
            ],
        ], [
            'new_password.regex'     => 'The password must contain at least 1 uppercase letter, 1 digit, and 1 special character.',
            'new_password.confirmed' => 'The password confirmation does not match.',
        ]);

        $credential = StoreCredential::find($this->credentialId);
        if (!$credential) {
            $this->error = 'Session expired. Please start again.';
            $this->step  = 1;
            return;
        }

        $credential->update([
            'password' => Hash::make($this->new_password),
        ]);

        session()->flash('message', 'Password updated successfully. Please sign in with your new password.');

        return redirect()->route('login');
    }

    public function render()
    {
        return view('livewire.forgot-password')->layout('layouts.guest');
    }
}
