<?php

use App\Http\Controllers\Api\V1\AktivitasController;
use App\Http\Controllers\Api\V1\AuthController;
use App\Http\Controllers\Api\V1\BerandaController;
use App\Http\Controllers\Api\V1\EdukasiController;
use App\Http\Controllers\Api\V1\KomoditasController;
use App\Http\Controllers\Api\V1\LahanController;
use App\Http\Controllers\Api\V1\PanenController;
use App\Http\Controllers\Api\V1\SiklusTanamController;
use App\Http\Controllers\Api\V1\MarketplaceController;
use App\Http\Controllers\Api\V1\NotifikasiController;
use App\Http\Controllers\Api\V1\PenjualanController;
use App\Http\Controllers\Api\V1\PermintaanKeanggotaanController;
use App\Http\Controllers\DokumenController;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| API Routes - V1
|--------------------------------------------------------------------------
*/
Route::prefix('v1')->group(function () {
    // Media Serving dengan CORS penuh untuk Mobile & Web
    Route::get('/media/{path}', function (string $path) {
        $filePath = storage_path('app/public/' . $path);
        if (!file_exists($filePath)) {
            return response()->json(['success' => false, 'message' => 'Berkas tidak ditemukan.'], 404);
        }
        $mime = mime_content_type($filePath) ?: 'application/octet-stream';
        return response()->file($filePath, [
            'Content-Type' => $mime,
            'Access-Control-Allow-Origin' => '*',
            'Access-Control-Allow-Methods' => 'GET, OPTIONS',
            'Cache-Control' => 'public, max-age=86400',
        ]);
    })->where('path', '.*')->name('api.v1.media');

    // Autentikasi Mobile Anggota
    Route::post('/auth/login', [AuthController::class, 'login'])->middleware('throttle:5,1')->name('api.v1.auth.login');

    // Rute Terproteksi Bearer Token Sanctum
    Route::middleware('auth:sanctum')->group(function () {
        Route::get('/auth/me', [AuthController::class, 'me'])->name('api.v1.auth.me');
        Route::put('/auth/me', [AuthController::class, 'updateProfile'])->name('api.v1.auth.update-profile');
        Route::post('/auth/me', [AuthController::class, 'updateProfile'])->name('api.v1.auth.update-profile.post');
        Route::post('/auth/logout', [AuthController::class, 'logout'])->name('api.v1.auth.logout');
        Route::delete('/auth/account', [AuthController::class, 'deleteAccount'])->middleware('throttle:3,1')->name('api.v1.auth.account.delete');

        // Ringkasan Dashboard Beranda Mobile
        Route::get('/beranda/ringkasan', [BerandaController::class, 'ringkasan'])->name('api.v1.beranda.ringkasan');

        // Notifikasi In-App
        Route::get('/notifikasi/unread-count', [NotifikasiController::class, 'unreadCount'])->name('api.v1.notifikasi.unread-count');
        Route::post('/notifikasi/baca-semua', [NotifikasiController::class, 'bacaSemua'])->name('api.v1.notifikasi.baca-semua');
        Route::patch('/notifikasi/{id}/baca', [NotifikasiController::class, 'baca'])->name('api.v1.notifikasi.baca');
        Route::get('/notifikasi', [NotifikasiController::class, 'index'])->name('api.v1.notifikasi.index');

        // Konten Edukasi Pertanian (Tips, Panduan, Artikel)
        Route::get('/edukasi', [EdukasiController::class, 'index'])->name('api.v1.edukasi.index');
        Route::get('/edukasi/{type}/{id}', [EdukasiController::class, 'show'])->name('api.v1.edukasi.show');

        // Master komoditas dapat dibaca oleh pengguna mobile yang sudah login.
        Route::get('/komoditas', [KomoditasController::class, 'index'])->name('api.v1.komoditas.index');
        Route::get('/komoditas/harga-pasar', [KomoditasController::class, 'hargaPasar'])->name('api.v1.komoditas.harga-pasar');
        Route::get('/kelompok', [PermintaanKeanggotaanController::class, 'groups'])->name('api.v1.kelompok.index');
        Route::get('/membership-requests/me', [PermintaanKeanggotaanController::class, 'mine'])->name('api.v1.membership-requests.mine');
        Route::post('/membership-requests', [PermintaanKeanggotaanController::class, 'store'])->middleware('throttle:5,1')->name('api.v1.membership-requests.store');

        // Data lahan, aktivitas, dan dokumen hanya untuk anggota dengan keanggotaan aktif.
        Route::middleware('membership.active')->group(function () {
            Route::post('/lahan/{lahan}', [LahanController::class, 'update'])->name('api.v1.lahan.update.post');
            Route::post('/lahan/{id}/mulai-persiapan', [LahanController::class, 'mulaiPersiapan'])->name('api.v1.lahan.mulai-persiapan');
            Route::post('/lahan/{id}/batalkan-persiapan', [LahanController::class, 'batalkanPersiapan'])->name('api.v1.lahan.batalkan-persiapan');
            Route::get('/lahan/{id}/siklus', [SiklusTanamController::class, 'indexByLahan'])->name('api.v1.lahan.siklus.index');
            Route::get('/lahan/{id}/siklus/berjalan', [SiklusTanamController::class, 'berjalan'])->name('api.v1.lahan.siklus.berjalan');
            Route::apiResource('lahan', LahanController::class)->names([
                'index' => 'api.v1.lahan.index',
                'store' => 'api.v1.lahan.store',
                'show' => 'api.v1.lahan.show',
                'update' => 'api.v1.lahan.update',
                'destroy' => 'api.v1.lahan.destroy',
            ]);

            // --- Siklus Tanam ---
            Route::apiResource('siklus-tanam', SiklusTanamController::class)->only(['index', 'store', 'show'])->names([
                'index' => 'api.v1.siklus-tanam.index',
                'store' => 'api.v1.siklus-tanam.store',
                'show' => 'api.v1.siklus-tanam.show',
            ]);
            Route::post('/siklus-tanam/{siklus_tanam}/ajukan-selesai', [SiklusTanamController::class, 'ajukanSelesai'])->name('api.v1.siklus-tanam.ajukan-selesai');
            Route::post('/siklus-tanam/{siklus_tanam}/ajukan-gagal', [SiklusTanamController::class, 'ajukanGagal'])->name('api.v1.siklus-tanam.ajukan-gagal');
            Route::post('/siklus-tanam/{siklus_tanam}/batalkan', [SiklusTanamController::class, 'batalkan'])->name('api.v1.siklus-tanam.batalkan');
            Route::post('/siklus-tanam/{siklus_tanam}/verifikasi-pengakhiran', [SiklusTanamController::class, 'verifikasiPengakhiran'])->name('api.v1.siklus-tanam.verifikasi-pengakhiran');
            Route::get('/siklus-tanam/{siklus_tanam}/bukti-gagal', [SiklusTanamController::class, 'viewBuktiGagal'])->name('api.v1.siklus-tanam.bukti-gagal');

            Route::post('/aktivitas/{aktivita}', [AktivitasController::class, 'update'])->name('api.v1.aktivitas.update.post');
            Route::apiResource('aktivitas', AktivitasController::class)->names([
                'index' => 'api.v1.aktivitas.index',
                'store' => 'api.v1.aktivitas.store',
                'show' => 'api.v1.aktivitas.show',
                'update' => 'api.v1.aktivitas.update',
                'destroy' => 'api.v1.aktivitas.destroy',
            ]);

            Route::post('/panen/{panen}/verifikasi', [PanenController::class, 'verifikasi'])->name('api.v1.panen.verifikasi');
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
            Route::delete('/documents/sppt/{idDokumen}', [DokumenController::class, 'apiDeleteSppt'])->name('api.v1.documents.sppt.delete');

            // --- Marketplace Produk Saya ---
            Route::get('/marketplace/produk', [MarketplaceController::class, 'indexProdukSaya'])->name('api.v1.marketplace.produk.index');
            Route::post('/marketplace/produk', [MarketplaceController::class, 'storeProduk'])->name('api.v1.marketplace.produk.store');
            Route::get('/marketplace/produk/{id}', [MarketplaceController::class, 'showProduk'])->name('api.v1.marketplace.produk.show');
            Route::post('/marketplace/produk/{id}', [MarketplaceController::class, 'updateProduk'])->name('api.v1.marketplace.produk.update');
            Route::delete('/marketplace/produk/{id}', [MarketplaceController::class, 'destroyProduk'])->name('api.v1.marketplace.produk.destroy');

            // --- Transaksi Penjualan ---
            Route::get('/penjualan', [PenjualanController::class, 'index'])->name('api.v1.penjualan.index');
            Route::post('/penjualan', [PenjualanController::class, 'store'])->name('api.v1.penjualan.store');
            Route::get('/penjualan/{id}', [PenjualanController::class, 'show'])->name('api.v1.penjualan.show');
            Route::post('/penjualan/{id}/pembayaran', [PenjualanController::class, 'tambahPembayaran'])->name('api.v1.penjualan.pembayaran');
            Route::post('/penjualan/{id}/lunas', [PenjualanController::class, 'tandaiLunas'])->name('api.v1.penjualan.lunas');
            Route::post('/penjualan/{id}/batalkan', [PenjualanController::class, 'batalkan'])->name('api.v1.penjualan.batalkan');
            Route::post('/penjualan/{id}/serahkan', [PenjualanController::class, 'tandaiDiserahkan'])->name('api.v1.penjualan.serahkan');
        });
    });
});
