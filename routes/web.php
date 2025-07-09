<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\SessionController;

Route::get('/', [SessionController::class, 'create'])->name('login');
Route::post('/login', [SessionController::class, 'store']);

Route::middleware(['auth'])->group(function () {
  Route::get('/dashboard', function () {return "dashboard";})->name('dashboard');
});

Route::post('/logout', [SessionController::class, 'destroy'])->name('logout');