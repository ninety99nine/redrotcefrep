<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\AnalyticController;

Route::prefix('analytics')
    ->middleware(['auth:sanctum', 'store.permission'])
    ->controller(AnalyticController::class)
    ->group(function () {
        Route::get('/', 'showAnalytics')->name('show.analytics');
    });
