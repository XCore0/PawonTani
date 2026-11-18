<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Models\KelompokTani;
use App\Models\PermintaanKeanggotaan;
use Illuminate\Database\QueryException;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use App\Services\AuditLogger;

class PermintaanKeanggotaanController extends Controller
{
    public function groups(Request $request): JsonResponse
    {
        abort_unless($request->user()?->role === 'Anggota' && $request->user()->status === 'Aktif', 403);

        $groups = KelompokTani::query()
            ->where('status', 'Aktif')
            ->orderBy('nama_kelompok')
            ->get(['id_kelompok', 'nama_kelompok', 'alamat']);

        return response()->json(['success' => true, 'data' => $groups]);
    }

    public function mine(Request $request): JsonResponse
    {
        $user = $request->user();
        abort_unless($user?->role === 'Anggota', 403);

        $requests = PermintaanKeanggotaan::query()
            ->with('kelompokTani:id_kelompok,nama_kelompok')
            ->where('id_pengguna', $user->id_pengguna)
            ->latest()
            ->limit(10)
            ->get(['id', 'id_pengguna', 'id_kelompok', 'alasan', 'status', 'catatan_admin', 'ditinjau_pada', 'created_at']);

        return response()->json(['success' => true, 'data' => $requests]);
    }

    public function store(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'id_kelompok' => ['required', 'string', 'exists:kelompok_tani,id_kelompok'],
            'alasan' => ['nullable', 'string', 'max:500'],
        ]);

        $user = $request->user();
        abort_unless($user && $user->role === 'Anggota' && $user->status === 'Aktif', 403, 'Hanya akun Anggota aktif yang dapat mengajukan keanggotaan.');

        $group = KelompokTani::query()->whereKey($validated['id_kelompok'])->where('status', 'Aktif')->first();
        abort_unless($group, 422, 'Kelompok tani tujuan tidak aktif atau tidak tersedia.');

        try {
            $application = DB::transaction(function () use ($user, $validated) {
                $lockedUser = $user->newQuery()->whereKey($user->id_pengguna)->lockForUpdate()->firstOrFail();
                abort_unless($lockedUser->status === 'Aktif' && $lockedUser->role === 'Anggota', 403);

                $membership = $lockedUser->anggota()->lockForUpdate()->first();
                abort_if($membership && $membership->status_keanggotaan === 'Aktif', 422, 'Keanggotaan Anda masih aktif. Keluar dari kelompok saat ini terlebih dahulu.');

                $pending = PermintaanKeanggotaan::query()
                    ->where('id_pengguna', $lockedUser->id_pengguna)
                    ->where('status', 'Menunggu')
                    ->lockForUpdate()
                    ->exists();
                abort_if($pending, 422, 'Anda masih memiliki permintaan keanggotaan yang menunggu keputusan Admin.');

                return PermintaanKeanggotaan::create([
                    'id_pengguna' => $lockedUser->id_pengguna,
                    'id_kelompok' => $validated['id_kelompok'],
                    'alasan' => $validated['alasan'] ?? null,
                    'status' => 'Menunggu',
                ]);
            });
        } catch (QueryException $exception) {
            report($exception);
            return response()->json(['success' => false, 'message' => 'Permintaan belum dapat disimpan. Silakan coba kembali.'], 500);
        }

        AuditLogger::log('keanggotaan.diajukan', 'Anggota mengajukan bergabung ke kelompok tani baru.', 'PermintaanKeanggotaan', $application->id, $group->id_kelompok, $request);

        return response()->json([
            'success' => true,
            'message' => 'Permintaan keanggotaan berhasil dikirim dan menunggu pemeriksaan Admin.',
            'data' => $application->load('kelompokTani:id_kelompok,nama_kelompok'),
        ], 201);
    }
}
