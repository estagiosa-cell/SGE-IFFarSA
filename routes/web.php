<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Auth\SessionController;
use App\Http\Controllers\Auth\PasswordResetController;
use App\Http\Controllers\Admin\UserController;

Route::get('/', [SessionController::class, 'create'])->name('login');
Route::post('/login', [SessionController::class, 'store'])->name('store.login');

// Rotas de Recuperação de Senha
Route::get('/forgot-password', [PasswordResetController::class, 'create'])->name('password.request');
Route::post('/forgot-password', [PasswordResetController::class, 'store'])->name('password.email');
Route::get('/reset-password/{token}', [PasswordResetController::class, 'edit'])->name('password.reset');
Route::put('/reset-password', [PasswordResetController::class, 'update'])->name('password.update');


// Rotas dos usuários autenticados
Route::middleware(['auth'])->group(function () {
    Route::middleware(['can:is-admin'])->group(function () {
        Route::view('/dashboard', 'admin.dashboard')->name('admin.dashboard'); // rota temporária, apenas para testes

        // Rotas de Usuários
        Route::get('/users', [UserController::class, 'index'])->name('admin.users.index');
        Route::get('/users/create', [UserController::class, 'create'])->name('admin.users.create');

        // Rotas para desaticvar e reativar usuários
        Route::patch('/users/{user}/deactivate', [UserController::class, 'deactivate'])->name('admin.users.deactivate');
        Route::patch('/users/{user}/reactivate', [UserController::class, 'reactivate'])->name('admin.users.reactivate');
    });

    Route::post('/logout', [SessionController::class, 'destroy'])->name('logout');
});
