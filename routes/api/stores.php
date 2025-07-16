<?php

use Illuminate\Support\Facades\Route;
use App\Http\Middleware\StorePermission;
use App\Http\Controllers\StoreController;

Route::middleware(['auth:sanctum'])
    ->prefix('stores')
    ->controller(StoreController::class)
    ->group(function () {
        Route::get('/', 'showStores')->name('show.stores');
        Route::post('/', 'createStore')->name('create.store');
        Route::delete('/', 'deleteStores')->name('delete.stores');
        Route::get('/{alias}', 'showStoreByAlias')->name('show.store.by.alias');

        Route::middleware([StorePermission::class])->prefix('{store}')->group(function () {
            Route::get('/', 'showStore')->name('show.store');
            Route::put('/', 'updateStore')->name('update.store');
            Route::delete('/', 'deleteStore')->name('delete.store');
            Route::get('/insights', 'showStoreInsights')->name('show.store.insights');
            Route::get('/qr-code', 'showStoreQrCode')->withoutMiddleware(['auth:sanctum', StorePermission::class])->name('show.store.qr.code');
            Route::get('/qr-code-preview', 'showStoreQrCodePreview')->withoutMiddleware(['auth:sanctum', StorePermission::class])->name('show.store.qr.code.preview');
        });
    });
