<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\TeamMemberController;

Route::prefix('team-members')
    ->middleware(['auth:sanctum', 'store.permission'])
    ->controller(TeamMemberController::class)
    ->group(function () {
        Route::get('/', 'showTeamMembers')->name('show.team.members');
        Route::post('/', 'addTeamMember')->name('add.team.member');
        Route::delete('/', 'removeTeamMembers')->name('remove.team.members');

        // Explicit route model binding applied: AppServiceProvider.php
        Route::prefix('{teamMember}')->group(function () {
            Route::get('/', 'showTeamMember')->name('show.team.member');
            Route::put('/', 'updateTeamMember')->name('update.team.member');
            Route::delete('/', 'removeTeamMember')->name('remove.team.member');
        });
    });
