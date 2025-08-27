<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\MediaFileController;

Route::prefix('media-files')
    ->middleware(['auth:sanctum', 'store.permission'])
    ->controller(MediaFileController::class)
    ->group(function () {
        Route::get('/', 'showMediaFiles')->name('show.media.files');
        Route::post('/', 'createMediaFile')->name('create.media.file');
        Route::delete('/', 'deleteMediaFiles')->name('delete.media.files');

        // Explicit route model binding applied: AppServiceProvider.php
        Route::prefix('{mediaFile}')->group(function () {
            Route::get('/', 'showMediaFile')->name('show.media.file');
            Route::put('/', 'updateMediaFile')->name('update.media.file');
            Route::delete('/', 'deleteMediaFile')->name('delete.media.file');
            Route::get('/download', 'downloadMediaFile')->name('download.media.file');
        });
    });
