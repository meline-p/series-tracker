<?php

use App\Http\Controllers\SeriesController;
use App\Http\Controllers\DatabaseController;
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
Route::post('/seasons/{season}/toggle-watched', [SeriesController::class, 'toggleSeasonWatched'])
    ->name('seasons.toggle-watched');
Route::post('/series/{series}/toggle-watched', [SeriesController::class, 'toggleSeriesWatched'])
    ->name('series.toggle-watched');

Route::post('/database/export', [DatabaseController::class, 'export'])
    ->name('database.export');
Route::post('/database/import', [DatabaseController::class, 'import'])
    ->name('database.import');

Route::delete('/series/{series}', [SeriesController::class, 'destroy'])
    ->name('series.destroy');
