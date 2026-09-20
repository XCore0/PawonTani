<?php

use App\Http\Controllers\AuthController;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Pengurus\AnggotaController;
use App\Http\Controllers\Admin\KelompokTaniController;
use App\Http\Controllers\Admin\PengurusController;
use App\Http\Controllers\Admin\ArtikelController;
use App\Http\Controllers\ArtikelController as FrontendArtikelController;
/* ============================================================
   AUTH ROUTES
   ============================================================ */
Route::get('/login', [AuthController::class, 'showLogin'])->name('login');
Route::post('/login', [AuthController::class, 'login']);
Route::post('/logout', [AuthController::class, 'logout'])->name('logout');

Route::get('/', function () {
    return view('home');
})->name('home');

/* ============================================================
   Helper: Error / Coming Soon Page
   ============================================================ */
$errorPage = function (string $pageTitle, string $description, string $layout = 'Admin.Layout._layout') {
    return view('Error', compact('pageTitle', 'description', 'layout'));
};

/* ============================================================
   PUBLIC ARTICLE ROUTES
   ============================================================ */
Route::get('/artikel', [FrontendArtikelController::class, 'index'])->name('artikel.index');
Route::get('/artikel/{id}', [FrontendArtikelController::class, 'show'])->whereNumber('id')->name('artikel.show');

/* ============================================================
   PENGURUS ROUTES (role: Pengurus)
   ============================================================ */
Route::prefix('pengurus')->name('pengurus.')->middleware(['auth', 'role:Pengurus'])->group(function () use ($errorPage) {
    // Implemented pages
    Route::get('/dashboard', function () {
        return view('Pengurus.Content.Dashboard');
    })->name('dashboard');

    Route::get('/anggota', [AnggotaController::class, 'index'])->name('anggota');
    Route::post('/anggota', [AnggotaController::class, 'store'])->name('anggota.store');
    Route::put('/anggota/{id_pengguna}', [AnggotaController::class, 'update'])->name('anggota.update');
    Route::delete('/anggota/{id_pengguna}', [AnggotaController::class, 'destroy'])->name('anggota.destroy');

    // Pages in development
    Route::get('/lahan', fn () => $errorPage(
        'Kelola Lahan',
        'Kelola data lahan pertanian anggota kelompok tani.',
        'Pengurus.Layout._layout'
    ))->name('lahan');

    Route::get('/monitoring', fn () => $errorPage(
        'Monitoring Pertanian',
        'Pantau kondisi dan perkembangan pertanian secara real-time.',
        'Pengurus.Layout._layout'
    ))->name('monitoring');

    Route::get('/panen', fn () => $errorPage(
        'Panen',
        'Catat dan kelola data hasil panen kelompok tani.',
        'Pengurus.Layout._layout'
    ))->name('panen');

    Route::get('/penjualan', fn () => $errorPage(
        'Penjualan',
        'Kelola transaksi dan riwayat penjualan hasil pertanian.',
        'Pengurus.Layout._layout'
    ))->name('penjualan');

    Route::get('/laporan', fn () => $errorPage(
        'Laporan',
        'Lihat dan unduh laporan aktivitas kelompok tani.',
        'Pengurus.Layout._layout'
    ))->name('laporan');

    Route::get('/informasi', fn () => $errorPage(
        'Informasi & Prediksi',
        'Dapatkan informasi cuaca dan prediksi hasil pertanian.',
        'Pengurus.Layout._layout'
    ))->name('informasi');

    Route::get('/edukasi', fn () => $errorPage(
        'Edukasi',
        'Akses materi edukasi dan panduan pertanian.',
        'Pengurus.Layout._layout'
    ))->name('edukasi');

    Route::get('/notifikasi', fn () => view('Notifications', ['layout' => 'Pengurus.Layout._layout']))->name('notifikasi');

    Route::get('/profil', fn () => $errorPage(
        'Profil',
        'Kelola informasi profil dan preferensi akun.',
        'Pengurus.Layout._layout'
    ))->name('profil');
});

/* ============================================================
   ADMIN ROUTES (role: PPL)
   ============================================================ */
use App\Http\Controllers\Admin\TipsController;

Route::prefix('admin')->name('admin.')->middleware(['auth', 'role:PPL'])->group(function () use ($errorPage) {
    Route::get('/dashboard', function () {
        return view('Admin.Content.Dashboard');
    })->name('dashboard');

    // Pengurus Management
    Route::get('/pengurus', [PengurusController::class, 'index'])->name('pengurus');
    Route::post('/pengurus', [PengurusController::class, 'store'])->name('pengurus.store');
    Route::put('/pengurus/{id_pengguna}', [PengurusController::class, 'update'])->name('pengurus.update');
    Route::delete('/pengurus/{id_pengguna}', [PengurusController::class, 'destroy'])->name('pengurus.destroy');

    // Kelompok Tani CRUD
    Route::get('/kelompok', [KelompokTaniController::class, 'index'])->name('kelompok');
    Route::post('/kelompok', [KelompokTaniController::class, 'store'])->name('kelompok.store');
    Route::put('/kelompok/{id_kelompok}', [KelompokTaniController::class, 'update'])->name('kelompok.update');
    Route::delete('/kelompok/{id_kelompok}', [KelompokTaniController::class, 'destroy'])->name('kelompok.destroy');

    // Pages in development
    Route::get('/verifikasi-lapangan', fn () => $errorPage(
        'Verifikasi Lapangan',
        'Kelola proses verifikasi data dan kondisi kelompok tani di lapangan.'
    ))->name('verifikasi');

    Route::get('/aktivitas', fn () => $errorPage(
        'Aktivitas',
        'Pantau aktivitas pertanian dan kegiatan kelompok tani secara terpusat.'
    ))->name('aktivitas');

    // Tips Edukasi CRUD
    Route::get('/edukasi/tips', [TipsController::class, 'index'])->name('edukasi.tips');
    Route::post('/edukasi/tips', [TipsController::class, 'store'])->name('edukasi.tips.store');
    Route::put('/edukasi/tips/{id_tips}', [TipsController::class, 'update'])->name('edukasi.tips.update');
    Route::delete('/edukasi/tips/{id_tips}', [TipsController::class, 'destroy'])->name('edukasi.tips.destroy');
    Route::post('/edukasi/komoditas', [TipsController::class, 'storeKomoditas'])->name('edukasi.komoditas.store');

    Route::get('/edukasi/artikel', [ArtikelController::class, 'index'])->name('edukasi.artikel');
    Route::get('/edukasi/artikel/create', [ArtikelController::class, 'create'])->name('edukasi.artikel.create');
    Route::post('/edukasi/artikel', [ArtikelController::class, 'store'])->name('edukasi.artikel.store');
    Route::get('/edukasi/artikel/{artikel}', [ArtikelController::class, 'show'])->name('edukasi.artikel.show');
    Route::get('/edukasi/artikel/{artikel}/edit', [ArtikelController::class, 'edit'])->name('edukasi.artikel.edit');
    Route::put('/edukasi/artikel/{artikel}', [ArtikelController::class, 'update'])->name('edukasi.artikel.update');
    Route::delete('/edukasi/artikel/{artikel}', [ArtikelController::class, 'destroy'])->name('edukasi.artikel.destroy');

    Route::get('/edukasi/panduan', fn () => view('Admin.Content.Panduan', [
        'contentType' => 'Panduan',
        'items' => [
            ['title' => 'Panduan Lengkap Budidaya Jagung Hibrida', 'target' => 'Semua Kelompok', 'image' => 'jagung-hibrida.jpg', 'date' => '2024-11-10', 'status' => 'Publik'],
            ['title' => 'Pengairan Efisien dengan Sistem Irigasi Tetes', 'target' => 'Kelompok Tani Mekar Sari', 'image' => 'irigasi-tetes.jpg', 'date' => '2024-10-28', 'status' => 'Publik'],
            ['title' => 'Panduan Pengolahan Tanah Sebelum Tanam', 'target' => 'Semua Kelompok', 'image' => 'olah-tanah.jpg', 'date' => '2024-10-10', 'status' => 'Draft'],
        ],
    ]))->name('edukasi.panduan');

    Route::get('/notifikasi', fn () => view('Notifications'))->name('notifikasi');

    Route::get('/profil', fn () => $errorPage(
        'Profil',
        'Kelola informasi profil dan preferensi akun administrator.'
    ))->name('profil');
});
