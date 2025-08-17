<?php

use Illuminate\Support\Facades\Route;
use App\Http\Middleware\StorePermission;
use App\Http\Controllers\PricingPlanController;

Route::middleware(['auth:sanctum'])
    ->prefix('pricing-plans')
    ->controller(PricingPlanController::class)
    ->group(function () {
        Route::get('/', 'showPricingPlans')->name('show.pricing.plans');
        Route::post('/', 'createPricingPlan')->name('create.pricing.plan');
        Route::delete('/', 'deletePricingPlans')->name('delete.pricing.plans');

        Route::middleware(['store.permission'])->prefix('{pricingPlan}')->group(function () {
            Route::get('/', 'showPricingPlan')->name('show.pricing.plan');
            Route::put('/', 'updatePricingPlan')->name('update.pricing.plan');
            Route::delete('/', 'deletePricingPlan')->name('delete.pricing.plan');
            Route::post('/pay', 'payPricingPlan')->name('pay.pricing.plan');
            Route::post('/pay/verify/{transaction}', 'verifyPricingPlanPayment')->name('verify.pricing.plan.payment');
        });
    });
