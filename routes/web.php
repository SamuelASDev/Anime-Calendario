<?php

use App\Models\Anime;
use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return view('welcome', [
        'featuredAnimes' => Anime::query()
            ->whereNotNull('image')->where('image', '!=', '')
            ->latest('id')->limit(4)->get(['id', 'title', 'image', 'episodes']),
    ]);
})->name('home');

require __DIR__.'/profiles.php';
require __DIR__.'/catalog.php';
require __DIR__.'/planning.php';
require __DIR__.'/admin.php';
require __DIR__.'/auth.php';
