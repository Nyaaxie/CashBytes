<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Api\ApiAuthController;
use App\Http\Controllers\Api\ApiWalletController;
use App\Http\Controllers\Api\ApiTransferController;
use App\Http\Controllers\Api\ApiSavingsController;
use App\Http\Controllers\Api\ApiLoadController;
use App\Http\Controllers\Api\ApiBillsController;
use App\Http\Controllers\Api\ApiTransactionController;
use App\Http\Controllers\Api\ApiProfileController;

// ── PUBLIC ───────────────────────────────────────────────────────
Route::prefix('v1')->group(function () {

    Route::post('/register', [ApiAuthController::class, 'register']);
    Route::post('/login',    [ApiAuthController::class, 'login'])
        ->middleware('throttle:5,1'); // 5 attempts per minute

    // ── AUTHENTICATED ────────────────────────────────────────────
    Route::middleware('auth:sanctum')->group(function () {

        // Auth
        Route::post('/logout',          [ApiAuthController::class, 'logout']);
        Route::post('/logout-all',      [ApiAuthController::class, 'logoutAll']);

        // Wallet
        Route::get('/wallet',           [ApiWalletController::class, 'index']);

        // Fund Transfer
        Route::post('/transfer',        [ApiTransferController::class, 'send']);

        // Savings Goals
        Route::get('/savings',                      [ApiSavingsController::class, 'index']);
        Route::post('/savings',                     [ApiSavingsController::class, 'store']);
        Route::get('/savings/{goalId}',             [ApiSavingsController::class, 'show']);
        Route::post('/savings/{goalId}/allocate',   [ApiSavingsController::class, 'allocate']);

        // Buy Load
        Route::get('/load/networks',    [ApiLoadController::class, 'networks']);
        Route::post('/load',            [ApiLoadController::class, 'buy']);

        // Pay Bills
        Route::get('/bills',            [ApiBillsController::class, 'index']);
        Route::get('/bills/{billerId}', [ApiBillsController::class, 'show']);
        Route::post('/bills/pay',       [ApiBillsController::class, 'pay']);

        // Transactions
        Route::get('/transactions',     [ApiTransactionController::class, 'index']);

        // Profile
        Route::get('/profile',          [ApiProfileController::class, 'index']);
        Route::put('/profile',          [ApiProfileController::class, 'update']);
        Route::put('/profile/password', [ApiProfileController::class, 'updatePassword']);
    });
});