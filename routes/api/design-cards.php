<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\DesignCardController;

Route::prefix('design-cards')
    ->middleware(['auth:sanctum', 'store.permission'])
    ->controller(DesignCardController::class)
    ->group(function () {
        //  Allow Guest shopping on showDesignCards
        Route::get('/', 'showDesignCards')->withoutMiddleware(['auth:sanctum', 'store.permission'])->name('show.design.cards');
        Route::post('/', 'createDesignCard')->name('create.design.card');
        Route::delete('/', 'deleteDesignCards')->name('delete.design.cards');
        Route::post('/arrangement', 'updateDesignCardArrangement')->name('update.design.card.arrangement');
        Route::get('/configurations', 'showDesignCardConfigurations')->name('show.design.card.configurations');

        Route::prefix('{designCard}')->group(function () {
            //  Allow Guest shopping on showDesignCard
            Route::get('/', 'showDesignCard')->withoutMiddleware(['auth:sanctum', 'store.permission'])->name('show.design.card');
            Route::put('/', 'updateDesignCard')->name('update.design.card');
            Route::delete('/', 'deleteDesignCard')->name('delete.design.card');
        });
    });
?>
