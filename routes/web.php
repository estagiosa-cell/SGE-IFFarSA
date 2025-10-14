<?php

use App\Http\Controllers\Admin\CompanyController;
use App\Http\Controllers\Admin\CourseController;
use App\Http\Controllers\Admin\DashboardController;
use App\Http\Controllers\Admin\InternshipController;
use App\Http\Controllers\Admin\InternshipDocumentController;
use App\Http\Controllers\Admin\InternshipTypeController;
use App\Http\Controllers\Admin\SyncDataController;
use App\Http\Controllers\Admin\UserController;
use App\Http\Controllers\Auth\PasswordResetController;
use App\Http\Controllers\Auth\SessionController;
use App\Http\Controllers\GoogleAuthController;
use App\Http\Controllers\InternshipViewController;
use Illuminate\Support\Facades\Route;

Route::get('/', [SessionController::class, 'create'])->name('login');
Route::post('/login', [SessionController::class, 'store'])->name('store.login');

// Rotas de Recuperação de Senha
Route::get('/forgot-password', [PasswordResetController::class, 'create'])->name('password.request');
Route::post('/forgot-password', [PasswordResetController::class, 'store'])->name('password.email');
Route::get('/reset-password/{token}', [PasswordResetController::class, 'edit'])->name('password.reset');
Route::put('/reset-password', [PasswordResetController::class, 'update'])->name('password.update');

// Rotas dos usuários autenticados
Route::middleware(['auth'])->group(function () {
    Route::post('/logout', [SessionController::class, 'destroy'])->name('logout');

    Route::middleware(['can:is-admin'])->group(function () {
        Route::get('/dashboard', DashboardController::class)->name('admin.dashboard');

        // Rotas de Usuários
        Route::get('/users', [UserController::class, 'index'])->name('admin.users.index');
        Route::get('/users/create', [UserController::class, 'create'])->name('admin.users.create');
        Route::post('/users', [UserController::class, 'store'])->name('admin.users.store');
        Route::get('/users/{id}/edit', [UserController::class, 'edit'])->name('admin.users.edit');
        Route::put('/users/{id}', [UserController::class, 'update'])->name('admin.users.update');
        Route::post('/users/import', [UserController::class, 'import'])->name('admin.users.import');
        Route::delete('/users/{id}', [UserController::class, 'destroy'])->name('admin.users.destroy');
        Route::patch('/users/{id}/restore', [UserController::class, 'restore'])->name('admin.users.restore');
        Route::patch('/users/{user}/deactivate', [UserController::class, 'deactivate'])->name('admin.users.deactivate');
        Route::patch('/users/{user}/reactivate', [UserController::class, 'reactivate'])->name('admin.users.reactivate');

        // Rotas de Cursos
        Route::get('/courses', [CourseController::class, 'index'])->name('admin.courses.index');
        Route::get('/courses/create', [CourseController::class, 'create'])->name('admin.courses.create');
        Route::post('/courses', [CourseController::class, 'store'])->name('admin.courses.store');
        Route::get('/courses/{id}/edit', [CourseController::class, 'edit'])->name('admin.courses.edit');
        Route::put('/courses/{id}', [CourseController::class, 'update'])->name('admin.courses.update');
        Route::delete('/courses/{id}', [CourseController::class, 'destroy'])->name('admin.courses.destroy');
        Route::patch('/courses/{id}/restore', [CourseController::class, 'restore'])->name('admin.courses.restore');

        // Rotas de Tipos de Estágio
        Route::get('/internship-types', [InternshipTypeController::class, 'index'])->name('admin.internship-types.index');
        Route::get('/internship-types/create', [InternshipTypeController::class, 'create'])->name('admin.internship-types.create');
        Route::post('/internship-types', [InternshipTypeController::class, 'store'])->name('admin.internship-types.store');
        Route::get('/internship-types/{id}/edit', [InternshipTypeController::class, 'edit'])->name('admin.internship-types.edit');
        Route::put('/internship-types/{id}', [InternshipTypeController::class, 'update'])->name('admin.internship-types.update');
        Route::delete('/internship-types/{id}', [InternshipTypeController::class, 'destroy'])->name('admin.internship-types.destroy');
        Route::patch('/internship-types/{id}/restore', [InternshipTypeController::class, 'restore'])->name('admin.internship-types.restore');

        // Rotas de autenticação com Google
        Route::get('/google/redirect', [GoogleAuthController::class, 'redirect'])->name('google.redirect');
        Route::get('/google/callback', [GoogleAuthController::class, 'callback'])->name('google.callback');

        // Rota de sincronização de dados
        Route::post('/sync/data', SyncDataController::class)->name('admin.sync.data');

        // Rotas de Estágios
        Route::get('/internships', [InternshipController::class, 'index'])->name('admin.internships.index');
        Route::get('/internships/{internship}', [InternshipController::class, 'edit'])->name('admin.internships.edit');
        Route::put('/internships/{internship}', [InternshipController::class, 'update'])->name('admin.internships.update');
        Route::delete('/internships/{internship}', [InternshipController::class, 'destroy'])->name('admin.internships.destroy');
        Route::patch('/internships/{id}/restore', [InternshipController::class, 'restore'])->name('admin.internships.restore');
        Route::get('/api/companies', [InternshipController::class, 'getCompanies'])->name('admin.internships.companies-by-cnpj');

        // Rota de geração de documentos de estágio
        Route::post('/estagios/{internshipId}/gerar-documento', InternshipDocumentController::class)->name('admin.internships.documents.generate');

        // rotas de partes concendentes
        Route::get('/companies', [CompanyController::class, 'index'])->name('admin.companies.index');
        Route::get('/companies/create', [CompanyController::class, 'create'])->name('admin.companies.create');
        Route::post('/companies', [CompanyController::class, 'store'])->name('admin.companies.store');
        Route::post('/companies/import', [CompanyController::class, 'import'])->name('admin.companies.import');
        Route::get('/companies/{company}/edit', [CompanyController::class, 'edit'])->name('admin.companies.edit');
        Route::put('/companies/{company}', [CompanyController::class, 'update'])->name('admin.companies.update');
        Route::delete('/companies/{company}', [CompanyController::class, 'destroy'])->name('admin.companies.destroy');
        Route::patch('/companies/{id}/restore', [CompanyController::class, 'restore'])->name('admin.companies.restore');
    });

    // Rotas para Coordenadores e Orientadores
    Route::middleware(['can:view-internships'])->group(function () {
        Route::get('/estagios', [InternshipViewController::class, 'index'])->name('internship-view.index');
        Route::get('/estagios/{internship}', [InternshipViewController::class, 'show'])->name('internship-view.show');
    });
});
