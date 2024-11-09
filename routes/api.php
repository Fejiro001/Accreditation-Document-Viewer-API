<?php

use App\Models\User;
use Illuminate\Http\Request;
use App\Http\Resources\UserResource;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\FolderController;

Route::get('/user', function () {
    return UserResource::collection(User::all());
});

Route::get('/auth/{provider}', [AuthController::class, 'redirectToProvider']);

Route::get('/auth/{provider}/callback', [AuthController::class, 'handleProviderCallback']);

Route::middleware(['auth:api', 'role:admin'])->group(function () {
    Route::post('/folders/{folderId}/add-user', [FolderController::class, 'addUser'])->name('folders.addUser');
});

Route::middleware(['auth:api', 'role:user'])->group(function () {
    Route::get('/folders/{folderId}', [FolderController::class, 'viewFolder'])->name('folders.viewFolder');
});
