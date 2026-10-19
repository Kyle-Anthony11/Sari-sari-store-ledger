<?php

namespace App\Http\Livewire;

use App\Models\StoreProfile;
use App\Models\StoreSetting;
use App\Services\AuthService;
use Illuminate\Support\Facades\Hash;
use Livewire\Component;

class StoreAccount extends Component
{
    public $profile;

    // Edit Profile Modal
    public $showEditProfileModal = false;
    public $editForm = [
        'owner_first_name' => '',
        'owner_middle_name' => '',
        'owner_last_name' => '',
        'owner_suffix' => '',
        'store_name' => '',
        'contact_number' => '',
        'store_address' => '',
        'address_notes' => '',
    ];

    // Change Password Modal
    public $showChangePasswordModal = false;
    public $current_password = '';
    public $new_password = '';
    public $new_password_confirmation = '';
    public $password_error = '';

    public $min_credit_limit = 0;

    // Change PIN Modal
    public $showChangePinModal = false;
    public $current_pin = '';
    public $new_pin = '';
    public $new_pin_confirmation = '';
    public $pin_error = '';

    // Transaction Settings Modal
    public $showTransactionSettingsModal = false;
    public $surcharge = 0.00;
    public $interest_rate = 0.00;
    public $transaction_duration_days = 30;
    public $max_credit_limit = 0;

    public function mount()
    {
        $this->profile = AuthService::user();
        $profile = StoreProfile::first();

        if ($profile && request()->query('update') === 'email') {
            $this->isUpdate = true;
            $this->existingCredential = $profile->credentials;

            if ($this->existingCredential) {
                // Pre‑fill the email field
                $this->email = $this->existingCredential->email;
                // Optionally pre‑fill other fields (password/pin should be left blank for security)
            }
        }

        // If the profile no longer exists, force logout and redirect to login
        if (!$this->profile) {
            AuthService::logout();
            return redirect()->route('login');
        }

        $this->resetEditForm();
        $this->loadSettings();
    }

    public function resetEditForm()
    {
        if (!$this->profile) {
            $this->editForm = [
                'owner_first_name' => '',
                'owner_middle_name' => '',
                'owner_last_name' => '',
                'owner_suffix' => '',
                'store_name' => '',
                'contact_number' => '',
                'store_address' => '',
                'address_notes' => '',
            ];
            return;
        }

        $this->editForm = [
            'owner_first_name' => $this->profile->owner_first_name,
            'owner_middle_name' => $this->profile->owner_middle_name,
            'owner_last_name' => $this->profile->owner_last_name,
            'owner_suffix' => $this->profile->owner_suffix,
            'store_name' => $this->profile->store_name,
            'contact_number' => $this->profile->contact_number,
            'store_address' => $this->profile->store_address,
            'address_notes' => $this->profile->address_notes,
        ];
    }

    protected function loadSettings()
    {
        // Guard against null profile (should not happen after mount check, but safety)
        if (!$this->profile) {
            $this->surcharge = 0.00;
            $this->interest_rate = 0.00;
            $this->transaction_duration_days = 30;
            $this->max_credit_limit = 0;
            $this->min_credit_limit = 0;
            return;
        }

        $settings = $this->profile->settings;
        if ($settings) {
            $this->surcharge = $settings->surcharge;
            $this->interest_rate = $settings->interest_rate;
            $this->transaction_duration_days = $settings->transaction_duration_days;
            $this->max_credit_limit = $settings->max_credit_limit;
        } else {
            $this->surcharge = 0.00;
            $this->interest_rate = 0.00;
            $this->transaction_duration_days = 30;
            $this->max_credit_limit = 0;
            $this->min_credit_limit = 0;
        }
    }

    public function openEditProfileModal()
    {
        $this->resetEditForm();
        $this->showEditProfileModal = true;
    }

    public function updateProfile()
    {
        $this->validate([
            'editForm.owner_first_name' => ['required', 'string', 'max:255', 'regex:/^[a-zA-Z\s]+$/'],
            'editForm.owner_middle_name' => ['nullable', 'string', 'max:255', 'regex:/^[a-zA-Z\s]*$/'],
            'editForm.owner_last_name' => ['required', 'string', 'max:255', 'regex:/^[a-zA-Z\s]+$/'],
            'editForm.owner_suffix' => 'nullable|string|max:255',
            'editForm.store_name' => 'required|string|max:255',
            'editForm.contact_number' => ['required', 'string', 'regex:/^\+639\d{9}$/'],
            'editForm.store_address' => 'required|string',
        ]);

        $this->profile->update($this->editForm);
        $this->showEditProfileModal = false;
        $this->dispatch('showAlert', 'Store profile updated successfully.');
        $this->profile->refresh();
        $this->resetEditForm();
    }

    public function openChangePasswordModal()
    {
        $this->resetPasswordForm();
        $this->showChangePasswordModal = true;
    }

    public function resetPasswordForm()
    {
        $this->current_password = '';
        $this->new_password = '';
        $this->new_password_confirmation = '';
        $this->password_error = '';
    }

    public function updatePassword()
    {
        $this->validate([
            'current_password' => 'required|string',
            'new_password' => 'required|string|min:6|confirmed',
        ]);

        $credential = $this->profile->credentials;

        if (!Hash::check($this->current_password, $credential->password)) {
            $this->password_error = 'Current password is incorrect.';
            return;
        }

        $credential->update([
            'password' => Hash::make($this->new_password)
        ]);

        $this->showChangePasswordModal = false;
        $this->dispatch('showAlert', 'Password changed successfully.');
        $this->resetPasswordForm();
    }

    public function openChangePinModal()
    {
        $this->resetPinForm();
        $this->showChangePinModal = true;
    }

    public function resetPinForm()
    {
        $this->current_pin = '';
        $this->new_pin = '';
        $this->new_pin_confirmation = '';
        $this->pin_error = '';
    }

    public function updatePin()
    {
        $this->validate([
            'current_pin' => 'required|digits:4',
            'new_pin' => 'required|digits:4|confirmed',
        ]);

        $credential = $this->profile->credentials;

        if (!Hash::check($this->current_pin, $credential->pin)) {
            $this->pin_error = 'Current PIN is incorrect.';
            return;
        }

        $credential->update([
            'pin' => Hash::make($this->new_pin)
        ]);

        $this->showChangePinModal = false;
        $this->dispatch('showAlert', 'PIN changed successfully.');
        $this->resetPinForm();
    }

    public function openTransactionSettingsModal()
    {
        $this->loadSettings(); // refresh before showing
        $this->showTransactionSettingsModal = true;
    }

    public function saveTransactionSettings()
    {
        $this->validate([
            'surcharge' => 'required|numeric|min:0',
            'interest_rate' => 'required|numeric|min:0|max:100',
            'transaction_duration_days' => 'required|integer|min:1|max:365',
            'max_credit_limit' => 'nullable|numeric|min:0',
            'min_credit_limit' => 'nullable|numeric|min:0',
        ]);

        if (
            ($this->max_credit_limit ?? 0) > 0 &&
            ($this->min_credit_limit ?? 0) > 0 &&
            $this->min_credit_limit > $this->max_credit_limit
        ) {
            $this->addError('min_credit_limit', 'Minimum credit limit cannot exceed the max credit limit.');
            return;
        }
        $settings = $this->profile->settings;
        $data = [
            'surcharge' => $this->surcharge,
            'interest_rate' => $this->interest_rate,
            'transaction_duration_days' => $this->transaction_duration_days,
            'max_credit_limit' => $this->max_credit_limit ?? 0,
            'min_credit_limit' => $this->min_credit_limit ?? 0,
        ];

        if ($settings) $settings->update($data);
        else StoreSetting::create(['store_profile_id' => $this->profile->id] + $data);

        $this->showTransactionSettingsModal = false;
        $this->dispatch('showAlert', 'Transaction settings updated successfully.');
    }

    public function logout()
    {
        AuthService::logout();
        return redirect()->route('login');
    }

    public function render()
    {
        return view('livewire.store-account');
    }
}
