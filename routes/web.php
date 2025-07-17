<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Auth\SessionController;
use App\Http\Controllers\Auth\PasswordResetController;

Route::get('/', [SessionController::class, 'create'])->name('login');
Route::post('/login', [SessionController::class, 'store']);

// Rotas de Recuperação de Senha
Route::get('/forgot-password', [PasswordResetController::class, 'create'])->name('password.request');
Route::post('/forgot-password', [PasswordResetController::class, 'store'])->name('password.email');
Route::get('/reset-password/{token}', [PasswordResetController::class, 'edit'])->name('password.reset');
Route::put('/reset-password', [PasswordResetController::class, 'update'])->name('password.update');


// Rotas dos usuários autenticados
Route::middleware(['auth'])->group(function () {
    Route::view('/dashboard', 'dashboard')->name('dashboard'); // rota temporária, apenas para testes
    Route::view('/estagios', 'dashboard')->name('estagios'); // rota temporária, apenas para testes
    Route::post('/logout', [SessionController::class, 'destroy'])->name('logout');
});
