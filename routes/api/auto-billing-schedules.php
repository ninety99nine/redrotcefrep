<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\AutoBillingScheduleController;

Route::prefix('auto-billing-schedules')
    ->middleware(['auth:sanctum'])
    ->controller(AutoBillingScheduleController::class)
    ->group(function () {
        Route::get('/', 'showAutoBillingSchedules')->name('show.auto.billing.schedules');
        Route::post('/', 'createAutoBillingSchedule')->name('create.auto.billing.schedule');
        Route::delete('/', 'deleteAutoBillingSchedules')->name('delete.auto.billing.schedules');

        // Explicit route model binding applied: AppServiceProvider.php
        Route::prefix('{autoBillingSchedule}')->group(function () {
            Route::get('/', 'showAutoBillingSchedule')->name('show.auto.billing.schedule');
            Route::put('/', 'updateAutoBillingSchedule')->name('update.auto.billing.schedule');
            Route::delete('/', 'deleteAutoBillingSchedule')->name('delete.auto.billing.schedule');
        });
    });
