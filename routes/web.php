<?php

use Illuminate\Support\Facades\Route;
use App\Http\Livewire\Customers;
use App\Http\Livewire\Dashboard;
use App\Http\Livewire\ProductLists;
use App\Http\Livewire\Transactions;
use App\Http\Livewire\TransactionsHistory;
use App\Http\Livewire\Login;
use App\Http\Livewire\StoreSetup;
use App\Http\Livewire\StoreAccount;
use App\Http\Livewire\ForgotPassword;
use App\Http\Controllers\ReportsExportController;
use App\Http\Controllers\VerificationController;
use App\Http\Controllers\TermsController;
use App\Models\StoreProfile;

// Guest routes
Route::get('/login', Login::class)->name('login');
Route::get('/forgot-password', ForgotPassword::class)->name('password.forgot');
Route::get('/store-setup', StoreSetup::class)->name('store-setup');

// Redirect root to login or dashboard
Route::get('/', function () {
    $profile = StoreProfile::first();
    if (!$profile) {
        return redirect()->route('store-setup');
    }

    $credential = $profile->credentials;
    if (!$credential || empty($credential->email)) {
        return redirect()->route('store-setup');
    }

    return redirect()->route('login');
});

// Verification routes – now accessible to guests
Route::get('/verify-email', [VerificationController::class, 'verify'])->name('verify-email');
Route::post('/verify/resend', [VerificationController::class, 'resend'])->name('verify.resend');
Route::get('/verify-notice', [VerificationController::class, 'notice'])->name('verify.notice');

// Authenticated routes (require login, verified email, and valid email)
Route::middleware(['auth.store', 'verified.store', 'valid.email'])->group(function () {
    Route::get('/dashboard', Dashboard::class)->name('dashboard');
    Route::get('/reports/export', [ReportsExportController::class, 'export'])->name('reports.export');
    Route::get('/terms-and-conditions/pdf', [TermsController::class, 'download'])->name('terms.pdf');
    Route::get('/customers', Customers::class)->name('customers');
    Route::get('/product-list', ProductLists::class)->name('product-list');
    Route::get('/transactions', Transactions::class)->name('transactions');
    Route::get('/transactions-history', TransactionsHistory::class)->name('transactions-history');
    Route::get('/store-account', StoreAccount::class)->name('store-account');
    Route::get('/reports', \App\Http\Livewire\Reports::class)->name('reports');
    Route::post('/logout', function () {
        \App\Services\AuthService::logout();
        return redirect()->route('login');
    })->name('logout');
});
