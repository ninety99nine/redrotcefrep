<?php

use Illuminate\Support\Facades\Route;
use App\Http\Middleware\StorePermission;
use App\Http\Controllers\OrderController;

Route::prefix('orders')
    ->middleware(['auth:sanctum', StorePermission::class])
    ->controller(OrderController::class)
    ->group(function () {
        Route::get('/', 'showOrders')->name('show.orders');
        Route::post('/', 'createOrder')->name('create.order');
        Route::put('/', 'updateOrders')->name('update.orders');
        Route::delete('/', 'deleteOrders')->name('delete.orders');
        Route::post('/download', 'downloadOrders')->name('download.orders');

        // Explicit route model binding applied: AppServiceProvider.php
        Route::prefix('{order}')->group(function () {
            Route::get('/', 'showOrder')->name('show.order');
            Route::put('/', 'updateOrder')->name('update.order');
            Route::delete('/', 'deleteOrder')->name('delete.order');
        });
    });
