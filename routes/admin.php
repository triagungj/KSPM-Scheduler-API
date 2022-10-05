<?php

use App\Http\Controllers\Admin\AdminAuthController;
use App\Http\Controllers\API\NewsController;
use Illuminate\Support\Facades\Route;

Route::group(['prefix' => 'admin', 'namespace' => 'Admin'], function () {
    Route::get('/login', [AdminAuthController::class, 'getLogin'])->name('adminLogin');
    Route::post('/login', [AdminAuthController::class, 'loginAdmin'])->name('adminLoginPost');    
});

Route::group(['prefix' => 'admin', 'namespace' => 'Admin', 'middleware' => ['auth:sanctum']], function () {
    Route::post('logout', [AdminAuthController::class, 'logoutAdmin']);
    Route::get('profile', [AdminAuthController::class, 'getProfileAdmin']);
    Route::post('update', [AdminAuthController::class, 'updateAdmin']);
    Route::post('change-password', [AdminAuthController::class, 'changePasswordAdmin']);

    Route::get('news', [NewsController::class, 'index']);
    Route::get('news/{id}', [NewsController::class, 'detail']);
    Route::post('news', [NewsController::class, 'create']);
    Route::put('news', [NewsController::class, 'update']);
    Route::delete('news/{id}', [NewsController::class, 'delete']);
});