<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Models\Artikel;
use App\Models\Panduan;
use App\Models\Tip;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class EdukasiController extends Controller
{
    /**
     * Mengambil daftar konten edukasi gabungan (Tips, Panduan, Artikel) dari database.
     */
    public function index(Request $request): JsonResponse
    {
        $kategoriFilter = strtolower((string) $request->query('kategori', 'semua'));
        $search = trim((string) $request->query('q', ''));
        $limit = $request->has('limit') ? (int) $request->query('limit') : null;

        $items = collect();

        // 1. Ambil Tips
        if ($kategoriFilter === 'semua' || $kategoriFilter === 'tips') {
            $tipsQuery = Tip::with('komoditas')->where('status', 'Publik');
            if ($search !== '') {
                $tipsQuery->where(function ($q) use ($search) {
                    $q->where('judul', 'ilike', "%{$search}%")
                      ->orWhere('ringkasan', 'ilike', "%{$search}%");
                });
            }
            $tips = $tipsQuery->get()->map(function (Tip $item) {
                return $this->formatItem($item, 'TIPS', $item->id_tips);
            });
            $items = $items->concat($tips);
        }

        // 2. Ambil Panduan
        if ($kategoriFilter === 'semua' || $kategoriFilter === 'panduan') {
            $panduanQuery = Panduan::with('komoditas')->where('status', 'Publik');
            if ($search !== '') {
                $panduanQuery->where(function ($q) use ($search) {
                    $q->where('judul', 'ilike', "%{$search}%")
                      ->orWhere('ringkasan', 'ilike', "%{$search}%");
                });
            }
            $panduan = $panduanQuery->get()->map(function (Panduan $item) {
                return $this->formatItem($item, 'PANDUAN', $item->id_panduan);
            });
            $items = $items->concat($panduan);
        }

        // 3. Ambil Artikel
        if ($kategoriFilter === 'semua' || $kategoriFilter === 'artikel') {
            $artikelQuery = Artikel::with('komoditas')->where('status', 'Publik');
            if ($search !== '') {
                $artikelQuery->where(function ($q) use ($search) {
                    $q->where('judul', 'ilike', "%{$search}%")
                      ->orWhere('ringkasan', 'ilike', "%{$search}%");
                });
            }
            $artikel = $artikelQuery->get()->map(function (Artikel $item) {
                return $this->formatItem($item, 'ARTIKEL', $item->id_artikel);
            });
            $items = $items->concat($artikel);
        }

        // Urutkan berdasarkan tanggal terbaru
        $sorted = $items->sortByDesc('tanggal_raw')->values();

        if ($limit !== null && $limit > 0) {
            $sorted = $sorted->take($limit)->values();
        }

        return response()->json([
            'success' => true,
            'message' => 'Daftar edukasi pertanian berhasil diambil.',
            'data' => $sorted,
        ]);
    }

    /**
     * Mengambil detail satu konten edukasi berdasarkan tipe dan ID.
     */
    public function show(string $type, string $id): JsonResponse
    {
        $normalizedType = strtoupper($type);
        $item = null;

        switch ($normalizedType) {
            case 'TIPS':
                $tip = Tip::with('komoditas')->where('status', 'Publik')->where('id_tips', $id)->first();
                if ($tip) {
                    $item = $this->formatItem($tip, 'TIPS', $tip->id_tips);
                }
                break;

            case 'PANDUAN':
                $panduan = Panduan::with('komoditas')->where('status', 'Publik')->where('id_panduan', $id)->first();
                if ($panduan) {
                    $item = $this->formatItem($panduan, 'PANDUAN', $panduan->id_panduan);
                }
                break;

            case 'ARTIKEL':
                $artikel = Artikel::with('komoditas')->where('status', 'Publik')->where('id_artikel', $id)->first();
                if ($artikel) {
                    $item = $this->formatItem($artikel, 'ARTIKEL', $artikel->id_artikel);
                }
                break;
        }

        if (!$item) {
            return response()->json([
                'success' => false,
                'message' => 'Konten edukasi tidak ditemukan.',
                'data' => null,
            ], 404);
        }

        return response()->json([
            'success' => true,
            'message' => 'Detail konten edukasi berhasil diambil.',
            'data' => $item,
        ]);
    }

    /**
     * Format struktur item standar untuk API Mobile.
     */
    private function formatItem(mixed $item, string $type, string $id): array
    {
        $content = (string) ($item->isi ?? $item->ringkasan ?? '');
        $wordCount = str_word_count(strip_tags($content));
        $minutes = max(1, (int) ceil($wordCount / 180));
        $readTime = "{$minutes} menit baca";

        $dateFormatted = null;
        $dateRaw = null;
        if (!empty($item->tanggal)) {
            $dateRaw = $item->tanggal->format('Y-m-d');
            $dateFormatted = $item->tanggal->translatedFormat('d M Y');
        } elseif (!empty($item->created_at)) {
            $dateRaw = $item->created_at->format('Y-m-d');
            $dateFormatted = $item->created_at->translatedFormat('d M Y');
        }

        $gambarUrl = null;
        if (!empty($item->gambar)) {
            if (str_starts_with($item->gambar, 'http')) {
                $gambarUrl = $item->gambar;
            } else {
                $gambarUrl = asset('storage/' . ltrim($item->gambar, '/'));
            }
        }

        return [
            'id' => $id,
            'type' => $type,
            'title' => (string) ($item->judul ?? ''),
            'category' => $type,
            'kategori_label' => (string) ($item->kategori ?? $type),
            'summary' => (string) ($item->ringkasan ?? ''),
            'content' => $content,
            'image_url' => $gambarUrl,
            'read_time' => $readTime,
            'date' => $dateFormatted,
            'tanggal_raw' => $dateRaw,
            'commodity_name' => $item->komoditas?->nama_komoditas ?? null,
            'target' => $item->target ?? null,
        ];
    }
}
