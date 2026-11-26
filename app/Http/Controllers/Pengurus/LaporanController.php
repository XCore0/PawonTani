<?php

namespace App\Http\Controllers\Pengurus;

use App\Http\Controllers\Controller;
use App\Models\AktivitasTanam;
use App\Models\Lahan;
use App\Models\Panen;
use App\Models\Pengguna;
use App\Models\TransaksiPenjualan;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Http\Request;
use Illuminate\View\View;

class LaporanController extends Controller
{
    public function index(Request $request): View
    {
        return view('Pengurus.Content.Laporan.Index', $this->getReportData($request));
    }

    public function cetak(Request $request): View
    {
        return view('Pengurus.Content.Laporan.Print', $this->getReportData($request, true));
    }

    private function getReportData(Request $request, bool $forPrint = false): array
    {
        /** @var Pengguna|null $pengurus */
        $pengurus = $request->user();

        abort_unless(
            $pengurus
                && $pengurus->role === 'Pengurus'
                && $pengurus->status === 'Aktif'
                && filled($pengurus->id_kelompok),
            403,
            'Akses laporan hanya tersedia untuk Pengurus aktif yang terdaftar pada kelompok tani.'
        );

        $kelompok = $pengurus->kelompokTani;
        abort_unless($kelompok && $kelompok->status === 'Aktif', 403, 'Kelompok tani tidak aktif atau tidak ditemukan.');

        $validated = $request->validate([
            'tanggal_mulai' => ['nullable', 'date'],
            'tanggal_selesai' => ['nullable', 'date', 'after_or_equal:tanggal_mulai'],
        ], [
            'tanggal_mulai.date' => 'Tanggal mulai tidak valid.',
            'tanggal_selesai.date' => 'Tanggal selesai tidak valid.',
            'tanggal_selesai.after_or_equal' => 'Tanggal selesai harus sama atau setelah tanggal mulai.',
        ]);

        $tanggalMulai = Carbon::parse($validated['tanggal_mulai'] ?? now()->startOfMonth()->toDateString())->startOfDay();
        $tanggalSelesai = Carbon::parse($validated['tanggal_selesai'] ?? now()->toDateString())->endOfDay();
        $idKelompok = $pengurus->id_kelompok;

        $anggotaQuery = Pengguna::query()
            ->with('anggota')
            ->where('role', 'Anggota')
            ->where('id_kelompok', $idKelompok)
            ->whereHas('anggota', fn ($query) => $query->where('status_keanggotaan', '!=', 'Keluar'));

        $totalAnggota = (clone $anggotaQuery)->count();
        $anggota = (clone $anggotaQuery)
            ->orderBy('nama')
            ->get(['id_pengguna', 'nama', 'no_telepon', 'status', 'created_at']);

        $lahanQuery = Lahan::query()->where('id_kelompok', $idKelompok);
        $totalLahan = (clone $lahanQuery)->count();
        $totalLuasLahan = (float) (clone $lahanQuery)->sum('luas');

        $aktivitasBase = AktivitasTanam::query()
            ->where('id_kelompok', $idKelompok)
            ->whereBetween('tanggal', [$tanggalMulai->toDateString(), $tanggalSelesai->toDateString()]);

        $totalAktivitas = (clone $aktivitasBase)->count();
        $totalBiayaAktivitas = (float) (clone $aktivitasBase)->sum('biaya');
        $aktivitas = (clone $aktivitasBase)
            ->with(['pengguna:id_pengguna,nama', 'lahan:id_lahan,nama_lahan'])
            ->orderByDesc('tanggal')
            ->orderByDesc('created_at')
            ->when(!$forPrint, fn ($query) => $query->limit(12))
            ->get();

        $panenBase = Panen::query()
            ->where('id_kelompok', $idKelompok)
            ->whereBetween('tanggal_panen', [$tanggalMulai->toDateString(), $tanggalSelesai->toDateString()]);

        $totalPanen = (clone $panenBase)->count();
        $totalPanenDisetujui = (clone $panenBase)->where('status', 'disetujui')->count();
        $totalBeratPanen = (float) (clone $panenBase)->sum('berat_kg');
        $estimasiNilaiPanen = (float) (clone $panenBase)
            ->where('status', 'disetujui')
            ->sum(DB::raw('berat_kg * harga_per_kg'));

        $panen = (clone $panenBase)
            ->with(['pengguna:id_pengguna,nama', 'lahan:id_lahan,nama_lahan'])
            ->orderByDesc('tanggal_panen')
            ->orderByDesc('created_at')
            ->when(!$forPrint, fn ($query) => $query->limit(12))
            ->get();

        $penjualanTersedia = Schema::hasTable('transaksi_penjualan')
            && Schema::hasColumn('transaksi_penjualan', 'id_kelompok');
        $penjualan = collect();
        $totalTransaksiPenjualan = 0;
        $totalBeratTerjual = 0.0;
        $totalNilaiPenjualan = 0.0;

        if ($penjualanTersedia) {
            $penjualanBase = TransaksiPenjualan::query()
                ->where('id_kelompok', $idKelompok)
                ->where('status_pembayaran', '!=', 'dibatalkan')
                ->whereBetween('tanggal_transaksi', [$tanggalMulai->toDateString(), $tanggalSelesai->toDateString()]);

            $totalTransaksiPenjualan = (clone $penjualanBase)->count();
            $totalBeratTerjual = (float) (clone $penjualanBase)->where('satuan', 'kg')->sum('jumlah');
            $totalNilaiPenjualan = (float) (clone $penjualanBase)->sum('total_harga');

            $penjualan = (clone $penjualanBase)
                ->with([
                    'panen:id_panen,kode_panen',
                    'penjual:id_pengguna,nama',
                    'pencatat:id_pengguna,nama',
                    'komoditas:id_komoditas,nama_komoditas',
                ])
                ->orderByDesc('tanggal_transaksi')
                ->orderByDesc('created_at')
                ->when(!$forPrint, fn ($query) => $query->limit(12))
                ->get();
        }

        return [
            'pengurus' => $pengurus,
            'kelompok' => $kelompok,
            'anggota' => $anggota,
            'totalAnggota' => $totalAnggota,
            'totalLahan' => $totalLahan,
            'totalLuasLahan' => $totalLuasLahan,
            'aktivitas' => $aktivitas,
            'totalAktivitas' => $totalAktivitas,
            'totalBiayaAktivitas' => $totalBiayaAktivitas,
            'panen' => $panen,
            'totalPanen' => $totalPanen,
            'totalPanenDisetujui' => $totalPanenDisetujui,
            'totalBeratPanen' => $totalBeratPanen,
            'estimasiNilaiPanen' => $estimasiNilaiPanen,
            'penjualan' => $penjualan,
            'penjualanTersedia' => $penjualanTersedia,
            'totalTransaksiPenjualan' => $totalTransaksiPenjualan,
            'totalBeratTerjual' => $totalBeratTerjual,
            'totalNilaiPenjualan' => $totalNilaiPenjualan,
            'tanggalMulai' => $tanggalMulai,
            'tanggalSelesai' => $tanggalSelesai,
            'forPrint' => $forPrint,
        ];
    }
}
