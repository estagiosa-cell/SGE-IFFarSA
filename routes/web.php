<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Auth\SessionController;
use App\Http\Controllers\Auth\PasswordResetController;
use App\Http\Controllers\Admin\UserController;
use App\Http\Controllers\Admin\CourseController;
use App\Http\Controllers\Admin\InternshipTypeController;
use App\Http\Controllers\Admin\CnpjExceptionController;

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
        Route::post('/users', [UserController::class, 'store'])->name('admin.users.store');
        Route::get('/users/{id}/edit', [UserController::class, 'edit'])->name('admin.users.edit');
        Route::put('/users/{id}', [UserController::class, 'update'])->name('admin.users.update');
        Route::post('/users/import', [UserController::class, 'import'])->name('admin.users.import');
        Route::delete('/users/{id}', [UserController::class, 'destroy'])->name('admin.users.destroy');
        Route::patch('/users/{user}/deactivate', [UserController::class, 'deactivate'])->name('admin.users.deactivate');
        Route::patch('/users/{user}/reactivate', [UserController::class, 'reactivate'])->name('admin.users.reactivate');

        // Rotas de Cursos
        Route::get('/courses', [CourseController::class, 'index'])->name('admin.courses.index');
        Route::get('/courses/create', [CourseController::class, 'create'])->name('admin.courses.create');
        Route::post('/courses', [CourseController::class, 'store'])->name('admin.courses.store');
        Route::get('/courses/{id}/edit', [CourseController::class, 'edit'])->name('admin.courses.edit');
        Route::put('/courses/{id}', [CourseController::class, 'update'])->name('admin.courses.update');

        // Rotas de Tipos de Estágio
        Route::get('/internship-types', [InternshipTypeController::class, 'index'])->name('admin.internship-types.index');
        Route::get('/internship-types/create', [InternshipTypeController::class, 'create'])->name('admin.internship-types.create');
        Route::post('/internship-types', [InternshipTypeController::class, 'store'])->name('admin.internship-types.store');
        Route::get('/internship-types/{id}/edit', [InternshipTypeController::class, 'edit'])->name('admin.internship-types.edit');
        Route::put('/internship-types/{id}', [InternshipTypeController::class, 'update'])->name('admin.internship-types.update');

        // Rotas de Exceções de CNPJ
        Route::get('/cnpj-exceptions', [CnpjExceptionController::class, 'index'])->name('admin.cnpj-exceptions.index');
        Route::get('/cnpj-exceptions/create', [CnpjExceptionController::class, 'create'])->name('admin.cnpj-exceptions.create');
        Route::post('/cnpj-exceptions', [CnpjExceptionController::class, 'store'])->name('admin.cnpj-exceptions.store');
        Route::get('/cnpj-exceptions/{id}/edit', [CnpjExceptionController::class, 'edit'])->name('admin.cnpj-exceptions.edit');
        Route::put('/cnpj-exceptions/{id}', [CnpjExceptionController::class, 'update'])->name('admin.cnpj-exceptions.update');
        Route::delete('/cnpj-exceptions/{id}', [CnpjExceptionController::class, 'destroy'])->name('admin.cnpj-exceptions.destroy');
    });

    Route::post('/logout', [SessionController::class, 'destroy'])->name('logout');
});
