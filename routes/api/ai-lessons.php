<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\AiLessonController;

Route::middleware(['auth:sanctum'])
    ->prefix('ai-lessons')
    ->controller(AiLessonController::class)
    ->group(function () {
        Route::get('/', 'showAiLessons')->name('show.ai.lessons');
        Route::post('/', 'createAiLesson')->name('create.ai.lesson');
        Route::delete('/', 'deleteAiLessons')->name('delete.ai.lessons');

        Route::prefix('{aiLesson}')->group(function () {
            Route::get('/', 'showAiLesson')->name('show.ai.lesson');
            Route::put('/', 'updateAiLesson')->name('update.ai.lesson');
            Route::delete('/', 'deleteAiLesson')->name('delete.ai.lesson');
        });
    });
