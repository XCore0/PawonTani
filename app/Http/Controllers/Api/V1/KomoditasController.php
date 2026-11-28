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
            ->get(['id_komoditas', 'nama_komoditas', 'kategori', 'umur_panen_hari']);

        return response()->json([
            'success' => true,
            'message' => 'Daftar komoditas berhasil diambil.',
            'data' => $komoditas->map(fn($item) => [
                'id_komoditas' => (string) $item->id_komoditas,
                'nama_komoditas' => (string) $item->nama_komoditas,
                'kategori' => (string) $item->kategori,
                'umur_panen_hari' => $item->umur_panen_hari !== null ? (int) $item->umur_panen_hari : null,
            ]),
        ]);
    }

    /**
     * Mengambil referensi harga pasar komoditas terkini (sama seperti pada dashboard website Pengurus).
     */
    public function hargaPasar(Request $request, CommodityService $commodityService): JsonResponse
    {
        $user = $request->user();
        $wilayahNama = $user?->lokasi_nama ?: ($user?->kelompokTani?->alamat ?: 'Jawa Timur');
        $prices = $commodityService->getCommodityPrices($wilayahNama);

        return response()->json([
            'success' => true,
            'message' => 'Referensi harga pasar komoditas berhasil diambil.',
            'data' => $prices,
        ]);
    }

    /**
     * Mengambil kalender musim tanam aktif dan panduan agronomi (sinkron dengan web Pengurus).
     */
    public function kalenderTanam(Request $request): JsonResponse
    {
        return response()->json([
            'success' => true,
            'message' => 'Data kalender musim tanam dan agronomi berhasil diambil.',
            'data' => [
                'musim_aktif' => 'MT-I (Rendeng)',
                'periode' => 'Oktober - Februari',
                'komoditas_utama' => 'Padi Sawah & Palawija',
                'tahap_siklus' => [
                    [
                        'kode' => 'Fase 01',
                        'judul' => 'Persiapan & Semai',
                        'deskripsi' => 'Olah tanah, persemaian bibit, dan perbaikan pematang sawah.',
                        'status' => 'selesai',
                    ],
                    [
                        'kode' => 'Fase 02',
                        'judul' => 'Vegetatif & Rawat',
                        'deskripsi' => 'Pembentukan anakan aktif, penyiangan gulma, dan pemupukan susulan I & II.',
                        'status' => 'berjalan',
                    ],
                    [
                        'kode' => 'Fase 03',
                        'judul' => 'Generatif & Panen',
                        'deskripsi' => 'Fase bunting, pembungaan, pengisian bulir padi, dan persiapan panen raya.',
                        'status' => 'mendatang',
                    ],
                ],
                'rekomendasi_pekan_ini' => [
                    [
                        'judul' => 'Pengairan Berselang (Intermittent)',
                        'deskripsi' => 'Keringkan lahan 2-3 hari untuk merangsang oksigenasi perakaran sebelum penggenangan kembali.',
                        'kategori' => 'Pengairan',
                    ],
                    [
                        'judul' => 'Monitoring Penggerek Batang & Wereng Coklat',
                        'deskripsi' => 'Amati rumpun padi bagian bawah pada pagi hari sebelum aplikasi semprot bila melebihi ambang batas.',
                        'kategori' => 'Pencegahan Hama',
                    ],
                    [
                        'judul' => 'Pemupukan Susulan Lanjutan',
                        'deskripsi' => 'Gunakan kombinasi NPK dan Urea berimbang sesuai dosis rekomendasi e-RDKK poktan.',
                        'kategori' => 'Nutrisi & Pupuk',
                    ],
                ],
                'jadwal_kegiatan' => [
                    ['hari' => 'Senin', 'kegiatan' => 'Pemeriksaan debit saluran irigasi petak'],
                    ['hari' => 'Rabu', 'kegiatan' => 'Pembersihan gulma liar pematang'],
                    ['hari' => 'Jumat', 'kegiatan' => 'Pencatatan perkembangan tanaman di PawonTani'],
                ],
            ],
        ]);
    }
}

