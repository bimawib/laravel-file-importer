<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\PermissionController;
use App\Http\Controllers\RoleController;
use App\Http\Controllers\UserController;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Here is where you can register API routes for your application. These
| routes are loaded by the RouteServiceProvider and all of them will
| be assigned to the "api" middleware group. Make something great!
|
*/

Route::post('/register', [AuthController::class, 'register']);
Route::post('/login', [AuthController::class, 'login']);

Route::middleware(['jwt'])->group(function () {
    Route::get('/login-information', [AuthController::class, 'userInfo']);
    Route::post('/logout', [AuthController::class, 'logout']);
}); 

Route::middleware(['jwt', 'permission:users:manage'])->prefix('admin')->group(function () {
    Route::get('/permissions', [PermissionController::class, 'listPermissions']);
    Route::post('/permissions', [PermissionController::class, 'createPermission']);
    Route::put('/permissions/{permission}', [PermissionController::class, 'updatePermission']);

    Route::get('/roles', [RoleController::class, 'listRoles']);
    Route::post('/roles', [RoleController::class, 'createRole']);
    Route::put('/roles/{role}', [RoleController::class, 'updateRole']);

    Route::post('/roles/assign', [RoleController::class, 'assignRoleToUser']);
    Route::get('/users', [UserController::class, 'listUsers']);
});