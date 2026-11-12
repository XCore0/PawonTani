<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Http\Requests\Api\StoreAktivitasRequest;
use App\Http\Requests\Api\UpdateAktivitasRequest;
use App\Http\Resources\AktivitasResource;
use App\Models\AktivitasTanam;
use App\Models\Lahan;
use Carbon\Carbon;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;
use Throwable;

class AktivitasController extends Controller
{
    /**
     * Mengambil daftar aktivitas tanam terfilter.
     */
    public function index(Request $request): JsonResponse
    {
        $user = $request->user();

        if (!$user) {
            return response()->json([
                'success' => false,
                'message' => 'Autentikasi diperlukan.',
            ], 401);
        }

        $query = AktivitasTanam::with(['lahan.komoditas', 'pengguna']);

        // Scope berdasarkan aktor:
        // Jika Anggota, tampilkan aktivitas miliknya atau pada lahannya
        if ($user->role === 'Anggota') {
            $query->where('id_pengguna', $user->id_pengguna);
        } elseif ($user->role === 'Pengurus' && !empty($user->id_kelompok)) {
            $query->where('id_kelompok', $user->id_kelompok);
        }

        // Filter per Lahan
        if ($lahanId = ($request->query('id_lahan') ?? $request->query('lahan_id'))) {
            $query->where('id_lahan', $lahanId);
        }

        // Filter per Kategori
        if ($kategori = $request->query('kategori')) {
            $query->where('kategori', strtolower($kategori));
        }

        // Filter per Kondisi
        if ($kondisi = ($request->query('kondisi') ?? $request->query('condition'))) {
            $kondisiNormalized = match (strtolower($kondisi)) {
                'sehat' => 'sehat',
                'perluperhatian', 'perlu_perhatian' => 'perluPerhatian',
                'terseranghama', 'terserang_hama' => 'terserangHama',
                default => $kondisi,
            };
            $query->where('kondisi_tanaman', $kondisiNormalized);
        }

        // Pencarian teks
        if ($search = $request->query('q')) {
            $like = DB::getDriverName() === 'pgsql' ? 'ILIKE' : 'LIKE';
            $query->where(function ($q) use ($search, $like) {
                $q->where('judul', $like, "%{$search}%")
                  ->orWhere('catatan', $like, "%{$search}%")
                  ->orWhereHas('lahan', function ($lq) use ($search, $like) {
                      $lq->where('nama_lahan', $like, "%{$search}%");
                  });
            });
        }

        // Urutkan terbaru
        $query->orderByDesc('tanggal')->orderByDesc('created_at');

        $limit = (int) $request->query('limit', 50);
        $aktivitas = $query->take($limit)->get();

        return response()->json([
            'success' => true,
            'message' => 'Daftar aktivitas berhasil dimuat.',
            'data' => AktivitasResource::collection($aktivitas),
        ]);
    }

    /**
     * Menyimpan aktivitas tanam baru.
     */
    public function store(StoreAktivitasRequest $request): JsonResponse
    {
        $user = $request->user();

        try {
            $lahan = Lahan::findOrFail($request->input('id_lahan'));

            // Pastikan Anggota hanya mencatat di lahan miliknya atau kelompoknya
            if ($user->role === 'Anggota' && (string) $lahan->id_pengguna !== (string) $user->id_pengguna) {
                return response()->json([
                    'success' => false,
                    'message' => 'Anda tidak memiliki hak akses untuk mencatat aktivitas di lahan ini.',
                ], 403);
            }

            $fotoPath = null;
            if ($request->hasFile('foto_aktivitas')) {
                $file = $request->file('foto_aktivitas');
                $fotoPath = $file->store('aktivitas', 'public');
            } elseif ($request->filled('foto_aktivitas') && is_string($request->input('foto_aktivitas'))) {
                $val = $request->input('foto_aktivitas');
                if (str_starts_with($val, 'http') || str_starts_with($val, 'aktivitas/')) {
                    $fotoPath = $val;
                }
            }

            // Normalisasi tanggal
            $tanggalInput = $request->input('tanggal');
            try {
                $tanggal = Carbon::parse($tanggalInput)->toDateString();
            } catch (Throwable) {
                $tanggal = now()->toDateString();
            }

            $kategori = strtolower($request->input('kategori'));
            $judul = $request->input('judul');
            if (empty($judul)) {
                $judul = ucfirst($kategori);
            }

            $kondisi = $request->input('kondisi_tanaman', 'sehat');
            $kondisiNormalized = match (strtolower($kondisi)) {
                'sehat' => 'sehat',
                'perluperhatian', 'perlu_perhatian' => 'perluPerhatian',
                'terseranghama', 'terserang_hama' => 'terserangHama',
                default => $kondisi,
            };

            $aktivitas = new AktivitasTanam();
            $aktivitas->id_lahan = $lahan->id_lahan;
            $aktivitas->id_pengguna = $user->id_pengguna;
            $aktivitas->id_kelompok = $lahan->id_kelompok ?? $user->id_kelompok;
            $aktivitas->kategori = $kategori;
            $aktivitas->judul = $judul;
            $aktivitas->kondisi_tanaman = $kondisiNormalized;
            $aktivitas->tanggal = $tanggal;
            $aktivitas->waktu = $request->input('waktu', now()->format('H:i') . ' WIB');
            $aktivitas->hari_setelah_tanam = $request->input('hari_setelah_tanam', 30);
            $aktivitas->catatan = $request->input('catatan');
            $aktivitas->foto_aktivitas = $fotoPath;
            $aktivitas->biaya = (float) $request->input('biaya', 0);

            $aktivitas->saveWithUniqueRetry();
            $aktivitas->load(['lahan.komoditas', 'pengguna']);

            return response()->json([
                'success' => true,
                'message' => 'Aktivitas tanam berhasil dicatat.',
                'data' => new AktivitasResource($aktivitas),
            ], 201);
        } catch (Throwable $e) {
            Log::error('Gagal menyimpan aktivitas tanam: ' . $e->getMessage(), [
                'trace' => $e->getTraceAsString(),
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Terjadi kesalahan sistem saat menyimpan aktivitas: ' . $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Detail satu aktivitas tanam.
     */
    public function show(string $id): JsonResponse
    {
        $aktivitas = AktivitasTanam::with(['lahan.komoditas', 'pengguna'])->find($id);

        if (!$aktivitas) {
            return response()->json([
                'success' => false,
                'message' => 'Data aktivitas tidak ditemukan.',
            ], 404);
        }

        return response()->json([
            'success' => true,
            'message' => 'Detail aktivitas berhasil dimuat.',
            'data' => new AktivitasResource($aktivitas),
        ]);
    }

    /**
     * Memperbarui data aktivitas tanam.
     */
    public function update(UpdateAktivitasRequest $request, string $id): JsonResponse
    {
        $user = $request->user();
        $aktivitas = AktivitasTanam::find($id);

        if (!$aktivitas) {
            return response()->json([
                'success' => false,
                'message' => 'Data aktivitas tidak ditemukan.',
            ], 404);
        }

        // Otorisasi hak akses ubah
        if ($user->role === 'Anggota' && (string) $aktivitas->id_pengguna !== (string) $user->id_pengguna) {
            return response()->json([
                'success' => false,
                'message' => 'Anda tidak memiliki hak untuk mengubah aktivitas ini.',
            ], 403);
        }

        try {
            if ($request->has('id_lahan')) {
                $lahan = Lahan::findOrFail($request->input('id_lahan'));
                $aktivitas->id_lahan = $lahan->id_lahan;
                $aktivitas->id_kelompok = $lahan->id_kelompok ?? $aktivitas->id_kelompok;
            }

            if ($request->has('kategori')) {
                $kategori = strtolower($request->input('kategori'));
                $aktivitas->kategori = $kategori;
                if (!$request->has('judul')) {
                    $aktivitas->judul = ucfirst($kategori);
                }
            }

            if ($request->has('judul')) {
                $aktivitas->judul = $request->input('judul');
            }

            if ($request->has('kondisi_tanaman')) {
                $kondisi = $request->input('kondisi_tanaman');
                $aktivitas->kondisi_tanaman = match (strtolower($kondisi)) {
                    'sehat' => 'sehat',
                    'perluperhatian', 'perlu_perhatian' => 'perluPerhatian',
                    'terseranghama', 'terserang_hama' => 'terserangHama',
                    default => $kondisi,
                };
            }

            if ($request->has('tanggal')) {
                try {
                    $aktivitas->tanggal = Carbon::parse($request->input('tanggal'))->toDateString();
                } catch (Throwable) {
                    // Biarkan nilai lama jika gagal parse
                }
            }

            if ($request->has('waktu')) {
                $aktivitas->waktu = $request->input('waktu');
            }

            if ($request->has('hari_setelah_tanam')) {
                $aktivitas->hari_setelah_tanam = $request->input('hari_setelah_tanam');
            }

            if ($request->has('catatan')) {
                $aktivitas->catatan = $request->input('catatan');
            }

            if ($request->has('biaya')) {
                $aktivitas->biaya = (float) $request->input('biaya');
            }

            if ($request->hasFile('foto_aktivitas')) {
                if ($aktivitas->foto_aktivitas && !str_starts_with($aktivitas->foto_aktivitas, 'http')) {
                    Storage::disk('public')->delete($aktivitas->foto_aktivitas);
                }
                $file = $request->file('foto_aktivitas');
                $aktivitas->foto_aktivitas = $file->store('aktivitas', 'public');
            } elseif ($request->filled('foto_aktivitas') && is_string($request->input('foto_aktivitas'))) {
                $val = $request->input('foto_aktivitas');
                if (str_starts_with($val, 'http') || str_starts_with($val, 'aktivitas/')) {
                    $aktivitas->foto_aktivitas = $val;
                }
            }

            $aktivitas->save();
            $aktivitas->load(['lahan.komoditas', 'pengguna']);

            return response()->json([
                'success' => true,
                'message' => 'Aktivitas tanam berhasil diperbarui.',
                'data' => new AktivitasResource($aktivitas),
            ]);
        } catch (Throwable $e) {
            Log::error('Gagal memperbarui aktivitas tanam: ' . $e->getMessage());

            return response()->json([
                'success' => false,
                'message' => 'Terjadi kesalahan sistem saat memperbarui aktivitas: ' . $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Menghapus aktivitas tanam (soft delete).
     */
    public function destroy(Request $request, string $id): JsonResponse
    {
        $user = $request->user();
        $aktivitas = AktivitasTanam::find($id);

        if (!$aktivitas) {
            return response()->json([
                'success' => false,
                'message' => 'Data aktivitas tidak ditemukan.',
            ], 404);
        }

        // Otorisasi hak akses hapus
        if ($user->role === 'Anggota' && (string) $aktivitas->id_pengguna !== (string) $user->id_pengguna) {
            return response()->json([
                'success' => false,
                'message' => 'Anda tidak memiliki hak untuk menghapus aktivitas ini.',
            ], 403);
        }

        $aktivitas->delete();

        return response()->json([
            'success' => true,
            'message' => 'Aktivitas tanam berhasil dihapus.',
            'data' => null,
        ]);
    }
}

