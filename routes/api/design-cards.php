<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\DesignCardController;

Route::prefix('design-cards')
    ->middleware(['auth:sanctum', 'store.permission'])
    ->controller(DesignCardController::class)
    ->group(function () {
        Route::get('/', 'showDesignCards')->name('show.design.cards');
        Route::post('/', 'createDesignCard')->name('create.design.card');
        Route::delete('/', 'deleteDesignCards')->name('delete.design.cards');
        Route::post('/arrangement', 'updateDesignCardArrangement')->name('update.design.card.arrangement');

        // Explicit route model binding applied: AppServiceProvider.php
        Route::prefix('{designCard}')->group(function () {
            Route::get('/', 'showDesignCard')->name('show.design.card');
            Route::put('/', 'updateDesignCard')->name('update.design.card');
            Route::delete('/', 'deleteDesignCard')->name('delete.design.card');
        });
});
