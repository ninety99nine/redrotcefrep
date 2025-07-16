<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\CourierController;

Route::prefix('couriers')
    ->middleware(['auth:sanctum'])
    ->controller(CourierController::class)
    ->group(function () {
        Route::get('/', 'showCouriers')->name('show.couriers');
        Route::post('/', 'createCourier')->name('create.courier');
        Route::delete('/', 'deleteCouriers')->name('delete.couriers');

        // Explicit route model binding applied: AppServiceProvider.php
        Route::prefix('{courier}')->group(function () {
            Route::get('/', 'showCourier')->name('show.courier');
            Route::put('/', 'updateCourier')->name('update.courier');
            Route::delete('/', 'deleteCourier')->name('delete.courier');
        });
    });
