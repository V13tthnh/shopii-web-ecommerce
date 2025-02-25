<?php

use App\Http\Controllers\Admin\AdminController;
use App\Http\Controllers\Auth\AuthController;
use App\Http\Controllers\Auth\PermissionController;
use App\Http\Controllers\Auth\RoleController;
use Illuminate\Support\Facades\Route;

Route::prefix('admin')->group(function(){
    Route::post('/login', [AuthController::class, 'login']);
});

Route::middleware('auth:sanctum')->prefix('admin')->group(function(){
    Route::get('/me', [AuthController::class, 'me']);
    Route::post('/logout', [AuthController::class, 'logout']);

    Route::middleware('role:admin')->group(function(){
        Route::middleware('admin.permission:manage roles')->group(function(){
            Route::apiResource('roles', RoleController::class);
        });

        Route::middleware('admin.permission:manage permissions')->group(function () {
            Route::apiResource('permissions', PermissionController::class)->only(['index', 'store']);
        });

        Route::middleware('admin.permission:manage admins')->group(function () {
            Route::apiResource('users', AdminController::class);
        });
    });
});
