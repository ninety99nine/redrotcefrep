<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\PaymentMethodController;

Route::prefix('payment-methods')
    ->middleware(['auth:sanctum'])
    ->controller(PaymentMethodController::class)
    ->group(function () {
        Route::get('/', 'showPaymentMethods')->name('show.payment.methods');
        Route::post('/', 'createPaymentMethod')->name('create.payment.method');
        Route::delete('/', 'deletePaymentMethods')->name('delete.payment.methods');

        // Explicit route model binding applied: AppServiceProvider.php
        Route::prefix('{paymentMethod}')->group(function () {
            Route::get('/', 'showPaymentMethod')->name('show.payment.method');
            Route::put('/', 'updatePaymentMethod')->name('update.payment.method');
            Route::delete('/', 'deletePaymentMethod')->name('delete.payment.method');
        });
    });
