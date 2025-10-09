<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\StorePaymentMethodController;

Route::prefix('store-payment-methods')
    ->middleware(['auth:sanctum', 'store.permission'])
    ->controller(StorePaymentMethodController::class)
    ->group(function () {
        //  Allow Guest shopping on showStorePaymentMethods
        Route::get('/', 'showStorePaymentMethods')->withoutMiddleware(['auth:sanctum', 'store.permission'])->name('show.store.payment.methods');
        Route::post('/', 'createStorePaymentMethod')->name('create.store.payment.method');
        Route::delete('/', 'deleteStorePaymentMethods')->name('delete.store.payment.methods');
        Route::post('/arrangement', 'updateStorePaymentMethodArrangement')->name('update.store.payment.method.arrangement');

        // Explicit route model binding applied: AppServiceProvider.php
        Route::prefix('{storePaymentMethod}')->group(function () {
            //  Allow Guest shopping on showStorePaymentMethod
            Route::get('/', 'showStorePaymentMethod')->withoutMiddleware(['auth:sanctum', 'store.permission'])->name('show.store.payment.method');
            Route::put('/', 'updateStorePaymentMethod')->name('update.store.payment.method');
            Route::delete('/', 'deleteStorePaymentMethod')->name('delete.store.payment.method');
    });
});
