<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\AuthController;

Route::prefix('auth')->group(function () {
    Route::get('/google', [AuthController::class, 'redirectToGoogle'])->name('social.auth.google');
    Route::get('/google/callback', [AuthController::class, 'handleGoogleCallback'])->name('social.auth.google.callback');
    Route::get('/facebook', [AuthController::class, 'redirectToFacebook'])->name('social.auth.facebook');
    Route::get('/facebook/callback', [AuthController::class, 'handleFacebookCallback'])->name('social.auth.facebook.callback');
    Route::get('/linkedin', [AuthController::class, 'redirectToLinkedIn'])->name('social.auth.linkedin');
    Route::get('/linkedin/callback', [AuthController::class, 'handleLinkedInCallback'])->name('social.auth.linkedin.callback');
});

Route::get('/{any}', function () {
    return view('welcome');
})->where('any', '.*');
