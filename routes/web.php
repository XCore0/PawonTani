<?php

use App\Http\Controllers\AuthController;
use App\Http\Controllers\ProfileController;
use App\Http\Controllers\Public\EdukasiController as PublicEdukasiController;
use App\Http\Controllers\DokumenController;
use App\Http\Controllers\Admin\DokumenERdkkController;
use App\Http\Controllers\Admin\DashboardController as AdminDashboardController;
use App\Http\Controllers\Admin\AnggotaController as AdminAnggotaController;
use App\Http\Controllers\Admin\LogAktivitasController;
use App\Http\Controllers\Admin\PermintaanKeanggotaanController as AdminPermintaanKeanggotaanController;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Pengurus\AnggotaController;
use App\Http\Controllers\Pengurus\DashboardController as PengurusDashboardController;
use App\Http\Controllers\Pengurus\LahanController;
use App\Http\Controllers\Pengurus\MonitoringController;
use App\Http\Controllers\Pengurus\PanenController as PengurusPanenController;
use App\Services\CommodityService;
use App\Services\HarvestPredictionService;
use App\Http\Controllers\Admin\KelompokTaniController;
use App\Http\Controllers\Admin\PengurusController;
use App\Http\Controllers\Admin\ArtikelController;
use App\Http\Controllers\Admin\PanduanController;
use App\Http\Controllers\Admin\TipsController;
use App\Models\Tip;
use App\Models\Artikel;
use App\Models\Panduan;
use App\Models\Produk;
use App\Http\Controllers\Customer\MarketplaceController;

/* ============================================================
   CORS MEDIA SERVING (STORAGE FALLBACK)
   ============================================================ */
Route::get('/storage/{path}', function ($path) {
    $filePath = storage_path('app/public/' . $path);
    if (!file_exists($filePath)) {
        abort(404);
    }
    $mime = mime_content_type($filePath) ?: 'application/octet-stream';
    return response()->file($filePath, [
        'Content-Type' => $mime,
        'Access-Control-Allow-Origin' => '*',
        'Access-Control-Allow-Methods' => 'GET, OPTIONS',
    ]);
})->where('path', '.*');

/* ============================================================
   AUTH ROUTES
   ============================================================ */
Route::get('/login', [AuthController::class, 'showLogin'])->name('login');
Route::post('/login', [AuthController::class, 'login'])->middleware('throttle:5,1');
Route::post('/logout', [AuthController::class, 'logout'])->name('logout');

Route::get('/', function () {
    $tipsTerbaru = Tip::with('komoditas')
        ->where('status', 'Publik')
        ->latest('tanggal')
        ->first();

    $artikelTerbaru = Artikel::with('komoditas')
        ->where('status', 'Publik')
        ->latest('tanggal')
        ->first();

    $panduanTerbaru = Panduan::with('komoditas')
        ->where('status', 'Publik')
        ->latest('tanggal')
        ->first();

    // Cuplikan produk marketplace untuk section teaser di landing page
    $produkUnggulan = Produk::with(['pengguna.kelompokTani', 'komoditas'])
        ->publik()
        ->where('status', 'aktif')
        ->latest()
        ->take(4)
        ->get();

    return view('home', compact('tipsTerbaru', 'artikelTerbaru', 'panduanTerbaru', 'produkUnggulan'));
})->name('home');

/* ============================================================
   PUBLIC EDUCATION DETAIL ROUTES
   ============================================================ */
Route::get('/tips/{id_tips}', [PublicEdukasiController::class, 'showTip'])->name('tips.show');
Route::get('/artikel/{id_artikel}', [PublicEdukasiController::class, 'showArtikel'])->name('artikel.show');
Route::get('/panduan/{slug}', [PublicEdukasiController::class, 'showPanduan'])->name('panduan.show');

/* ============================================================
   PUBLIC EDUCATION ROUTES
   ============================================================ */
Route::prefix('edukasi')->name('edukasi.')->group(function () {
    Route::get('/semua', [PublicEdukasiController::class, 'indexSemua'])->name('semua');
    Route::get('/tips', [PublicEdukasiController::class, 'indexTips'])->name('tips');
    Route::get('/artikel', [PublicEdukasiController::class, 'indexArtikel'])->name('artikel');
    Route::get('/panduan', [PublicEdukasiController::class, 'indexPanduan'])->name('panduan');
});

/* ============================================================
   CUSTOMER MARKETPLACE ROUTES
   ============================================================ */
Route::prefix('marketplace')->name('marketplace.')->group(function () {
    Route::get('/', [MarketplaceController::class, 'home'])->name('home');
    Route::get('/katalog', [MarketplaceController::class, 'katalog'])->name('katalog');
    Route::get('/produk/{id}', [MarketplaceController::class, 'detail'])->name('detail');
    Route::get('/penjual/{id}', [MarketplaceController::class, 'penjual'])->name('penjual');
});

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
    Route::get('/dashboard', [PengurusDashboardController::class, 'index'])->name('dashboard');

    Route::get('/anggota', [AnggotaController::class, 'index'])->name('anggota');
    Route::get('/anggota/cari-lama', [AnggotaController::class, 'searchFormerMembers'])->name('anggota.search-former');
    Route::post('/anggota/{id_pengguna}/masukkan-kembali', [AnggotaController::class, 'restoreFormerMember'])->name('anggota.restore-former');
    Route::post('/anggota', [AnggotaController::class, 'store'])->name('anggota.store');
    Route::put('/anggota/{id_pengguna}', [AnggotaController::class, 'update'])->name('anggota.update');
    Route::delete('/anggota/{id_pengguna}', [AnggotaController::class, 'destroy'])->name('anggota.destroy');
    Route::get('/dokumen/anggota/{idPengguna}/{jenis}', [DokumenController::class, 'downloadIdentity'])->name('dokumen.identity.download');
    Route::get('/dokumen/anggota/{idPengguna}/{jenis}/preview', [DokumenController::class, 'previewIdentity'])->name('dokumen.identity.preview');
    Route::get('/dokumen/sppt/{idDokumen}/download', [DokumenController::class, 'downloadSppt'])->name('dokumen.sppt.download');
    Route::get('/dokumen/sppt/{idDokumen}/preview', [DokumenController::class, 'previewSppt'])->name('dokumen.sppt.preview');
    Route::post('/lahan/{idLahan}/sppt', [DokumenController::class, 'uploadSpptPengurus'])->name('lahan.sppt.upload');

    // Kelola Lahan
    Route::get('/lahan', [LahanController::class, 'index'])->name('lahan');
    Route::get('/lahan/{lahan}', [LahanController::class, 'show'])->name('lahan.show');
    Route::post('/lahan/{lahan}/verifikasi', [LahanController::class, 'verifikasi'])->name('lahan.verifikasi');
    Route::post('/lahan/{lahan}/siklus/{siklus}/verifikasi', [LahanController::class, 'verifikasiSiklus'])->name('lahan.siklus.verifikasi');

    // Pages in development

    // Monitoring Pertanian & Aktivitas
    Route::get('/monitoring', [MonitoringController::class, 'index'])->name('monitoring');

    // Kelola Hasil Panen Kelompok Tani
    Route::get('/panen', [PengurusPanenController::class, 'index'])->name('panen');
    Route::get('/panen/{id}', [PengurusPanenController::class, 'show'])->name('panen.show');
    Route::post('/panen/{id}/verifikasi', [PengurusPanenController::class, 'verifikasi'])->name('panen.verifikasi');

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
        /** @var \App\Models\Pengguna|null $pengurus */
        $pengurus = Auth::user();
        $kelompok = $pengurus?->kelompokTani;

        $commodityService = app(CommodityService::class);
        $harvestService = app(HarvestPredictionService::class);

        $commodities = $commodityService->getCommodityPrices();
        $ricePrediction = $commodityService->getRicePricePrediction();
        $harvestPredictions = $kelompok ? $harvestService->getPredictions($kelompok->id_kelompok) : [];

        // Weather data: fetch from Open-Meteo using stored coordinates
        $weatherData = null;
        $weatherError = null;
        if ($pengurus && $pengurus->latitude && $pengurus->longitude) {
            $lat = $pengurus->latitude;
            $lon = $pengurus->longitude;
            $lokasiNama = $pengurus->lokasi_nama ?? 'Lokasi tersimpan';

            $url = 'https://api.open-meteo.com/v1/forecast?latitude=' . $lat . '&longitude=' . $lon .
                '&current=temperature_2m,relative_humidity_2m,weather_code,wind_speed_10m' .
                '&hourly=temperature_2m&daily=weather_code,temperature_2m_max,temperature_2m_min,precipitation_probability_mean' .
                '&timezone=Asia%2FJakarta&forecast_days=7';
            $ctx = stream_context_create(['http' => ['timeout' => 10, 'ignore_errors' => true]]);
            $response = @file_get_contents($url, false, $ctx);
            if ($response) {
                $weatherData = json_decode($response, true);
                if ($weatherData) {
                    $weatherData['_lokasi_nama'] = $lokasiNama;
                } else {
                    $weatherError = 'Gagal memproses data cuaca.';
                    $weatherData = null;
                }
            } else {
                $weatherError = 'Gagal terhubung ke layanan cuaca. Coba lagi nanti.';
            }
        } elseif ($pengurus && $pengurus->kecamatan_id && !$pengurus->latitude) {
            // Auto-geocode using kabupaten name from regions data
            $regionsData = cache()->remember('regions_data', 86400, fn () => require app_path('Data/regions.php'));
            $kabupatenNama = null;
            $lokasiNama = $pengurus->lokasi_nama ?? '';
            
            // Find kabupaten name from regions data
            foreach ($regionsData as $prov) {
                if (isset($prov['kotkab'][$pengurus->kabupaten_id])) {
                    $kabupatenNama = $prov['kotkab'][$pengurus->kabupaten_id]['nama'] ?? null;
                    break;
                }
            }
            
            // Clean up kabupaten name for geocoding (remove "KABUPATEN " or "KOTA " prefix)
            $kabupatenClean = $kabupatenNama ? preg_replace('/^(KABUPATEN|KOTA)\s+/i', '', $kabupatenNama) : null;
            
            // Try geocoding with cleaned kabupaten name first, then original, then lokasi_nama
            $searchTerms = array_filter([$kabupatenClean, $kabupatenNama, $lokasiNama]);
            $lat = null;
            $lon = null;
            $ctx = stream_context_create(['http' => ['timeout' => 10, 'ignore_errors' => true]]);
            
            foreach ($searchTerms as $term) {
                if (!$term) continue;
                $geocodeUrl = 'https://geocoding-api.open-meteo.com/v1/search?name=' . urlencode($term) . '&count=3&language=id&format=json';
                $geocodeResponse = @file_get_contents($geocodeUrl, false, $ctx);
                if ($geocodeResponse) {
                    $geocodeData = json_decode($geocodeResponse, true);
                    if ($geocodeData && !empty($geocodeData['results'])) {
                        // Find result that matches Indonesia
                        foreach ($geocodeData['results'] as $result) {
                            if (($result['country_code'] ?? '') === 'ID') {
                                $lat = $result['latitude'] ?? null;
                                $lon = $result['longitude'] ?? null;
                                break;
                            }
                        }
                        if ($lat && $lon) break;
                    }
                }
            }
            
            if ($lat && $lon) {
                // Save coordinates to database
                $pengurus->update(['latitude' => $lat, 'longitude' => $lon]);
                
                // Fetch weather with the new coordinates
                $url = 'https://api.open-meteo.com/v1/forecast?latitude=' . $lat . '&longitude=' . $lon .
                    '&current=temperature_2m,relative_humidity_2m,weather_code,wind_speed_10m' .
                    '&hourly=temperature_2m&daily=weather_code,temperature_2m_max,temperature_2m_min,precipitation_probability_mean' .
                    '&timezone=Asia%2FJakarta&forecast_days=7';
                $response = @file_get_contents($url, false, $ctx);
                if ($response) {
                    $weatherData = json_decode($response, true);
                    if ($weatherData) {
                        $weatherData['_lokasi_nama'] = $lokasiNama ?: $kabupatenNama;
                    } else {
                        $weatherError = 'Gagal memproses data cuaca.';
                    }
                } else {
                    $weatherError = 'Gagal terhubung ke layanan cuaca.';
                }
            }
            
            if (!$weatherData && !$weatherError) {
                $weatherError = 'Koordinat belum tersimpan. Silakan pilih lokasi dan simpan ulang.';
            }
        }

        // Get all provinces for dropdown (cached)
        $provinces = cache()->remember('provinces_list', 86400, function () {
            $regions = require app_path('Data/regions.php');
            $provinces = [];
            foreach ($regions as $id => $data) {
                $provinces[$id] = $data['nama'];
            }
            return $provinces;
        });

        return view('Pengurus.Content.InformasiPrediksi', compact(
            'kelompok',
            'pengurus',
            'weatherData',
            'weatherError',
            'commodities',
            'ricePrediction',
            'harvestPredictions',
            'provinces'
        ));
    })->name('informasi');

    Route::get('/edukasi', function () {
        $tips = Tip::with('komoditas')->where('status', 'Publik')->latest('tanggal')->get();
        $artikels = Artikel::with('komoditas')->where('status', 'Publik')->latest('tanggal')->get();
        $panduans = Panduan::with('komoditas')->where('status', 'Publik')->latest('tanggal')->get();

        $stats = [
            'tips' => ['total' => $tips->count()],
            'artikel' => ['total' => $artikels->count()],
            'panduan' => ['total' => $panduans->count()],
        ];

        return view('Pengurus.Content.Konten', compact('tips', 'artikels', 'panduans', 'stats'));
    })->name('edukasi');

    Route::get('/notifikasi', fn () => view('Notifications', ['layout' => 'Pengurus.Layout._layout']))->name('notifikasi');

    Route::get('/profil', [ProfileController::class, 'show'])->name('profil');
    Route::put('/profil', [ProfileController::class, 'update'])->name('profil.update');
});

/* ============================================================
   ADMIN ROUTES (role: PPL)
   ============================================================ */

Route::prefix('admin')->name('admin.')->middleware(['auth', 'role:PPL'])->group(function () use ($errorPage) {
    Route::get('/dashboard', [AdminDashboardController::class, 'index'])->name('dashboard');

    // Data Anggota: pemulihan keanggotaan dan penghapusan data pribadi oleh Admin.
    Route::get('/anggota', [AdminAnggotaController::class, 'index'])->name('anggota');
    Route::post('/anggota/{idPengguna}/aktifkan', [AdminAnggotaController::class, 'restore'])->name('anggota.restore');
    Route::get('/permintaan-keanggotaan', [AdminPermintaanKeanggotaanController::class, 'index'])->name('permintaan-keanggotaan');
    Route::post('/permintaan-keanggotaan/{id}/setujui', [AdminPermintaanKeanggotaanController::class, 'approve'])->middleware('throttle:10,1')->name('permintaan-keanggotaan.approve');
    Route::post('/permintaan-keanggotaan/{id}/tolak', [AdminPermintaanKeanggotaanController::class, 'reject'])->middleware('throttle:10,1')->name('permintaan-keanggotaan.reject');
    Route::delete('/anggota/{idPengguna}', [AdminAnggotaController::class, 'destroy'])->middleware('throttle:3,1')->name('anggota.destroy');

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

    // Dokumen e-RDKK: Admin/PPL hanya melihat dan mengunduh.
    Route::get('/dokumen-erdkk', [DokumenERdkkController::class, 'index'])->name('dokumen.erdkk');
    Route::get('/dokumen-erdkk/anggota/{idPengguna}/download-all', [DokumenController::class, 'downloadMemberArchive'])->middleware('throttle:10,1')->name('dokumen.member.download-all');
    Route::get('/dokumen-erdkk/anggota/{idPengguna}/{jenis}', [DokumenController::class, 'downloadIdentity'])->name('dokumen.identity.download');
    Route::get('/dokumen-erdkk/anggota/{idPengguna}/{jenis}/preview', [DokumenController::class, 'previewIdentity'])->name('dokumen.identity.preview');
    Route::get('/dokumen-erdkk/sppt/{idDokumen}/download', [DokumenController::class, 'downloadSppt'])->name('dokumen.sppt.download');
    Route::get('/dokumen-erdkk/sppt/{idDokumen}/preview', [DokumenController::class, 'previewSppt'])->name('dokumen.sppt.preview');
    // Alias rute lama agar bookmark sebelumnya tidak putus.
    Route::get('/verifikasi-lapangan', fn () => redirect()->route('admin.dokumen.erdkk'))->name('verifikasi');

    Route::get('/log-aktivitas', [LogAktivitasController::class, 'index'])->name('log-aktivitas');
    Route::get('/aktivitas', fn () => redirect()->route('admin.log-aktivitas'))->name('aktivitas');

    // Tips Edukasi CRUD
    Route::get('/edukasi/tips', [TipsController::class, 'index'])->name('edukasi.tips');
    Route::post('/edukasi/tips', [TipsController::class, 'store'])->name('edukasi.tips.store');
    Route::put('/edukasi/tips/{id_tips}', [TipsController::class, 'update'])->name('edukasi.tips.update');
    Route::delete('/edukasi/tips/{id_tips}', [TipsController::class, 'destroy'])->name('edukasi.tips.destroy');
    Route::post('/edukasi/komoditas', [TipsController::class, 'storeKomoditas'])->name('edukasi.komoditas.store');

    Route::get('/edukasi/artikel', [ArtikelController::class, 'index'])->name('edukasi.artikel');
    Route::post('/edukasi/artikel', [ArtikelController::class, 'store'])->name('edukasi.artikel.store');
    Route::put('/edukasi/artikel/{id_artikel}', [ArtikelController::class, 'update'])->name('edukasi.artikel.update');
    Route::delete('/edukasi/artikel/{id_artikel}', [ArtikelController::class, 'destroy'])->name('edukasi.artikel.destroy');

    // Panduan CRUD
    Route::get('/edukasi/panduan', [PanduanController::class, 'index'])->name('edukasi.panduan');
    Route::post('/edukasi/panduan', [PanduanController::class, 'store'])->name('edukasi.panduan.store');
    Route::put('/edukasi/panduan/{id_panduan}', [PanduanController::class, 'update'])->name('edukasi.panduan.update');
    Route::delete('/edukasi/panduan/{id_panduan}', [PanduanController::class, 'destroy'])->name('edukasi.panduan.destroy');

    Route::get('/notifikasi', fn () => view('Notifications'))->name('notifikasi');

    Route::get('/profil', [ProfileController::class, 'show'])->name('profil');
    Route::put('/profil', [ProfileController::class, 'update'])->name('profil.update');
});

/* ============================================================
   SHARED AUTHENTICATED ROUTES (AJAX helpers)
   ============================================================ */
Route::middleware(['auth'])->group(function () {
    Route::get('/ajax/check-username', [PengurusController::class, 'checkUsername'])
        ->name('ajax.check-username');

    // Location API for Pengurus (cached for speed)
    Route::get('/api/lokasi/kabupaten', function () {
        $provId = request('provinsi_id');
        if (!$provId) return response()->json([]);

        $regions = cache()->remember('regions_data', 86400, fn () => require app_path('Data/regions.php'));
        $provId = (int) $provId;
        $kotkab = $regions[$provId]['kotkab'] ?? [];
        return response()->json($kotkab);
    })->name('api.lokasi.kabupaten');

    Route::get('/api/lokasi/kecamatan', function () {
        $kabId = request('kabupaten_id');
        if (!$kabId) return response()->json([]);

        $regions = cache()->remember('regions_data', 86400, fn () => require app_path('Data/regions.php'));
        $kabId = (int) $kabId;

        // Build fast lookup: kabupaten_id => kecamatan
        $kabMap = cache()->remember('kabupaten_map', 86400, function () use ($regions) {
            $map = [];
            foreach ($regions as $prov) {
                foreach ($prov['kotkab'] as $kabId => $kab) {
                    $map[$kabId] = $kab['kecamatan'] ?? [];
                }
            }
            return $map;
        });

        return response()->json($kabMap[$kabId] ?? []);
    })->name('api.lokasi.kecamatan');

    Route::get('/api/lokasi/cuaca', function () {
        $lat = request('lat');
        $lon = request('lon');
        if (!$lat || !$lon) return response()->json(['error' => 'lat dan lon diperlukan'], 400);

        $url = 'https://api.open-meteo.com/v1/forecast?latitude=' . $lat . '&longitude=' . $lon .
            '&current=temperature_2m,relative_humidity_2m,weather_code,wind_speed_10m' .
            '&hourly=temperature_2m&daily=weather_code,temperature_2m_max,temperature_2m_min,precipitation_probability_mean' .
            '&timezone=Asia%2FJakarta&forecast_days=7';
        $ctx = stream_context_create(['http' => ['timeout' => 10, 'ignore_errors' => true]]);
        $response = @file_get_contents($url, false, $ctx);

        if (!$response) return response()->json(['error' => 'Gagal mengambil data cuaca'], 502);

        $data = json_decode($response, true);
        if (!$data) {
            return response()->json(['error' => 'Data cuaca tidak tersedia'], 404);
        }

        return response()->json($data);
    })->name('api.lokasi.cuaca');

    Route::post('/api/lokasi/simpan', function () {
        /** @var \App\Models\Pengguna $user */
        $user = Auth::user();
        $data = request()->validate([
            'provinsi_id' => 'nullable|string|max:10',
            'kabupaten_id' => 'nullable|string|max:10',
            'kecamatan_id' => 'nullable|string|max:10',
            'desa_id' => 'nullable|string|max:20',
            'lokasi_nama' => 'nullable|string|max:200',
            'latitude' => 'nullable|numeric',
            'longitude' => 'nullable|numeric',
        ]);

        $lat = isset($data['latitude']) ? (float)$data['latitude'] : null;
        $lon = isset($data['longitude']) ? (float)$data['longitude'] : null;

        // Jika koordinat belum dikirim dari GPS client, lakukan geocode
        if (($lat === null || $lon === null) && (!empty($data['lokasi_nama']) || !empty($data['kabupaten_id']))) {
            $regionsData = cache()->remember('regions_data', 86400, fn () => require app_path('Data/regions.php'));
            $kabupatenNama = null;
            $kabId = (int) ($data['kabupaten_id'] ?? 0);
            if ($kabId > 0) {
                foreach ($regionsData as $prov) {
                    if (isset($prov['kotkab'][$kabId])) {
                        $kabupatenNama = $prov['kotkab'][$kabId]['nama'] ?? null;
                        break;
                    }
                }
            }

            $kabupatenClean = $kabupatenNama ? preg_replace('/^(KABUPATEN|KOTA)\s+/i', '', $kabupatenNama) : null;
            $searchTerms = array_filter([$kabupatenClean, $kabupatenNama, $data['lokasi_nama'] ?? null]);
            $ctx = stream_context_create(['http' => ['timeout' => 8, 'ignore_errors' => true]]);

            foreach ($searchTerms as $term) {
                $geocodeUrl = 'https://geocoding-api.open-meteo.com/v1/search?name=' . urlencode($term) . '&count=3&language=id&format=json';
                $geocodeResponse = @file_get_contents($geocodeUrl, false, $ctx);
                if ($geocodeResponse) {
                    $geocodeData = json_decode($geocodeResponse, true);
                    if ($geocodeData && !empty($geocodeData['results'])) {
                        foreach ($geocodeData['results'] as $result) {
                            if (($result['country_code'] ?? '') === 'ID') {
                                $lat = $result['latitude'] ?? null;
                                $lon = $result['longitude'] ?? null;
                                break;
                            }
                        }
                        if ($lat && $lon) break;
                    }
                }
            }
        }

        $data['latitude'] = $lat;
        $data['longitude'] = $lon;
        $user->update($data);

        return response()->json([
            'success' => true,
            'lokasi' => $user->lokasi_nama,
            'lat' => $lat,
            'lon' => $lon,
            'message' => 'Lokasi berhasil disimpan dan diperbarui.'
        ]);
    })->name('api.lokasi.simpan');
});

/* ============================================================
   PUBLIC IMAGE MEDIA ROUTE
   Hanya gambar non-sensitif pada folder media yang diizinkan.
   Dokumen KTP/KK/SPPT tidak boleh disimpan di public storage.
   ============================================================ */
Route::get('/media/{path}', function (string $path) {
    $path = ltrim(str_replace('\\', '/', $path), '/');
    abort_if(str_contains($path, '..') || str_contains($path, "\0"), 404);
    abort_unless(preg_match('/^(lahan|aktivitas|panen|pawontani\/produk)\/[A-Za-z0-9_\/.-]+\.(jpg|jpeg|png|webp)$/i', $path), 404);

    $root = realpath(storage_path('app/public'));
    $fullPath = realpath(storage_path('app/public/' . $path));
    abort_unless($root && $fullPath && str_starts_with($fullPath, $root . DIRECTORY_SEPARATOR) && is_file($fullPath), 404);

    $mime = (new \finfo(FILEINFO_MIME_TYPE))->file($fullPath);
    abort_unless(in_array($mime, ['image/jpeg', 'image/png', 'image/webp'], true), 404);

    return response()->file($fullPath, [
        'Content-Type' => $mime,
        'X-Content-Type-Options' => 'nosniff',
        'Access-Control-Allow-Origin' => '*',
        'Cache-Control' => 'public, max-age=86400',
    ]);
})->where('path', '.*')->name('media.serve');
