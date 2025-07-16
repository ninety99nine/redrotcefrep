<?php

use Illuminate\Support\Facades\Route;
use App\Http\Middleware\StorePermission;
use App\Http\Controllers\PromotionController;

Route::prefix('promotions')
    ->middleware(['auth:sanctum', StorePermission::class])
    ->controller(PromotionController::class)
    ->group(function () {
        Route::get('/', 'showPromotions')->name('show.promotions');
        Route::post('/', 'createPromotion')->name('create.promotion');
        Route::delete('/', 'deletePromotions')->name('delete.promotions');

        //  Explicit route model binding applied: AppServiceProvider.php
        Route::prefix('{promotion}')->group(function () {
            Route::get('/', 'showPromotion')->name('show.promotion');
            Route::put('/', 'updatePromotion')->name('update.promotion');
            Route::delete('/', 'deletePromotion')->name('delete.promotion');
        });
    });
