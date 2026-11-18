<?php

use App\Http\Controllers\Api\V1\AktivitasController;
use App\Http\Controllers\Api\V1\AuthController;
use App\Http\Controllers\Api\V1\BerandaController;
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

        // Ringkasan Dashboard Beranda Mobile
        Route::get('/beranda/ringkasan', [BerandaController::class, 'ringkasan'])->name('api.v1.beranda.ringkasan');

        // Master Komoditas - Mobile
        Route::get('/komoditas', [KomoditasController::class, 'index'])->name('api.v1.komoditas.index');

        // Modul Lahan - Mobile Anggota
        Route::post('/lahan/{lahan}', [LahanController::class, 'update'])->name('api.v1.lahan.update.post');
        Route::apiResource('lahan', LahanController::class)->names([
            'index' => 'api.v1.lahan.index',
            'store' => 'api.v1.lahan.store',
            'show' => 'api.v1.lahan.show',
            'update' => 'api.v1.lahan.update',
            'destroy' => 'api.v1.lahan.destroy',
        ]);

        // Modul Aktivitas Tanam - Mobile Anggota
        Route::post('/aktivitas/{aktivita}', [AktivitasController::class, 'update'])->name('api.v1.aktivitas.update.post');
        Route::apiResource('aktivitas', AktivitasController::class)->names([
            'index' => 'api.v1.aktivitas.index',
            'store' => 'api.v1.aktivitas.store',
            'show' => 'api.v1.aktivitas.show',
            'update' => 'api.v1.aktivitas.update',
            'destroy' => 'api.v1.aktivitas.destroy',
        ]);
    });
});
