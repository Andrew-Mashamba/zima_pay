<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Api\EsbController;
use App\Http\Controllers\Api\UniversalPaymentLinkController;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Here is where you can register API routes for your application. These
| routes are loaded by the RouteServiceProvider and all of them will
| be assigned to the "api" middleware group. Make something great!
|
*/

Route::middleware('auth:sanctum')->get('/user', function (Request $request) {
    return $request->user();
});

// ESB API Routes
Route::prefix('esb')->middleware(['api'])->group(function () {
    Route::get('/health', [EsbController::class, 'health']);
    Route::get('/services', [EsbController::class, 'getServices']);
    Route::post('/{serviceCode}', [EsbController::class, 'handleRequest']);
});

// Callback Routes
Route::prefix('callback')->group(function () {
    Route::post('/{aggregatorCode}', [App\Http\Controllers\Api\CallbackController::class, 'handleCallback']);
    Route::get('/status/{transactionId}', [App\Http\Controllers\Api\CallbackController::class, 'checkStatus'])->name('api.callback.status');
});

// Universal Payment Link API Routes
Route::prefix('payment-links')->group(function () {
    Route::post('/generate-universal', [UniversalPaymentLinkController::class, 'generateUniversal']);
    Route::get('/universal/{shortCode}', [UniversalPaymentLinkController::class, 'getUniversalPaymentLink']);
    Route::get('/universal/{shortCode}/stats', [UniversalPaymentLinkController::class, 'getUniversalPaymentLinkStats']);
});
