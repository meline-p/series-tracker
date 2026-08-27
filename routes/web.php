<?php

use App\Http\Controllers\SeriesController;
use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return view('welcome');
});

Route::get('/', [SeriesController::class, 'index'])
    ->name('series.index');

Route::get('/series/search', [SeriesController::class, 'search'])
    ->name('series.search');
Route::post('/series/sync', [SeriesController::class, 'sync'])
    ->name('series.sync');

Route::post('/series', [SeriesController::class, 'store'])->name('series.store');
Route::get('/series/{series}', [SeriesController::class, 'show'])
    ->name('series.show');
Route::post('/episodes/{episode}/toggle-watched', [SeriesController::class, 'toggleWatched'])
    ->name('episodes.toggle-watched');
