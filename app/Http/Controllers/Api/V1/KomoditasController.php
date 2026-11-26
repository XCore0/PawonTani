<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Models\Komoditas;
use App\Services\CommodityService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class KomoditasController extends Controller
{
    /**
     * Mengambil daftar master komoditas untuk pilihan dropdown aplikasi mobile.
     * Terurut berdasarkan kategori kemudian nama komoditas.
     */
    public function index(Request $request): JsonResponse
    {
        $komoditas = Komoditas::query()
            ->orderBy('kategori')
            ->orderBy('nama_komoditas')
            ->get(['id_komoditas', 'nama_komoditas', 'kategori']);

        return response()->json([
            'success' => true,
            'message' => 'Daftar komoditas berhasil diambil.',
            'data' => $komoditas->map(fn($item) => [
                'id_komoditas' => (string) $item->id_komoditas,
                'nama_komoditas' => (string) $item->nama_komoditas,
                'kategori' => (string) $item->kategori,
            ]),
        ]);
    }

    /**
     * Mengambil referensi harga pasar komoditas terkini (sama seperti pada dashboard website Pengurus).
     */
    public function hargaPasar(Request $request, CommodityService $commodityService): JsonResponse
    {
        $prices = $commodityService->getCommodityPrices();

        return response()->json([
            'success' => true,
            'message' => 'Referensi harga pasar komoditas berhasil diambil.',
            'data' => $prices,
        ]);
    }
}

