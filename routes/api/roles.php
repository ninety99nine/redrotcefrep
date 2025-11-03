<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\RoleController;

Route::prefix('roles')
    ->middleware(['auth:sanctum', 'store.permission'])
    ->controller(RoleController::class)
    ->group(function () {
        Route::get('/', 'showRoles')->name('show.roles');
        Route::post('/', 'createRole')->name('create.role');
        Route::put('/', 'updateRoles')->name('update.roles');
        Route::delete('/', 'deleteRoles')->name('delete.roles');

        // Explicit route model binding applied: AppServiceProvider.php
        Route::prefix('{role}')->group(function () {
            Route::get('/', 'showRole')->name('show.role');
            Route::put('/', 'updateRole')->name('update.role');
            Route::delete('/', 'deleteRole')->name('delete.role');
        });
    });
