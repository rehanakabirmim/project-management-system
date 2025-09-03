<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Api\Auth\AuthController;
use App\Http\Controllers\Api\User\UserController;


// Public Auth routes
Route::prefix('auth')->group(function () {
    Route::post('/register', [AuthController::class, 'register']);
    Route::post('/login', [AuthController::class, 'login']);
    Route::post('/forgot-password', [AuthController::class, 'forgotPassword']);
    Route::post('/reset-password', [AuthController::class, 'resetPassword']);
});

// Protected routes (needs login)
Route::middleware('auth:sanctum')->group(function () {

    // Logout still under auth
    Route::post('/auth/logout', [AuthController::class, 'logout']);

    // User profile routes (no auth prefix needed)
    Route::get('/user/profile', [UserController::class, 'profile']);
    Route::put('/user/update', [UserController::class, 'update']);
});



