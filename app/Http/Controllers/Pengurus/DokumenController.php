<?php

namespace App\Http\Controllers\Pengurus;

use App\Http\Controllers\Controller;
use App\Models\AksesDokumen;
use App\Models\DokumenAnggota;
use App\Models\DokumenLahan;
use App\Models\Lahan;
use App\Models\LogAksesDokumen;
use App\Models\Pengguna;
use Illuminate\Http\Request;
use Illuminate\View\View;

class DokumenController extends Controller
{
    /**
     * Tampilan utama Dokumen Anggota dan Lahan di area kerja Pengurus.
     * Pengurus hanya dapat melihat anggota kelompoknya dan hanya dapat
     * membuka dokumen yang telah diberikan izin aktif oleh pemiliknya.
     */
    public function index(Request $request): View
    {
        /** @var \App\Models\Pengguna $pengurus */
        $pengurus = $request->user();
        abort_unless($pengurus && $pengurus->role === 'Pengurus' && $pengurus->id_kelompok, 403);

        $query = Pengguna::query()
            ->where('role', 'Anggota')
            ->where('id_kelompok', $pengurus->id_kelompok)
            ->whereHas('anggota', fn ($q) => $q->where('status_keanggotaan', '!=', 'Dihapus'))
            ->with([
                'anggota',
                'dokumenIdentitas',
                'lahan' => fn ($q) => $q->with('dokumenSppt'),
            ]);

        if ($search = trim((string) $request->query('q'))) {
            $like = \Illuminate\Support\Facades\DB::getDriverName() === 'pgsql' ? 'ILIKE' : 'LIKE';
            $query->where(fn ($q) => $q->where('nama', $like, "%{$search}%")
                ->orWhere('nik', $like, "%{$search}%")
                ->orWhere('username', $like, "%{$search}%"));
        }

        $anggotaList = $query->orderBy('nama')->paginate(15)->withQueryString();

        // Ambil seluruh izin akses dokumen yang aktif untuk kelompok ini
        $activePermissions = AksesDokumen::where('id_kelompok', $pengurus->id_kelompok)
            ->where('status_akses', 'aktif')
            ->where(function ($q) {
                $q->whereNull('waktu_berakhir')->orWhere('waktu_berakhir', '>=', now());
            })
            ->get();

        // Kelompokkan izin berdasarkan id_pengguna dan tipe_dokumen untuk lookup cepat
        $permissionMap = [];
        foreach ($activePermissions as $perm) {
            $permissionMap[$perm->id_pengguna][$perm->tipe_dokumen][$perm->id_dokumen] = $perm;
            // id_dokumen 0 menandakan izin untuk seluruh dokumen kategori tersebut
            $permissionMap[$perm->id_pengguna][$perm->tipe_dokumen][0] = $perm;
        }

        // Statistik ringkas kelompok
        $allAnggota = Pengguna::where('role', 'Anggota')
            ->where('id_kelompok', $pengurus->id_kelompok)
            ->whereHas('anggota', fn ($q) => $q->where('status_keanggotaan', '!=', 'Dihapus'));

        $totalAnggota = (clone $allAnggota)->count();

        $totalDokumenIdentitasLengkap = (clone $allAnggota)
            ->whereHas('dokumenIdentitas', fn ($q) => $q->where('jenis', 'KTP'))
            ->whereHas('dokumenIdentitas', fn ($q) => $q->where('jenis', 'KK'))
            ->count();

        $totalSpptKurang = (clone $allAnggota)
            ->whereHas('lahan', fn ($q) => $q->whereDoesntHave('dokumenSppt'))
            ->count();

        $totalDokumenDibagikan = $activePermissions->count();

        $anggota = $anggotaList;

        // 10 log aktivitas akses dokumen terbaru kelompok
        $recentLogs = LogAksesDokumen::whereHas('pengguna', fn ($q) => $q->where('id_kelompok', $pengurus->id_kelompok))
            ->with(['pengguna', 'aksesDokumen'])
            ->latest('waktu_akses')
            ->take(10)
            ->get();

        return view('Pengurus.Content.Dokumen.Index', compact(
            'anggota',
            'anggotaList',
            'permissionMap',
            'totalAnggota',
            'totalDokumenIdentitasLengkap',
            'totalSpptKurang',
            'totalDokumenDibagikan',
            'recentLogs'
        ));
    }
}
