<?php

use App\Http\Controllers\{
    GoogleDriveController,
    UserController,
    AuthController
};
use App\Http\Middleware\AddTokenFromCookie;
use App\Http\Middleware\CheckRole;
use Illuminate\Support\Facades\Route;

// Public routes
Route::get('/login', [AuthController::class, 'redirectToProvider']);

// Google OAuth callback
Route::get('/google/callback', [AuthController::class, 'handleProviderCallback']);

// Authenticated user routes
Route::middleware([AddTokenFromCookie::class, 'auth:api'])->group(function () {
    Route::get('/user', [AuthController::class, 'getUserInfo']);
    Route::post('/logout', [AuthController::class, 'logout']);

    Route::get('/folders/{parentFolderId?}', [GoogleDriveController::class, 'listAllFolders']);
});

// Admin routes
Route::middleware([AddTokenFromCookie::class, 'auth:api', 'role:admin'])->group(function () {
    Route::get('/users', [UserController::class, 'index']);
    Route::post('/users', [UserController::class, 'store']);
    Route::put('/users/{user}', [UserController::class, 'update']);
    Route::get('/users/{user}', [UserController::class, 'show']);
    Route::delete('/users/{user}', [UserController::class, 'destroy']);
});
