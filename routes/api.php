<?php

use App\Http\Controllers\API\AuthController;
use App\Http\Controllers\API\ProfileController;
use Illuminate\Support\Facades\Route;

// * AUTH
Route::post('auth/register', [AuthController::class, 'register']);
Route::post('auth/login', [AuthController::class, 'login']);
Route::group(['middleware' => ['auth:sanctum']], function () {
    Route::post('auth/logout', [AuthController::class, 'logout']);
});

// * PROFILE
Route::group(['middleware' => ['auth:sanctum']], function () {
    Route::get('profile', [ProfileController::class, 'index']);
});
