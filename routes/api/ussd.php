<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\UssdController;

Route::prefix('ussd')
    ->middleware(['auth:sanctum'])
    ->controller(UssdController::class)
    ->group(function () {
        Route::get('/profile-summary', 'showProfileSummary')->name('show.profile.summary');
    });
