<?php

use App\Http\Controllers\AnimeController;
use App\Http\Controllers\LibraryController;
use Illuminate\Support\Facades\Route;

Route::middleware('auth')->group(function () {
    Route::post('/anime/store', [AnimeController::class, 'store'])->middleware('throttle:30,1')->name('anime.store');
    Route::get('/animes', [AnimeController::class, 'allAnimeIndex'])
        ->name('anime.index.all');

    Route::get('/anime/search', [AnimeController::class, 'search'])
        ->name('anime.search');

    Route::get('/anime/{anime}', [AnimeController::class, 'show'])
        ->whereNumber('anime')
        ->name('anime.show');

    Route::get('/anime/{anime}/edit-synopsis', [AnimeController::class, 'editSynopsis'])
        ->whereNumber('anime')
        ->middleware('admin')->name('anime.synopsis.edit');

    Route::patch('/anime/{anime}/synopsis', [AnimeController::class, 'updateSynopsis'])
        ->whereNumber('anime')
        ->middleware('admin')->name('anime.synopsis.update');

    Route::post('/concluidos/{anime}/ja-assisti', [LibraryController::class, 'markAsWatched'])
        ->name('completed.mark-watched');

});
