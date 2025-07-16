<?php

use Illuminate\Support\Facades\Route;
use App\Http\Middleware\StorePermission;
use App\Http\Controllers\AddressController;

Route::prefix('addresses')
    ->middleware(['auth:sanctum', StorePermission::class])
    ->controller(AddressController::class)
    ->group(function () {
        Route::get('/', 'showAddresses')->name('show.addresses');
        Route::post('/', 'createAddress')->name('create.address');
        Route::delete('/', 'deleteAddresses')->name('delete.addresses');
        Route::get('/country/options', 'showCountryAddressOptions')->withoutMiddleware([StorePermission::class])->name('delete.addresses');

        // Explicit route model binding applied: AppServiceProvider.php
        Route::prefix('{address}')->group(function () {
            Route::get('/', 'showAddress')->name('show.address');
            Route::put('/', 'updateAddress')->name('update.address');
            Route::delete('/', 'deleteAddress')->name('delete.address');
        });
    });
