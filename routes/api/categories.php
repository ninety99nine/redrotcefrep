<?php

use Illuminate\Support\Facades\Route;
use App\Http\Middleware\StorePermission;
use App\Http\Controllers\CategoryController;

Route::prefix('categories')
    ->middleware(['auth:sanctum', 'store.permission'])
    ->controller(CategoryController::class)
    ->group(function () {
        Route::get('/', 'showCategories')->name('show.categories');
        Route::post('/', 'createCategory')->name('create.category');
        Route::put('/', 'updateCategories')->name('update.categories');
        Route::delete('/', 'deleteCategories')->name('delete.categories');
        Route::post('/visibility', 'updateCategoryVisibility')->name('update.category.visibility');
        Route::post('/arrangement', 'updateCategoryArrangement')->name('update.category.arrangement');

        //  Explicit route model binding applied: AppServiceProvider.php
        Route::prefix('{category}')->group(function () {
            Route::get('/', 'showCategory')->name('show.category');
            Route::put('/', 'updateCategory')->name('update.category');
            Route::delete('/', 'deleteCategory')->name('delete.category');
        });
    });
