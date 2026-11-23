<?php

use App\Http\Controllers\Api\V1\AktivitasController;
use App\Http\Controllers\Api\V1\AuthController;
use App\Http\Controllers\Api\V1\BerandaController;
use App\Http\Controllers\Api\V1\KomoditasController;
use App\Http\Controllers\Api\V1\LahanController;
use App\Http\Controllers\Api\V1\PanenController;
use App\Http\Controllers\Api\V1\PermintaanKeanggotaanController;
use App\Http\Controllers\DokumenController;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| API Routes - V1
|--------------------------------------------------------------------------
*/
Route::prefix('v1')->group(function () {
    // Autentikasi Mobile Anggota
    Route::post('/auth/login', [AuthController::class, 'login'])->middleware('throttle:5,1')->name('api.v1.auth.login');

    // Rute Terproteksi Bearer Token Sanctum
    Route::middleware('auth:sanctum')->group(function () {
        Route::get('/auth/me', [AuthController::class, 'me'])->name('api.v1.auth.me');
        Route::post('/auth/logout', [AuthController::class, 'logout'])->name('api.v1.auth.logout');
        Route::delete('/auth/account', [AuthController::class, 'deleteAccount'])->middleware('throttle:3,1')->name('api.v1.auth.account.delete');

        // Ringkasan Dashboard Beranda Mobile
        Route::get('/beranda/ringkasan', [BerandaController::class, 'ringkasan'])->name('api.v1.beranda.ringkasan');

        // Master komoditas dapat dibaca oleh pengguna mobile yang sudah login.
        Route::get('/komoditas', [KomoditasController::class, 'index'])->name('api.v1.komoditas.index');
        Route::get('/kelompok', [PermintaanKeanggotaanController::class, 'groups'])->name('api.v1.kelompok.index');
        Route::get('/membership-requests/me', [PermintaanKeanggotaanController::class, 'mine'])->name('api.v1.membership-requests.mine');
        Route::post('/membership-requests', [PermintaanKeanggotaanController::class, 'store'])->middleware('throttle:5,1')->name('api.v1.membership-requests.store');

        // Data lahan, aktivitas, dan dokumen hanya untuk anggota dengan keanggotaan aktif.
        Route::middleware('membership.active')->group(function () {
            Route::post('/lahan/{lahan}', [LahanController::class, 'update'])->name('api.v1.lahan.update.post');
            Route::apiResource('lahan', LahanController::class)->names([
                'index' => 'api.v1.lahan.index',
                'store' => 'api.v1.lahan.store',
                'show' => 'api.v1.lahan.show',
                'update' => 'api.v1.lahan.update',
                'destroy' => 'api.v1.lahan.destroy',
            ]);

            Route::post('/aktivitas/{aktivita}', [AktivitasController::class, 'update'])->name('api.v1.aktivitas.update.post');
            Route::apiResource('aktivitas', AktivitasController::class)->names([
                'index' => 'api.v1.aktivitas.index',
                'store' => 'api.v1.aktivitas.store',
                'show' => 'api.v1.aktivitas.show',
                'update' => 'api.v1.aktivitas.update',
                'destroy' => 'api.v1.aktivitas.destroy',
            ]);

            Route::post('/panen/{panen}', [PanenController::class, 'update'])->name('api.v1.panen.update.post');
            Route::apiResource('panen', PanenController::class)->names([
                'index' => 'api.v1.panen.index',
                'store' => 'api.v1.panen.store',
                'show' => 'api.v1.panen.show',
                'update' => 'api.v1.panen.update',
                'destroy' => 'api.v1.panen.destroy',
            ]);

            Route::get('/documents', [DokumenController::class, 'apiIndex'])->name('api.v1.documents.index');
            Route::post('/documents/identity/{jenis}', [DokumenController::class, 'apiUploadIdentity'])->name('api.v1.documents.identity.upload');
            Route::get('/documents/identity/{jenis}', [DokumenController::class, 'apiDownloadIdentity'])->name('api.v1.documents.identity.download');
            Route::post('/lahan/{idLahan}/sppt', [DokumenController::class, 'apiUploadSppt'])->name('api.v1.documents.sppt.upload');
            Route::get('/lahan/{idLahan}/sppt', [DokumenController::class, 'apiDownloadSppt'])->name('api.v1.documents.sppt.download');
            Route::get('/documents/sppt/{idDokumen}', [DokumenController::class, 'apiDownloadSpptDocument'])->name('api.v1.documents.sppt.document.download');
        });
    });
});
