<?php

use Illuminate\Support\Facades\Route;
use App\Http\Middleware\StorePermission;
use App\Http\Controllers\TagController;

Route::prefix('tags')
    ->middleware(['auth:sanctum', 'store.permission'])
    ->controller(TagController::class)
    ->group(function () {
        Route::get('/', 'showTags')->name('show.tags');
        Route::post('/', 'createTag')->name('create.tag');
        Route::delete('/', 'deleteTags')->name('delete.tags');

        //  Explicit route model binding applied: AppServiceProvider.php
        Route::prefix('{tag}')->group(function () {
            Route::get('/', 'showTag')->name('show.tag');
            Route::put('/', 'updateTag')->name('update.tag');
            Route::delete('/', 'deleteTag')->name('delete.tag');
        });
    });
