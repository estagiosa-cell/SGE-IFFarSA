<?php

use App\Http\Controllers\Auth\SessionController;
use Illuminate\Support\Facades\Route;

Route::get('/', [SessionController::class, 'create'])->name('login');
Route::post('/login', [SessionController::class, 'store']);

Route::middleware(['auth'])->group(function () {
    Route::view('/dashboard', 'dashboard')->name('dashboard'); // rota temporária, apenas para testes
    Route::view('/estagios', 'dashboard')->name('estagios'); // rota temporária, apenas para testes
    Route::post('/logout', [SessionController::class, 'destroy'])->name('logout');
});
