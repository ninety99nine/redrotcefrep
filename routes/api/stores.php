<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\StoreController;

Route::middleware(['auth:sanctum'])
    ->prefix('stores')
    ->controller(StoreController::class)
    ->group(function () {
        Route::get('/', 'showStores')->name('show.stores');
        Route::post('/', 'createStore')->name('create.store');
        Route::delete('/', 'deleteStores')->name('delete.stores');

        //  Allow Guest shopping on showStoreByAlias
        Route::get('/alias/{alias}', 'showStoreByAlias')->withoutMiddleware(['auth:sanctum', 'store.permission'])->name('show.store.by.alias');

        // Explicit route model binding applied: AppServiceProvider.php
        Route::middleware(['store.permission', 'record.store.visit'])->prefix('{store}')->group(function () {
            //  Allow Guest shopping on showStore
            Route::get('/', 'showStore')->withoutMiddleware(['auth:sanctum', 'store.permission'])->name('show.store');
            Route::put('/', 'updateStore')->name('update.store');
            Route::delete('/', 'deleteStore')->name('delete.store');
            Route::post('/follow', 'followStore')->name('follow.store');
            Route::post('/unfollow', 'unfollowStore')->name('unfollow.store');
            Route::get('/insights', 'showStoreInsights')->name('show.store.insights');

            Route::withoutMiddleware(['auth:sanctum', 'store.permission', 'record.store.visit'])->group(function () {
                Route::get('/qr-code', 'showStoreQrCode')->name('show.store.qr.code');
                Route::get('/qr-code-preview', 'showStoreQrCodePreview')->name('show.store.qr.code.preview');
            });
        });
    });
