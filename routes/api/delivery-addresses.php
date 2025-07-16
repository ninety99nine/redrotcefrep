<?php

use Illuminate\Support\Facades\Route;
use App\Http\Middleware\StorePermission;
use App\Http\Controllers\DeliveryAddressController;

Route::prefix('delivery-addresses')
    ->middleware(['auth:sanctum', StorePermission::class])
    ->controller(DeliveryAddressController::class)
    ->group(function () {
        Route::get('/', 'showDeliveryAddresses')->name('show.delivery.addresses');
        Route::post('/', 'createDeliveryAddress')->name('create.delivery.address');
        Route::delete('/', 'deleteDeliveryAddresses')->name('delete.delivery.addresses');

        // Explicit route model binding applied: AppServiceProvider.php
        Route::prefix('{deliveryAddress}')->group(function () {
            Route::get('/', 'showDeliveryAddress')->name('show.delivery.address');
            Route::put('/', 'updateDeliveryAddress')->name('update.delivery.address');
            Route::delete('/', 'deleteDeliveryAddress')->name('delete.delivery.address');
        });
    });
