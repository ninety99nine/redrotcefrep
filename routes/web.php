<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\AppController;
use App\Http\Controllers\PWAController;
use App\Http\Controllers\AuthController;

Route::prefix('auth')->group(function () {
    Route::get('/google', [AuthController::class, 'redirectToGoogle'])->name('social.auth.google');
    Route::get('/google/callback', [AuthController::class, 'handleGoogleCallback'])->name('social.auth.google.callback');
    Route::get('/facebook', [AuthController::class, 'redirectToFacebook'])->name('social.auth.facebook');
    Route::get('/facebook/callback', [AuthController::class, 'handleFacebookCallback'])->name('social.auth.facebook.callback');
    Route::get('/linkedin', [AuthController::class, 'redirectToLinkedIn'])->name('social.auth.linkedin');
    Route::get('/linkedin/callback', [AuthController::class, 'handleLinkedInCallback'])->name('social.auth.linkedin.callback');
});

// CUSTOM DOMAIN ROUTES (ONLY FOR NON-LOCALHOST)
$host = Request::getHost();

if (!in_array($host, ['localhost', '127.0.0.1', 'host.docker.internal'])) {
    Route::domain('{domain}')->middleware(['resolve.store.by.domain', 'record.store.visit'])->group(function () {
        Route::get('/manifest.json', [PWAController::class, 'manifest'])->name('domain.pwa.manifest');
        Route::get('/{any?}', [AppController::class, 'render'])->where('any', '.*')->name('domain.shop.render');
    });
}

// Routes for alias-based storefronts
Route::prefix('{alias}')->middleware('record.store.visit')->group(function () {
    Route::get('/manifest.json', [PWAController::class, 'manifest'])->name('alias.pwa.manifest');
    Route::get('/{any?}', [AppController::class, 'render'])->where('any', '.*')->name('alias.shop.render');
});

// Fallback for non-domain, non-alias requests
Route::get('/{any}', [AppController::class, 'render'])->where('any', '.*')->name('app.render');
