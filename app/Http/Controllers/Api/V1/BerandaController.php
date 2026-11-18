<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Models\AktivitasTanam;
use App\Models\Artikel;
use App\Models\Komoditas;
use App\Models\Lahan;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class BerandaController extends Controller
{
    /**
     * Mengambil data agregat beranda mobile:
     * - Statistik (Total lahan, lahan aktif, komoditas unik, luas total)
     * - Data cuaca Open-Meteo berbasis GPS / lokasi profil user
     * - Daftar artikel edukasi terbaru
     */
    public function ringkasan(Request $request): JsonResponse
    {
        $user = $request->user();

        // 1. Data Statistik Lahan Anggota
        $lahanQuery = Lahan::query();
        if ($user && $user->role === 'Anggota') {
            $lahanQuery->where('id_pengguna', $user->id_pengguna);
        }

        $allLahans = $lahanQuery->with('komoditas')->get();
        $totalLahan = $allLahans->count();
        $lahanAktif = $allLahans->filter(fn($l) => strcasecmp($l->status_operasional ?? '', 'aktif') === 0)->count();
        $lahanIstirahat = $allLahans->filter(fn($l) => strcasecmp($l->status_operasional ?? '', 'istirahat') === 0)->count();
        $lahanPersiapan = $allLahans->filter(fn($l) => strcasecmp($l->status_operasional ?? '', 'persiapan') === 0)->count();
        $totalLuas = (float) $allLahans->sum('luas');

        // Komoditas unik yang ditanam anggota
        $komoditasList = $allLahans->pluck('komoditas.nama_komoditas')->filter()->unique()->values();
        $totalKomoditas = $komoditasList->count();
        if ($totalKomoditas === 0) {
            $totalKomoditas = Komoditas::count();
        }

        // 2. Data Aktivitas Tanam Terbaru
        $aktivitasQuery = AktivitasTanam::query();
        if ($user && $user->role === 'Anggota') {
            $aktivitasQuery->where('id_pengguna', $user->id_pengguna);
        }
        $totalAktivitas = $aktivitasQuery->count();

        // 3. Data Cuaca (Open-Meteo)
        // Ambil lat/lon dari query param (jika HP kirim via GPS) atau fallback ke profil user / default Jawa Timur
        $lat = $request->query('lat', $user?->latitude ?? -8.1724);
        $lon = $request->query('lon', $user?->longitude ?? 113.7007);
        $lokasiNama = $this->resolveLocationName($lat, $lon, $user?->lokasi_nama);

        $cuaca = $this->fetchWeather($lat, $lon, $lokasiNama);

        // 4. Artikel / Tips Edukasi Terbaru
        $artikels = Artikel::where('status', 'Publik')
            ->latest('tanggal')
            ->take(4)
            ->get(['id_artikel', 'judul', 'kategori', 'tanggal', 'gambar', 'ringkasan', 'isi']);

        return response()->json([
            'success' => true,
            'message' => 'Berhasil mengambil ringkasan beranda',
            'data' => [
                'user' => [
                    'nama' => $user?->nama ?? 'Petani PawonTani',
                    'role' => $user?->role ?? 'Anggota',
                    'kelompok' => $user?->kelompokTani?->nama_kelompok ?? 'Kelompok Tani',
                ],
                'statistik' => [
                    'total_lahan' => $totalLahan,
                    'lahan_aktif' => $lahanAktif,
                    'lahan_istirahat' => $lahanIstirahat,
                    'lahan_persiapan' => $lahanPersiapan,
                    'total_luas_ha' => round($totalLuas, 2),
                    'total_komoditas' => $totalKomoditas,
                    'komoditas_list' => $komoditasList->take(3),
                    'total_aktivitas' => $totalAktivitas,
                ],
                'cuaca' => $cuaca,
                'artikels' => $artikels,
            ],
        ]);
    }

    /**
     * Memanggil API Open-Meteo untuk data cuaca presisi
     */
    protected function fetchWeather($lat, $lon, string $lokasiNama): array
    {
        $cacheKey = "mobile_weather_{$lat}_{$lon}";

        return cache()->remember($cacheKey, 600, function () use ($lat, $lon, $lokasiNama) {
            try {
                $url = "https://api.open-meteo.com/v1/forecast?latitude={$lat}&longitude={$lon}" .
                    "&current=temperature_2m,relative_humidity_2m,weather_code,wind_speed_10m" .
                    "&daily=weather_code,temperature_2m_max,temperature_2m_min,precipitation_probability_mean" .
                    "&timezone=Asia%2FJakarta&forecast_days=7";

                $response = Http::timeout(6)->get($url);

                if ($response->successful()) {
                    $json = $response->json();
                    $current = $json['current'] ?? [];
                    $daily = $json['daily'] ?? [];
                    $wCode = (int) ($current['weather_code'] ?? 0);

                    return [
                        'suhu' => round($current['temperature_2m'] ?? 28),
                        'kelembapan' => $current['relative_humidity_2m'] ?? 80,
                        'kecepatan_angin' => $current['wind_speed_10m'] ?? 5,
                        'kode_cuaca' => $wCode,
                        'kondisi' => $this->wmoToIndonesian($wCode),
                        'lokasi' => $lokasiNama,
                        'suhu_maks' => round($daily['temperature_2m_max'][0] ?? 31),
                        'suhu_min' => round($daily['temperature_2m_min'][0] ?? 24),
                        'peluang_hujan' => $daily['precipitation_probability_mean'][0] ?? 20,
                        'forecast_daily' => array_map(function ($date, $code, $hi, $lo, $precip) {
                            return [
                                'tanggal' => $date,
                                'kode' => $code,
                                'kondisi' => $this->wmoToIndonesian($code),
                                'suhu_maks' => round($hi),
                                'suhu_min' => round($lo),
                                'hujan_persen' => round($precip ?? 0),
                            ];
                        }, $daily['time'] ?? [], $daily['weather_code'] ?? [], $daily['temperature_2m_max'] ?? [], $daily['temperature_2m_min'] ?? [], $daily['precipitation_probability_mean'] ?? []),
                    ];
                }
            } catch (\Throwable $e) {
                Log::warning('Weather fetch error: ' . $e->getMessage());
            }

            // Fallback cuaca aman jika offline
            return [
                'suhu' => 28,
                'kelembapan' => 78,
                'kecepatan_angin' => 4.2,
                'kode_cuaca' => 2,
                'kondisi' => 'Cerah Berawan',
                'lokasi' => $lokasiNama,
                'suhu_maks' => 31,
                'suhu_min' => 24,
                'peluang_hujan' => 25,
                'forecast_daily' => [],
            ];
        });
    }

    /**
     * Cari nama kota/kabupaten berdasarkan koordinat GPS
     */
    protected function resolveLocationName($lat, $lon, ?string $fallback): string
    {
        if (empty($lat) || empty($lon)) {
            return $fallback ?? 'Jember, Jawa Timur';
        }

        $cacheKey = "geo_name_{$lat}_{$lon}";
        return cache()->remember($cacheKey, 86400, function () use ($lat, $lon, $fallback) {
            try {
                // Reverse geocoding menggunakan BigDataCloud Free Client API (cepat & no auth)
                $res = Http::timeout(4)->get("https://api.bigdatacloud.net/data/reverse-geocode-client?latitude={$lat}&longitude={$lon}&localityLanguage=id");
                if ($res->successful()) {
                    $json = $res->json();
                    $city = $json['city'] ?? $json['locality'] ?? $json['principalSubdivision'] ?? null;
                    $admin = $json['principalSubdivision'] ?? '';
                    if ($city) {
                        return $admin && !str_contains($city, $admin) ? "{$city}, {$admin}" : $city;
                    }
                }
            } catch (\Throwable $e) {
                Log::warning('Reverse geocode error: ' . $e->getMessage());
            }

            return $fallback ?? 'Jember, Jawa Timur';
        });
    }

    protected function wmoToIndonesian(int $code): string
    {
        return match (true) {
            $code === 0, $code === 1 => 'Cerah',
            $code === 2, $code === 3 => 'Cerah Berawan',
            in_array($code, [45, 48]) => 'Berkabut',
            in_array($code, [51, 53, 55]) => 'Gerimis',
            in_array($code, [61, 63, 65, 80, 81, 82]) => 'Hujan',
            in_array($code, [95, 96, 99]) => 'Hujan Petir',
            default => 'Berawan',
        };
    }
}
