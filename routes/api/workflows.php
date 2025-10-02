<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\WorkflowController;

Route::prefix('workflows')
    ->middleware(['auth:sanctum', 'store.permission'])
    ->controller(WorkflowController::class)
    ->group(function () {
        Route::get('/', 'showWorkflows')->name('show.workflow');
        Route::post('/', 'createWorkflow')->name('create.workflow');
        Route::delete('/', 'deleteWorkflows')->name('delete.workflow');
        Route::post('/arrangement', 'updateWorkflowArrangement')->name('update.workflow.arrangement');

        // Explicit route model binding applied: AppServiceProvider.php
        Route::prefix('{workflow}')->group(function () {
            Route::get('/', 'showWorkflow')->name('show.workflow');
            Route::put('/', 'updateWorkflow')->name('update.workflow');
            Route::delete('/', 'deleteWorkflow')->name('delete.workflow');
        });
});
