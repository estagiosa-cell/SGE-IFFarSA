<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\ShowWelcomePageController;

Route::get('/', ShowWelcomePageController::class)->name('welcome');
