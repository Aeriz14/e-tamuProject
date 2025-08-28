<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Api\GuestController;
use App\Http\Controllers\Api\AuthController;
use App\Http\Controllers\Api\CheckInController;
use App\Http\Controllers\Api\DashboardController;
use App\Http\Controllers\DivisionController;
use App\Http\Controllers\AppointmentController;
use App\Http\Controllers\Api\AdminController; // 1. Impor koki baru

/*
|--------------------------------------------------------------------------
| Pelayan API (API Routes)
|--------------------------------------------------------------------------
*/

/*
|--------------------------------------------------------------------------
| Rute Publik (Tidak Perlu Kartu Akses/Login)
|--------------------------------------------------------------------------
*/
Route::post('/register', [GuestController::class, 'register']);
Route::post('/login', [AuthController::class, 'login']);
Route::get('/divisions', [DivisionController::class, 'index']);
Route::post('/appointments', [AppointmentController::class, 'store']);

/*
|--------------------------------------------------------------------------
| Rute Terproteksi (Wajib Memiliki Kartu Akses/Token)
|--------------------------------------------------------------------------
*/
Route::middleware('auth:sanctum')->group(function () {
    // Pesanan untuk Dashboard
    Route::get('/dashboard/summary', [DashboardController::class, 'getDashboardSummary']);

    // Pesanan untuk mengelola Janji Temu
    Route::get('/appointments', [AppointmentController::class, 'index']);
    Route::get('/appointments/{id}', [AppointmentController::class, 'show']);
    Route::put('/appointments/{id}', [AppointmentController::class, 'update']);
    Route::delete('/appointments/{id}', [AppointmentController::class, 'destroy']);

    // --- RUTE BARU UNTUK SUPER ADMIN ---
    // 2. Tambahkan grup rute yang hanya bisa diakses oleh super_admin
    Route::middleware('role:super_admin')->group(function () {
        Route::get('/admin/management-data', [AdminController::class, 'getManagementData']);
        Route::post('/admin/users', [AdminController::class, 'storeUser']);
        Route::delete('/admin/users/{user}', [AdminController::class, 'destroyUser']);
        Route::post('/admin/divisions', [AdminController::class, 'storeDivision']);
        Route::delete('/admin/divisions/{division}', [AdminController::class, 'destroyDivision']);
    });
    // --- AKHIR RUTE BARU ---

    // Logout
    Route::post('/logout', [AuthController::class, 'logout']);
});
