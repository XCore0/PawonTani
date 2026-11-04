<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Http\Resources\LahanResource;
use App\Models\Lahan;
use App\Http\Requests\Api\StoreLahanRequest;
use App\Http\Requests\Api\UpdateLahanRequest;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class LahanController extends Controller
{
    /**
     * Ambil daftar lahan milik anggota yang sedang login.
     */
    public function index(Request $request): JsonResponse
    {
        $lahan = Lahan::with(['komoditas', 'kelompokTani'])
            ->where('id_pengguna', $request->user()->id_pengguna)
            ->orderByDesc('created_at')
            ->get();

        return response()->json([
            'success' => true,
            'message' => 'Data lahan berhasil diambil.',
            'data' => LahanResource::collection($lahan),
        ]);
    }

    /**
     * Ambil detail data lahan milik anggota yang sedang login.
     */
    public function show(Request $request, Lahan $lahan): JsonResponse
    {
        if ((string) $lahan->id_pengguna !== (string) $request->user()->id_pengguna) {
            return response()->json([
                'success' => false,
                'message' => 'Anda tidak memiliki akses untuk melihat lahan ini.',
            ], 403);
        }

        $lahan->load(['komoditas', 'kelompokTani', 'riwayatVerifikasi']);

        return response()->json([
            'success' => true,
            'message' => 'Detail data lahan berhasil diambil.',
            'data' => new LahanResource($lahan),
        ]);
    }

    /**
     * Daftarkan lahan baru untuk anggota yang sedang login.
     */
    public function store(StoreLahanRequest $request): JsonResponse
    {
        $user = $request->user();

        $lahan = new Lahan([
            'id_pengguna' => $user->id_pengguna,
            'id_kelompok' => $user->id_kelompok,
            'nama_lahan' => $request->input('nama_lahan'),
            'luas' => $request->input('luas'),
            'alamat_lahan' => $request->input('alamat_lahan'),
            'latitude' => $request->input('latitude'),
            'longitude' => $request->input('longitude'),
            'id_komoditas' => $request->input('id_komoditas'),
            'musim_tanam' => $request->input('musim_tanam'),
            'keterangan' => $request->input('keterangan'),
            'status_operasional' => 'aktif',
            'status_verifikasi' => 'menunggu',
        ]);

        $saved = $lahan->saveWithUniqueRetry();

        if (!$saved) {
            return response()->json([
                'success' => false,
                'message' => 'Gagal menyimpan data lahan. Silakan coba lagi.',
            ], 500);
        }

        $lahan->load(['komoditas', 'kelompokTani']);

        return response()->json([
            'success' => true,
            'message' => 'Lahan berhasil didaftarkan.',
            'data' => new LahanResource($lahan),
        ]);
    }

    /**
     * Perbarui data lahan milik anggota yang sedang login.
     */
    public function update(UpdateLahanRequest $request, Lahan $lahan): JsonResponse
    {
        $lahan->fill($request->only([
            'nama_lahan',
            'luas',
            'alamat_lahan',
            'latitude',
            'longitude',
            'id_komoditas',
            'musim_tanam',
            'keterangan',
        ]));

        $updated = $lahan->save();

        if (!$updated) {
            return response()->json([
                'success' => false,
                'message' => 'Gagal memperbarui data lahan. Silakan coba lagi.',
            ], 500);
        }

        $lahan->load(['komoditas', 'kelompokTani']);

        return response()->json([
            'success' => true,
            'message' => 'Lahan berhasil diperbarui.',
            'data' => new LahanResource($lahan),
        ]);
    }

    /**
     * Hapus data lahan milik anggota yang sedang login (soft delete).
     */
    public function destroy(Request $request, Lahan $lahan): JsonResponse
    {
        if ((string) $lahan->id_pengguna !== (string) $request->user()->id_pengguna) {
            return response()->json([
                'success' => false,
                'message' => 'Anda tidak memiliki akses untuk menghapus lahan ini.',
            ], 403);
        }

        $lahan->delete();

        return response()->json([
            'success' => true,
            'message' => 'Data lahan berhasil dihapus.',
        ]);
    }
}
