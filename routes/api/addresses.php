<?php

use Illuminate\Support\Facades\Route;
use App\Http\Middleware\StorePermission;
use App\Http\Controllers\AddressController;

Route::prefix('addresses')
    ->middleware(['auth:sanctum', StorePermission::class.':optional'])
    ->controller(AddressController::class)
    ->group(function () {
        Route::get('/', 'showAddresses')->name('show.addresses');
        Route::post('/', 'createAddress')->name('create.address');
        Route::delete('/', 'deleteAddresses')->name('delete.addresses');
        Route::post('/validate', 'validateAddress')->name('validate.address');
        Route::get('/country/options', 'showCountryAddressOptions')->name('show.country.address.options');

        // Explicit route model binding applied: AppServiceProvider.php
        Route::prefix('{address}')->group(function () {
            Route::get('/', 'showAddress')->name('show.address');
            Route::put('/', 'updateAddress')->name('update.address');
            Route::delete('/', 'deleteAddress')->name('delete.address');
        });
    });
