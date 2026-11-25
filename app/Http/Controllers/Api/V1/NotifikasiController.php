<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Http\Resources\NotifikasiResource;
use App\Models\Notifikasi;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class NotifikasiController extends Controller
{
    /**
     * Mengambil daftar notifikasi milik pengguna login secara terpaginasi.
     * Query param:
     * - filter: 'semua' (default) | 'belum_dibaca'
     * - page: int
     */
    public function index(Request $request): JsonResponse
    {
        $user = $request->user();
        if (!$user || $user->status !== 'Aktif') {
            return response()->json([
                'success' => false,
                'message' => 'Akses ditolak. Pengguna tidak aktif.',
            ], 403);
        }

        $filter = $request->query('filter', 'semua');
        if (!in_array($filter, ['semua', 'belum_dibaca'], true)) {
            return response()->json([
                'success' => false,
                'message' => 'Filter notifikasi tidak valid. Pilihan yang tersedia: semua, belum_dibaca.',
                'errors' => [
                    'filter' => ['Pilihan filter yang valid adalah: semua, belum_dibaca.'],
                ],
            ], 422);
        }

        $query = Notifikasi::untukPengguna($user->id_pengguna)
            ->with([
                'pengirim:id_pengguna,nama',
                'lahan:id_lahan,nama_lahan',
            ]);

        if ($filter === 'belum_dibaca') {
            $query->belumDibaca();
        }

        $page = max(1, (int) $request->query('page', 1));

        // Urutan: terbaru berdasarkan created_at, secondary id_notifikasi untuk stabilitas pagination
        $notifications = $query->orderByDesc('created_at')
            ->orderByDesc('id_notifikasi')
            ->paginate(15, ['*'], 'page', $page);

        return response()->json([
            'success' => true,
            'message' => 'Daftar notifikasi berhasil diambil.',
            'data' => NotifikasiResource::collection($notifications),
            'meta' => [
                'current_page' => $notifications->currentPage(),
                'last_page' => $notifications->lastPage(),
                'per_page' => $notifications->perPage(),
                'total' => $notifications->total(),
            ],
            'links' => [
                'first' => $notifications->url(1),
                'last' => $notifications->url($notifications->lastPage()),
                'prev' => $notifications->previousPageUrl(),
                'next' => $notifications->nextPageUrl(),
            ],
        ]);
    }

    /**
     * Mengambil jumlah aktual notifikasi belum dibaca milik pengguna login.
     */
    public function unreadCount(Request $request): JsonResponse
    {
        $user = $request->user();
        if (!$user || $user->status !== 'Aktif') {
            return response()->json([
                'success' => false,
                'message' => 'Akses ditolak. Pengguna tidak aktif.',
            ], 403);
        }

        $count = Notifikasi::untukPengguna($user->id_pengguna)
            ->belumDibaca()
            ->count();

        return response()->json([
            'success' => true,
            'message' => 'Jumlah notifikasi belum dibaca berhasil diambil.',
            'data' => [
                'unread_count' => $count,
            ],
        ]);
    }

    /**
     * Menandai satu notifikasi tertentu sebagai sudah dibaca (Idempotent).
     * Anti-IDOR: hanya notifikasi dengan id_penerima milik pengguna login yang dapat diakses.
     */
    public function baca(Request $request, string $id): JsonResponse
    {
        $user = $request->user();
        if (!$user || $user->status !== 'Aktif') {
            return response()->json([
                'success' => false,
                'message' => 'Akses ditolak. Pengguna tidak aktif.',
            ], 403);
        }

        // Anti-IDOR: cari spesifik berdasarkan id_penerima = user login
        $notifikasi = Notifikasi::where('id_penerima', $user->id_pengguna)
            ->where('id_notifikasi', $id)
            ->with(['pengirim:id_pengguna,nama', 'lahan:id_lahan,nama_lahan'])
            ->first();

        if (!$notifikasi) {
            // Gunakan 404 agar tidak membocorkan keberadaan notifikasi milik pengguna lain
            return response()->json([
                'success' => false,
                'message' => 'Notifikasi tidak ditemukan.',
            ], 404);
        }

        // Idempotency: jika belum dibaca, tandai dibaca dan simpan timestamp baca pertama
        if (!$notifikasi->sudah_dibaca) {
            $notifikasi->sudah_dibaca = true;
            $notifikasi->dibaca_pada = now();
            $notifikasi->save();
        }

        return response()->json([
            'success' => true,
            'message' => 'Notifikasi berhasil ditandai sebagai dibaca.',
            'data' => new NotifikasiResource($notifikasi),
        ]);
    }

    /**
     * Menandai seluruh notifikasi belum dibaca milik pengguna login sebagai sudah dibaca (Idempotent).
     */
    public function bacaSemua(Request $request): JsonResponse
    {
        $user = $request->user();
        if (!$user || $user->status !== 'Aktif') {
            return response()->json([
                'success' => false,
                'message' => 'Akses ditolak. Pengguna tidak aktif.',
            ], 403);
        }

        $now = now();
        // Hanya update notifikasi milik pengguna login yang saat ini masih belum dibaca
        $affected = Notifikasi::where('id_penerima', $user->id_pengguna)
            ->where('sudah_dibaca', false)
            ->update([
                'sudah_dibaca' => true,
                'dibaca_pada' => $now,
                'updated_at' => $now,
            ]);

        return response()->json([
            'success' => true,
            'message' => 'Semua notifikasi berhasil ditandai sebagai dibaca.',
            'data' => [
                'updated_count' => $affected,
            ],
        ]);
    }
}
