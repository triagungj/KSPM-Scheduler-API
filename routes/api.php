<?php

use App\Http\Controllers\API\AuthController;
use App\Http\Controllers\API\ProfileController;
use App\Http\Controllers\API\ScheduleRequestController;
use App\Http\Controllers\API\SesiController;
use App\Http\Controllers\API\FileController;
use App\Http\Controllers\API\ValidationController;
use App\Models\ScheduleRequest;
use Illuminate\Support\Facades\Route;

// * AUTH
Route::post('auth/register', [AuthController::class, 'register']);
Route::post('auth/login', [AuthController::class, 'login']);
Route::group(['middleware' => ['auth:sanctum']], function () {
    Route::post('auth/change-password', [AuthController::class, 'changePassword']);
    Route::post('auth/logout', [AuthController::class, 'logout']);
});

// *FILE
Route::get('image/{imagename}', [FileController::class, 'image']);
Route::get('file/{fileName}', [FileController::class, 'file']);

// * PROFILE
Route::group(['middleware' => ['auth:sanctum']], function () {
    Route::get('profile', [ProfileController::class, 'index']);
    Route::post('profile/edit', [ProfileController::class, 'edit']);
});

// * SESSION

// * SCHEDULE REQUEST
Route::group(['middleware' => ['auth:sanctum']], function () {
    Route::get('request/session', [ScheduleRequestController::class, 'getListSession']);
    Route::get('request', [ScheduleRequestController::class, 'getListMySession']);
    Route::post('request/save', [ScheduleRequestController::class, 'saveRequest']);
    Route::post('request/send', [ScheduleRequestController::class, 'requestSchedule']);
    Route::get('request/postpone', [ScheduleRequestController::class, 'postpone']);
});

// * VALIDATION
Route::group(['middleware' => ['auth:sanctum']], function () {
    Route::get('validation/count', [
        ValidationController::class, 'getListCount'
    ]);
    Route::post('validation/list', [ValidationController::class, 'getListValidation']);
    Route::get('validation/{id}', [ValidationController::class, 'getDetailValidation']);
    Route::post('validation/reject', [ValidationController::class, 'rejectScheduleRequest']);
    Route::post('validation/accept/{id}', [ValidationController::class, 'acceptScheduleRequest']);
});
