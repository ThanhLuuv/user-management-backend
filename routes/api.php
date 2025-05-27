<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Api\AuthController;
use App\Http\Controllers\Api\UserController;


Route::prefix('auth')->group(function () {
    Route::post('register', [AuthController::class, 'register']);
    Route::post('login', [AuthController::class, 'login']);
    // Route::post('forgot-password', [AuthController::class, 'forgotPassword']);
    // Route::post('reset-password', [AuthController::class, 'resetPassword']);
});

Route::middleware('auth:api')->group(function () {

    Route::prefix('auth')->group(function () {
        // Route::post('logout', [AuthController::class, 'logout']);
        Route::post('refresh', [AuthController::class, 'refresh']);
        Route::get('me', [AuthController::class, 'me']);
        // Route::put('change-password', [AuthController::class, 'changePassword']);
    });

    Route::prefix('users')->group(function () {
        Route::get('profile', [UserController::class, 'profile']);
        Route::put('profile', [UserController::class, 'updateProfile']);
    });

    Route::middleware('role:admin')->prefix('admin')->group(function () {
        Route::get('users', [UserController::class, 'index']);              // Danh sách tất cả user
        Route::get('users/{account}', [UserController::class, 'show']);     // Xem user theo ID
        Route::put('users/{account}', [UserController::class, 'update']);   // Cập nhật user bất kỳ
        Route::delete('users/{account}', [UserController::class, 'destroy']);// Xóa user
    });

    // User chỉ được cập nhật chính mình (có thể để route chung, kiểm tra trong controller hoặc policy)
    Route::put('users/{account}', [UserController::class, 'update'])
        ->where('account', '[0-9]+');
});
