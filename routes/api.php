<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Api\v1\AuthController;
use App\Http\Controllers\Api\v1\UserController;
use App\Http\Controllers\Api\v1\FolderController;
use App\Http\Controllers\Api\v1\ResourceController;
use App\Http\Controllers\Api\v1\TagController;
use App\Http\Controllers\Api\v1\AdminController;

Route::prefix('v1')->group(function () {
    Route::post('/register', [AuthController::class, 'register']);
    Route::post('/login', [AuthController::class, 'login']);

    Route::middleware('auth:api')->group(function () {
        Route::post('/logout', [AuthController::class, 'logout']);
    
        Route::get('/user/me', [UserController::class, 'me']);
        Route::put('/user/me', [UserController::class, 'update']);
        Route::delete('/user/me', [UserController::class, 'destroy']);

        Route::post('/folders', [FolderController::class, 'store']);
        Route::get('/folders', [FolderController::class, 'index']);
        Route::get('/folders/{id}', [FolderController::class, 'show']);
        Route::put('/folders/{id}', [FolderController::class, 'update']);
        Route::delete('/folders/{id}', [FolderController::class, 'destroy']);

        Route::post('/folders/{id}/resources', [ResourceController::class, 'store']);

        Route::get('/resources', [ResourceController::class, 'index']);
        Route::get('/resources/{id}', [ResourceController::class, 'show']);
        Route::put('/resources/{id}', [ResourceController::class, 'update']);
        Route::delete('/resources/{id}', [ResourceController::class, 'destroy']);

        Route::get('tags', [TagController::class, 'index']);
    });

    Route::middleware(['auth:api', 'isAdmin'])->group(function () {
        Route::get('/admin/users', [AdminController::class, 'index']);
        Route::delete('/admin/users/{id}', [AdminController::class, 'destroy']);
        Route::get('/admin/stats', [AdminController::class, 'stats']);
    }); 
});
