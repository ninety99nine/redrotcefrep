<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\AiMessageController;

Route::prefix('ai-messages')
    ->middleware(['auth:sanctum'])
    ->controller(AiMessageController::class)
    ->group(function () {
        Route::get('/', 'showAiMessages')->name('show.ai.messages');
        Route::post('/', 'createAiMessage')->name('create.ai.message');
        Route::delete('/', 'deleteAiMessages')->name('delete.ai.messages');

        // Explicit route model binding applied: AppServiceProvider.php
        Route::prefix('{aiMessage}')->group(function () {
            Route::get('/', 'showAiMessage')->name('show.ai.message');
            Route::put('/', 'updateAiMessage')->name('update.ai.message');
            Route::delete('/', 'deleteAiMessage')->name('delete.ai.message');
        });
    });
