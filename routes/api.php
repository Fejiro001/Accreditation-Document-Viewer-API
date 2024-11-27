<?php

use App\Http\Controllers\GoogleDriveController;
use App\Http\Controllers\UserController;
use App\Http\Controllers\UserFolderController;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\AuthController;

Route::get('/google/redirect', [AuthController::class, 'redirectToProvider']);

Route::get('/google/callback', [AuthController::class, 'handleProviderCallback']);

Route::get('/user', [AuthController::class, 'getUserName']);

Route::get('/folders/{parentFolderId?}', [GoogleDriveController::class, 'listAllFolders']);

Route::middleware(['auth:api', 'admin'])->group(function() {
    Route::get('/users', [UserController::class, 'index']);
    Route::post('/users', [UserController::class, 'store']);
    Route::post('/users/{user}/folders', [UserFolderController::class, 'store']);
    Route::delete('/users/{user}/folders/{folder}', [UserFolderController::class, 'destroy']);
});

Route::post('/logout', [AuthController::class, 'logout']);