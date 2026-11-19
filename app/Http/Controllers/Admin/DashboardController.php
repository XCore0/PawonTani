<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Anggota;
use App\Models\DokumenAnggota;
use App\Models\KelompokTani;
use App\Models\Komoditas;
use App\Models\Lahan;
use App\Models\LogAktivitasSistem;
use App\Models\Pengguna;
use App\Models\PermintaanKeanggotaan;
use Illuminate\Http\Request;
use Illuminate\View\View;

class DashboardController extends Controller
{
    public function index(Request $request): View
    {
        // 1. STATISTIK KARTU RINGKASAN (KPI CARDS)
        $totalKelompok = KelompokTani::count();
        $kelompokAktif = KelompokTani::where('status', 'Aktif')->count();
        $persenKelompokAktif = $totalKelompok > 0 ? round(($kelompokAktif / $totalKelompok) * 100) : 0;

        $totalPetani = Pengguna::where('role', 'Anggota')->count();
        $totalPengurus = Pengguna::where('role', 'Pengurus')->count();

        $permintaanPending = PermintaanKeanggotaan::where('status', 'menunggu')->count();

        $totalLuasLahan = (float) Lahan::sum('luas');
        $totalLahan = Lahan::count();

        // Status Verifikasi Lahan
        $lahanMenungguVerifikasi = Lahan::whereIn('status_verifikasi', ['menunggu', 'menunggu_verifikasi'])->count();
        $lahanTerverifikasi = Lahan::where('status_verifikasi', 'terverifikasi')->count();
        $lahanPerluPerbaikan = Lahan::where('status_verifikasi', 'perlu_perbaikan')->count();

        // 2. KELENGKAPAN DOKUMEN e-RDKK
        $allAnggota = Pengguna::where('role', 'Anggota')->whereHas('anggota', fn ($q) => $q->where('status_keanggotaan', '!=', 'Dihapus'));
        $totalAnggotaValid = (clone $allAnggota)->count();
        
        $dokumenLengkapCount = (clone $allAnggota)->whereHas('dokumenIdentitas', function ($q) {
            $q->where('jenis', 'KTP');
        })->whereHas('dokumenIdentitas', function ($q) {
            $q->where('jenis', 'KK');
        })->count();

        $spptKurangCount = (clone $allAnggota)->whereHas('lahan', function ($q) {
            $q->whereDoesntHave('dokumenSppt');
        })->count();

        // 3. DAFTAR PERMINTAAN KEANGGOTAAN TERBARU (Pending)
        $permintaanTerbaru = PermintaanKeanggotaan::with(['pengguna', 'kelompokTani'])
            ->where('status', 'menunggu')
            ->latest()
            ->take(5)
            ->get();

        // 4. DAFTAR LAHAN BUTUH VERIFIKASI / PERHATIAN
        $lahanButuhPerhatian = Lahan::with(['pengguna', 'kelompokTani', 'komoditas'])
            ->whereIn('status_verifikasi', ['menunggu', 'menunggu_verifikasi', 'perlu_perbaikan'])
            ->latest('updated_at')
            ->take(5)
            ->get();

        // 5. SEBARAN KOMODITAS UTAMA (Lahan per Komoditas)
        $sebaranKomoditas = Komoditas::whereHas('lahan')
            ->withCount('lahan')
            ->withSum('lahan', 'luas')
            ->orderByDesc('lahan_sum_luas')
            ->take(5)
            ->get();

        // 6. KELOMPOK TANI DENGAN ANGGOTA TERBANYAK
        $topKelompok = KelompokTani::withCount(['pengguna as anggota_count' => function ($q) {
                $q->where('role', 'Anggota');
            }])
            ->withCount('lahan')
            ->withSum('lahan', 'luas')
            ->orderByDesc('anggota_count')
            ->take(4)
            ->get();

        // 7. LOG AKTIVITAS SISTEM TERKINI
        $recentLogs = LogAktivitasSistem::with('actor:id_pengguna,nama,role')
            ->latest('created_at')
            ->take(5)
            ->get();

        // Sapaan Waktu Indonesia
        $hour = (int) date('H');
        if ($hour >= 5 && $hour < 11) {
            $sapaanWaktu = 'Selamat Pagi';
        } elseif ($hour >= 11 && $hour < 15) {
            $sapaanWaktu = 'Selamat Siang';
        } elseif ($hour >= 15 && $hour < 18) {
            $sapaanWaktu = 'Selamat Sore';
        } else {
            $sapaanWaktu = 'Selamat Malam';
        }

        return view('Admin.Content.Dashboard', compact(
            'totalKelompok',
            'kelompokAktif',
            'persenKelompokAktif',
            'totalPetani',
            'totalPengurus',
            'permintaanPending',
            'totalLuasLahan',
            'totalLahan',
            'lahanMenungguVerifikasi',
            'lahanTerverifikasi',
            'lahanPerluPerbaikan',
            'totalAnggotaValid',
            'dokumenLengkapCount',
            'spptKurangCount',
            'permintaanTerbaru',
            'lahanButuhPerhatian',
            'sebaranKomoditas',
            'topKelompok',
            'recentLogs',
            'sapaanWaktu'
        ));
    }
}
