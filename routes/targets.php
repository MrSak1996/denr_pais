<?php

use Illuminate\Support\Facades\Route;

Route::middleware('auth')->group(function () {

    Route::get('/targets/index', ['App\\Http\\Controllers\\Targets\\TargetController'::class, 'index'])
        ->name('targets.index');

    Route::post('/targets', ['App\\Http\\Controllers\\Targets\\TargetController', 'store'])
        ->name('targets.store');

});