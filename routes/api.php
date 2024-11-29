<?php

use App\Http\Controllers\{
    GoogleDriveController,
    UserController,
    UserFolderController,
    AuthController
};
use App\Http\Middleware\AddTokenFromCookie;
use App\Http\Middleware\AdminMiddleware;
use Illuminate\Support\Facades\Route;

// Public routes
Route::get('/login', [AuthController::class, 'redirectToProvider']);

// Google OAuth callback
Route::get('/google/callback', [AuthController::class, 'handleProviderCallback']);

// Authenticated user routes
Route::middleware([AddTokenFromCookie::class, 'auth:api'])->group(function () {
    Route::get('/user', [AuthController::class, 'getUserName']);
    Route::post('/logout', [AuthController::class, 'logout']);
    Route::get('/folders/{parentFolderId?}', [GoogleDriveController::class, 'listAllFolders']);
});

// Admin routes
Route::middleware([AddTokenFromCookie::class, 'auth:api', AdminMiddleware::class])->group(function () {
    Route::apiResource('users', UserController::class)->only(['index', 'store']);
    Route::post('/users/{user}/folders', [UserFolderController::class, 'store']);
    Route::delete('/users/{user}/folders/{folder}', [UserFolderController::class, 'destroy']);
});
