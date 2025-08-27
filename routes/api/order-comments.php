<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\OrderCommentController;

Route::prefix('order-comments')
    ->middleware(['auth:sanctum', 'store.permission'])
    ->controller(OrderCommentController::class)
    ->group(function () {
        Route::get('/', 'showOrderComments')->name('show.order.comments');
        Route::post('/', 'createOrderComment')->name('create.order.comment');
        Route::delete('/', 'deleteOrderComments')->name('delete.order.comments');

        // Explicit route model binding applied: AppServiceProvider.php
        Route::prefix('{orderComment}')->group(function () {
            Route::get('/', 'showOrderComment')->name('show.order.comment');
            Route::put('/', 'updateOrderComment')->name('update.order.comment');
            Route::delete('/', 'deleteOrderComment')->name('delete.order.comment');
        });
    });
