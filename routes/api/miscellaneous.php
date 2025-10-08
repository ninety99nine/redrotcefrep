<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\MiscellaneousController;

Route::get('/filters', [MiscellaneousController::class, 'showFilters'])->name('show.filters');
Route::get('/sorting', [MiscellaneousController::class, 'showSorting'])->name('show.sorting');

Route::post('/convert/currency', [MiscellaneousController::class, 'convertCurrency'])->name('convert.currency');
