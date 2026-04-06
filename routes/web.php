<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\AnimeController;
use App\Http\Controllers\ProfileController;
use App\Http\Controllers\WatchPlanController;
use App\Http\Controllers\PersonalWatchPlanController;
use App\Http\Controllers\Admin\WatchPlanAdminController;

/*
|--------------------------------------------------------------------------
| Rotas públicas
|--------------------------------------------------------------------------
*/

Route::get('/', function () {
    return view('welcome');
})->name('home');

/*
|--------------------------------------------------------------------------
| Rotas autenticadas
|--------------------------------------------------------------------------
*/

Route::middleware(['auth'])->group(function () {

    /*
    |--------------------------------------------------------------------------
    | Dashboard
    |--------------------------------------------------------------------------
    */

    Route::get('/dashboard', function () {
        return view('dashboard');
    })->middleware(['verified'])->name('dashboard');

    /*
    |--------------------------------------------------------------------------
    | Perfil
    |--------------------------------------------------------------------------
    */

    Route::get('/profile', [ProfileController::class, 'edit'])
        ->name('profile.edit');

    Route::patch('/profile', [ProfileController::class, 'update'])
        ->name('profile.update');

    Route::delete('/profile', [ProfileController::class, 'destroy'])
        ->name('profile.destroy');

    Route::patch('/profile/public', [ProfileController::class, 'updatePublicProfile'])
        ->name('profile.public.update');

    Route::get('/u/{username}', [ProfileController::class, 'show'])
        ->name('profile.show');

    /*
    |--------------------------------------------------------------------------
    | Usuários públicos
    |--------------------------------------------------------------------------
    */

    Route::get('/users', function () {
        $users = \App\Models\User::where('is_public', true)->get();
        return view('users.index', compact('users'));
    })->name('users.index');

    /*
    |--------------------------------------------------------------------------
    | Anime
    |--------------------------------------------------------------------------
    */

    Route::get('/anime/search', [AnimeController::class, 'search'])
        ->name('anime.search');

    Route::get('/anime/{anime}', [AnimeController::class, 'show'])
        ->whereNumber('anime')
        ->name('anime.show');

    /*
    |--------------------------------------------------------------------------
    | Watch plans globais
    |--------------------------------------------------------------------------
    */

    Route::get('/watch-plans/create/{anime}', [WatchPlanController::class, 'create'])
        ->name('watch-plans.create');

    Route::post('/watch-plans/store/{anime}', [WatchPlanController::class, 'store'])
        ->name('watch-plans.store');

    Route::get('/calendar', [WatchPlanController::class, 'calendar'])
        ->name('calendar');

    Route::get('/history', [WatchPlanController::class, 'history'])
        ->name('history');

    Route::post('/history/confirm', [WatchPlanController::class, 'confirm'])
        ->name('history.confirm');

    Route::get('/concluidos', [WatchPlanController::class, 'completed'])
        ->name('completed.animes');

    Route::get('/concluidos/{anime}/review', [WatchPlanController::class, 'createReview'])
        ->name('completed.review.create');

    Route::post('/concluidos/{anime}/review', [WatchPlanController::class, 'storeReview'])
        ->name('completed.review.store');

    /*
    |--------------------------------------------------------------------------
    | Espaço pessoal
    |--------------------------------------------------------------------------
    */

    Route::get('/me/calendar', [PersonalWatchPlanController::class, 'calendar'])
        ->name('personal.calendar');

    Route::get('/me/history', [PersonalWatchPlanController::class, 'history'])
        ->name('personal.history');

    Route::post('/me/history/confirm', [PersonalWatchPlanController::class, 'confirm'])
        ->name('personal.history.confirm');

    Route::get('/me/completed', [PersonalWatchPlanController::class, 'completed'])
        ->name('personal.completed');

    Route::get('/me/completed/{anime}/review', [PersonalWatchPlanController::class, 'createReview'])
        ->name('personal.completed.review.create');

    Route::post('/me/completed/{anime}/review', [PersonalWatchPlanController::class, 'storeReview'])
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

    Route::patch('/me/animes/{anime}/favorite', [PersonalWatchPlanController::class, 'toggleFavorite'])
        ->name('personal.animes.favorite');

    Route::patch('/me/animes/{anime}/top', [PersonalWatchPlanController::class, 'updateTopPosition'])
        ->name('personal.animes.top');

    Route::patch('/me/animes/{anime}/top/add', [PersonalWatchPlanController::class, 'addToTop'])
        ->name('personal.animes.addTop');

    Route::patch('/me/animes/{anime}/top/remove', [PersonalWatchPlanController::class, 'removeFromTop'])
        ->name('personal.animes.removeTop');
});

/*
|--------------------------------------------------------------------------
| Rotas admin
|--------------------------------------------------------------------------
*/

Route::middleware(['auth', 'admin'])->group(function () {

    Route::post('/anime/store', [AnimeController::class, 'store'])
        ->name('anime.store');

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

require __DIR__ . '/auth.php';