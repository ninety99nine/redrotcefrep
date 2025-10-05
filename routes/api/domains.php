<?php

use App\Http\Controllers\DomainController;
use Illuminate\Support\Facades\Route;

Route::prefix('domains')
    ->middleware(['auth:sanctum', 'store.permission'])
    ->controller(DomainController::class)
    ->group(function () {
        Route::get('/', 'showDomains')->name('show.domains');
        Route::post('/', 'createDomain')->name('create.domain');
        Route::delete('/', 'deleteDomains')->name('delete.domains');
        Route::get('/server-ip', 'showServerIp')->name('show.server.ip');
        Route::post('/search', 'searchDomains')->name('search.domains');
        Route::post('/purchase', 'purchaseDomain')->name('purchase.domain');
        Route::get('/pricing', 'showDomainPricing')->name('show.domain.pricing');
        Route::post('/verify-connection', 'verifyDomainConnection')->name('verify.domain.connection');

        // Explicit route model binding applied: AppServiceProvider.php
        Route::prefix('{domain}')->group(function () {
            Route::get('/', 'showDomain')->name('show.domain');
            Route::put('/', 'updateDomain')->name('update.domain');
            Route::delete('/', 'deleteDomain')->name('delete.domain');
        });
    });
