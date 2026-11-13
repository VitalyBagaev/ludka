<?php

use App\Http\Controllers\Api\AdminController;
use App\Http\Controllers\Api\GameController;
use App\Http\Controllers\Api\ManagerController;
use App\Http\Controllers\UserController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

Route::post('/register', [UserController::class, 'registerUser']);
Route::post('/login', [UserController::class, 'authUser']);

Route::get('/cases', [GameController::class, 'cases']);
Route::get('/cases/{id}', [GameController::class, 'showCase']);
Route::get('/skins', [GameController::class, 'skins']);
Route::get('/promo', [GameController::class, 'promo']);
Route::get('/live-drops', [GameController::class, 'liveDrops']);
Route::get('/users/{user}', [UserController::class, 'publicProfile']);
Route::get('/secret-case', [GameController::class, 'secretCase']);
Route::get('/battles', [GameController::class, 'battles']);

Route::middleware('auth:sanctum')->group(function () {
    Route::get('/user', fn (Request $request) => $request->user()->toFrontend());
    Route::get('/user/profile', [UserController::class, 'profile']);
    Route::post('/secret-case/open', [GameController::class, 'openSecretCase']);
    Route::post('/user/avatar', [UserController::class, 'updateAvatar']);
    Route::delete('/user/avatar', [UserController::class, 'deleteAvatar']);
    Route::post('/logout', [UserController::class, 'logoutUser']);

    Route::get('/inventory', [GameController::class, 'inventory']);
    Route::post('/inventory/sell-all', [GameController::class, 'sellAllItems']);
    Route::post('/inventory/{id}/sell', [GameController::class, 'sellItem']);
    Route::post('/balance/deposit', [GameController::class, 'deposit']);
    Route::post('/inventory/withdraw', [GameController::class, 'withdrawSkins']);
    Route::post('/cases/{id}/open', [GameController::class, 'openCase']);
    Route::post('/upgrade', [GameController::class, 'upgrade']);
    Route::post('/contract', [GameController::class, 'contract']);

    Route::middleware('role:manager')->prefix('manager')->group(function () {
        Route::get('/stats', [ManagerController::class, 'stats']);
        Route::get('/cases', [ManagerController::class, 'cases']);
        Route::post('/cases/{gameCase}/featured', [ManagerController::class, 'toggleFeatured']);
        Route::get('/live-drops', [ManagerController::class, 'liveDrops']);
    });

    Route::middleware('role:admin,super_admin')->prefix('admin')->group(function () {
        Route::get('/stats', [AdminController::class, 'stats']);

        Route::get('/skins', [AdminController::class, 'skins']);
        Route::post('/skins', [AdminController::class, 'storeSkin']);
        Route::post('/skins/{skin}', [AdminController::class, 'updateSkin']);
        Route::post('/skins/{skin}/image', [AdminController::class, 'uploadSkinImage']);
        Route::delete('/skins/{skin}', [AdminController::class, 'deleteSkin']);

        Route::get('/cases', [AdminController::class, 'cases']);
        Route::get('/cases/{gameCase}', [AdminController::class, 'showCase']);
        Route::post('/cases', [AdminController::class, 'storeCase']);
        Route::post('/cases/{gameCase}', [AdminController::class, 'updateCase']);
        Route::post('/cases/{gameCase}/image', [AdminController::class, 'uploadCaseImage']);
        Route::delete('/cases/{gameCase}', [AdminController::class, 'deleteCase']);

        Route::get('/withdrawals', [AdminController::class, 'withdrawals']);
        Route::post('/withdrawals/{withdrawal}/status', [AdminController::class, 'updateWithdrawalStatus']);
    });

    Route::middleware('role:super_admin')->prefix('admin')->group(function () {
        Route::get('/users', [UserController::class, 'getUsers']);
        Route::post('/users/{user}/role', [UserController::class, 'updateRole']);
        Route::delete('/users/{user}', [UserController::class, 'deleteUser']);
    });
});
