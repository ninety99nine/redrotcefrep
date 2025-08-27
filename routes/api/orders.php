<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\OrderController;

Route::prefix('orders')
    ->middleware(['auth:sanctum', 'store.permission'])
    ->controller(OrderController::class)
    ->group(function () {
        Route::get('/', 'showOrders')->name('show.orders');
        Route::post('/', 'createOrder')->name('create.order');
        Route::put('/', 'updateOrders')->name('update.orders');
        Route::delete('/', 'deleteOrders')->name('delete.orders');
        Route::post('/download', 'downloadOrders')->name('download.orders');
        Route::get('/status-counts', 'showOrderStatusCounts')->name('show.order.status.counts');

        // Explicit route model binding applied: AppServiceProvider.php
        Route::prefix('{order}')->group(function () {
            Route::get('/', 'showOrder')->name('show.order');
            Route::put('/', 'updateOrder')->name('update.order');
            Route::delete('/', 'deleteOrder')->name('delete.order');
        });
    });
