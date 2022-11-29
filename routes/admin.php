<?php

use App\Http\Controllers\Admin\AccountController;
use App\Http\Controllers\Admin\AdminAuthController;
use App\Http\Controllers\Admin\JabatanCategoryController;
use App\Http\Controllers\Admin\JabatanController;
use App\Http\Controllers\Admin\PertemuanController;
use App\Http\Controllers\API\NewsController;
use App\Http\Controllers\API\ScheduleController;
use App\Http\Controllers\Admin\SesiController;
use App\Http\Controllers\API\ValidationController;
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

    Route::get('account/partisipans', [
        AccountController::class,
        'getListPartisipan'
    ]);
    Route::post('account/partisipan', [AccountController::class, 'createPartisipan']);
    Route::get('account/partisipan/{id}', [AccountController::class, 'getPartisipan']);
    Route::put('account/partisipan/{id}', [AccountController::class, 'updatePartisipan']);
    Route::delete('account/partisipan/{id}', [AccountController::class, 'deletePartisipan']);
    Route::delete('account/partisipans', [AccountController::class, 'deleteAllPartisipan']);
    Route::post('account/partisipans', [AccountController::class, 'generatePartisipan']);

    Route::get('account/petugas', [
        AccountController::class,
        'getListPetugas'
    ]);
    Route::post('account/petugas', [AccountController::class, 'createPetugas']);
    Route::get('account/petugas/{id}', [AccountController::class, 'getPetugas']);
    Route::put('account/petugas/{id}', [AccountController::class, 'updatePetugas']);
    Route::delete('account/petugas/{id}', [AccountController::class, 'deletePetugas']);
    Route::delete('account/petugas', [AccountController::class, 'deleteAllPetugas']);

    Route::get('news', [NewsController::class, 'index']);
    Route::get('news/{id}', [NewsController::class, 'detail']);
    Route::post('news', [NewsController::class, 'create']);
    Route::put('news', [NewsController::class, 'update']);
    Route::delete('news/{id}', [NewsController::class, 'delete']);

    Route::get('schedule', [ScheduleController::class, 'getAllSchedule']);
    Route::get('schedule/count', [ValidationController::class, 'getListCount']);
    Route::post('schedule/generate', [ScheduleController::class, 'generateSchedule']);
    Route::post('schedule/submit', [ScheduleController::class, 'submitSchedule']);
    Route::delete('schedule/delete', [ScheduleController::class, 'deleteCurrentSchedule']);
    Route::get('schedule/reset', [ScheduleController::class, 'resetSchedule']);

    Route::get('master/pertemuan', [PertemuanController::class, 'index']);
    Route::get('master/pertemuan/{id}', [PertemuanController::class, 'get']);
    Route::post('master/pertemuan', [PertemuanController::class, 'create']);
    Route::put('master/pertemuan', [PertemuanController::class, 'update']);
    Route::delete('master/pertemuan/{id}', [PertemuanController::class, 'delete']);

    Route::get('master/sesi', [SesiController::class, 'index']);
    Route::get('master/sesi/{id}', [SesiController::class, 'get']);
    Route::post('master/sesi', [SesiController::class, 'create']);
    Route::put('master/sesi', [SesiController::class, 'update']);
    Route::delete('master/sesi/{id}', [SesiController::class, 'delete']);

    Route::get('master/jabatan', [JabatanController::class, 'index']);
    Route::get('master/jabatan/{id}', [JabatanController::class, 'get']);
    Route::post('master/jabatan', [JabatanController::class, 'create']);
    Route::put('master/jabatan', [JabatanController::class, 'update']);
    Route::delete('master/jabatan/{id}', [JabatanController::class, 'delete']);

    Route::get('master/jabatan-category', [JabatanCategoryController::class, 'index']);
    Route::get('master/jabatan-category/{id}', [JabatanCategoryController::class, 'get']);
    Route::post('master/jabatan-category', [JabatanCategoryController::class, 'create']);
    Route::put('master/jabatan-category', [JabatanCategoryController::class, 'update']);
    Route::delete('master/jabatan-category/{id}', [JabatanCategoryController::class, 'delete']);
});
