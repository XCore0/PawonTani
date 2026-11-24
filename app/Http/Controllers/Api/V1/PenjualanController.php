<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Services\PenjualanService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Symfony\Component\HttpKernel\Exception\AccessDeniedHttpException;
use Throwable;

class PenjualanController extends Controller
{
    public function __construct(
        protected PenjualanService $penjualanService
    ) {}

    /**
     * Mengambil riwayat transaksi penjualan milik petani yang sedang login.
     */
    public function index(Request $request): JsonResponse
    {
        $user = $request->user();
        if (!$user) {
            return response()->json(['success' => false, 'message' => 'Unauthenticated'], 401);
        }

        $transaksi = $this->penjualanService->getPenjualanForUser($user, $request);

        return response()->json([
            'success' => true,
            'message' => 'Daftar transaksi penjualan berhasil diambil.',
            'data' => $transaksi->map(function ($item) {
                return [
                    'id_penjualan' => $item->id_penjualan,
                    'kode_transaksi' => $item->kode_transaksi,
                    'id_pengguna' => $item->id_pengguna,
                    'id_produk' => $item->id_produk,
                    'id_komoditas' => $item->id_komoditas,
                    'nama_komoditas' => $item->komoditas?->nama_komoditas,
                    'nama_produk' => $item->nama_produk,
                    'nama_pembeli' => $item->nama_pembeli,
                    'no_telepon_pembeli' => $item->no_telepon_pembeli,
                    'jumlah' => $item->jumlah,
                    'satuan' => $item->satuan,
                    'harga_satuan' => $item->harga_satuan,
                    'total_harga' => $item->total_harga,
                    'total_dibayar' => $item->total_dibayar,
                    'sisa_pembayaran' => $item->sisa_pembayaran,
                    'status_pembayaran' => $item->status_pembayaran,
                    'status_label' => $item->status_label,
                    'status_penyerahan' => $item->status_penyerahan,
                    'tanggal_transaksi' => $item->tanggal_transaksi?->toDateString(),
                    'waktu_transaksi' => $item->waktu_transaksi,
                    'metode_pembayaran' => $item->metode_pembayaran,
                    'catatan' => $item->catatan,
                    'alasan_batal' => $item->alasan_batal,
                    'tanggal_diserahkan' => $item->tanggal_diserahkan?->toIso8601String(),
                    'tanggal_dibatalkan' => $item->tanggal_dibatalkan?->toIso8601String(),
                    'created_at' => $item->created_at?->toIso8601String(),
                ];
            }),
        ]);
    }

    /**
     * Catat transaksi penjualan baru (baik terhubung ke produk marketplace maupun mandiri).
     */
    public function store(Request $request): JsonResponse
    {
        $user = $request->user();
        if (!$user) {
            return response()->json(['success' => false, 'message' => 'Unauthenticated'], 401);
        }

        $validated = $request->validate([
            'id_produk' => 'nullable|string|exists:produk,id_produk',
            'id_komoditas' => 'nullable|string|exists:komoditas,id_komoditas',
            'nama_produk' => 'nullable|string|max:150',
            'nama_pembeli' => 'nullable|string|max:150',
            'no_telepon_pembeli' => 'nullable|string|max:30',
            'jumlah' => 'required|numeric|min:0.01',
            'satuan' => 'nullable|string|max:30',
            'harga_satuan' => 'required|numeric|min:1',
            'nominal_dibayar' => 'nullable|numeric|min:0',
            'status_pembayaran' => 'nullable|in:belum_dibayar,dp,lunas,tempo',
            'tanggal_transaksi' => 'nullable|date',
            'waktu_transaksi' => 'nullable|string|max:20',
            'metode_pembayaran' => 'nullable|string|max:50',
            'catatan' => 'nullable|string',
        ]);

        try {
            $trx = $this->penjualanService->catatPenjualan($user, $validated);

            return response()->json([
                'success' => true,
                'message' => 'Transaksi penjualan berhasil dicatat.',
                'data' => $trx,
            ], 201);
        } catch (AccessDeniedHttpException $e) {
            return response()->json(['success' => false, 'message' => $e->getMessage()], 403);
        } catch (Throwable $e) {
            return response()->json([
                'success' => false,
                'message' => 'Gagal mencatat transaksi: ' . $e->getMessage(),
            ], 422);
        }
    }

    /**
     * Detail transaksi penjualan beserta riwayat pembayaran multi-stage.
     */
    public function show(Request $request, string $id): JsonResponse
    {
        $user = $request->user();

        try {
            $item = $this->penjualanService->getDetailPenjualan($user, $id);

            return response()->json([
                'success' => true,
                'message' => 'Detail transaksi penjualan berhasil diambil.',
                'data' => [
                    'id_penjualan' => $item->id_penjualan,
                    'kode_transaksi' => $item->kode_transaksi,
                    'id_pengguna' => $item->id_pengguna,
                    'id_produk' => $item->id_produk,
                    'id_komoditas' => $item->id_komoditas,
                    'nama_komoditas' => $item->komoditas?->nama_komoditas,
                    'nama_produk' => $item->nama_produk,
                    'nama_pembeli' => $item->nama_pembeli,
                    'no_telepon_pembeli' => $item->no_telepon_pembeli,
                    'jumlah' => $item->jumlah,
                    'satuan' => $item->satuan,
                    'harga_satuan' => $item->harga_satuan,
                    'total_harga' => $item->total_harga,
                    'total_dibayar' => $item->total_dibayar,
                    'sisa_pembayaran' => $item->sisa_pembayaran,
                    'status_pembayaran' => $item->status_pembayaran,
                    'status_label' => $item->status_label,
                    'status_penyerahan' => $item->status_penyerahan,
                    'tanggal_transaksi' => $item->tanggal_transaksi?->toDateString(),
                    'waktu_transaksi' => $item->waktu_transaksi,
                    'metode_pembayaran' => $item->metode_pembayaran,
                    'catatan' => $item->catatan,
                    'alasan_batal' => $item->alasan_batal,
                    'status_pengembalian_dana' => $item->status_pengembalian_dana,
                    'tanggal_diserahkan' => $item->tanggal_diserahkan?->toIso8601String(),
                    'tanggal_dibatalkan' => $item->tanggal_dibatalkan?->toIso8601String(),
                    'riwayat_pembayaran' => $item->riwayatPembayaran->map(function ($p) {
                        return [
                            'id_pembayaran' => $p->id_pembayaran,
                            'nominal' => $p->nominal,
                            'tanggal_pembayaran' => $p->tanggal_pembayaran?->toDateString(),
                            'keterangan' => $p->keterangan,
                            'status_verifikasi' => $p->status_verifikasi,
                            'created_at' => $p->created_at?->toIso8601String(),
                        ];
                    }),
                ],
            ]);
        } catch (AccessDeniedHttpException $e) {
            return response()->json(['success' => false, 'message' => $e->getMessage()], 403);
        } catch (Throwable $e) {
            return response()->json(['success' => false, 'message' => 'Transaksi tidak ditemukan.'], 404);
        }
    }

    /**
     * Tambah pembayaran bertahap (DP tambahan / pelunasan).
     */
    public function tambahPembayaran(Request $request, string $id): JsonResponse
    {
        $user = $request->user();

        $validated = $request->validate([
            'nominal' => 'required|numeric|min:1',
            'tanggal_pembayaran' => 'nullable|date',
            'keterangan' => 'nullable|string|max:255',
        ]);

        try {
            $trx = $this->penjualanService->tambahPembayaran($user, $id, $validated);

            return response()->json([
                'success' => true,
                'message' => 'Pembayaran berhasil dicatat.',
                'data' => $trx,
            ]);
        } catch (AccessDeniedHttpException $e) {
            return response()->json(['success' => false, 'message' => $e->getMessage()], 403);
        } catch (Throwable $e) {
            return response()->json([
                'success' => false,
                'message' => $e->getMessage(),
            ], 422);
        }
    }

    /**
     * Tandai transaksi Lunas setelah pembayaran penuh diverifikasi manual oleh petani.
     */
    public function tandaiLunas(Request $request, string $id): JsonResponse
    {
        $user = $request->user();

        try {
            $trx = $this->penjualanService->tandaiLunas($user, $id);

            return response()->json([
                'success' => true,
                'message' => 'Transaksi berhasil ditandai Lunas. Stok fisik telah diperbarui.',
                'data' => $trx,
            ]);
        } catch (AccessDeniedHttpException $e) {
            return response()->json(['success' => false, 'message' => $e->getMessage()], 403);
        } catch (Throwable $e) {
            return response()->json([
                'success' => false,
                'message' => $e->getMessage(),
            ], 422);
        }
    }

    /**
     * Batalkan transaksi sebelum penyerahan barang.
     */
    public function batalkan(Request $request, string $id): JsonResponse
    {
        $user = $request->user();

        $validated = $request->validate([
            'alasan_batal' => 'required|string|min:3|max:500',
        ]);

        try {
            $trx = $this->penjualanService->batalkanTransaksi($user, $id, $validated['alasan_batal']);

            return response()->json([
                'success' => true,
                'message' => 'Transaksi berhasil dibatalkan dan alokasi stok telah dikembalikan.',
                'data' => $trx,
            ]);
        } catch (AccessDeniedHttpException $e) {
            return response()->json(['success' => false, 'message' => $e->getMessage()], 403);
        } catch (Throwable $e) {
            return response()->json([
                'success' => false,
                'message' => $e->getMessage(),
            ], 422);
        }
    }

    /**
     * Tandai barang sudah diserahkan kepada pembeli.
     */
    public function tandaiDiserahkan(Request $request, string $id): JsonResponse
    {
        $user = $request->user();

        try {
            $trx = $this->penjualanService->tandaiBarangDiserahkan($user, $id);

            return response()->json([
                'success' => true,
                'message' => 'Status barang berhasil diperbarui menjadi Sudah Diserahkan.',
                'data' => $trx,
            ]);
        } catch (AccessDeniedHttpException $e) {
            return response()->json(['success' => false, 'message' => $e->getMessage()], 403);
        } catch (Throwable $e) {
            return response()->json([
                'success' => false,
                'message' => $e->getMessage(),
            ], 422);
        }
    }
}
