<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\ReviewController;

Route::prefix('reviews')
    ->middleware(['auth:sanctum', 'store.permission'])
    ->controller(ReviewController::class)
    ->group(function () {
        Route::get('/', 'showReviews')->name('show.reviews');
        Route::post('/', 'createReview')->name('create.review');
        Route::put('/', 'updateReviews')->name('update.reviews');
        Route::delete('/', 'deleteReviews')->name('delete.reviews');

        // Explicit route model binding applied: AppServiceProvider.php
        Route::prefix('{review}')->group(function () {
            Route::get('/', 'showReview')->name('show.review');
            Route::put('/', 'updateReview')->name('update.review');
            Route::delete('/', 'deleteReview')->name('delete.review');
        });
    });
