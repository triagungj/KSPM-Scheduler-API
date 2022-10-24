<?php

use App\Http\Controllers\API\AuthController;
use App\Http\Controllers\API\ProfileController;
use App\Http\Controllers\API\ScheduleRequestController;
use App\Http\Controllers\API\FileController;
use App\Http\Controllers\API\NewsController;
use App\Http\Controllers\API\ScheduleController;
use App\Http\Controllers\API\ValidationController;
use Illuminate\Support\Facades\Route;

// * AUTH
Route::get('auth/contact', [AuthController::class, 'getAdminContact']);
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
    Route::get('validation/count', [ValidationController::class, 'getListCount']);
    Route::post('validation/list', [ValidationController::class, 'getListValidation']);
    Route::get('validation/{id}', [ValidationController::class, 'getDetailValidation']);
    Route::post('validation/reject', [ValidationController::class, 'rejectScheduleRequest']);
    Route::post('validation/accept/{id}', [ValidationController::class, 'acceptScheduleRequest']);
});

// * SCHEDULE
Route::group(['middleware' => ['auth:sanctum']], function () {
    Route::get('schedule', [ScheduleController::class, 'getListMySchedule']);
    Route::get('schedule/list', [ScheduleController::class, 'getListSchedule']);
    Route::get('schedule/session/{id}', [ScheduleController::class, 'getListDetailSchedule']);
    Route::post('schedule/detail', [ScheduleController::class, 'getDetailSchedule']);
    Route::get('schedule/generate', [ScheduleController::class, 'generateSchedule']);
});

// * NEWS
Route::group(['middleware' => ['auth:sanctum']], function () {
    Route::get('news', [NewsController::class, 'index']);
    Route::get('news/{id}', [NewsController::class, 'detail']);
});
