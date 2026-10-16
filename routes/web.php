<?php

use Illuminate\Support\Facades\Route;

Route::view('/', 'welcome')->name('home');

require __DIR__.'/profiles.php';
require __DIR__.'/catalog.php';
require __DIR__.'/planning.php';
require __DIR__.'/admin.php';
require __DIR__.'/auth.php';
