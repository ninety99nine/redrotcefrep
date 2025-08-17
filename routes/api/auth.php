<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\AuthController;

Route::prefix('auth')->group(function () {

    Route::post('/login', [AuthController::class, 'login'])->name('auth.login');
    Route::post('/register', [AuthController::class, 'register'])->name('auth.register');
    Route::post('/setup-password', [AuthController::class, 'setupPassword'])->name('auth.setup.password');
    Route::post('/forgot-password', [AuthController::class, 'forgotPassword'])->name('auth.forgot.password');
    Route::post('/validate-token', [AuthController::class, 'validateToken'])->name('auth.validate.token');
    Route::post('/reset-password', [AuthController::class, 'resetPassword'])->name('auth.reset.password');
    Route::get('/terms-and-conditions', [AuthController::class, 'showTermsAndConditions'])->name('show.terms.and.conditions');
    Route::get('/social-login-links', [AuthController::class, 'showSocialLoginLinks'])->name('show.social.login.links');

    Route::middleware('auth:sanctum')->group(function () {
        Route::post('/logout', [AuthController::class, 'logout'])->name('auth.logout');
        Route::get('/user', [AuthController::class, 'showAuthUser'])->name('auth.show.user');
        Route::put('/user', [AuthController::class, 'updateAuthUser'])->name('auth.update.user');
    });
});
