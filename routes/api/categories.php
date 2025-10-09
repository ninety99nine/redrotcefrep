<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\CategoryController;

Route::prefix('categories')
    ->middleware(['auth:sanctum', 'store.permission'])
    ->controller(CategoryController::class)
    ->group(function () {
        //  Allow Guest shopping on showCategories
        Route::get('/', 'showCategories')->withoutMiddleware(['auth:sanctum', 'store.permission'])->name('show.categories');
        Route::post('/', 'createCategory')->name('create.category');
        Route::put('/', 'updateCategories')->name('update.categories');
        Route::delete('/', 'deleteCategories')->name('delete.categories');
        Route::post('/visibility', 'updateCategoryVisibility')->name('update.category.visibility');
        Route::post('/arrangement', 'updateCategoryArrangement')->name('update.category.arrangement');

        //  Explicit route model binding applied: AppServiceProvider.php
        Route::prefix('{category}')->group(function () {
            //  Allow Guest shopping on showCategory
            Route::get('/', 'showCategory')->withoutMiddleware(['auth:sanctum', 'store.permission'])->name('show.category');
            Route::put('/', 'updateCategory')->name('update.category');
            Route::delete('/', 'deleteCategory')->name('delete.category');
        });
    });
