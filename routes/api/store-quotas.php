<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\StoreQuotaController;

Route::prefix('store-quotas')
    ->middleware(['auth:sanctum', 'store.permission'])
    ->controller(StoreQuotaController::class)
    ->group(function () {
        Route::get('/', 'showStoreQuotas')->name('show.store.quotas');
        Route::post('/', 'createStoreQuota')->name('create.store.quota');
        Route::delete('/', 'deleteStoreQuotas')->name('delete.store.quotas');

        // Explicit route model binding applied: AppServiceProvider.php
        Route::prefix('{storeQuota}')->group(function () {
            Route::get('/', 'showStoreQuota')->name('show.store.quota');
            Route::put('/', 'updateStoreQuota')->name('update.store.quota');
            Route::delete('/', 'deleteStoreQuota')->name('delete.store.quota');
        });
    });
