<?php

namespace App\Http\Controllers\Pengurus;

use App\Http\Controllers\Controller;
use App\Models\AktivitasTanam;
use App\Models\Lahan;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\View\View;

class MonitoringController extends Controller
{
    /**
     * Menampilkan dashboard monitoring aktivitas pertanian kelompok tani.
     */
    public function index(Request $request): View
    {
        /** @var \App\Models\Pengguna $pengurus */
        $pengurus = $request->user();

        // Validasi akses pengurus
        abort_unless(
            $pengurus && $pengurus->status === 'Aktif' && $pengurus->role === 'Pengurus' && !empty($pengurus->id_kelompok),
            403,
            'Akses ditolak. Halaman ini hanya untuk pengurus aktif kelompok tani.'
        );

        $kelompok = $pengurus->kelompokTani;
        $idKelompok = $pengurus->id_kelompok;

        // Query dasar ter-scope ke kelompok tani pengurus
        $query = AktivitasTanam::where('id_kelompok', $idKelompok)
            ->with([
                'lahan.komoditas',
                'lahan.pengguna:id_pengguna,nama,no_telepon',
                'pengguna:id_pengguna,nama,foto_profil,no_telepon',
            ]);

        // Ringkasan Statistik
        $baseStatsQuery = AktivitasTanam::where('id_kelompok', $idKelompok);
        $totalAktivitas = (clone $baseStatsQuery)->count();
        $aktivitasBulanIni = (clone $baseStatsQuery)
            ->whereMonth('tanggal', now()->month)
            ->whereYear('tanggal', now()->year)
            ->count();
        $tanamanSehat = (clone $baseStatsQuery)->where('kondisi_tanaman', 'sehat')->count();
        $tanamanPerhatian = (clone $baseStatsQuery)
            ->whereIn('kondisi_tanaman', ['perluPerhatian', 'perlu_perhatian', 'terserangHama', 'terserang_hama'])
            ->count();
        $lahanDimonitor = (clone $baseStatsQuery)->distinct('id_lahan')->count('id_lahan');

        $stats = [
            'total' => $totalAktivitas,
            'bulan_ini' => $aktivitasBulanIni,
            'sehat' => $tanamanSehat,
            'perlu_perhatian' => $tanamanPerhatian,
            'lahan_dimonitor' => $lahanDimonitor,
        ];

        // Filter Kategori
        if ($kategori = $request->query('kategori')) {
            $query->where('kategori', strtolower($kategori));
        }

        // Filter Kondisi
        if ($kondisi = $request->query('kondisi')) {
            $kondisiNormalized = match (strtolower($kondisi)) {
                'sehat' => 'sehat',
                'perlu_perhatian', 'perluperhatian' => 'perluPerhatian',
                'terserang_hama', 'terseranghama' => 'terserangHama',
                default => $kondisi,
            };
            $query->where('kondisi_tanaman', $kondisiNormalized);
        }

        // Filter Lahan
        if ($idLahan = $request->query('id_lahan')) {
            $query->where('id_lahan', $idLahan);
        }

        // Filter Pencarian Teks
        if ($search = $request->query('q')) {
            $like = DB::getDriverName() === 'pgsql' ? 'ILIKE' : 'LIKE';
            $query->where(function ($q) use ($search, $like) {
                $q->where('judul', $like, "%{$search}%")
                  ->orWhere('catatan', $like, "%{$search}%")
                  ->orWhereHas('pengguna', function ($uq) use ($search, $like) {
                      $uq->where('nama', $like, "%{$search}%");
                  })
                  ->orWhereHas('lahan', function ($lq) use ($search, $like) {
                      $lq->where('nama_lahan', $like, "%{$search}%");
                  });
            });
        }

        // Paginasi aktivitas (10 per halaman)
        $daftarAktivitas = $query->orderByDesc('tanggal')->orderByDesc('created_at')->paginate(10)->withQueryString();

        // Daftar semua lahan dalam poktan untuk dropdown filter
        $daftarLahan = Lahan::where('id_kelompok', $idKelompok)
            ->orderBy('nama_lahan')
            ->get(['id_lahan', 'nama_lahan']);

        return view('Pengurus.Content.Monitoring', compact(
            'daftarAktivitas',
            'stats',
            'pengurus',
            'kelompok',
            'daftarLahan'
        ));
    }
}

