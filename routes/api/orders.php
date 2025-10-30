<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\OrderController;

Route::prefix('orders')
    ->middleware(['auth:sanctum', 'store.permission'])
    ->controller(OrderController::class)
    ->group(function () {
        Route::get('/', 'showOrders')->name('show.orders');
        //  Allow Guest shopping on createOrder
        Route::post('/', 'createOrder')->withoutMiddleware(['auth:sanctum', 'store.permission'])->name('create.order');
        Route::put('/', 'updateOrders')->name('update.orders');
        Route::delete('/', 'deleteOrders')->name('delete.orders');
        Route::post('/download', 'downloadOrders')->name('download.orders');
        Route::get('/status-counts', 'showOrderStatusCounts')->name('show.order.status.counts');
        //  Allow Guest shopping on verifyOrderPayment
        Route::post('/verify-payment/{transaction}', 'verifyOrderPayment')->withoutMiddleware(['auth:sanctum', 'store.permission'])->name('verify.order.payment');

        // Explicit route model binding applied: AppServiceProvider.php
        Route::prefix('{order}')->group(function () {
            //  Allow Guest shopping on showOrder and updateOrder
            Route::get('/', 'showOrder')->withoutMiddleware(['auth:sanctum', 'store.permission'])->name('show.order');
            Route::put('/', 'updateOrder')->withoutMiddleware(['auth:sanctum', 'store.permission'])->name('update.order');
            Route::delete('/', 'deleteOrder')->name('delete.order');

            //  Allow Guest shopping on payOrder
            Route::post('/pay', 'payOrder')->withoutMiddleware(['auth:sanctum', 'store.permission'])->name('pay.order');
        });
    });
