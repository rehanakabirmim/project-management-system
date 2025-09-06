<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Api\Auth\AuthController;
use App\Http\Controllers\Api\User\UserController;
use App\Http\Controllers\Api\RolePermission\RoleController;
use App\Http\Controllers\Api\RolePermission\PermissionController;
use App\Http\Controllers\Api\RolePermission\UserRoleController;
use App\Http\Controllers\Api\Department\DepartmentController;
use App\Http\Controllers\Api\Client\ClientController;
use App\Http\Controllers\Api\Project\ProjectController;
use App\Http\Controllers\Api\ProjectPhase\ProjectPhaseController;
use App\Http\Controllers\Api\ProjectMember\ProjectMemberController;
use App\Http\Controllers\API\Task\TaskController;
use App\Http\Controllers\API\User\UserOffdayController;



// -----------------------------
// Public Auth routes
// -----------------------------
Route::prefix('auth')->group(function () {
    Route::post('/register', [AuthController::class, 'register']);
    Route::post('/login', [AuthController::class, 'login']);
    Route::post('/forgot-password', [AuthController::class, 'forgotPassword']);
    Route::post('/reset-password', [AuthController::class, 'resetPassword']);
   
});

// -----------------------------
// Protected routes (requires login)
// -----------------------------
Route::middleware('auth:sanctum')->group(function () {

    // Logout
    Route::post('/auth/logout', [AuthController::class, 'logout']);

    // User profile
    Route::get('/user/profile', [UserController::class, 'profile']);
    Route::put('/user/update', [UserController::class, 'update']);

    // -----------------------------
    // Role & Permission Routes
    // -----------------------------
    Route::prefix('role-permission')->group(function () {

        // Roles
        Route::get('roles', [RoleController::class, 'index']);
        Route::post('roles', [RoleController::class, 'store']);
        Route::put('roles/{role}', [RoleController::class, 'update']);
        Route::delete('roles/{role}', [RoleController::class, 'destroy']);

        // Permissions
        Route::get('permissions', [PermissionController::class, 'index']);
        Route::post('permissions', [PermissionController::class, 'store']);

        // Assign roles/permissions to users
        Route::post('users/{user}/assign-role', [UserRoleController::class, 'assignRole']);
        Route::post('users/{user}/assign-permission', [UserRoleController::class, 'assignPermission']);
        Route::get('users/{user}/roles', [UserRoleController::class, 'getUserRoles']);
        Route::get('users/{user}/permissions', [UserRoleController::class, 'getUserPermissions']);
    });

    // -----------------------------
    // Department Routes
    // -----------------------------
    Route::prefix('department')->group(function () {
        Route::get('/', [DepartmentController::class, 'index']);          
        Route::post('/', [DepartmentController::class, 'store']);         
        Route::get('/{id}', [DepartmentController::class, 'show']);       
        Route::put('/{id}', [DepartmentController::class, 'update']);     
        Route::delete('/{id}', [DepartmentController::class, 'destroy']); 
    });

 // -----------------------------
    // Client Routes
    // -----------------------------
    Route::prefix('client')->group(function () {
        Route::get('/', [ClientController::class, 'index']);          
        Route::post('/', [ClientController::class, 'store']);         
        Route::get('/{id}', [ClientController::class, 'show']);       
        Route::put('/{id}', [ClientController::class, 'update']);     
        Route::delete('/{id}', [ClientController::class, 'destroy']); 
        });

 // -----------------------------
    // Project Routes
    // -----------------------------

    Route::prefix('projects')->group(function () {
        Route::get('/', [ProjectController::class, 'index']);          // GET all projects
        Route::post('/', [ProjectController::class, 'store']); //new project create
        Route::get('/deadline', [ProjectController::class, 'deadlines']);//project dealine
        Route::get('/{id}', [ProjectController::class, 'show']);       // GET single project
        Route::put('/{id}', [ProjectController::class, 'update']);     // PUT update project
        Route::delete('/{id}', [ProjectController::class, 'destroy']); // DELETE project
       //project dealine with reamings days
    });


 // -----------------------------
    // ProjectPhase Routes
    // -----------------------------

    Route::prefix('project-phases')->group(function () {
        
        Route::get('/', [ProjectPhaseController::class, 'index']);            
        Route::post('/', [ProjectPhaseController::class, 'store']); 
        Route::post('/bulk', [ProjectPhaseController::class, 'storeMultiple']); 
        Route::get('/{id}', [ProjectPhaseController::class, 'show']);       
        Route::put('/{id}', [ProjectPhaseController::class, 'update']);     
        Route::delete('/{id}', [ProjectPhaseController::class, 'destroy']); 
    });


 // -----------------------------
    // project-members Routes
    // -----------------------------

    Route::prefix('project-members')->group(function () {
        Route::get('/', [ProjectMemberController::class,'index']);          
        Route::post('/', [ProjectMemberController::class,'store']);         
        Route::post('/bulk', [ProjectMemberController::class, 'storeMultiple']); 
        Route::get('/{id}', [ProjectMemberController::class,'show']);       
        Route::put('/{id}', [ProjectMemberController::class,'update']);     
        Route::delete('/{id}', [ProjectMemberController::class,'destroy']); 
    });


 // -----------------------------
    // Task  Routes
    // -----------------------------


    Route::prefix('tasks')->group(function () {
        Route::get('/', [TaskController::class, 'index']);
        Route::post('/', [TaskController::class, 'store']);
        Route::get('/{task}', [TaskController::class, 'show']);
        Route::put('/{task}', [TaskController::class, 'update']);
        Route::delete('/{task}', [TaskController::class, 'destroy']);
    });


    // -----------------------------
    // UserOffday  Routes
    // -----------------------------

    Route::prefix('user-offdays')->group(function () {
        Route::get('/', [UserOffdayController::class, 'index']);            
        Route::post('/', [UserOffdayController::class, 'store']);           
        Route::get('/{userOffday}', [UserOffdayController::class, 'show']); 
        Route::put('/{userOffday}', [UserOffdayController::class, 'update']); 
        Route::delete('/{userOffday}', [UserOffdayController::class, 'destroy']); 
    });


    

});
