<?php

use Illuminate\Support\Facades\Route;
use App\Http\Middleware\StorePermission;
use App\Http\Controllers\DeliveryMethodController;

Route::prefix('delivery-methods')
    ->middleware(['auth:sanctum', 'store.permission'])
    ->controller(DeliveryMethodController::class)
    ->group(function () {
        Route::get('/', 'showDeliveryMethods')->name('show.delivery.methods');
        Route::post('/', 'createDeliveryMethod')->name('create.delivery.method');
        Route::delete('/', 'deleteDeliveryMethods')->name('delete.delivery.methods');
        Route::post('/arrangement', 'updateDeliveryMethodArrangement')->name('update.delivery.method.arrangement');
        Route::post('/schedule-options', 'showDeliveryMethodScheduleOptions')->withoutMiddleware('auth:sanctum')->name('show.delivery.method.schedule.options');

        // Explicit route model binding applied: AppServiceProvider.php
        Route::prefix('{deliveryMethod}')->group(function () {
            Route::get('/', 'showDeliveryMethod')->name('show.delivery.method');
            Route::put('/', 'updateDeliveryMethod')->name('update.delivery.method');
            Route::delete('/', 'deleteDeliveryMethod')->name('delete.delivery.method');
        });
});
