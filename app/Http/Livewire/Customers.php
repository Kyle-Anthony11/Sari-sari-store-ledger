<?php

namespace App\Http\Livewire;

use Livewire\Component;
use Livewire\WithPagination;
use Livewire\WithFileUploads;
use App\Models\Customer;
use App\Models\TransactionHistory;
use App\Models\Payment;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Storage;
use App\Services\AuthService;

class Customers extends Component
{
    use WithPagination;
    use WithFileUploads;
    protected $paginationTheme = 'simple-tailwind-custom';

    public $search = '';
    public $showCreateModal = false;
    public $showDeleteModal = false;
    public $editingCustomer = null;
    public $customerToDelete = null;
    public $showDetailsModal = false;
    public $customerDetails = null;

    public string $creditLimitSort = '';
    // === PIN verification properties ===
    public string $deletePin = '';
    public string $deletePinError = '';

    // Terms and Agreement checkbox property
    public $agreeTerms = false;

    // Terms attachment file (used in the create/edit modal)
    public $termsAttachment;

    // ─── NEW: Terms attachment update (used in the details modal) ───
    public bool $showUpdateTermsUpload = false;
    public $updateTermsAttachment = null;
    public int $uploadKey = 0;

    protected $queryString = [
        'search' => ['except' => ''],
    ];

    public $form = [
        'name' => '',
        'contact_number' => '',
        'address' => '',
        'credit_limit' => 0,
    ];

    public function updatedCreditLimitSort()
    {
        $this->resetPage();
    }

    protected function rules()
{
    $uniqueRule = 'unique:customers,name';
    if ($this->editingCustomer) {
        $uniqueRule .= ',' . $this->editingCustomer->id;
    }

    $rules = [
        'form.name' => ['required', 'string', 'max:255', $uniqueRule, 'regex:/^[a-zA-Z\s]+$/'],
        'form.contact_number' => ['nullable', 'string', 'regex:/^\+639\d{9}$/'],
        'form.address' => 'nullable|string',
        'form.credit_limit' => 'nullable|numeric|min:0',
        'termsAttachment' => 'nullable|image|max:5120',
    ];

    if (!$this->editingCustomer) {
        $rules['agreeTerms'] = 'accepted';
        $rules['termsAttachment'] = 'required|image|max:5120';
    } elseif ($this->editingCustomer && is_null($this->editingCustomer->accepted_terms_at)) {
        $rules['agreeTerms'] = 'accepted';
        $rules['termsAttachment'] = 'required|image|max:5120';
    }

    return $rules;
}


protected function messages()
{
    return [
        'agreeTerms.accepted'         => 'You must read and agree to the Terms and Conditions.',
        'termsAttachment.required'    => 'Please attach the signed Terms & Conditions image.',
        'termsAttachment.image'       => 'The attached file must be a valid image.',
        'termsAttachment.max'         => 'The image must not exceed 5MB.',
    ];
}

    protected function getGlobalMinCreditLimit(): float
    {
        $profile = AuthService::user();
        $settings = $profile?->settings;
        return (float) ($settings->min_credit_limit ?? 0);
    }

    public function updatedSearch()
    {
        $this->resetPage();
    }

    protected function getGlobalMaxCreditLimit(): float
    {
        $profile = AuthService::user();
        $settings = $profile?->settings;
        return (float) ($settings->max_credit_limit ?? 0);
    }

    public function viewDetails($id)
    {
        $this->customerDetails = Customer::with('transactions')->find($id);
        $this->showDetailsModal = true;

        // Reset the attachment-update sub-form state
        $this->showUpdateTermsUpload = false;
        $this->updateTermsAttachment = null;
        $this->resetErrorBag('updateTermsAttachment');
    }

    public function closeDetailsModal()
    {
        $this->showDetailsModal = false;
        $this->customerDetails = null;

        // Reset the attachment-update sub-form state
        $this->showUpdateTermsUpload = false;
        $this->updateTermsAttachment = null;
        $this->resetErrorBag('updateTermsAttachment');
    }

    // ─── NEW: Open / cancel / save the attachment update flow ───
    public function openUpdateTermsUpload()
    {
        $this->showUpdateTermsUpload = true;
        $this->updateTermsAttachment = null;
        $this->resetErrorBag('updateTermsAttachment');
        $this->uploadKey++; // forces the file input to re-render clean
    }

    public function cancelUpdateTermsUpload()
    {
        $this->showUpdateTermsUpload = false;
        $this->updateTermsAttachment = null;
        $this->resetErrorBag('updateTermsAttachment');
    }

    public function saveUpdatedTermsAttachment()
    {
        $this->validate([
            'updateTermsAttachment' => 'required|image|max:5120',
        ], [
            'updateTermsAttachment.required' => 'Please select an image file to upload.',
            'updateTermsAttachment.image'    => 'The file must be a valid image.',
            'updateTermsAttachment.max'      => 'The image must not exceed 5MB.',
        ]);

        if (!$this->customerDetails) {
            $this->dispatch('showAlert', 'No customer selected.');
            return;
        }

        $customer = Customer::find($this->customerDetails->id);
        if (!$customer) {
            $this->dispatch('showAlert', 'Customer not found.');
            return;
        }

        // Store the new file FIRST
        $newPath = $this->updateTermsAttachment->store('terms_attachments', 'public');
        $oldPath = $customer->terms_attachment_path;

        // Point the customer at the new file
        $customer->update(['terms_attachment_path' => $newPath]);

        // Only delete the old file after a successful update
        if ($oldPath && $oldPath !== $newPath && Storage::disk('public')->exists($oldPath)) {
            Storage::disk('public')->delete($oldPath);
        }

        // Refresh the details pane with the latest data
        $this->customerDetails = Customer::with('transactions')->find($customer->id);
        $this->updateTermsAttachment = null;
        $this->showUpdateTermsUpload = false;

        $this->dispatch('showAlert', 'Attached Terms & Conditions updated successfully!');
    }

    public function createCustomer()
    {
        $this->resetForm();
        $this->showCreateModal = true;
    }

    public function saveCustomer()
    {
        $this->validate();

        $globalMax = $this->getGlobalMaxCreditLimit();
        $enteredLimit = (float) $this->form['credit_limit'];

        if ($globalMax > 0 && $enteredLimit > $globalMax) {
            $this->dispatch('showAlert', "Credit limit cannot exceed the global max credit limit of ₱" . number_format($globalMax, 2));
            return;
        }

        $this->form['credit_limit'] = round($enteredLimit, 2);
        $this->form['accepted_terms_at'] = now();

        if ($this->termsAttachment) {
            $this->form['terms_attachment_path'] = $this->termsAttachment->store('terms_attachments', 'public');
        }

        $globalMin = $this->getGlobalMinCreditLimit();
        if ($globalMin > 0 && $enteredLimit > 0 && $enteredLimit < $globalMin) {
            $this->dispatch('showAlert', "Credit limit cannot be less than the minimum credit limit of ₱" . number_format($globalMin, 2));
            return;
        }


        Customer::create($this->form);
        $this->showCreateModal = false;
        $this->resetForm();
        $this->dispatch('showAlert', 'Customer created successfully!');
    }

    public function editCustomer($id)
    {
        $this->editingCustomer = Customer::find($id);
        $this->form = $this->editingCustomer->toArray();
        $this->form['credit_limit'] = round((float) $this->form['credit_limit'], 2);
        $this->termsAttachment = null;
        $this->showCreateModal = true;
    }

    public function updateCustomer()
    {
        $this->validate();

        $globalMax = $this->getGlobalMaxCreditLimit();
        $enteredLimit = (float) $this->form['credit_limit'];

        if ($globalMax > 0 && $enteredLimit > $globalMax) {
            $this->dispatch('showAlert', "Credit limit cannot exceed the global max credit limit of ₱" . number_format($globalMax, 2));
            return;
        }

        if ($this->editingCustomer) {
            $this->form['credit_limit'] = round($enteredLimit, 2);

            // If terms were just accepted, record the acceptance date
            if ($this->agreeTerms && is_null($this->editingCustomer->accepted_terms_at)) {
                $this->form['accepted_terms_at'] = now();
            }

            // Handle attachment upload
            if ($this->termsAttachment) {
                $this->form['terms_attachment_path'] = $this->termsAttachment->store('terms_attachments', 'public');
            }

            $globalMin = $this->getGlobalMinCreditLimit();
            if ($globalMin > 0 && $enteredLimit > 0 && $enteredLimit < $globalMin) {
                $this->dispatch('showAlert', "Credit limit cannot be less than the minimum credit limit of ₱" . number_format($globalMin, 2));
                return;
            }


            $this->editingCustomer->update($this->form);
            $this->editingCustomer->updateRiskLevel();


            $this->dispatch('showAlert', 'Customer updated successfully!');
        }
    }

    public function confirmDeleteCustomer($id)
    {
        $customer = Customer::find($id);
        $this->customerToDelete = $customer;
        // Reset PIN fields before showing modal
        $this->deletePin = '';
        $this->deletePinError = '';
        $this->showDeleteModal = true;
    }

    public function deleteCustomer()
    {
        if (!$this->customerToDelete) {
            return;
        }

        // PIN verification
        $user = AuthService::user();
        $credential = $user?->credentials;
        if (!$credential || !Hash::check($this->deletePin, $credential->pin)) {
            $this->deletePinError = 'Invalid PIN. Deletion cancelled.';
            return;
        }

        $customer = $this->customerToDelete;

        // Prevent if balance > 0 (unpaid transactions)
        if ((float) $customer->total_balance > 0) {
            $this->dispatch('showAlert', 'Cannot delete customer with outstanding balance.');
            $this->cancelDelete();
            return;
        }

        // Prevent if any UNPAID transactions exist (safety check)
        if ($customer->transactions()->where('status', '!=', 'PAID')->exists()) {
            $this->dispatch('showAlert', 'Cannot delete customer with unpaid transactions.');
            $this->cancelDelete();
            return;
        }

        // Detach customer from transactions (now allowed, column is nullable)
        $customer->transactions()->update(['customer_id' => null]);

        // Detach customer from payments
        $customer->payments()->update(['customer_id' => null]);

        // Detach customer from transaction_history
        \App\Models\TransactionHistory::where('customer_id', $customer->id)
            ->update(['customer_id' => null]);

        // Finally delete the customer
        $customer->delete();

        $this->showDeleteModal = false;
        $this->customerToDelete = null;
        $this->deletePin = '';
        $this->deletePinError = '';
        $this->dispatch('showAlert', 'Customer deleted. All transaction history preserved.');
    }

    public function cancelDelete()
    {
        $this->showDeleteModal = false;
        $this->customerToDelete = null;
        $this->deletePin = '';
        $this->deletePinError = '';
    }

    public function resetForm()
    {
        $this->form = [
            'name' => '',
            'contact_number' => '',
            'address' => '',
            'credit_limit' => 0.00,
        ];
        $this->editingCustomer = null;
        $this->agreeTerms = false;
        $this->termsAttachment = null;
    }

    public function closeModal()
    {
        $this->resetForm();
        $this->showCreateModal = false;
    }

    public function render()
    {
        $query = Customer::query()
            ->when(trim($this->search) !== '', function ($query) {
                $query->where(function ($query) {
                    $query->where('name', 'like', '%' . trim($this->search) . '%')
                        ->orWhere('contact_number', 'like', '%' . trim($this->search) . '%');
                });
            });

        if ($this->creditLimitSort === 'high_low') {
            $query->orderByDesc('credit_limit');
        } elseif ($this->creditLimitSort === 'low_high') {
            $query->orderBy('credit_limit');
        } else {
            $query->orderBy('name');
        }

        $customers = $query->paginate(5);

        return view('livewire.customers', compact('customers'));
    }
}
