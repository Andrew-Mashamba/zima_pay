<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Public\PaymentController;

Route::get('/', function () {
    return view('welcome');
});

Route::middleware([
    'auth:sanctum',
    config('jetstream.auth_session'),
    'verified',
])->group(function () {
    Route::get('/dashboard', function () {
        return view('dashboard');
    })->name('dashboard');
    
    Route::get('/system', \App\Livewire\System::class)->name('system');
});

// Public Payment Routes with Security Middleware
Route::prefix('pay')->group(function () {
    Route::get('/{shortCode}', \App\Livewire\Public\PaymentPage::class)->name('public.payment.page');
    Route::get('/{shortCode}/success', [PaymentController::class, 'showSuccessPage'])->name('public.payment.success');
    Route::get('/{shortCode}/failure', [PaymentController::class, 'showFailurePage'])->name('public.payment.failure');
    Route::get('/{shortCode}/cancelled', [PaymentController::class, 'showCancelledPage'])->name('public.payment.cancelled');
    Route::get('/{shortCode}/details', [PaymentController::class, 'getPaymentLinkDetails'])->name('public.payment.details');
    Route::post('/receipt/{transactionId}', [PaymentController::class, 'downloadReceipt'])->name('public.payment.receipt');
});


