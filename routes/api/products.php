<?php

use Illuminate\Support\Facades\Route;
use App\Http\Middleware\StorePermission;
use App\Http\Controllers\ProductController;

Route::prefix('products')
    ->middleware(['auth:sanctum', StorePermission::class])
    ->controller(ProductController::class)
    ->group(function () {
        Route::get('/', 'showProducts')->name('show.products');
        Route::post('/', 'createProduct')->name('create.product');
        Route::delete('/', 'deleteProducts')->name('delete.products');
        Route::post('/visibility', 'updateProductVisibility')->name('update.product.visibility');
        Route::post('/arrangement', 'updateProductArrangement')->name('update.product.arrangement');

        //  Explicit route model binding applied: AppServiceProvider.php
        Route::prefix('{product}')->group(function () {
            Route::get('/', 'showProduct')->name('show.product');
            Route::put('/', 'updateProduct')->name('update.product');
            Route::delete('/', 'deleteProduct')->name('delete.product');
            Route::get('/variations', 'showProductVariations')->name('show.product.variations');
            Route::post('/variations', 'createProductVariations')->name('create.product.variations');
        });
    });
