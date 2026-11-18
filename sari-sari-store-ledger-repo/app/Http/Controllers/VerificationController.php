<?php

namespace App\Http\Controllers;

use App\Models\StoreCredential;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Mail;
use App\Mail\VerifyStoreEmail;
use App\Services\VerificationMailService;

class VerificationController extends Controller
{
    public function verify(Request $request)
    {
        $token = $request->query('token');
        $credential = StoreCredential::where('verification_token', $token)->first();

        if (!$credential) {
            return redirect()->route('login')->withErrors(['email' => 'Invalid verification link.']);
        }

        if ($credential->hasVerifiedEmail()) {
            return redirect()->route('login')->with('message', 'Email already verified.');
        }

        $credential->markEmailAsVerified(); // uses the new method

        return redirect()->route('login')->with('message', 'Email verified! You can now log in.');
    }

    public function resend(Request $request)
    {
        $email = $request->input('email');
        $credential = StoreCredential::where('email', $email)->first();

        if (!$credential) {
            return back()->withErrors(['email' => 'Account not found.']);
        }

        if ($credential->hasVerifiedEmail()) {
            return redirect()->route('login')->with('message', 'Email already verified.');
        }

        $token = $credential->generateVerificationToken();
        $verificationUrl = route('verify-email', ['token' => $token]);

        $sent = VerificationMailService::send($credential, $verificationUrl);

        if ($sent) {
            return back()->with('message', 'Verification email resent successfully.');
        } else {
            return back()->withErrors(['email' => 'Unable to send email. Please try again later.']);
        }
    }

    public function notice()
    {
        return view('auth.verify-notice');
    }
}
