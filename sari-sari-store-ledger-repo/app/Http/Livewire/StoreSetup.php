<?php

namespace App\Http\Livewire;

use App\Models\StoreCredential;
use App\Models\StoreProfile;
use App\Services\AuthService;
use Illuminate\Support\Facades\Hash;
use Livewire\Component;
use App\Models\StoreSetting;
use Illuminate\Support\Facades\Mail;
use App\Mail\VerifyStoreEmail;
use App\Services\VerificationMailService;

class StoreSetup extends Component
{
    public $step = 1;

    // Profile fields
    public $owner_first_name = '';
    public $owner_middle_name = '';
    public $owner_last_name = '';
    public $owner_suffix = '';
    public $store_name = '';
    public $contact_number = '';
    public $store_address = '';
    public $address_notes = '';

    // Credentials fields
    public $email = '';
    public $password = '';
    public $password_confirmation = '';
    public $pin = '';
    public $pin_confirmation = '';

    public $isUpdate = false;
    public $existingCredential = null;

    protected function rules()
    {
        // Profile fields are always required
        $rules = [
            'owner_first_name' => ['required', 'string', 'max:255', 'regex:/^[a-zA-Z\s]+$/'],
            'owner_middle_name' => ['nullable', 'string', 'max:255', 'regex:/^[a-zA-Z\s]*$/'],
            'owner_last_name'  => ['required', 'string', 'max:255', 'regex:/^[a-zA-Z\s]+$/'],
            'store_name'       => 'required|string|max:255',
            'contact_number' => ['required', 'string', 'regex:/^\+639\d{9}$/'],
            'store_address'    => 'required|string',
        ];

        // Email rule: unique except when updating the current credential
        $emailRule = 'required|email';
        if ($this->isUpdate && $this->existingCredential) {
            $emailRule .= '|unique:store_credentials,email,' . $this->existingCredential->id;
        } else {
            $emailRule .= '|unique:store_credentials,email';
        }
        $rules['email'] = $emailRule;

        // Password and PIN are optional during update; required for initial setup
        if ($this->isUpdate) {
            $rules['password'] = 'nullable|string|min:6|confirmed|regex:/^(?=.*[A-Z])(?=.*\d)(?=.*[\W_]).+$/';
            $rules['pin'] = 'nullable|digits:4|confirmed';
        } else {
            $rules['password'] = 'required|string|min:6|confirmed|regex:/^(?=.*[A-Z])(?=.*\d)(?=.*[\W_]).+$/';
            $rules['pin'] = 'required|digits:4|confirmed';
        }

        return $rules;
    }

    protected function getRulesForStep($step)
    {
        if ($step == 1) {
            return [
                'owner_first_name' => ['required', 'string', 'max:255', 'regex:/^[a-zA-Z\s]+$/'],
                'owner_middle_name' => ['nullable', 'string', 'max:255', 'regex:/^[a-zA-Z\s]*$/'],
                'owner_last_name' => ['required', 'string', 'max:255', 'regex:/^[a-zA-Z\s]+$/'],
                'store_name' => 'required|string|max:255',
                'contact_number' => ['required', 'string', 'regex:/^\+639\d{9}$/'],
                'store_address' => 'required|string',
            ];
        }
        return [
            'email' => 'required|email|unique:store_credentials,email',
            'password' => [
                'required',
                'string',
                'min:6',
                'regex:/^(?=.*[A-Z])(?=.*\d)(?=.*[\W_]).+$/'
            ],
            'pin' => 'required|digits:4',
        ];
    }

    protected function getValidationMessages()
    {
        return [
            'contact_number.digits' => 'The contact number must be exactly 11 digits.',
            'contact_number.regex' => 'The contact number must be in the format +63 9XX XXX XXXX (e.g. +639171234567).',
            'pin.digits' => 'The PIN must be exactly 4 digits.',
            'password.regex' => 'The password must contain at least 1 uppercase letter, 1 digit, and 1 special character.',
            'owner_first_name.regex' => 'The first name may only contain letters and spaces.',
            'owner_middle_name.regex' => 'The middle name may only contain letters and spaces.',
            'owner_last_name.regex' => 'The last name may only contain letters and spaces.',
        ];
    }

    public function nextStep()
    {
        $this->validate($this->getRulesForStep($this->step), $this->getValidationMessages());
        $this->step++;
    }

    public function previousStep()
    {
        $this->step--;
    }

    public function mount()
    {
        // If the store profile already exists and we are in update mode
        $profile = StoreProfile::first();
        if ($profile && request()->query('update') === 'email') {
            $this->isUpdate = true;
            $this->existingCredential = $profile->credentials;

            if ($this->existingCredential) {
                // Pre‑fill the email field
                $this->email = $this->existingCredential->email;
                // Leave password and PIN blank (they are optional on update)
            }
        }
    }

    public function saveSetup()
    {
        $this->validate($this->rules(), $this->getValidationMessages());

        \DB::transaction(function () use (&$credential) {
            $profile = StoreProfile::first();

            if ($this->isUpdate && $this->existingCredential) {
                // --- Update existing credential ---
                $credential = $this->existingCredential;
                $oldEmail = $credential->email;

                $credential->email = $this->email;
                if (!empty($this->password)) {
                    $credential->password = Hash::make($this->password);
                }
                if (!empty($this->pin)) {
                    $credential->pin = Hash::make($this->pin);
                }

                // If email changed, unverify and generate new token
                if ($credential->isDirty('email')) {
                    $credential->email_verified_at = null;
                    $credential->verification_token = null;
                }
                $credential->save();

                // If email changed, send verification and logout
                if ($credential->wasChanged('email')) {
                    $token = $credential->generateVerificationToken();
                    $verificationUrl = route('verify-email', ['token' => $token]);

                    // --- Send email via Resend facade ---
                    $mailSent = VerificationMailService::send($credential, $verificationUrl);
                    if (!$mailSent) {
                        // Log the error and optionally set a flash message
                        \Log::error('Verification email failed to send for credential ID: ' . $credential->id);
                        session()->flash('mail_error', 'We could not send the verification email. Please try resending later.');
                    }

                    session(['verification_email' => $this->email]);
                    AuthService::logout();
                    $this->redirectToVerifyNotice = true;
                } else {
                    // Only password/PIN updated – keep logged in
                    $this->dispatch('showAlert', 'Credentials updated successfully.');
                    $this->redirectToDashboard = true;
                }
            } else {
                // --- New store setup ---
                $profile = StoreProfile::create([
                    'owner_first_name' => $this->owner_first_name,
                    'owner_middle_name' => $this->owner_middle_name,
                    'owner_last_name' => $this->owner_last_name,
                    'owner_suffix' => $this->owner_suffix,
                    'store_name' => $this->store_name,
                    'contact_number' => $this->contact_number,
                    'store_address' => $this->store_address,
                    'address_notes' => $this->address_notes,
                ]);

                $credential = StoreCredential::create([
                    'store_profile_id' => $profile->id,
                    'email' => $this->email,
                    'password' => Hash::make($this->password),
                    'pin' => Hash::make($this->pin),
                    'email_verified_at' => null,
                    'verification_token' => null,
                ]);

                $profile->update(['store_credentials_id' => $credential->id]);

                StoreSetting::create([
                    'store_profile_id' => $profile->id,
                    'surcharge' => 0,
                    'interest_rate' => 0,
                    'transaction_duration_days' => 30,
                    'max_credit_limit' => 0,
                ]);

                // --- Send verification email via Resend facade ---
                $token = $credential->generateVerificationToken();
                $verificationUrl = route('verify-email', ['token' => $token]);

                $mailSent = VerificationMailService::send($credential, $verificationUrl);
                if (!$mailSent) {
                    \Log::error('Verification email failed to send for new credential ID: ' . $credential->id);
                    session()->flash('mail_error', 'We could not send the verification email. Please try resending later.');
                }

                session(['verification_email' => $this->email]);
                AuthService::logout();
                $this->redirectToVerifyNotice = true;
            }
        });

        // Redirect after transaction
        if (isset($this->redirectToVerifyNotice) && $this->redirectToVerifyNotice) {
            return redirect()->route('verify.notice');
        }
        if (isset($this->redirectToDashboard) && $this->redirectToDashboard) {
            return redirect()->route('dashboard');
        }
        // Fallback (should not happen)
        return redirect()->route('login');
    }

    public function render()
    {
        return view('livewire.store-setup')->layout('layouts.guest');
    }
}
