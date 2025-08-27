<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\AiAssistantTokenUsageController;

Route::prefix('ai-assistant-token-usages')
    ->middleware(['auth:sanctum'])
    ->controller(AiAssistantTokenUsageController::class)
    ->group(function () {
        Route::get('/', 'showAiAssistantTokenUsages')->name('show.ai.assistant.token.usages');
        Route::post('/', 'createAiAssistantTokenUsage')->name('create.ai.assistant.token.usage');
        Route::delete('/', 'deleteAiAssistantTokenUsages')->name('delete.ai.assistant.token.usages');

        // Explicit route model binding applied: AppServiceProvider.php
        Route::prefix('{aiAssistantTokenUsage}')->group(function () {
            Route::get('/', 'showAiAssistantTokenUsage')->name('show.ai.assistant.token.usage');
            Route::put('/', 'updateAiAssistantTokenUsage')->name('update.ai.assistant.token.usage');
            Route::delete('/', 'deleteAiAssistantTokenUsage')->name('delete.ai.assistant.token.usage');
        });
    });
