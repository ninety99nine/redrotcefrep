<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\AiTopicController;

Route::middleware(['auth:sanctum'])
    ->prefix('ai-topics')
    ->controller(AiTopicController::class)
    ->group(function () {
        Route::get('/', 'showAiTopics')->name('show.ai.topics');
        Route::post('/', 'createAiTopic')->name('create.ai.topic');
        Route::delete('/', 'deleteAiTopics')->name('delete.ai.topics');

        Route::prefix('{aiTopic}')->group(function () {
            Route::get('/', 'showAiTopic')->name('show.ai.topic');
            Route::put('/', 'updateAiTopic')->name('update.ai.topic');
            Route::delete('/', 'deleteAiTopic')->name('delete.ai.topic');
        });
    });
