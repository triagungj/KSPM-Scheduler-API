<?php

use App\Http\Controllers\API\AuthController;
use App\Http\Controllers\API\ProfileController;
use App\Http\Controllers\API\SesiController;
use GuzzleHttp\Psr7\Request;
use Illuminate\Support\Facades\Route;

// * AUTH
Route::post('auth/register', [AuthController::class, 'register']);
Route::post('login', [AuthController::class, 'login']);
Route::group(['middleware' => ['auth:sanctum']], function () {
    Route::post('auth/change-password', [AuthController::class, 'changePassword']);
    Route::post('auth/logout', [AuthController::class, 'logout']);
});

// * PROFILE
Route::group(['middleware' => ['auth:sanctum']], function () {
    Route::get('profile', [ProfileController::class, 'index']);
    Route::post('profile/edit', [ProfileController::class, 'edit']);
});

// * SESSION
Route::get('schedule/session', [SesiController::class, 'index']);
