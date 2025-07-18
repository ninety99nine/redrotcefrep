<?php

use Illuminate\Support\Facades\Route;
use App\Http\Middleware\StorePermission;
use App\Http\Controllers\CustomerController;

Route::prefix('customers')
    ->middleware(['auth:sanctum', StorePermission::class])
    ->controller(CustomerController::class)
    ->group(function () {
        Route::get('/', 'showCustomers')->name('show.customers');
        Route::post('/', 'createCustomer')->name('create.customer');
        Route::delete('/', 'deleteCustomers')->name('delete.customers');

        // Explicit route model binding applied: AppServiceProvider.php
        Route::prefix('{customer}')->group(function () {
            Route::get('/', 'showCustomer')->name('show.customer');
            Route::put('/', 'updateCustomer')->name('update.customer');
            Route::delete('/', 'deleteCustomer')->name('delete.customer');
        });
    });
