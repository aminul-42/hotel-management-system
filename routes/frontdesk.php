<?php

use Illuminate\Support\Facades\Route;

Route::prefix('frontdesk')
    ->name('frontdesk.')
    ->middleware(['auth', 'frontdesk'])
    ->group(function () {
        Route::get('/dashboard', function () {
            return view('frontdesk.dashboard');
        })->name('dashboard');
    });