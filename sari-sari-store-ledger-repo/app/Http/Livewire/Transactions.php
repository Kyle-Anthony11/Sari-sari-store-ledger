<?php

namespace App\Http\Livewire;

use App\Models\Transaction;
use App\Models\Customer;
use App\Models\Payment;
use App\Models\TransactionHistory;
use App\Models\Product;
use App\Models\StoreSetting;
use App\Services\AuthService;
use App\Services\TransactionNumberGenerator;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Livewire\Component;
use Livewire\WithPagination;
use Illuminate\Support\Facades\Hash;
use Livewire\WithFileUploads;

class Transactions extends Component
{
    use WithPagination;
    use WithFileUploads;
    protected $paginationTheme = 'simple-tailwind-custom';

    public string $currentGroupId = '';
    public $paymentGroupTransactions = null;
    public array $selectedPaymentTransactionIds = [];
    public bool $showGroupPaymentModal = false;
    public bool $showGroupDetailsModal = false;
    public $newCustomerTermsAttachment = null;
    public $groupDetailsTransactions = null;
    public ?string $groupToDelete = null;
    public ?string $groupToPay = null;

    // Search filters
    public string $searchCustomerName = '';
    public string $searchItemName = '';
    public string $searchDate = '';
    public string $statusFilter = 'All';

    public bool $showReceiptModal = false;
    public array $receiptData = [];

    // Wizard state
    public int $step = 1;
    public string $customerType = 'existing';
    public ?Customer $selectedCustomer = null;
    public ?int $selectedCustomerId = null;

    // New customer form
    public string $newCustomerName = '';
    public string $newCustomerContactNumber = '';
    public string $newCustomerAddress = '';
    public float $newCustomerCreditLimit = 0;
    public $newCustomerAgreeTerms = false;

    // Transaction fields
    public string $transactionDate = '';
    public string $notes = '';
    public string $deletePinError = '';
    public array $selectedProducts = [];

    // Modals visibility
    public bool $showCustomerTypeModal = false;
    public bool $showNewCustomerModal = false;
    public bool $showExistingCustomerModal = false;
    public bool $showProductsModal = false;
    public bool $showOverviewModal = false;
    public bool $showDeleteModal = false;
    public ?int $transactionToDelete = null;
    public string $deletePin = '';
    public string $pinError = '';

    // Product creation inside wizard
    public string $productName = '';
    public string $productUnitType = 'Per Piece';
    public ?float $productPrice = null;
    public string $productSearch = '';
    public string $existingCustomerSearch = '';

    // Edit single transaction (legacy)
    public bool $showEditModal = false;
    public ?int $editingTransactionId = null;
    public ?object $editSelectedProduct = null;

    public bool $editingIsOverdue    = false;
    public int  $editingDaysOverdue  = 0;
    public int  $editingWeeksOverdue = 0;
    public int  $editingDaysUntilDue = 0;
    public array $editForm = [
        'quantity' => 1.0,
        'unit_override' => 'Per Piece',
        'price' => 0.0,
        'notes' => '',
    ];
    public string $paymentMethod = 'cash';
    public string $referenceNumber = '';
    public string $editCustomerName = '';
    public string $editTransactionDate = '';
    public string $editDueDate = '';
    public float $editSurchargeTotal = 0.0;
    public float $editInterestRate = 0.0;
    public float $editSurchargePerUnit = 0.0;

    // Payment
    public bool $showPaymentModal = false;
    public ?int $paymentTransactionId = null;
    public ?Transaction $paymentTransactionData = null;
    public float $cashGiven = 0;
    public float $totalToPay = 0;
    public float $change = 0;

    public bool $showPaymentScopeModal = false;
    public ?int $pendingTransactionId = null;
    public ?string $paymentMode = null;
    public array $bulkTransactions = [];
    public array $transactionItems = [];

    public bool $showEditGroupModal = false;
    public ?string $editingGroupId = null;
    public ?Customer $editingCustomer = null;
    public $editingTransactionDate = null;
    public $editingDueDate = null;
    public float $editingSurchargePerUnit = 0.0;
    public float $editingInterestRate = 0.0;
    public $editingAvailableProducts;
    public array $editingSelectedProducts = [];
    public ?int $editingNewProductId = null;

    protected $storeSettings;

    protected $queryString = ['searchCustomerName', 'searchItemName', 'searchDate', 'statusFilter'];

    public function mount()
    {
        $this->transactionDate = Carbon::today()->format('Y-m-d');
        $this->editingAvailableProducts = collect();
    }

    protected function getStoreSettings()
    {
        if (!$this->storeSettings) {
            $profile = AuthService::user();
            $this->storeSettings = $profile?->settings ?? new StoreSetting([
                'surcharge' => 0,
                'interest_rate' => 0,
                'transaction_duration_days' => 30,
            ]);
        }
        return $this->storeSettings;
    }

    public function getSurchargePerUnitProperty()
    {
        return (float) $this->getStoreSettings()->surcharge;
    }

    public function getComputedSelectedTotalProperty()
    {
        $base = array_sum(array_column($this->selectedProducts, 'base_total'));
        $surcharge = array_sum(array_column($this->selectedProducts, 'surcharge'));
        return $base + $surcharge;
    }

    /**
     * Sum of surcharge_total across unpaid lines in the Edit Transaction modal.
     */
    public function getEditingTotalSurchargeProperty(): float
    {
        $sum = 0.0;
        foreach ($this->editingSelectedProducts as $line) {
            if (! empty($line['is_paid'])) {
                continue;
            }
            $sum += (float) ($line['surcharge_total'] ?? 0);
        }
        return round($sum, 2);
    }

    /**
     * Interest accrued on all unpaid lines in the Edit Transaction modal.
     * Uses snapshot rate + weeks overdue (same formula as Transaction model).
     */
    public function getEditingTotalInterestProperty(): float
    {
        if (
            ! $this->editingIsOverdue
            || $this->editingInterestRate <= 0
            || $this->editingWeeksOverdue <= 0
        ) {
            return 0.0;
        }

        $totalBase = 0.0;
        foreach ($this->editingSelectedProducts as $line) {
            if (! empty($line['is_paid'])) {
                continue;
            }
            $totalBase += (float) ($line['base_total'] ?? 0);
        }

        return round($totalBase * ($this->editingInterestRate / 100) * $this->editingWeeksOverdue, 2);
    }

    /**
     * Grand total shown in the Edit Transaction modal footer:
     *   sum(unpaid line base_total + surcharge_total) + editingTotalInterest
     */
    public function getEditingGrandTotalProperty(): float
    {
        $sum = 0.0;
        foreach ($this->editingSelectedProducts as $line) {
            if (! empty($line['is_paid'])) {
                continue;
            }
            $sum += (float) ($line['base_total'] ?? 0) + (float) ($line['surcharge_total'] ?? 0);
        }
        return round($sum + $this->editingTotalInterest, 2);
    }

    // --- Wizard steps ---
    public function createTransaction()
    {
        $this->resetValidation();
        $this->resetWizard();
        $this->currentGroupId = (string) Str::uuid();
        $this->showCustomerTypeModal = true;
    }

    public function cancelWizard()
    {
        $this->resetWizard();
        $this->showCustomerTypeModal = false;
        $this->showNewCustomerModal = false;
        $this->showExistingCustomerModal = false;
        $this->showProductsModal = false;
        $this->showOverviewModal = false;
        $this->customerType = 'existing';
        $this->selectedCustomer = null;
        $this->selectedCustomerId = null;
        $this->dispatch('showAlert', 'Transaction setup cancelled.');
    }

    public function chooseExistingCustomer()
    {
        $this->customerType = 'existing';
        $this->showCustomerTypeModal = false;
        $this->showExistingCustomerModal = true;
        $this->existingCustomerSearch = '';
        $this->selectedCustomer = null;
        $this->selectedCustomerId = null;
    }

    public function chooseNewCustomer()
    {
        $this->customerType = 'new';
        $this->showCustomerTypeModal = false;
        $this->showNewCustomerModal = true;
        $this->resetNewCustomerForm();
    }

    public function backToCustomerType()
    {
        $this->showNewCustomerModal = false;
        $this->showExistingCustomerModal = false;
        $this->showCustomerTypeModal = true;
    }

    public function saveNewCustomerAndContinue()
    {
        $this->validate([
            'newCustomerName' => ['required', 'string', 'max:255', 'unique:customers,name', 'regex:/^[a-zA-Z\s]+$/'],
            'newCustomerContactNumber' => ['nullable', 'string', 'regex:/^\+639\d{9}$/'],
            'newCustomerAddress' => 'nullable|string',
            'newCustomerCreditLimit' => 'nullable|numeric|min:0',
            'newCustomerAgreeTerms' => 'accepted',
            'newCustomerTermsAttachment' => 'required|image|max:5120',
        ], [
            'newCustomerName.regex' => 'The customer name may only contain letters and spaces.',
            'newCustomerContactNumber.regex' => 'The contact number must be in the format +63 9XX XXX XXXX.',
            'newCustomerAgreeTerms.accepted' => 'You must read and agree to the Terms and Conditions.',
            'newCustomerTermsAttachment.required' => 'Please attach the signed Terms & Conditions image.',
            'newCustomerTermsAttachment.image' => 'The attached file must be a valid image.',
            'newCustomerTermsAttachment.max'   => 'The image must not exceed 5MB.',
        ]);

        $globalMax = $this->getGlobalMaxCreditLimit();
        $globalMin = $this->getGlobalMinCreditLimit();
        $enteredLimit = (float) ($this->newCustomerCreditLimit ?? 0);

        if ($globalMax > 0 && $enteredLimit > $globalMax) {
            $this->dispatch('showAlert', "Credit limit cannot exceed the global max credit limit of ₱" . number_format($globalMax, 2));
            return;
        }

        if ($globalMin > 0 && $enteredLimit > 0 && $enteredLimit < $globalMin) {
            $this->dispatch('showAlert', "Credit limit cannot be less than the minimum credit limit of ₱" . number_format($globalMin, 2));
            return;
        }

        $contactNumber = !empty($this->newCustomerContactNumber) ? $this->newCustomerContactNumber : null;

        $termsPath = null;
        if ($this->newCustomerTermsAttachment) {
            $termsPath = $this->newCustomerTermsAttachment->store('terms_attachments', 'public');
        }

        $customer = Customer::create([
            'name' => $this->newCustomerName,
            'contact_number' => $contactNumber,
            'address' => $this->newCustomerAddress ?: null,
            'credit_limit' => round($enteredLimit, 2),
            'total_balance' => 0,
            'is_active' => true,
            'accepted_terms_at' => now(),
            'terms_attachment_path' => $termsPath,
        ]);

        $this->selectedCustomerId = $customer->id;
        $this->selectedCustomer = $customer;
        $this->showNewCustomerModal = false;
        $this->newCustomerTermsAttachment = null;
        $this->goToProducts();
    }

    public function getExistingCustomersProperty()
    {
        $query = Customer::where('is_active', true);
        if (trim($this->existingCustomerSearch)) {
            $query->where('name', 'like', '%' . $this->existingCustomerSearch . '%');
        }
        return $query->orderBy('name')->get(['id', 'name', 'contact_number', 'credit_limit']);
    }

    protected function getGlobalMinCreditLimit(): float
    {
        $settings = $this->getStoreSettings();
        return (float) ($settings->min_credit_limit ?? 0);
    }

    public function selectExistingCustomer($customerId)
    {
        $this->selectedCustomerId = $customerId;
        $this->selectedCustomer = Customer::findOrFail($customerId);
        $this->showExistingCustomerModal = false;
        $this->goToProducts();
    }

    private function goToProducts()
    {
        $this->resetProductsSelection();
        $this->transactionDate = Carbon::today()->format('Y-m-d');
        $this->notes = '';
        $this->showProductsModal = true;
        $this->showOverviewModal = false;
    }

    private function resetNewCustomerForm(): void
    {
        $this->newCustomerName = '';
        $this->newCustomerContactNumber = '';
        $this->newCustomerAddress = '';
        $this->newCustomerCreditLimit = 0;
        $this->newCustomerAgreeTerms = false;
        $this->newCustomerTermsAttachment = null;
    }

    public function backToCustomerSelection()
    {
        $this->showProductsModal = false;
        if ($this->selectedCustomerId && $this->customerType === 'existing') {
            $this->showExistingCustomerModal = true;
        } elseif ($this->customerType === 'new') {
            $this->showNewCustomerModal = true;
        } else {
            $this->showCustomerTypeModal = true;
        }
    }

    public function goToOverview()
    {
        if (!$this->selectedCustomerId) {
            $this->dispatch('showAlert', 'Please choose a customer.');
            return;
        }
        if (count($this->selectedProducts) === 0) {
            $this->dispatch('showAlert', 'Please select at least one product.');
            return;
        }
        $this->showProductsModal = false;
        $this->showOverviewModal = true;
    }

    public function backToProducts()
    {
        $this->showOverviewModal = false;
        $this->showProductsModal = true;
    }

    // --- Product management ---
    public function getAvailableProductsProperty()
    {
        $query = Product::where('is_active', true);
        if (trim($this->productSearch)) {
            $query->where('name', 'like', '%' . $this->productSearch . '%');
        }
        return $query->orderBy('name')->limit(50)->get(['id', 'name', 'unit', 'price']);
    }

    public function toggleProduct($productId, $unit, $price)
    {
        $index = array_search($productId, array_column($this->selectedProducts, 'product_id'));
        if ($index !== false) {
            array_splice($this->selectedProducts, $index, 1);
        } else {
            $surchargePerUnit = $this->getSurchargePerUnitProperty();
            $this->selectedProducts[] = [
                'product_id' => $productId,
                'name' => '',
                'unit_override' => $unit === 'Kilos' ? 'Per Kilos' : 'Per Piece',
                'qty' => 1.0,
                'price' => (float) $price,
                'base_total' => (float) $price,
                'surcharge' => $surchargePerUnit * 1.0,
                'total' => (float) $price + ($surchargePerUnit * 1.0),
            ];
        }
        $this->refreshSelectedProducts();
    }

    public function removeSelectedProduct($index)
    {
        unset($this->selectedProducts[$index]);
        $this->selectedProducts = array_values($this->selectedProducts);
    }

    public function updatedSelectedProducts()
    {
        $this->refreshSelectedProducts();
    }

    private function refreshSelectedProducts()
    {
        $ids = array_column($this->selectedProducts, 'product_id');
        $products = Product::whereIn('id', $ids)->get(['id', 'name', 'price']);
        $map = $products->keyBy('id');
        $surchargePerUnit = $this->getSurchargePerUnitProperty();

        foreach ($this->selectedProducts as $i => $sp) {
            $pid = $sp['product_id'];
            if (isset($map[$pid])) {
                $this->selectedProducts[$i]['name'] = $map[$pid]->name;
                $this->selectedProducts[$i]['price'] = $sp['price'] ?? (float) $map[$pid]->price;
                $qty = (float) ($sp['qty'] ?? 0);
                $unitPrice = $this->selectedProducts[$i]['price'];
                $this->selectedProducts[$i]['base_total'] = $unitPrice * $qty;
                $this->selectedProducts[$i]['surcharge'] = $surchargePerUnit * $qty;
                $this->selectedProducts[$i]['total'] = $this->selectedProducts[$i]['base_total'] + $this->selectedProducts[$i]['surcharge'];
            }
        }
    }

    public function createProductFromWizard()
    {
        $this->validate([
            'productName' => 'required|string|max:255',
            'productUnitType' => 'required|in:Per Piece,Per Kilos',
            'productPrice' => 'required|numeric|min:0.01',
        ]);

        $unit = $this->productUnitType === 'Per Kilos' ? 'Kilos' : 'Pieces';
        $product = Product::create([
            'name' => $this->productName,
            'unit' => $unit,
            'price' => (float) $this->productPrice,
            'is_active' => true,
        ]);

        $this->productName = '';
        $this->productPrice = null;
        $this->toggleProduct($product->id, $product->unit, (float) $product->price);
    }

    // --- Save final transaction ---
    public function saveTransactionFromWizard()
    {
        $this->validate([
            'selectedCustomerId' => 'required|exists:customers,id',
            'transactionDate' => 'required|date',
        ]);

        if (count($this->selectedProducts) === 0) {
            $this->dispatch('showAlert', 'No products selected.');
            return;
        }

        $customer = Customer::findOrFail($this->selectedCustomerId);
        $settings = $this->getStoreSettings();
        $surchargePerUnit = $settings->surcharge;
        $interestRate = (float) $settings->interest_rate;
        $durationDays = (int) $settings->transaction_duration_days;

        $dueDate = Carbon::parse($this->transactionDate)->addDays($durationDays);
        $status = Carbon::today()->gt($dueDate) ? 'OVERDUE' : 'PENDING';

        $totalDebt = 0;
        foreach ($this->selectedProducts as $sp) {
            $qty = (float) $sp['qty'];
            if ($qty <= 0) continue;
            $lineBase = (float) $sp['price'] * $qty;
            $lineSurcharge = $surchargePerUnit * $qty;
            $totalDebt += $lineBase + $lineSurcharge;
        }

        $currentBalance = (float) ($customer->total_balance ?? 0);
        $newTotalBalance = $currentBalance + $totalDebt;

        $individualLimit = (float) ($customer->credit_limit ?? 0);
        $globalMax = $this->getGlobalMaxCreditLimit();

        $effectiveLimit = $individualLimit;
        if ($globalMax > 0 && ($individualLimit == 0 || $globalMax < $individualLimit)) {
            $effectiveLimit = $globalMax;
        }

        if (is_null($customer->accepted_terms_at)) {
            $this->dispatch('showAlert', 'This customer has not accepted the Terms and Conditions. Please update the customer record to accept terms first.');
            return;
        }

        if ($effectiveLimit > 0 && $newTotalBalance > $effectiveLimit) {
            $limitText = $globalMax > 0 && $globalMax < $individualLimit
                ? "global max credit limit of ₱" . number_format($globalMax, 2)
                : "credit limit of ₱" . number_format($effectiveLimit, 2);
            $this->dispatch('showAlert', "Transaction would exceed {$limitText}. Available: ₱" . number_format(max(0, $effectiveLimit - $currentBalance), 2));
            return;
        }

        $transactionNumber = app(TransactionNumberGenerator::class)->generate();

        DB::transaction(function () use ($customer, $dueDate, $status, $surchargePerUnit, $interestRate, $durationDays, $transactionNumber) {
            foreach ($this->selectedProducts as $sp) {
                $qty = (float) $sp['qty'];
                if ($qty <= 0) continue;

                $product = Product::findOrFail($sp['product_id']);
                $unitPrice = (float) $sp['price'];
                $lineBase = $unitPrice * $qty;
                $lineSurcharge = $surchargePerUnit * $qty;

                Transaction::create([
                    'products_id' => $product->id,
                    'customer_id' => $customer->id,
                    'quantity' => $qty,
                    'base_total' => $lineBase,
                    'surcharge_total' => $lineSurcharge,
                    'interest_rate' => $interestRate,
                    'total_amount' => $lineBase + $lineSurcharge,
                    'transaction_date' => $this->transactionDate,
                    'due_date' => $dueDate,
                    'status' => $status,
                    'notes' => $this->notes,
                    'product_name' => $product->name,
                    'product_unit' => $product->unit,
                    'surcharge_at_creation' => $surchargePerUnit,
                    'interest_rate_at_creation' => $interestRate,
                    'duration_days_at_creation' => $durationDays,
                    'group_id' => $this->currentGroupId,
                    'transaction_number' => $transactionNumber,
                ]);

                $customer->increment('total_balance', $lineBase + $lineSurcharge);
            }

            $customer->updateRiskLevel();
        });

        $this->resetWizard();
        $this->showOverviewModal = false;
        $this->dispatch('showAlert', 'Transaction recorded successfully!');
        $this->currentGroupId = '';
    }

    private function resetWizard()
    {
        $this->selectedCustomerId = null;
        $this->selectedCustomer = null;
        $this->selectedProducts = [];
        $this->transactionDate = Carbon::today()->format('Y-m-d');
        $this->notes = '';
        $this->newCustomerName = '';
        $this->newCustomerContactNumber = '';
        $this->newCustomerAddress = '';
        $this->newCustomerCreditLimit = 0;
        $this->productName = '';
        $this->productPrice = null;
        $this->productSearch = '';
        $this->existingCustomerSearch = '';
        $this->showCustomerTypeModal = false;
        $this->showNewCustomerModal = false;
        $this->showExistingCustomerModal = false;
        $this->showProductsModal = false;
        $this->showOverviewModal = false;
        $this->currentGroupId = '';
        $this->newCustomerTermsAttachment = null;
    }

    private function resetProductsSelection()
    {
        $this->selectedProducts = [];
        $this->productSearch = '';
    }

    // --- Edit single transaction (legacy) ---
    public function editTransaction($id)
    {
        $transaction = Transaction::with('product', 'customer')->findOrFail($id);
        if ($transaction->status === 'PAID') {
            $this->dispatch('showAlert', 'Paid transactions cannot be edited.');
            return;
        }

        $this->editingTransactionId = $transaction->id;

        $this->editCustomerName = $transaction->customer?->name ?? 'N/A';
        $this->editTransactionDate = $transaction->transaction_date->format('Y-m-d');
        $this->editDueDate = $transaction->due_date->format('Y-m-d');
        $this->editSurchargeTotal = $transaction->surcharge_total;
        $this->editInterestRate = $transaction->interest_rate;
        $this->editSurchargePerUnit = $transaction->surcharge_at_creation;

        $this->editForm = [
            'quantity' => (float) number_format($transaction->quantity, 2),
            'unit_override' => $transaction->product->unit === 'Kilos' ? 'Per Kilos' : 'Per Piece',
            'price' => (float) ($transaction->product->price ?? 0),
            'notes' => $transaction->notes,
        ];

        $this->editSelectedProduct = $transaction->product;
        $this->showEditModal = true;
    }

    public function updateEditTransaction()
    {
        $this->validate([
            'editForm.quantity' => 'required|numeric|min:0.01',
            'editForm.price' => 'required|numeric|min:0.01',
            'editForm.unit_override' => 'required|in:Per Piece,Per Kilos',
            'editForm.notes' => 'nullable|string',
        ]);

        $transaction = Transaction::with('customer')->findOrFail($this->editingTransactionId);
        $oldTotal = $transaction->total_amount;

        $qty = (float) $this->editForm['quantity'];
        $newPrice = (float) $this->editForm['price'];
        $surchargePerUnit = (float) $transaction->surcharge_at_creation;

        $newBase = $newPrice * $qty;
        $newSurcharge = $surchargePerUnit * $qty;
        $newTotal = $newBase + $newSurcharge;

        $customer = $transaction->customer;
        if ($customer) {
            $currentBalance = (float) $customer->total_balance;
            $balanceDelta = $newTotal - $oldTotal;
            $newBalance = $currentBalance + $balanceDelta;

            $effectiveLimit = (float) ($customer->credit_limit ?? 0);
            $globalMax = $this->getGlobalMaxCreditLimit();
            if ($globalMax > 0 && ($effectiveLimit == 0 || $globalMax < $effectiveLimit)) {
                $effectiveLimit = $globalMax;
            }

            if ($effectiveLimit > 0 && $newBalance > $effectiveLimit) {
                $this->dispatch('showAlert', "Update would exceed credit limit. Available: ₱" . number_format(max(0, $effectiveLimit - $currentBalance), 2));
                return;
            }

            $customer->increment('total_balance', $balanceDelta);
            $customer->updateRiskLevel();
        }

        $transaction->update([
            'quantity' => $qty,
            'base_total' => $newBase,
            'surcharge_total' => $newSurcharge,
            'total_amount' => $newTotal,
            'notes' => $this->editForm['notes'],
            'product_unit' => $this->editForm['unit_override'] === 'Per Kilos' ? 'Kilos' : 'Pieces',
        ]);

        $this->showEditModal = false;
        $this->resetEditForm();
        $this->dispatch('showAlert', 'Transaction updated successfully.');
    }

    private function resetEditForm()
    {
        $this->editingTransactionId = null;
        $this->editForm = [
            'quantity' => 1.0,
            'unit_override' => 'Per Piece',
            'price' => 0.0,
            'notes' => '',
        ];
        $this->editSelectedProduct = null;
        $this->editCustomerName = '';
        $this->editTransactionDate = '';
        $this->editDueDate = '';
        $this->editSurchargeTotal = 0.0;
        $this->editInterestRate = 0.0;
        $this->editSurchargePerUnit = 0.0;
    }

    // --- Payment ---
    public function choosePaymentScope($transactionId)
    {
        $this->pendingTransactionId = $transactionId;
        $this->showPaymentScopeModal = true;
    }

    public function paySelectedProductOnly()
    {
        $this->paymentMode = 'single';
        $this->showPaymentScopeModal = false;
        $this->payTransaction($this->pendingTransactionId);
    }

    public function payAllSameDate()
    {
        $transaction = Transaction::find($this->pendingTransactionId);
        if (!$transaction) {
            $this->dispatch('showAlert', 'Transaction not found.');
            $this->showPaymentScopeModal = false;
            return;
        }

        $bulk = Transaction::with('product')
            ->where('customer_id', $transaction->customer_id)
            ->whereDate('transaction_date', $transaction->transaction_date)
            ->where('status', '!=', 'PAID')
            ->get();

        if ($bulk->isEmpty()) {
            $this->dispatch('showAlert', 'No other unpaid transactions found for this date.');
            $this->showPaymentScopeModal = false;
            return;
        }

        $this->transactionItems = $bulk
            ->map(fn ($tx) => $this->buildTransactionItem($tx))
            ->toArray();

        $this->bulkTransactions = $bulk->map(function ($tx) {
            return [
                'id'            => $tx->id,
                'product_name'  => $tx->product?->name ?? 'Deleted Product',
                'current_total' => $tx->current_total,
            ];
        })->toArray();

        $this->paymentMode = 'bulk';
        $this->showPaymentScopeModal = false;

        $this->paymentTransactionId = $this->pendingTransactionId;
        $this->paymentTransactionData = $transaction;
        $this->totalToPay = collect($this->transactionItems)->sum('total');
        $this->cashGiven = 0;
        $this->change = 0;
        $this->paymentMethod = 'cash';
        $this->referenceNumber = '';
        $this->showPaymentModal = true;
    }

    public function cancelPaymentScope()
    {
        $this->showPaymentScopeModal = false;
        $this->pendingTransactionId = null;
    }

    public function payTransaction($id)
    {
        $transaction = Transaction::with('product')->findOrFail($id);
        if ($transaction->status === 'PAID') {
            $this->dispatch('showAlert', 'Transaction already paid.');
            return;
        }

        $this->transactionItems = [$this->buildTransactionItem($transaction)];

        $this->paymentMode = 'single';
        $this->paymentTransactionId = $transaction->id;
        $this->paymentTransactionData = $transaction;
        $this->totalToPay = (float) $transaction->current_total;
        $this->cashGiven = 0;
        $this->change = 0;
        $this->paymentMethod = 'cash';
        $this->referenceNumber = '';
        $this->showPaymentModal = true;
    }

    /**
     * Shared builder used by all three payment entry points so the row's
     * `total` always matches the sum of base + surcharge + interest.
     */
    private function buildTransactionItem(Transaction $tx): array
    {
        $qty       = (float) $tx->quantity;
        $base      = (float) $tx->base_total;
        $unitPrice = $qty > 0 ? $base / $qty : 0.0;
        $surcharge = (float) $tx->surcharge_total;
        $interest  = (float) $tx->interest_amount;
        $total     = (float) $tx->current_total;

        return [
            'product_name' => $tx->display_product_name,
            'quantity'     => $qty,
            'unit_price'   => $unitPrice,
            'surcharge'    => $surcharge,
            'interest'     => $interest,
            'total'        => $total,
        ];
    }

    public function submitPayment()
    {
        $transactionIds = [];
        $customer = null;

        if ($this->paymentMode === 'single') {
            $transactionIds = [$this->paymentTransactionId];
            $transaction = Transaction::with('customer')->find($this->paymentTransactionId);
            if (!$transaction || $transaction->status === 'PAID') {
                $this->dispatch('showAlert', 'Invalid transaction.');
                return;
            }
            $customer = $transaction->customer;
            $totalDue = $transaction->current_total;
            $this->totalToPay = $totalDue;
        } elseif ($this->paymentMode === 'bulk') {
            $transactionIds = array_column($this->bulkTransactions, 'id');
            $transactions = Transaction::whereIn('id', $transactionIds)->get();
            if ($transactions->isEmpty()) {
                $this->dispatch('showAlert', 'No valid transactions.');
                return;
            }
            $customer = $transactions->first()->customer;
            $totalDue = $transactions->sum('current_total');
            $this->totalToPay = $totalDue;
        } elseif ($this->paymentMode === 'group') {
            $transactionIds = $this->selectedPaymentTransactionIds;
            $transactions = Transaction::whereIn('id', $transactionIds)->get();
            if ($transactions->isEmpty()) {
                $this->dispatch('showAlert', 'No transactions selected.');
                return;
            }
            $customer = $transactions->first()->customer;
            $totalDue = $transactions->sum('current_total');
            $this->totalToPay = $totalDue;
        } else {
            $this->dispatch('showAlert', 'Invalid payment mode.');
            return;
        }

        if (!$customer) {
            $this->dispatch('showAlert', 'Customer not found.');
            return;
        }

        if ($this->paymentMethod === 'gcash' && empty(trim($this->referenceNumber))) {
            $this->dispatch('showAlert', 'Reference number is required for Gcash payments.');
            return;
        }

        $this->validate([
            'cashGiven' => 'required|numeric|min:' . $totalDue,
        ]);

        $changeAmount = $this->change;

        $this->processPayment(
            $transactionIds,
            $this->cashGiven,
            $changeAmount,
            $this->paymentMethod,
            $this->referenceNumber
        );

        $paidTransactions = Transaction::whereIn('id', $transactionIds)->get();
        $this->receiptData = $this->buildReceiptData(
            $paidTransactions,
            $this->cashGiven,
            $changeAmount,
            $customer,
            $this->paymentMethod,
            $this->referenceNumber
        );

        $this->resetPaymentModalState();
        $this->showReceiptModal = true;
    }

    public function updateChange()
    {
        $this->change = max(0, (float) $this->cashGiven - $this->totalToPay);
    }

    private function processPayment($transactionIds, $cashGiven, $change, $paymentMethod, $referenceNumber)
    {
        $transactions = Transaction::whereIn('id', $transactionIds)->with('customer')->get();
        if ($transactions->isEmpty()) return;

        $customer = $transactions->first()->customer;
        if (!$customer) return;

        $totalDue = $transactions->sum('current_total');

        DB::transaction(function () use ($transactions, $customer, $cashGiven, $change, $paymentMethod, $referenceNumber, $totalDue) {
            $payment = Payment::create([
                'transactions_id'   => $transactions->first()->id,
                'customer_id'       => $customer->id,
                'total_amount'      => $totalDue,
                'cash_given'        => $cashGiven,
                'change'            => $change,
                'payment_date'      => Carbon::now(),
                'notes'             => "Payment for " . $transactions->count() . " transaction(s)",
                'payment_method'    => $paymentMethod,
                'reference_number'  => $paymentMethod === 'gcash' ? $referenceNumber : null,
            ]);

            foreach ($transactions as $transaction) {
                if ($transaction->status === 'PAID') continue;

                $paidAmount = $transaction->current_total;

                $transaction->update([
                    'status'     => 'PAID',
                    'payment_id' => $payment->id,
                ]);

                TransactionHistory::create([
                    'customer_id'     => $customer->id,
                    'customer_name'   => $customer->name,
                    'transactions_id' => $transaction->id,
                    'product_name'    => $transaction->display_product_name,
                    'transaction_date' => $transaction->transaction_date,
                    'due_date'        => $transaction->due_date,
                    'payment_date'    => Carbon::now(),
                    'surcharge_total' => $transaction->surcharge_total,
                    'interest_rate'   => $transaction->interest_rate,
                    'duration_days'   => $transaction->duration_days,
                    'total_amount'    => $paidAmount,
                    'cash_given'      => $cashGiven,
                    'change'          => $change,
                    'status'          => 'PAID',
                    'payments_id'     => $payment->id,
                    'payment_method'  => $paymentMethod,
                    'reference_number' => $paymentMethod === 'gcash' ? $referenceNumber : null,
                ]);

                $customer->decrement('total_balance', $paidAmount);
            }

            $customer->updateRiskLevel();
        });
    }

    private function buildReceiptData($transactions, $cashGiven, $change, $customer, $paymentMethod, $referenceNumber): array
    {
        $store = AuthService::user();
        $ownerName = trim(($store->owner_first_name ?? '') . ' ' . ($store->owner_middle_name ?? '') . ' ' . ($store->owner_last_name ?? ''));
        $items = [];
        $total = 0;

        foreach ($transactions as $tx) {
            $qty = (float) $tx->quantity;
            $unitPrice = $qty > 0 ? ($tx->base_total / $qty) : 0;
            $lineTotal = $tx->current_total;
            $total += $lineTotal;
            $items[] = [
                'name' => $tx->display_product_name,
                'quantity' => $qty,
                'unit_price' => $unitPrice,
                'total' => $lineTotal,
            ];
        }

        $isGcash = strtolower($paymentMethod) === 'gcash';

        $data = [
            'store_name'     => $store->store_name ?? 'Store',
            'owner_name'     => $ownerName,
            'customer_name'  => $customer->name,
            'items'          => $items,
            'total_amount'   => $total,
            'cash_given'     => $cashGiven,
            'change'         => $change,
            'payment_date'   => now()->format('F d, Y h:i A'),
            'payment_method' => $isGcash ? 'GCash' : 'Cash',
            'closeAction'    => 'closeReceipt',
        ];

        if ($isGcash && !empty($referenceNumber)) {
            $data['reference_number'] = $referenceNumber;
        }

        return $data;
    }

    public function closeReceipt()
    {
        $this->showReceiptModal = false;
        $this->receiptData = [];
    }

    public function updatedPaymentMethod($value): void
    {
        if ($value !== 'gcash') {
            $this->referenceNumber = '';
            $this->resetValidation('referenceNumber');
        }
    }

    // --- Whole-transaction View / Edit ---
    public function viewGroup($groupId)
    {
        $group = Transaction::where('group_id', $groupId)->get();
        $isFullyPaid = $group->every(fn($t) => $t->status === 'PAID');

        if ($isFullyPaid) {
            $this->groupDetailsTransactions = $group;
            $this->showGroupDetailsModal = true;
            return;
        }

        $this->openEditGroup($groupId);
    }

    public function closeGroupDetails()
    {
        $this->showGroupDetailsModal = false;
        $this->groupDetailsTransactions = null;
    }

    public function openEditGroup($groupId)
    {
        $lines = Transaction::where('group_id', $groupId)->with('product')->get();
        $this->editingGroupId = $groupId;
        $this->editingCustomer = $lines->first()->customer;
        $this->editingTransactionDate = $lines->first()->transaction_date;
        $this->editingDueDate = $lines->first()->due_date;
        $dueDate = $lines->first()->due_date ? Carbon::parse($lines->first()->due_date) : null;
        $today   = Carbon::today();

        if ($dueDate) {
            if ($today->greaterThan($dueDate)) {
                $this->editingIsOverdue    = true;
                $this->editingDaysOverdue  = $dueDate->diffInDays($today);
                $this->editingWeeksOverdue = (int) ceil($this->editingDaysOverdue / 7);
                $this->editingDaysUntilDue = 0;
            } else {
                $this->editingIsOverdue    = false;
                $this->editingDaysOverdue  = 0;
                $this->editingWeeksOverdue = 0;
                $this->editingDaysUntilDue = $today->diffInDays($dueDate, false);
            }
        } else {
            $this->editingIsOverdue    = false;
            $this->editingDaysOverdue  = 0;
            $this->editingWeeksOverdue = 0;
            $this->editingDaysUntilDue = 0;
        }
        $this->editingSurchargePerUnit = (float) $lines->first()->surcharge_at_creation;
        $this->editingInterestRate = (float) $lines->first()->interest_rate_at_creation;

        $this->editingSelectedProducts = $lines->map(function ($line) {
            return [
                'id' => $line->id,
                'product_id' => $line->products_id,
                'product_name' => $line->product?->name ?? 'Deleted Product',
                'unit_override' => $line->product_unit ?? 'Per Piece',
                'quantity' => (float) $line->quantity,
                'price' => (float) ($line->quantity > 0 ? $line->base_total / $line->quantity : 0),
                'base_total' => (float) $line->base_total,
                'surcharge_total' => (float) $line->surcharge_total,
                'total' => (float) $line->total_amount,
                'is_paid' => $line->status === 'PAID',
            ];
        })->toArray();

        $usedProductIds = $lines->pluck('products_id')->toArray();
        $this->editingAvailableProducts = Product::where('is_active', true)
            ->whereNotIn('id', $usedProductIds)
            ->get();

        $this->editingNewProductId = null;
        $this->showEditGroupModal = true;
    }

    public function addProductToGroup()
    {
        $productId = $this->editingNewProductId;

        if (!$productId) {
            $this->dispatch('showAlert', 'Please select a product.');
            return;
        }

        $product = Product::findOrFail($productId);
        $this->editingSelectedProducts[] = [
            'id' => null,
            'product_id' => $product->id,
            'product_name' => $product->name,
            'unit_override' => $product->unit === 'Kilos' ? 'Per Kilos' : 'Per Piece',
            'quantity' => 1.0,
            'price' => (float) $product->price,
            'base_total' => (float) $product->price,
            'surcharge_total' => $this->editingSurchargePerUnit * 1.0,
            'total' => (float) $product->price + ($this->editingSurchargePerUnit * 1.0),
            'is_paid' => false,
        ];

        $this->editingAvailableProducts = $this->editingAvailableProducts
            ->reject(fn($p) => $p->id == $productId)
            ->values();

        $this->editingNewProductId = null;
        $this->updateEditingTotals();
    }

    public function removeEditingLine($index)
    {
        $line = $this->editingSelectedProducts[$index] ?? null;
        if (!$line) return;

        if ($line['is_paid']) {
            $this->dispatch('showAlert', 'Cannot remove a paid item.');
            return;
        }

        unset($this->editingSelectedProducts[$index]);
        $this->editingSelectedProducts = array_values($this->editingSelectedProducts);
        $this->updateEditingTotals();
    }

    /**
     * Only the quantity of an unpaid line may change.
     * Price is locked for the life of the transaction.
     */
    public function updateEditingLine($index, $field, $value)
    {
        if (!isset($this->editingSelectedProducts[$index])) return;
        if ($this->editingSelectedProducts[$index]['is_paid']) return;

        if (!in_array($field, ['quantity'])) return;

        $this->editingSelectedProducts[$index][$field] = (float) $value;
        $this->recalculateEditingLine($index);
        $this->updateEditingTotals();
    }

    private function recalculateEditingLine($index)
    {
        $line = &$this->editingSelectedProducts[$index];
        $qty = (float) $line['quantity'];
        $price = (float) $line['price'];
        $line['base_total'] = $price * $qty;
        $line['surcharge_total'] = $this->editingSurchargePerUnit * $qty;
        $line['total'] = $line['base_total'] + $line['surcharge_total'];
    }

    private function updateEditingTotals()
    {
        // No action needed – computed properties handle totals.
    }

    public function saveGroupEdits()
    {
        $oldTotals = [];
        $newTotals = [];
        $customer = $this->editingCustomer;

        if (!$customer) {
            $this->dispatch('showAlert', 'No customer found for this transaction.');
            return;
        }

        $existingTransactionNumber = Transaction::where('group_id', $this->editingGroupId)->value('transaction_number');
        if (!$existingTransactionNumber) {
            $existingTransactionNumber = app(TransactionNumberGenerator::class)->generate();
        }

        DB::transaction(function () use (&$oldTotals, &$newTotals, $customer, $existingTransactionNumber) {
            foreach ($this->editingSelectedProducts as $index => &$line) {
                if ($line['is_paid']) continue;

                if ($line['id']) {
                    $transaction = Transaction::find($line['id']);
                    if (!$transaction) continue;

                    $oldTotal = $transaction->total_amount;
                    $transaction->quantity = $line['quantity'];
                    $transaction->base_total = $line['base_total'];
                    $transaction->surcharge_total = $line['surcharge_total'];
                    $transaction->total_amount = $line['total'];
                    $transaction->save();

                    $oldTotals[] = $oldTotal;
                    $newTotals[] = $line['total'];
                } else {
                    Transaction::create([
                        'group_id' => $this->editingGroupId,
                        'customer_id' => $customer->id,
                        'products_id' => $line['product_id'],
                        'quantity' => $line['quantity'],
                        'base_total' => $line['base_total'],
                        'surcharge_total' => $line['surcharge_total'],
                        'total_amount' => $line['total'],
                        'transaction_date' => $this->editingTransactionDate,
                        'due_date' => $this->editingDueDate,
                        'status' => 'PENDING',
                        'surcharge_at_creation' => $this->editingSurchargePerUnit,
                        'interest_rate_at_creation' => $this->editingInterestRate,
                        'product_name' => $line['product_name'],
                        'product_unit' => $line['unit_override'] === 'Per Kilos' ? 'Kilos' : 'Pieces',
                        'transaction_number' => $existingTransactionNumber,
                    ]);

                    $oldTotals[] = 0;
                    $newTotals[] = $line['total'];
                }
            }

            $existingIds = collect($this->editingSelectedProducts)->pluck('id')->filter()->toArray();
            $removedLines = Transaction::where('group_id', $this->editingGroupId)
                ->whereNotIn('id', $existingIds)
                ->where('status', '!=', 'PAID')
                ->get();

            foreach ($removedLines as $removed) {
                $oldTotals[] = $removed->total_amount;
                $newTotals[] = 0;
                $removed->delete();
            }

            $delta = array_sum($newTotals) - array_sum($oldTotals);
            if ($delta != 0) {
                $customer->total_balance += $delta;
                $customer->save();
            }

            $customer->updateRiskLevel();
        });

        $this->showEditGroupModal = false;
        $this->dispatch('showAlert', 'Transaction updated successfully.');
    }

    // --- Delete group ---
    public function deleteGroup()
    {
        if (!$this->groupToDelete) return;

        $user = AuthService::user();
        $credential = $user?->credentials;
        if (!$credential || !Hash::check($this->deletePin, $credential->pin)) {
            $this->deletePinError = 'Invalid PIN. Deletion cancelled.';
            return;
        }

        $transactions = Transaction::where('group_id', $this->groupToDelete)
            ->orWhere('id', $this->groupToDelete)
            ->get();

        if ($transactions->contains('status', 'PAID')) {
            $this->dispatch('showAlert', 'Cannot delete group with paid transactions.');
            $this->cancelDelete();
            return;
        }

        DB::transaction(function () use ($transactions) {
            foreach ($transactions as $transaction) {
                if ($transaction->payment_id) {
                    $payment = Payment::find($transaction->payment_id);
                    if ($payment) {
                        $payment->delete();
                    }
                }
                $customer = $transaction->customer;
                if ($customer && $transaction->status !== 'PAID') {
                    $customer->decrement('total_balance', $transaction->total_amount);
                    $customer->updateRiskLevel();
                }
                $transaction->delete();
            }
        });

        $this->showDeleteModal = false;
        $this->groupToDelete = null;
        $this->deletePin = '';
        $this->deletePinError = '';
        $this->dispatch('showAlert', 'Group deleted successfully.');
    }

    public function confirmDeleteGroup($groupId)
    {
        $this->groupToDelete = $groupId;
        $this->deletePin = '';
        $this->deletePinError = '';
        $this->showDeleteModal = true;
    }

    private function resetPaymentModalState()
    {
        $this->showPaymentModal = false;
        $this->paymentTransactionId = null;
        $this->paymentTransactionData = null;
        $this->cashGiven = 0;
        $this->change = 0;
        $this->paymentMode = null;
        $this->bulkTransactions = [];
        $this->pendingTransactionId = null;
        $this->paymentGroupTransactions = null;
        $this->selectedPaymentTransactionIds = [];
        $this->groupToPay = null;
        $this->paymentMethod = 'cash';
        $this->referenceNumber = '';
        $this->transactionItems = [];
    }

    public function closePaymentModal()
    {
        $this->resetPaymentModalState();
    }

    protected function getGlobalMaxCreditLimit(): float
    {
        $settings = $this->getStoreSettings();
        return (float) ($settings->max_credit_limit ?? 0);
    }

    public function confirmDeleteTransaction($id)
    {
        $transaction = Transaction::find($id);
        if (!$transaction) return;
        if ($transaction->status === 'PAID') {
            $this->dispatch('showAlert', 'Paid transactions cannot be deleted to preserve audit trail.');
            return;
        }
        $this->transactionToDelete = $id;
        $this->showDeleteModal = true;
    }

    public function deleteTransaction()
    {
        if (!$this->transactionToDelete) return;

        $transaction = Transaction::findOrFail($this->transactionToDelete);
        if ($transaction->status === 'PAID') {
            $this->dispatch('showAlert', 'Paid transactions cannot be deleted.');
            $this->cancelDelete();
            return;
        }

        $user = AuthService::user();
        $credential = $user?->credentials;
        if (!$credential || !Hash::check($this->deletePin, $credential->pin)) {
            $this->deletePinError = 'Invalid PIN. Deletion cancelled.';
            return;
        }

        if ($transaction->payment_id) {
            $payment = Payment::find($transaction->payment_id);
            if ($payment) {
                $payment->delete();
            }
        }

        if ($transaction->status !== 'PAID') {
            $customer = $transaction->customer;
            if ($customer) {
                $customer->decrement('total_balance', $transaction->total_amount);
                $customer->updateRiskLevel();
            }
        }

        $transaction->delete();

        $this->deletePin = '';
        $this->deletePinError = '';
        $this->showDeleteModal = false;
        $this->transactionToDelete = null;
        $this->dispatch('showAlert', 'Transaction deleted successfully. History preserved.');
    }

    public function payGroup($groupId)
    {
        $all = Transaction::where('group_id', $groupId)
            ->orWhere('id', $groupId)
            ->orderBy('id')
            ->get();

        $unpaid = $all->where('status', '!=', 'PAID');

        if ($unpaid->isEmpty()) {
            $this->dispatch('showAlert', 'No unpaid transactions in this group.');
            return;
        }

        $this->paymentGroupTransactions = $all;
        $this->selectedPaymentTransactionIds = $unpaid->pluck('id')->toArray();
        $this->groupToPay = $groupId;
        $this->showGroupPaymentModal = true;
    }

    public function togglePaymentSelection($transactionId)
    {
        $tx = Transaction::find($transactionId);
        if ($tx && $tx->status === 'PAID') {
            return;
        }

        if (in_array($transactionId, $this->selectedPaymentTransactionIds)) {
            $this->selectedPaymentTransactionIds = array_values(array_diff(
                $this->selectedPaymentTransactionIds,
                [$transactionId]
            ));
        } else {
            $this->selectedPaymentTransactionIds[] = $transactionId;
        }
    }

    public function selectAllPaymentTransactions()
    {
        $this->selectedPaymentTransactionIds = collect($this->paymentGroupTransactions)
            ->where('status', '!=', 'PAID')
            ->pluck('id')
            ->toArray();
    }

    public function processGroupPayment()
    {
        if (empty($this->selectedPaymentTransactionIds)) {
            $this->dispatch('showAlert', 'Please select at least one transaction to pay.');
            return;
        }

        $selectedTransactions = Transaction::whereIn('id', $this->selectedPaymentTransactionIds)->get();

        $this->transactionItems = $selectedTransactions
            ->map(fn ($tx) => $this->buildTransactionItem($tx))
            ->toArray();

        $this->paymentMode = 'group';
        $this->paymentTransactionId = $selectedTransactions->first()->id;
        $this->paymentTransactionData = $selectedTransactions->first();
        $this->totalToPay = collect($this->transactionItems)->sum('total');
        $this->cashGiven = 0;
        $this->change = 0;
        $this->paymentMethod = 'cash';
        $this->referenceNumber = '';
        $this->showGroupPaymentModal = false;
        $this->showPaymentModal = true;
    }

    public function cancelDelete()
    {
        $this->showDeleteModal = false;
        $this->transactionToDelete = null;
        $this->deletePin = '';
        $this->deletePinError = '';
    }

    // --- Render ---
    public function render()
    {
        $query = Transaction::with(['customer', 'product']);

        if ($this->searchCustomerName) {
            $query->whereHas('customer', fn($q) => $q->where('name', 'like', '%' . $this->searchCustomerName . '%'));
        }
        if ($this->searchItemName) {
            $query->whereHas('product', fn($q) => $q->where('name', 'like', '%' . $this->searchItemName . '%'));
        }
        if ($this->searchDate) {
            $query->whereDate('transaction_date', $this->searchDate);
        }
        if ($this->statusFilter !== 'All') {
            $query->where('status', $this->statusFilter);
        }

        $transactions = $query->latest('transaction_date')->get();

        $groups = $transactions->groupBy(function ($tx) {
            return $tx->group_id ?? $tx->id;
        })->map(function ($group) {
            $first = $group->first();
            $totalAmount     = $group->sum('total_amount');
            $remainingAmount = $group->where('status', '!=', 'PAID')->sum('total_amount');
            $statuses        = $group->pluck('status')->unique();

            if ($statuses->count() == 1 && $statuses->first() == 'PAID') {
                $status = 'PAID';
            } elseif ($statuses->contains('OVERDUE')) {
                $status = 'OVERDUE';
            } elseif ($remainingAmount <= 0) {
                $status = 'PAID';
            } else {
                $status = 'PENDING';
            }

            $productNames = $group->take(3)->map(fn($tx) => $tx->display_product_name)->implode(', ');
            if ($group->count() > 3) $productNames .= ', ...';

            return (object) [
                'group_id'          => $first->group_id ?? $first->id,
                'transaction_number' => $first->transaction_number,
                'customer'          => $first->customer,
                'transaction_date'  => $first->transaction_date,
                'due_date'          => $first->due_date,
                'total_amount'      => $totalAmount,
                'remaining_amount'  => $remainingAmount,
                'status'            => $status,
                'product_names'     => $productNames,
                'transactions'      => $group,
            ];
        })->sortByDesc('transaction_date')->values();

        $currentPage = \Illuminate\Pagination\Paginator::resolveCurrentPage();
        $perPage = 5;
        $currentItems = $groups->slice(($currentPage - 1) * $perPage, $perPage)->values();
        $paginatedGroups = new \Illuminate\Pagination\LengthAwarePaginator(
            $currentItems,
            $groups->count(),
            $perPage,
            $currentPage,
            ['path' => \Illuminate\Pagination\Paginator::resolveCurrentPath()]
        );

        return view('livewire.transactions', ['groups' => $paginatedGroups]);
    }
}