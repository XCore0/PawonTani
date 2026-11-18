<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Anggota;
use App\Models\KelompokTani;
use App\Models\Pengguna;
use App\Services\AccountErasureService;
use App\Services\AuditLogger;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\View\View;
use Illuminate\Http\RedirectResponse;

class AnggotaController extends Controller
{
    public function index(Request $request): View
    {
        $query = Pengguna::query()
            ->where('role', 'Anggota')
            ->whereHas('anggota')
            ->with(['anggota', 'kelompokTani:id_kelompok,nama_kelompok']);

        if ($status = $request->query('status')) {
            $query->whereHas('anggota', fn ($q) => $q->where('status_keanggotaan', $status));
        }
        if ($groupId = $request->query('kelompok')) {
            $query->where('id_kelompok', $groupId);
        }
        if ($search = trim((string) $request->query('q'))) {
            $operator = DB::getDriverName() === 'pgsql' ? 'ILIKE' : 'LIKE';
            $query->where(fn ($q) => $q->where('nama', $operator, "%{$search}%")
                ->orWhere('nik', $operator, "%{$search}%")
                ->orWhere('username', $operator, "%{$search}%"));
        }

        $members = $query->orderBy('nama')->paginate(20)->withQueryString();
        $groups = KelompokTani::orderBy('nama_kelompok')->get(['id_kelompok', 'nama_kelompok']);
        $stats = [
            'aktif' => Anggota::where('status_keanggotaan', 'Aktif')->count(),
            'keluar' => Anggota::where('status_keanggotaan', 'Keluar')->count(),
            'tidak_aktif' => Anggota::where('status_keanggotaan', 'Tidak Aktif')->count(),
        ];

        return view('Admin.Content.Anggota', compact('members', 'groups', 'stats'));
    }

    public function restore(Request $request, string $idPengguna): RedirectResponse
    {
        $member = Pengguna::where('role', 'Anggota')->whereKey($idPengguna)->with('anggota', 'kelompokTani')->firstOrFail();
        abort_unless($member->anggota && $member->anggota->status_keanggotaan === 'Keluar', 422, 'Hanya anggota yang berstatus Keluar yang dapat diaktifkan kembali.');
        abort_unless($member->kelompokTani && $member->kelompokTani->status === 'Aktif', 422, 'Kelompok asal tidak aktif atau tidak ditemukan. Pilih kelompok aktif terlebih dahulu melalui pengelolaan keanggotaan.');

        DB::transaction(function () use ($member) {
            $member->update(['status' => 'Aktif']);
            $member->anggota->update([
                'status_keanggotaan' => 'Aktif',
                'tanggal_keluar' => null,
                'alasan_keluar' => null,
                'dikeluarkan_oleh' => null,
            ]);
        });

        AuditLogger::log('anggota.aktifkan_kembali', 'Admin mengaktifkan kembali keanggotaan anggota.', 'Pengguna', $member->id_pengguna, $member->id_kelompok, $request);
        return back()->with('success', 'Keanggotaan anggota berhasil diaktifkan kembali.');
    }

    public function destroy(Request $request, string $idPengguna, AccountErasureService $erasure): RedirectResponse
    {
        $validated = $request->validate([
            'password' => ['required', 'string'],
            'konfirmasi' => ['required', 'in:HAPUS PERMANEN'],
        ], [
            'password.required' => 'Masukkan password akun Admin untuk mengonfirmasi tindakan ini.',
            'konfirmasi.in' => 'Ketik HAPUS PERMANEN untuk mengonfirmasi tindakan ini.',
        ]);

        if (!Hash::check($validated['password'], $request->user()->password)) {
            return back()->withErrors(['password' => 'Password Admin tidak sesuai.'])->withInput();
        }

        $member = Pengguna::where('role', 'Anggota')->whereKey($idPengguna)->firstOrFail();
        abort_unless($member->status !== 'Dihapus', 422, 'Akun ini sudah dihapus secara permanen.');
        $erasure->erase($member, $request, 'Penghapusan permanen oleh Admin/PPL.');

        return redirect()->route('admin.anggota')->with('success', 'Data pribadi dan dokumen anggota telah dihapus. Referensi audit teknis tetap dianonimkan untuk menjaga integritas riwayat.');
    }
}
