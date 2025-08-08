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
        Route::put('/', 'updateProducts')->name('update.products');
        Route::delete('/', 'deleteProducts')->name('delete.products');
        Route::post('/import', 'importProducts')->name('import.products');
        Route::post('/download', 'downloadProducts')->name('download.products');
        Route::post('/visibility', 'updateProductVisibility')->name('update.product.visibility');
        Route::post('/arrangement', 'updateProductArrangement')->name('update.product.arrangement');

        //  Explicit route model binding applied: AppServiceProvider.php
        Route::prefix('{product}')->group(function () {
            Route::get('/', 'showProduct')->name('show.product');
            Route::put('/', 'updateProduct')->name('update.product');
            Route::delete('/', 'deleteProduct')->name('delete.product');
            Route::get('/variants', 'showProductVariants')->name('show.product.variants');
        });
    });
