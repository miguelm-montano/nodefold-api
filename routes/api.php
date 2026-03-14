<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Api\AuthController;
use App\Http\Controllers\Api\UserController;
use App\Http\Controllers\Api\FolderController;
use App\Http\Controllers\Api\ResourceController;

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
    Route::delete('folders/{id}', [FolderController::class, 'destroy']);

    Route::post('/folders/{id}/resources', [ResourceController::class, 'store']);
    
    Route::get('/resources', [ResourceController::class, 'index']);

});

