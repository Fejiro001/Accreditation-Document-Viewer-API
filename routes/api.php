<?php

use App\Models\User;
use Illuminate\Http\Request;
use App\Http\Resources\UserResource;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\FileController;

Route::get('/user', function () {
    return UserResource::collection(User::all());
});

Route::get('/auth/{provider}', [AuthController::class, 'redirectToProvider']);

Route::get('/auth/{provider}/callback', [AuthController::class, 'handleProviderCallback']);

Route::middleware(['auth:api', 'role:admin'])->group(function() {
    Route::post('/folders/{id}/add-user', [FileController::class, 'addUser']);
});

Route::middleware('auth:api')->get('/folders/{id}/files', [FileController::class, 'listFiles']);