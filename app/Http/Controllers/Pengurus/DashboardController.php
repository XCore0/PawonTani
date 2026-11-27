<?php

namespace App\Http\Controllers\Pengurus;

use App\Http\Controllers\Controller;
use App\Models\AktivitasTanam;
use App\Models\Komoditas;
use App\Models\Lahan;
use App\Models\Pengguna;
use App\Models\SiklusTanam;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\View\View;

class DashboardController extends Controller
{
    public function index(Request $request): View
    {
        /** @var \App\Models\Pengguna|null $pengurus */
        $pengurus = Auth::user();
        $idKelompok = $pengurus?->id_kelompok;
        $kelompok = $pengurus?->kelompokTani;

        // 1. STATISTIK KPI UTAMA (4 Kartu Ringkasan)
        $totalAnggota = 0;
        $totalPetakLahan = 0;
        $totalLuasLahan = 0.0;
        $lahanTerverifikasi = 0;
        $lahanMenunggu = 0;
        $lahanPerbaikan = 0;
        $siklusMenunggu = 0;
        $totalAktivitasBulanIni = 0;

        $lahanMenungguTinjauan = collect();
        $aktivitasTerkini = collect();
        $sebaranKomoditas = collect();

        if ($idKelompok) {
            // Total Anggota
            $totalAnggota = Pengguna::where('role', 'Anggota')
                ->where('id_kelompok', $idKelompok)
                ->count();

            // Total Petak & Luas Lahan (berdasarkan id_kelompok pada lahan atau pemilik di kelompok)
            $lahanQuery = Lahan::where(function ($q) use ($idKelompok) {
                $q->where('id_kelompok', $idKelompok)
                  ->orWhereHas('pengguna', fn ($uq) => $uq->where('id_kelompok', $idKelompok));
            });

            $totalPetakLahan = (clone $lahanQuery)->count();
            $totalLuasLahan = (float) (clone $lahanQuery)->sum('luas');

            // Status Verifikasi Lahan
            $lahanTerverifikasi = (clone $lahanQuery)->where('status_verifikasi', 'terverifikasi')->count();
            $lahanMenunggu = (clone $lahanQuery)->whereIn('status_verifikasi', ['menunggu', 'menunggu_verifikasi'])->count();
            $lahanPerbaikan = (clone $lahanQuery)->where('status_verifikasi', 'perlu_perbaikan')->count();

            // Pengajuan Pengakhiran Siklus Tanam yang Menunggu Approval
            $siklusMenunggu = SiklusTanam::whereHas('lahan', function ($q) use ($idKelompok) {
                $q->where('id_kelompok', $idKelompok)
                  ->orWhereHas('pengguna', fn ($uq) => $uq->where('id_kelompok', $idKelompok));
            })->whereIn('status', ['menunggu_selesai', 'menunggu_gagal'])->count();

            // Aktivitas 30 Hari Terakhir
            $totalAktivitasBulanIni = AktivitasTanam::where(function ($q) use ($idKelompok) {
                    $q->where('id_kelompok', $idKelompok)
                      ->orWhereHas('pengguna', fn ($uq) => $uq->where('id_kelompok', $idKelompok));
                })
                ->where('tanggal', '>=', Carbon::now()->subDays(30))
                ->count();

            // 2. DAFTAR LAHAN MENUNGGU VERIFIKASI / TINJAUAN (Termasuk yang ada pengajuan siklus)
            $lahanMenungguTinjauan = (clone $lahanQuery)
                ->with(['pengguna', 'komoditas', 'siklusBerjalan'])
                ->where(function ($q) {
                    $q->whereIn('status_verifikasi', ['menunggu', 'menunggu_verifikasi', 'perlu_perbaikan'])
                      ->orWhereHas('siklusBerjalan', fn ($sq) => $sq->whereIn('status', ['menunggu_selesai', 'menunggu_gagal']));
                })
                ->latest('updated_at')
                ->take(5)
                ->get();

            // 3. CATATAN AKTIVITAS TANAM PETANI TERKINI (5 Aktivitas Terakhir)
            $aktivitasTerkini = AktivitasTanam::with(['pengguna', 'lahan'])
                ->where(function ($q) use ($idKelompok) {
                    $q->where('id_kelompok', $idKelompok)
                      ->orWhereHas('pengguna', fn ($uq) => $uq->where('id_kelompok', $idKelompok));
                })
                ->orderByDesc('tanggal')
                ->orderByDesc('created_at')
                ->take(5)
                ->get();

            // 4. SEBARAN KOMODITAS POKTAN
            $sebaranKomoditas = Komoditas::whereHas('lahan', function ($q) use ($idKelompok) {
                    $q->where(function ($sq) use ($idKelompok) {
                        $sq->where('id_kelompok', $idKelompok)
                          ->orWhereHas('pengguna', fn ($uq) => $uq->where('id_kelompok', $idKelompok));
                    });
                })
                ->withCount(['lahan' => function ($q) use ($idKelompok) {
                    $q->where(function ($sq) use ($idKelompok) {
                        $sq->where('id_kelompok', $idKelompok)
                          ->orWhereHas('pengguna', fn ($uq) => $uq->where('id_kelompok', $idKelompok));
                    });
                }])
                ->withSum(['lahan' => function ($q) use ($idKelompok) {
                    $q->where(function ($sq) use ($idKelompok) {
                        $sq->where('id_kelompok', $idKelompok)
                          ->orWhereHas('pengguna', fn ($uq) => $uq->where('id_kelompok', $idKelompok));
                    });
                }], 'luas')
                ->orderByDesc('lahan_sum_luas')
                ->take(5)
                ->get();
        }

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

        return view('Pengurus.Content.Dashboard', compact(
            'pengurus',
            'kelompok',
            'sapaanWaktu',
            'totalAnggota',
            'totalPetakLahan',
            'totalLuasLahan',
            'lahanTerverifikasi',
            'lahanMenunggu',
            'lahanPerbaikan',
            'siklusMenunggu',
            'totalAktivitasBulanIni',
            'lahanMenungguTinjauan',
            'aktivitasTerkini',
            'sebaranKomoditas'
        ));
    }
}
