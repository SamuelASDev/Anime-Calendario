<?php

use App\Http\Controllers\Admin\AnimeMetadataController;
use App\Http\Controllers\Admin\WatchPlanAdminController;
use Illuminate\Support\Facades\Route;

Route::middleware(['auth', 'admin'])->group(function () {

    Route::get('/admin/catalogo/dados', [AnimeMetadataController::class, 'index'])
        ->name('admin.animes.metadata');

    Route::post('/admin/catalogo/dados/sincronizar', [AnimeMetadataController::class, 'sync'])
        ->middleware('throttle:60,1')->name('admin.animes.metadata.sync');

    Route::post('/admin/catalogo/dados/repetir-falhas', [AnimeMetadataController::class, 'retryFailed'])
        ->name('admin.animes.metadata.retry');

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

    Route::patch('/admin/animes/{plan}/complete', [WatchPlanAdminController::class, 'complete'])
        ->name('admin.animes.complete');

    Route::patch('/admin/animes/{plan}/pause', [WatchPlanAdminController::class, 'pause'])
        ->name('admin.animes.pause');

    Route::patch('/admin/animes/{plan}/resume', [WatchPlanAdminController::class, 'resume'])
        ->name('admin.animes.resume');

    Route::delete('/admin/animes/{plan}', [WatchPlanAdminController::class, 'destroy'])
        ->name('admin.animes.destroy');
});
