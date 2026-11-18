<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\KelompokTani;
use App\Models\Pengguna;
use Illuminate\Http\Request;
use Illuminate\View\View;

class DokumenERdkkController extends Controller
{
    public function index(Request $request): View
    {
        $query = Pengguna::query()
            ->where('role', 'Anggota')
            ->whereHas('anggota', fn ($q) => $q->where('status_keanggotaan', '!=', 'Dihapus'))
            ->with([
                'kelompokTani:id_kelompok,nama_kelompok',
                'anggota',
                'dokumenIdentitas',
                'lahan' => fn ($q) => $q->with('dokumenSppt'),
            ]);

        if ($groupId = $request->query('kelompok')) {
            $query->where('id_kelompok', $groupId);
        }
        if ($status = $request->query('status')) {
            $query->whereHas('anggota', fn ($q) => $q->where('status_keanggotaan', $status));
        }
        if ($search = trim((string) $request->query('q'))) {
            $like = \Illuminate\Support\Facades\DB::getDriverName() === 'pgsql' ? 'ILIKE' : 'LIKE';
            $query->where(fn ($q) => $q->where('nama', $like, "%{$search}%")
                ->orWhere('nik', $like, "%{$search}%")
                ->orWhere('username', $like, "%{$search}%"));
        }

        $anggota = $query->orderBy('nama')->paginate(15)->withQueryString();
        $kelompok = KelompokTani::orderBy('nama_kelompok')->get(['id_kelompok', 'nama_kelompok']);

        $allMembers = Pengguna::where('role', 'Anggota')->whereHas('anggota', fn ($q) => $q->where('status_keanggotaan', '!=', 'Dihapus'));
        $totalAnggota = (clone $allMembers)->count();
        $totalDokumenIdentitasLengkap = (clone $allMembers)->whereHas('dokumenIdentitas', function ($q) {
            $q->where('jenis', 'KTP');
        })->whereHas('dokumenIdentitas', function ($q) {
            $q->where('jenis', 'KK');
        })->count();
        $totalSpptKurang = (clone $allMembers)->whereHas('lahan', function ($q) {
            $q->whereDoesntHave('dokumenSppt');
        })->count();

        return view('Admin.Content.DokumenERDKK', compact(
            'anggota', 'kelompok', 'totalAnggota', 'totalDokumenIdentitasLengkap', 'totalSpptKurang'
        ));
    }
}
