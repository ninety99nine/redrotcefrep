<?php

use Illuminate\Support\Facades\Route;
use App\Http\Middleware\StorePermission;
use App\Http\Controllers\AiAssistantController;

Route::prefix('ai-assistants')
    ->middleware(['auth:sanctum'])
    ->controller(AiAssistantController::class)
    ->group(function () {
        Route::get('/', 'showAiAssistants')->name('show.ai.assistants');
        Route::post('/', 'createAiAssistant')->name('create.ai.assistant');
        Route::delete('/', 'deleteAiAssistants')->name('delete.ai.assistants');

        // Explicit route model binding applied: AppServiceProvider.php
        Route::prefix('{aiAssistant}')->group(function () {
            Route::get('/', 'showAiAssistant')->name('show.ai.assistant');
            Route::put('/', 'updateAiAssistant')->name('update.ai.assistant');
            Route::delete('/', 'deleteAiAssistant')->name('delete.ai.assistant');
        });
    });
