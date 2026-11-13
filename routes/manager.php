<?php

use App\Http\Controllers\Manager\CaseController as ManagerCaseController;
use App\Http\Controllers\Manager\DashboardController as ManagerDashboardController;
use App\Http\Controllers\Manager\LiveDropController;
use App\Http\Controllers\Manager\UserController as ManagerUserController;
use Illuminate\Support\Facades\Route;

Route::middleware(['auth', 'role:admin,manager'])
    ->prefix('manager')
    ->name('manager.')
    ->group(function () {
        Route::get('/', [ManagerDashboardController::class, 'index'])->name('dashboard');
        Route::get('/cases', [ManagerCaseController::class, 'index'])->name('cases.index');
        Route::get('/cases/{gameCase}/edit', [ManagerCaseController::class, 'edit'])->name('cases.edit');
        Route::put('/cases/{gameCase}', [ManagerCaseController::class, 'update'])->name('cases.update');
        Route::patch('/cases/{gameCase}/featured', [ManagerCaseController::class, 'toggleFeatured'])->name('cases.featured');
        Route::get('/live-drops', [LiveDropController::class, 'index'])->name('live-drops.index');
        Route::get('/users', [ManagerUserController::class, 'index'])->name('users.index');
    });
