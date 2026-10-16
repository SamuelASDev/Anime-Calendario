<?php

use App\Http\Controllers\LibraryController;
use App\Http\Controllers\PersonalWatchPlanController;
use App\Http\Controllers\WatchPlanController;
use Illuminate\Support\Facades\Route;

Route::middleware('auth')->group(function () {
    Route::get('/me/watch-later', [LibraryController::class, 'watchLaterIndex'])->name('personal.watch-later.index');
    Route::post('/me/watch-later/{anime}', [LibraryController::class, 'saveForLater'])->whereNumber('anime')->name('personal.watch-later.store');
    Route::get('/watch-plans/create/{anime}', [WatchPlanController::class, 'create'])
        ->middleware('admin')->name('watch-plans.create');

    Route::post('/me/watch-plans/follow/{plan}', [PersonalWatchPlanController::class, 'followFromGlobal'])
        ->name('personal.watch-plans.follow');

    Route::post('/watch-plans/store/{anime}', [WatchPlanController::class, 'store'])
        ->middleware('admin')->name('watch-plans.store');

    Route::get('/calendar', [WatchPlanController::class, 'calendar'])
        ->name('calendar');

    Route::get('/history', [WatchPlanController::class, 'history'])
        ->name('history');

    Route::post('/history/confirm', [WatchPlanController::class, 'confirm'])
        ->middleware('admin')->name('history.confirm');

    Route::get('/concluidos', [WatchPlanController::class, 'completed'])
        ->name('completed.animes');

    Route::get('/concluidos/{anime}/review', [LibraryController::class, 'createReview'])
        ->name('completed.review.create');

    Route::post('/concluidos/{anime}/review', [LibraryController::class, 'storeReview'])
        ->name('completed.review.store');

    Route::get('/me/calendar', [PersonalWatchPlanController::class, 'calendar'])
        ->name('personal.calendar');

    Route::get('/me/history', [PersonalWatchPlanController::class, 'history'])
        ->name('personal.history');

    Route::post('/me/history/confirm', [PersonalWatchPlanController::class, 'confirm'])
        ->name('personal.history.confirm');

    Route::get('/me/completed', [PersonalWatchPlanController::class, 'completed'])
        ->name('personal.completed');

    Route::get('/me/completed/{anime}/review', [LibraryController::class, 'createReview'])
        ->name('personal.completed.review.create');

    Route::post('/me/completed/{anime}/review', [LibraryController::class, 'storeReview'])
        ->name('personal.completed.review.store');

    Route::get('/me/watch-plans/create/{anime}', [PersonalWatchPlanController::class, 'create'])
        ->name('personal.watch-plans.create');

    Route::post('/me/watch-plans/store/{anime}', [PersonalWatchPlanController::class, 'store'])
        ->name('personal.watch-plans.store');

    Route::get('/me/animes', [PersonalWatchPlanController::class, 'index'])
        ->name('personal.animes.index');

    Route::get('/me/animes/{plan}/edit', [PersonalWatchPlanController::class, 'edit'])
        ->name('personal.animes.edit');

    Route::put('/me/animes/{plan}', [PersonalWatchPlanController::class, 'update'])
        ->name('personal.animes.update');

    Route::get('/me/animes/{plan}/log', [PersonalWatchPlanController::class, 'createLog'])
        ->name('personal.animes.log.create');

    Route::post('/me/animes/{plan}/log', [PersonalWatchPlanController::class, 'storeLog'])
        ->name('personal.animes.log.store');

    Route::patch('/me/animes/{plan}/pause', [PersonalWatchPlanController::class, 'pause'])
        ->name('personal.animes.pause');

    Route::patch('/me/animes/{plan}/resume', [PersonalWatchPlanController::class, 'resume'])
        ->name('personal.animes.resume');

    Route::patch('/me/animes/{plan}/complete', [PersonalWatchPlanController::class, 'complete'])
        ->name('personal.animes.complete');

    Route::delete('/me/animes/{plan}', [PersonalWatchPlanController::class, 'destroy'])
        ->name('personal.animes.destroy');

    Route::patch('/me/animes/{anime}/favorite', [LibraryController::class, 'toggleFavorite'])
        ->name('personal.animes.favorite');

    Route::patch('/me/animes/{anime}/top', [LibraryController::class, 'updateTopPosition'])
        ->name('personal.animes.top');

    Route::patch('/me/animes/{anime}/top/add', [LibraryController::class, 'addToTop'])
        ->name('personal.animes.addTop');

    Route::patch('/me/animes/{anime}/top/remove', [LibraryController::class, 'removeFromTop'])
        ->name('personal.animes.removeTop');

    Route::patch('/personal/animes/{anime}/top/up', [LibraryController::class, 'moveTopUp'])
        ->name('personal.animes.top.up');

    Route::patch('/personal/animes/{anime}/top/down', [LibraryController::class, 'moveTopDown'])
        ->name('personal.animes.top.down');

});
