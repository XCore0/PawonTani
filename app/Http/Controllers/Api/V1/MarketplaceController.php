<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Models\Produk;
use App\Services\MarketplaceService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\ValidationException;
use Symfony\Component\HttpKernel\Exception\AccessDeniedHttpException;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Throwable;

class MarketplaceController extends Controller
{
    public function __construct(
        protected MarketplaceService $marketplaceService
    ) {}

    /**
     * Mengambil daftar produk milik petani yang sedang login (untuk Produk Saya di mobile).
     */
    public function indexProdukSaya(Request $request): JsonResponse
    {
        $user = $request->user();
        if (!$user) {
            return response()->json(['success' => false, 'message' => 'Unauthenticated'], 401);
        }

        $produk = $this->marketplaceService->getProdukForUser($user, $request);

        return response()->json([
            'success' => true,
            'message' => 'Daftar produk saya berhasil diambil.',
            'data' => $produk->map(function ($item) {
                return [
                    'id_produk' => $item->id_produk,
                    'id_pengguna' => $item->id_pengguna,
                    'id_komoditas' => $item->id_komoditas,
                    'nama_komoditas' => $item->komoditas?->nama_komoditas,
                    'nama_produk' => $item->nama_produk,
                    'foto_produk' => $item->foto_produk,
                    'deskripsi' => $item->deskripsi,
                    'jenis_harga' => $item->jenis_harga,
                    'harga' => $item->harga,
                    'harga_min' => $item->harga_min,
                    'harga_max' => $item->harga_max,
                    'harga_formatted' => $item->harga_formatted,
                    'satuan' => $item->satuan,
                    'stok_fisik' => $item->stok_fisik,
                    'stok_ditahan' => $item->stok_ditahan,
                    'stok_tersedia' => $item->stok_tersedia,
                    'total_terjual' => $item->total_terjual,
                    'status' => $item->status,
                    'created_at' => $item->created_at?->toIso8601String(),
                ];
            }),
        ]);
    }

    /**
     * Tambah produk marketplace baru dari Flutter.
     */
    public function storeProduk(Request $request): JsonResponse
    {
        $user = $request->user();
        if (!$user) {
            return response()->json(['success' => false, 'message' => 'Unauthenticated'], 401);
        }

        $validated = $request->validate([
            'nama_produk' => 'required|string|max:150',
            'id_komoditas' => 'nullable|string|exists:komoditas,id_komoditas',
            'deskripsi' => 'nullable|string',
            'jenis_harga' => 'required|in:tetap,rentang',
            'harga' => 'nullable|numeric|min:0',
            'harga_min' => 'nullable|numeric|min:0',
            'harga_max' => 'nullable|numeric|min:0',
            'satuan' => 'required|string|max:30',
            'stok_fisik' => 'required|numeric|min:0',
            'status' => 'nullable|in:aktif,tidak_aktif,stok_habis,diarsipkan',
            'foto' => 'nullable|image|max:5120', // Maks 5MB
        ]);

        try {
            $foto = $request->file('foto');
            $produk = $this->marketplaceService->createProduk($user, $validated, $foto);

            return response()->json([
                'success' => true,
                'message' => 'Produk marketplace berhasil ditambahkan.',
                'data' => $produk,
            ], 201);
        } catch (Throwable $e) {
            return response()->json([
                'success' => false,
                'message' => 'Gagal menambahkan produk: ' . $e->getMessage(),
            ], 422);
        }
    }

    /**
     * Detail produk milik petani.
     */
    public function showProduk(Request $request, string $id): JsonResponse
    {
        $user = $request->user();
        $produk = Produk::with('komoditas')->where('id_produk', $id)->firstOrFail();

        if ($produk->id_pengguna !== $user->id_pengguna) {
            return response()->json(['success' => false, 'message' => 'Akses ditolak.'], 403);
        }

        return response()->json([
            'success' => true,
            'message' => 'Detail produk berhasil diambil.',
            'data' => [
                'id_produk' => $produk->id_produk,
                'id_pengguna' => $produk->id_pengguna,
                'id_komoditas' => $produk->id_komoditas,
                'nama_komoditas' => $produk->komoditas?->nama_komoditas,
                'nama_produk' => $produk->nama_produk,
                'foto_produk' => $produk->foto_produk,
                'deskripsi' => $produk->deskripsi,
                'jenis_harga' => $produk->jenis_harga,
                'harga' => $produk->harga,
                'harga_min' => $produk->harga_min,
                'harga_max' => $produk->harga_max,
                'harga_formatted' => $produk->harga_formatted,
                'satuan' => $produk->satuan,
                'stok_fisik' => $produk->stok_fisik,
                'stok_ditahan' => $produk->stok_ditahan,
                'stok_tersedia' => $produk->stok_tersedia,
                'total_terjual' => $produk->total_terjual,
                'status' => $produk->status,
                'created_at' => $produk->created_at?->toIso8601String(),
            ],
        ]);
    }

    /**
     * Edit / update produk oleh petani.
     */
    public function updateProduk(Request $request, string $id): JsonResponse
    {
        $user = $request->user();

        $validated = $request->validate([
            'nama_produk' => 'sometimes|string|max:150',
            'id_komoditas' => 'nullable|string|exists:komoditas,id_komoditas',
            'deskripsi' => 'nullable|string',
            'jenis_harga' => 'sometimes|in:tetap,rentang',
            'harga' => 'nullable|numeric|min:0',
            'harga_min' => 'nullable|numeric|min:0',
            'harga_max' => 'nullable|numeric|min:0',
            'satuan' => 'sometimes|string|max:30',
            'stok_fisik' => 'sometimes|numeric|min:0',
            'status' => 'sometimes|in:aktif,tidak_aktif,stok_habis,diarsipkan',
            'foto' => 'nullable|image|max:5120',
        ]);

        try {
            $foto = $request->file('foto');
            $produk = $this->marketplaceService->updateProduk($user, $id, $validated, $foto);

            return response()->json([
                'success' => true,
                'message' => 'Produk marketplace berhasil diperbarui.',
                'data' => $produk,
            ]);
        } catch (AccessDeniedHttpException $e) {
            return response()->json(['success' => false, 'message' => $e->getMessage()], 403);
        } catch (Throwable $e) {
            return response()->json([
                'success' => false,
                'message' => 'Gagal memperbarui produk: ' . $e->getMessage(),
            ], 422);
        }
    }

    /**
     * Hapus / Arsipkan produk.
     */
    public function destroyProduk(Request $request, string $id): JsonResponse
    {
        $user = $request->user();

        try {
            $this->marketplaceService->archiveOrDeleteProduk($user, $id);

            return response()->json([
                'success' => true,
                'message' => 'Produk berhasil diarsipkan.',
            ]);
        } catch (AccessDeniedHttpException $e) {
            return response()->json(['success' => false, 'message' => $e->getMessage()], 403);
        } catch (Throwable $e) {
            return response()->json([
                'success' => false,
                'message' => 'Gagal mengarsipkan produk: ' . $e->getMessage(),
            ], 422);
        }
    }
}
