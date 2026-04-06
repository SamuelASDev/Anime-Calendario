<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\ProfileController;
use App\Http\Controllers\AnimeController;
use App\Http\Controllers\WatchPlanController;
use App\Http\Controllers\Admin\WatchPlanAdminController;


Route::get('/', function () {
    return view('dashboard');
})->middleware(['auth', 'verified'])->name('dashboard');

Route::get('/dashboard', function () {
    return view('dashboard');
})->middleware(['auth', 'verified'])->name('dashboard');

Route::middleware(['auth'])->group(function () {
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');

    Route::get('/anime/search', [AnimeController::class, 'search'])->name('anime.search');

    Route::get('/watch-plans/create/{anime}', [WatchPlanController::class, 'create'])
        ->name('watch-plans.create');

    Route::post('/watch-plans/store/{anime}', [WatchPlanController::class, 'store'])
        ->name('watch-plans.store');

    Route::get('/concluidos', [WatchPlanController::class, 'completed'])
        ->name('completed.animes');

    Route::get('/calendar', [WatchPlanController::class, 'calendar'])
        ->name('calendar');

    Route::get('/concluidos/{anime}/review', [WatchPlanController::class, 'createReview'])
        ->name('completed.review.create');

    Route::post('/concluidos/{anime}/review', [WatchPlanController::class, 'storeReview'])
        ->name('completed.review.store');

    Route::get('/history', [WatchPlanController::class, 'history'])
        ->name('history');

    Route::post('/history/confirm', [WatchPlanController::class, 'confirm'])
        ->name('history.confirm');
    
});

Route::middleware(['auth', 'admin'])->group(function () {
    Route::post('/anime/store', [AnimeController::class, 'store'])->name('anime.store');

    Route::get('/admin/animes', [WatchPlanAdminController::class, 'index'])
        ->name('admin.animes.index');

    Route::get('/admin/animes/{plan}/edit', [WatchPlanAdminController::class, 'edit'])
        ->name('admin.animes.edit');

    Route::put('/admin/animes/{plan}', [WatchPlanAdminController::class, 'update'])
        ->name('admin.animes.update');

    Route::get('/admin/animes/{plan}/log', [WatchPlanAdminController::class, 'createLog'])
        ->name('admin.animes.log.create');

    Route::post('/admin/animes/{plan}/log', [WatchPlanAdminController::class, 'storeLog'])
        ->name('admin.animes.log.store');

    Route::delete('/admin/animes/{plan}', [WatchPlanAdminController::class, 'destroy'])
        ->name('admin.animes.destroy');

    Route::patch('/admin/animes/{plan}/complete', [WatchPlanAdminController::class, 'complete'])
        ->name('admin.animes.complete');

    Route::patch('/admin/animes/{plan}/pause', [WatchPlanAdminController::class, 'pause'])
        ->name('admin.animes.pause');

    Route::patch('/admin/animes/{plan}/resume', [WatchPlanAdminController::class, 'resume'])
        ->name('admin.animes.resume');
});

require __DIR__ . '/auth.php';