<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Api\AuthController;
use App\Http\Controllers\Api\UserController;
use App\Http\Controllers\Api\FolderController;



Route::post('/register', [AuthController::class, 'register']);
Route::post('/login', [AuthController::class, 'login']);

Route::middleware('auth:api')->group(function () {
    Route::post('/logout', [AuthController::class, 'logout']);
    
    Route::get('/user/me', [UserController::class, 'me']);
    Route::put('/user/me', [UserController::class, 'update']);
    Route::delete('/user/me', [UserController::class, 'destroy']);

    Route::post('/folders', [FolderController::class, 'store']);
    Route::post('/folders/{id}/folders', [FolderController::class, 'storeSubfolder']);
});

