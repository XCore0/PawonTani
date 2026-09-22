<?php

use App\Http\Controllers\AuthController;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Pengurus\AnggotaController;
use App\Services\CommodityService;
use App\Services\HarvestPredictionService;
use App\Http\Controllers\Admin\KelompokTaniController;
use App\Http\Controllers\Admin\PengurusController;
use App\Http\Controllers\Admin\ArtikelController;
use App\Http\Controllers\Admin\PanduanController;
use App\Http\Controllers\Admin\TipsController;

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
            $regionsData = require app_path('Data/regions.php');
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

        // Get all provinces for dropdown
        $regions = require app_path('Data/regions.php');
        $provinces = [];
        foreach ($regions as $id => $data) {
            $provinces[$id] = $data['nama'];
        }

        return view('Pengurus.Content.InformasiPrediksi', compact(
            'kelompok',
            'pengurus',
            'weatherData',
            'weatherError',
            'commodities',
            'ricePrediction',
            'harvestPredictions',
            'provinces',
            'regions'
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
    Route::post('/edukasi/artikel', [ArtikelController::class, 'store'])->name('edukasi.artikel.store');
    Route::put('/edukasi/artikel/{id_artikel}', [ArtikelController::class, 'update'])->name('edukasi.artikel.update');
    Route::delete('/edukasi/artikel/{id_artikel}', [ArtikelController::class, 'destroy'])->name('edukasi.artikel.destroy');

    // Panduan CRUD
    Route::get('/edukasi/panduan', [PanduanController::class, 'index'])->name('edukasi.panduan');
    Route::post('/edukasi/panduan', [PanduanController::class, 'store'])->name('edukasi.panduan.store');
    Route::put('/edukasi/panduan/{id_panduan}', [PanduanController::class, 'update'])->name('edukasi.panduan.update');
    Route::delete('/edukasi/panduan/{id_panduan}', [PanduanController::class, 'destroy'])->name('edukasi.panduan.destroy');

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

    // Location API for Pengurus
    Route::get('/api/lokasi/kabupaten', function () {
        $provId = request('provinsi_id');
        if (!$provId) return response()->json([]);
        $regions = require app_path('Data/regions.php');
        $kotkab = $regions[$provId]['kotkab'] ?? [];
        return response()->json($kotkab);
    })->name('api.lokasi.kabupaten');

    Route::get('/api/lokasi/kecamatan', function () {
        $kabId = request('kabupaten_id');
        if (!$kabId) return response()->json([]);
        $regions = require app_path('Data/regions.php');
        foreach ($regions as $prov) {
            if (isset($prov['kotkab'][$kabId])) {
                return response()->json($prov['kotkab'][$kabId]['kecamatan'] ?? []);
            }
        }
        return response()->json([]);
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
        ]);
        
        // Geocode location name to get coordinates using Open-Meteo Geocoding API
        $lat = null;
        $lon = null;
        if (!empty($data['lokasi_nama']) || !empty($data['kabupaten_id'])) {
            // Get kabupaten name from regions data for better geocoding
            $regionsData = require app_path('Data/regions.php');
            $kabupatenNama = null;
            if (!empty($data['kabupaten_id'])) {
                foreach ($regionsData as $prov) {
                    if (isset($prov['kotkab'][$data['kabupaten_id']])) {
                        $kabupatenNama = $prov['kotkab'][$data['kabupaten_id']]['nama'] ?? null;
                        break;
                    }
                }
            }
            
            // Clean up kabupaten name (remove "KABUPATEN " or "KOTA " prefix)
            $kabupatenClean = $kabupatenNama ? preg_replace('/^(KABUPATEN|KOTA)\s+/i', '', $kabupatenNama) : null;
            
            // Try multiple search terms
            $searchTerms = array_filter([$kabupatenClean, $kabupatenNama, $data['lokasi_nama'] ?? null]);
            $ctx = stream_context_create(['http' => ['timeout' => 10, 'ignore_errors' => true]]);
            
            foreach ($searchTerms as $term) {
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
        }
        
        $data['latitude'] = $lat;
        $data['longitude'] = $lon;
        $user->update($data);
        return response()->json(['success' => true, 'lokasi' => $user->lokasi_nama, 'lat' => $lat, 'lon' => $lon]);
    })->name('api.lokasi.simpan');
});
