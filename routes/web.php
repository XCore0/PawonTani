<?php

use App\Http\Controllers\AuthController;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Pengurus\AnggotaController;
use App\Services\CommodityService;
use App\Services\HarvestPredictionService;

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

    Route::get('/informasi', function () {
        $pengurus = Auth::user();
        $kelompok = $pengurus?->kelompokTani;

        $commodityService = app(CommodityService::class);
        $harvestService = app(HarvestPredictionService::class);

        // Data cuaca statis untuk tampilan (tanpa integrasi API cuaca)
        $weatherData = [
            'current' => [
                'icon' => 'sun',
                'temp' => '28°C',
                'condition' => 'Cerah',
                'humidity' => '72%',
                'windSpeed' => '12 km/h',
                'date' => now()->locale('id')->isoFormat('dddd, D MMM YYYY'),
            ],
            'forecast' => [
                ['day' => 'Senin', 'icon' => 'sun', 'temp' => '28°C', 'rain' => '10%'],
                ['day' => 'Selasa', 'icon' => 'cloud-sun', 'temp' => '26°C', 'rain' => '30%'],
                ['day' => 'Rabu', 'icon' => 'cloud-rain', 'temp' => '24°C', 'rain' => '80%'],
                ['day' => 'Kamis', 'icon' => 'cloud-rain-wind', 'temp' => '25°C', 'rain' => '60%'],
                ['day' => 'Jumat', 'icon' => 'sun', 'temp' => '29°C', 'rain' => '5%'],
                ['day' => 'Sabtu', 'icon' => 'cloud-sun', 'temp' => '27°C', 'rain' => '20%'],
                ['day' => 'Minggu', 'icon' => 'sun', 'temp' => '30°C', 'rain' => '5%'],
            ],
            'recommendation' => [
                'status' => 'good',
                'message' => 'Baik untuk pemupukan & penyemprotan',
            ],
        ];

        $commodities = $commodityService->getCommodityPrices();
        $ricePrediction = $commodityService->getRicePricePrediction();
        $harvestPredictions = $kelompok ? $harvestService->getPredictions($kelompok->id_kelompok) : [];

        return view('Pengurus.Content.InformasiPrediksi', compact(
            'kelompok',
            'pengurus',
            'weatherData',
            'commodities',
            'ricePrediction',
            'harvestPredictions'
        ));
    })->name('informasi');

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
use App\Http\Controllers\Admin\KelompokTaniController;
use App\Http\Controllers\Admin\PengurusController;

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

    Route::get('/notifikasi', fn () => view('Notifications'))->name('notifikasi');

    Route::get('/profil', fn () => $errorPage(
        'Profil',
        'Kelola informasi profil dan preferensi akun administrator.'
    ))->name('profil');
});

/* ============================================================
   SHARED AUTHENTICATED ROUTES (AJAX helpers)
   ============================================================ */
Route::middleware(['auth'])->group(function () {
    Route::get('/ajax/check-username', [PengurusController::class, 'checkUsername'])
        ->name('ajax.check-username');
});
