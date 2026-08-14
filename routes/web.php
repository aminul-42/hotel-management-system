<?php

use App\Http\Controllers\AuthController;
use App\Http\Controllers\Customer\RegisterController;
use Illuminate\Support\Facades\Route;

use App\Http\Controllers\Customer\HomeController;

Route::get('/', [HomeController::class, 'index'])->name('home');

Route::get('/login', [AuthController::class, 'showLogin'])->name('login');
Route::post('/login', [AuthController::class, 'login'])->name('login.submit');
Route::post('/logout', [AuthController::class, 'logout'])->name('logout');
Route::post('/register', [RegisterController::class, 'register'])->name('register.submit');