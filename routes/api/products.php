<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\ProductController;

Route::prefix('products')
    ->middleware(['auth:sanctum', 'store.permission'])
    ->controller(ProductController::class)
    ->group(function () {
        //  Allow Guest shopping on showProducts
        Route::get('/', 'showProducts')->withoutMiddleware(['auth:sanctum', 'store.permission'])->name('show.products');
        Route::post('/', 'createProduct')->name('create.product');
        Route::put('/', 'updateProducts')->name('update.products');
        Route::delete('/', 'deleteProducts')->name('delete.products');
        Route::post('/import', 'importProducts')->name('import.products');
        Route::post('/visibility', 'updateProductVisibility')->name('update.product.visibility');
        Route::post('/arrangement', 'updateProductArrangement')->name('update.product.arrangement');

        //  Explicit route model binding applied: AppServiceProvider.php
        Route::prefix('{product}')->group(function () {
            //  Allow Guest shopping on showProduct
            Route::get('/', 'showProduct')->withoutMiddleware(['auth:sanctum', 'store.permission'])->name('show.product');
            Route::put('/', 'updateProduct')->name('update.product');
            Route::delete('/', 'deleteProduct')->name('delete.product');
            Route::get('/variants', 'showProductVariants')->name('show.product.variants');
        });
    });
