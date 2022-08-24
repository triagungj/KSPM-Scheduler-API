<?php

use App\Http\Controllers\API\AuthController;
use App\Http\Controllers\API\ProfileController;
use App\Http\Controllers\API\ScheduleRequestController;
use App\Http\Controllers\API\SesiController;
use App\Models\ScheduleRequest;
use Illuminate\Support\Facades\Route;

// * AUTH
Route::post('auth/register', [AuthController::class, 'register']);
Route::post('auth/login', [AuthController::class, 'login']);
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


// * SCHEDULE REQUEST
Route::group(['middleware' => ['auth:sanctum']], function () {
    Route::post('schedule/save', [ScheduleRequestController::class, 'saveRequest']);
    Route::post('schedule/request', [ScheduleRequestController::class, 'requestSchedule']);
});
