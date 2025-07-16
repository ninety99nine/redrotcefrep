<?php

use Illuminate\Support\Facades\Route;
use App\Http\Middleware\StorePermission;
use App\Http\Controllers\TransactionController;

Route::prefix('transactions')
    ->middleware(['auth:sanctum', StorePermission::class])
    ->controller(TransactionController::class)
    ->group(function () {
        Route::get('/', 'showTransactions')->name('show.transactions');
        Route::post('/', 'createTransaction')->name('create.transaction');
        Route::delete('/', 'deleteTransactions')->name('delete.transactions');

        // Explicit route model binding applied: AppServiceProvider.php
        Route::prefix('{transaction}')->group(function () {
            Route::get('/', 'showTransaction')->name('show.transaction');
            Route::put('/', 'updateTransaction')->name('update.transaction');
            Route::delete('/', 'deleteTransaction')->name('delete.transaction');
            Route::post('/renew', 'renewTransactionPaymentLink')->name('renew.transaction.payment.link');


        });
    });
