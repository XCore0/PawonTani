<?php

use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return view('home');
})->name('home');

Route::get('/login', function () {
    return view('Auth.Login');
})->name('login');

Route::post('/login', function () {
    return redirect()->route('admin.pengurus');
});

use App\Http\Controllers\Admin\KelompokTaniController;
use App\Http\Controllers\Admin\PengurusController;

$developmentPage = function (string $pageTitle, string $description, string $icon) {
    return view('Admin.Content.ComingSoon', compact('pageTitle', 'description', 'icon'));
};

Route::prefix('admin')->name('admin.')->group(function () use ($developmentPage) {
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
    Route::get('/verifikasi-lapangan', fn () => $developmentPage(
        'Verifikasi Lapangan',
        'Kelola proses verifikasi data dan kondisi kelompok tani di lapangan.',
        'file-check'
    ))->name('verifikasi');
    Route::get('/aktivitas', fn () => $developmentPage(
        'Aktivitas',
        'Pantau aktivitas pertanian dan kegiatan kelompok tani secara terpusat.',
        'activity'
    ))->name('aktivitas');
    Route::get('/edukasi/tips', fn () => view('Admin.Content.Tips', [
        'contentType' => 'Tips',
        'items' => [
            ['title' => '5 Tips Mengendalikan Hama Wereng Secara Alami', 'target' => 'Kelompok Tani Harapan Jaya', 'image' => 'wereng.jpg', 'date' => '2024-11-12', 'status' => 'Publik'],
            ['title' => 'Tips Hemat Air Saat Musim Kemarau', 'target' => 'Semua Kelompok', 'image' => 'hemat-air.jpg', 'date' => '2024-10-30', 'status' => 'Publik'],
            ['title' => 'Tips Mempercepat Pertumbuhan Akar Tanaman Muda', 'target' => 'Semua Kelompok', 'image' => 'akar-tanam.jpg', 'date' => '2024-10-15', 'status' => 'Draft'],
        ],
    ]))->name('edukasi.tips');
    Route::get('/edukasi/artikel', fn () => view('Admin.Content.Artikel', [
        'contentType' => 'Artikel',
        'items' => [
            ['title' => 'Cara Tepat Penggunaan Pupuk NPK untuk Padi', 'target' => 'Semua Kelompok', 'image' => 'pupuk-npk.jpg', 'date' => '2024-11-15', 'status' => 'Publik'],
            ['title' => 'Memanfaatkan Limbah Pertanian sebagai Kompos Berkualitas', 'target' => 'Semua Kelompok', 'image' => 'kompos.jpg', 'date' => '2024-11-05', 'status' => 'Draft'],
            ['title' => 'Mengenal Varietas Padi Unggul untuk Lahan Kering', 'target' => 'Semua Kelompok', 'image' => 'varietas.jpg', 'date' => '2024-10-20', 'status' => 'Publik'],
        ],
    ]))->name('edukasi.artikel');
    Route::get('/edukasi/panduan', fn () => view('Admin.Content.Panduan', [
        'contentType' => 'Panduan',
        'items' => [
            ['title' => 'Panduan Lengkap Budidaya Jagung Hibrida', 'target' => 'Semua Kelompok', 'image' => 'jagung-hibrida.jpg', 'date' => '2024-11-10', 'status' => 'Publik'],
            ['title' => 'Pengairan Efisien dengan Sistem Irigasi Tetes', 'target' => 'Kelompok Tani Mekar Sari', 'image' => 'irigasi-tetes.jpg', 'date' => '2024-10-28', 'status' => 'Publik'],
            ['title' => 'Panduan Pengolahan Tanah Sebelum Tanam', 'target' => 'Semua Kelompok', 'image' => 'olah-tanah.jpg', 'date' => '2024-10-10', 'status' => 'Draft'],
        ],
    ]))->name('edukasi.panduan');
    Route::get('/notifikasi', fn () => view('Admin.Content.Notifications'))->name('notifikasi');
    Route::get('/profil', fn () => $developmentPage(
        'Profil',
        'Kelola informasi profil dan preferensi akun administrator.',
        'user'
    ))->name('profil');
});

