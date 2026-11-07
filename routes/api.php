<?php

use App\Http\Controllers\Api\V1\AuthController;
use App\Http\Controllers\Api\V1\KomoditasController;
use App\Http\Controllers\Api\V1\LahanController;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| API Routes - V1
|--------------------------------------------------------------------------
*/
Route::prefix('v1')->group(function () {
    // Autentikasi Mobile Anggota
    Route::post('/auth/login', [AuthController::class, 'login'])->name('api.v1.auth.login');

    // Rute Terproteksi Bearer Token Sanctum
    Route::middleware('auth:sanctum')->group(function () {
        Route::get('/auth/me', [AuthController::class, 'me'])->name('api.v1.auth.me');
        Route::post('/auth/logout', [AuthController::class, 'logout'])->name('api.v1.auth.logout');

        // Master Komoditas - Mobile
        Route::get('/komoditas', [KomoditasController::class, 'index'])->name('api.v1.komoditas.index');

        // Modul Lahan - Mobile Anggota
        Route::apiResource('lahan', LahanController::class)->names([
            'index' => 'api.v1.lahan.index',
            'store' => 'api.v1.lahan.store',
            'show' => 'api.v1.lahan.show',
            'update' => 'api.v1.lahan.update',
            'destroy' => 'api.v1.lahan.destroy',
        ]);
    });
});
