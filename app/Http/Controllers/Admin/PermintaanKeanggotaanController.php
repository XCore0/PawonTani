<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Anggota;
use App\Models\PermintaanKeanggotaan;
use App\Services\AuditLogger;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\View\View;

class PermintaanKeanggotaanController extends Controller
{
    public function index(Request $request): View
    {
        $query = PermintaanKeanggotaan::query()
            ->with(['pengguna:id_pengguna,nama,status,id_kelompok', 'kelompokTani:id_kelompok,nama_kelompok', 'peninjau:id_pengguna,nama']);

        if ($status = $request->query('status')) {
            $query->where('status', $status);
        } else {
            $query->where('status', 'Menunggu');
        }

        $requests = $query->latest()->paginate(20)->withQueryString();
        $stats = [
            'menunggu' => PermintaanKeanggotaan::where('status', 'Menunggu')->count(),
            'disetujui' => PermintaanKeanggotaan::where('status', 'Disetujui')->count(),
            'ditolak' => PermintaanKeanggotaan::where('status', 'Ditolak')->count(),
        ];

        return view('Admin.Content.PermintaanKeanggotaan', compact('requests', 'stats'));
    }

    public function approve(Request $request, int $id): RedirectResponse
    {
        $validated = $request->validate(['catatan_admin' => ['nullable', 'string', 'max:500']]);

        $application = DB::transaction(function () use ($id, $request, $validated) {
            $application = PermintaanKeanggotaan::query()->lockForUpdate()->findOrFail($id);
            abort_unless($application->status === 'Menunggu', 422, 'Permintaan ini sudah diproses.');

            $member = $application->pengguna()->lockForUpdate()->firstOrFail();
            $group = $application->kelompokTani()->first();
            abort_unless($member->role === 'Anggota' && $member->status === 'Aktif', 422, 'Akun anggota tidak aktif atau tidak tersedia.');
            abort_unless($group && $group->status === 'Aktif', 422, 'Kelompok tujuan tidak aktif atau tidak tersedia.');

            $membership = $member->anggota()->first();
            abort_if($membership && $membership->status_keanggotaan === 'Aktif', 422, 'Anggota sudah aktif pada suatu kelompok. Permintaan ini tidak dapat disetujui.');
            if (!$membership) {
                $membership = new Anggota(['id_pengguna' => $member->id_pengguna]);
                $member->anggota()->save($membership);
                $membership = $member->anggota()->first();
            }

            $member->forceFill(['id_kelompok' => $group->id_kelompok])->save();
            $membership->forceFill([
                'status_keanggotaan' => 'Aktif',
                'tanggal_keluar' => null,
                'alasan_keluar' => null,
                'dikeluarkan_oleh' => null,
            ])->save();

            $application->update([
                'status' => 'Disetujui',
                'ditinjau_oleh' => $request->user()->id_pengguna,
                'catatan_admin' => $validated['catatan_admin'] ?? null,
                'ditinjau_pada' => now(),
            ]);

            return $application;
        });

        AuditLogger::log('keanggotaan.disetujui', 'Admin menyetujui permintaan bergabung ke kelompok baru.', 'PermintaanKeanggotaan', $application->id, $application->id_kelompok, $request);

        return back()->with('success', 'Permintaan keanggotaan disetujui. Anggota kini terdaftar pada kelompok tujuan.');
    }

    public function reject(Request $request, int $id): RedirectResponse
    {
        $validated = $request->validate(['catatan_admin' => ['nullable', 'string', 'max:500']]);

        $application = DB::transaction(function () use ($id, $request, $validated) {
            $application = PermintaanKeanggotaan::query()->lockForUpdate()->findOrFail($id);
            abort_unless($application->status === 'Menunggu', 422, 'Permintaan ini sudah diproses.');

            $application->update([
                'status' => 'Ditolak',
                'ditinjau_oleh' => $request->user()->id_pengguna,
                'catatan_admin' => $validated['catatan_admin'] ?? null,
                'ditinjau_pada' => now(),
            ]);

            return $application;
        });

        AuditLogger::log('keanggotaan.ditolak', 'Admin menolak permintaan bergabung ke kelompok baru.', 'PermintaanKeanggotaan', $application->id, $application->id_kelompok, $request);

        return back()->with('success', 'Permintaan keanggotaan ditolak.');
    }
}
