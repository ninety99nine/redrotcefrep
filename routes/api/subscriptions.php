<?php

use Illuminate\Support\Facades\Route;
use App\Http\Middleware\StorePermission;
use App\Http\Controllers\SubscriptionController;

Route::middleware(['auth:sanctum'])
    ->prefix('subscriptions')
    ->controller(SubscriptionController::class)
    ->group(function () {
        Route::get('/', 'showSubscriptions')->name('show.subscriptions');
        Route::post('/', 'createSubscription')->name('create.subscription');
        Route::delete('/', 'deleteSubscriptions')->name('delete.subscriptions');

        Route::middleware([StorePermission::class])->prefix('{subscription}')->group(function () {
            Route::get('/', 'showSubscription')->name('show.subscription');
            Route::put('/', 'updateSubscription')->name('update.subscription');
            Route::delete('/', 'deleteSubscription')->name('delete.subscription');
        });
    });
