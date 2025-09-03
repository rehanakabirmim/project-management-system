<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Api\Auth\AuthController;
use App\Http\Controllers\Api\User\UserController;
use App\Http\Controllers\Api\RolePermission\RoleController;
use App\Http\Controllers\Api\RolePermission\PermissionController;
use App\Http\Controllers\Api\RolePermission\UserRoleController;


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

    //Role & Permission

    // Role
    Route::get('roles', [RoleController::class,'index']);
    Route::post('roles', [RoleController::class,'store']);
    Route::put('roles/{role}', [RoleController::class,'update']);
    Route::delete('roles/{role}', [RoleController::class,'destroy']);

    // Permission
    Route::get('permissions', [PermissionController::class,'index']);
    Route::post('permissions', [PermissionController::class,'store']);

    // User role/permission
    Route::post('users/{user}/assign-role', [UserRoleController::class,'assignRole']);
    Route::post('users/{user}/assign-permission', [UserRoleController::class,'assignPermission']);
    Route::get('users/{user}/roles', [UserRoleController::class,'getUserRoles']);
    Route::get('users/{user}/permissions', [UserRoleController::class,'getUserPermissions']);
});



