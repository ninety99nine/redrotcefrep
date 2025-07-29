<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\MiscellaneousController;

Route::get('/filters', [MiscellaneousController::class, 'showFilters'])->name('show.filters');
Route::get('/sorting', [MiscellaneousController::class, 'showSorting'])->name('show.sorting');
Route::get('/social-media-links', [MiscellaneousController::class, 'showSocialMediaLinks'])->name('show.social.media.links');
