<?php

namespace App\Http\Livewire;

use App\Models\StoreProfile;
use App\Models\StoreCredential;
use App\Services\AuthService;
use Livewire\Component;

class Login extends Component
{
    public $email = '';
    public $password = '';
    public $pin = '';
    public $error = '';

    public function mount()
    {
        if (AuthService::check()) {
            return redirect()->route('dashboard');
        }

        $profile = StoreProfile::first();
        if (!$profile) {
            return redirect()->route('store-setup');
        }

        $credential = $profile->credentials;
        if (!$credential || empty($credential->email)) {
            return redirect()->route('store-setup');
        }
    }

    public function authenticate()
    {
        $this->validate([
            'email'    => 'required|email',
            'password' => 'required|string',
            'pin'      => 'required|digits:4',
        ]);

        if (AuthService::login($this->email, $this->password, $this->pin)) {
            return redirect()->intended(route('dashboard'));
        }

        // If login fails, check if email exists but not verified
        $credential = StoreCredential::where('email', $this->email)->first();
        if ($credential) {
            if (is_null($credential->email_verified_at)) {
                $this->error = 'Your email is not verified. Please check your inbox.';
            } elseif (empty($credential->email) || !filter_var($credential->email, FILTER_VALIDATE_EMAIL)) {
                // Redirect to update page if email invalid
                return redirect()->route('store-setup', ['update' => 'email']);
            } else {
                $this->error = 'Invalid Credentials';
            }
        } else {
            $this->error = 'Invalid Credentials';
        }

        // Clear fields (optional)
        $this->email = '';
        $this->password = '';
        $this->pin = '';
    }
    public function render()
    {
        return view('livewire.login')->layout('layouts.guest');
    }
}
