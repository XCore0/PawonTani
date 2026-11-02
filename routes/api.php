<?php

use App\Http\Controllers\Api\V1\AuthController;
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
    });
});
