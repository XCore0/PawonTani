<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Http\Requests\Api\StoreAktivitasRequest;
use App\Http\Requests\Api\UpdateAktivitasRequest;
use App\Http\Resources\AktivitasResource;
use App\Models\AktivitasTanam;
use App\Models\Lahan;
use App\Models\SiklusTanam;
use App\Services\AuditLogger;
use Carbon\Carbon;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;
use Throwable;

class AktivitasController extends Controller
{
    public function index(Request $request): JsonResponse
    {
        $user = $request->user();
        abort_unless($user && $user->role === 'Anggota' && $user->status === 'Aktif' && $user->anggota?->status_keanggotaan === 'Aktif', 403);

        $query = AktivitasTanam::with(['lahan.komoditas', 'pengguna', 'siklusTanam'])
            ->where('id_pengguna', $user->id_pengguna)
            ->where('id_kelompok', $user->id_kelompok);

        if ($lahanId = ($request->query('id_lahan') ?? $request->query('lahan_id'))) {
            $query->where('id_lahan', $lahanId);
        }
        if ($siklusId = ($request->query('id_siklus') ?? $request->query('siklus_id'))) {
            $query->where('id_siklus', $siklusId);
        }
        if ($kategori = $request->query('kategori')) {
            $query->where('kategori', strtolower($kategori));
        }
        if ($kondisi = ($request->query('kondisi') ?? $request->query('condition'))) {
            $normalized = match (strtolower($kondisi)) {
                'sehat' => 'sehat',
                'perluperhatian', 'perlu_perhatian' => 'perluPerhatian',
                'terseranghama', 'terserang_hama' => 'terserangHama',
                default => $kondisi,
            };
            $query->where('kondisi_tanaman', $normalized);
        }
        if ($search = trim((string) $request->query('q'))) {
            $like = DB::getDriverName() === 'pgsql' ? 'ILIKE' : 'LIKE';
            $query->where(fn ($q) => $q->where('judul', $like, "%{$search}%")
                ->orWhere('catatan', $like, "%{$search}%")
                ->orWhereHas('lahan', fn ($lq) => $lq->where('nama_lahan', $like, "%{$search}%")));
        }

        $limit = max(1, min((int) $request->query('limit', 50), 100));
        $items = $query->orderByDesc('tanggal')->orderByDesc('created_at')->take($limit)->get();

        return response()->json(['success' => true, 'message' => 'Daftar aktivitas berhasil dimuat.', 'data' => AktivitasResource::collection($items)]);
    }

    public function store(StoreAktivitasRequest $request): JsonResponse
    {
        $user = $request->user();
        abort_unless($user && $user->role === 'Anggota' && $user->status === 'Aktif' && $user->anggota?->status_keanggotaan === 'Aktif', 403);

        $land = Lahan::where('id_lahan', $request->input('id_lahan'))
            ->where('id_pengguna', $user->id_pengguna)
            ->where('id_kelompok', $user->id_kelompok)
            ->firstOrFail();

        try {
            return DB::transaction(function () use ($request, $user, $land) {
                // 1. Validasi Siklus Tanam dengan row locking
                $siklus = SiklusTanam::where('id_siklus', $request->input('id_siklus'))
                    ->lockForUpdate()
                    ->first();

                if (!$siklus) {
                    return response()->json([
                        'success' => false,
                        'message' => 'Siklus tanam tidak ditemukan.',
                    ], 404);
                }

                // 2. Validasi konsistensi Lahan: Lahan Aktivitas harus sama dengan Lahan Siklus
                if ($siklus->id_lahan !== $land->id_lahan) {
                    return response()->json([
                        'success' => false,
                        'message' => 'Siklus tanam tidak sesuai dengan lahan yang dipilih.',
                    ], 422);
                }

                // 3. Validasi otorisasi kepemilikan siklus oleh anggota sah
                if ($siklus->id_pengguna !== $user->id_pengguna || $siklus->id_kelompok !== $land->id_kelompok) {
                    return response()->json([
                        'success' => false,
                        'message' => 'Anda tidak memiliki hak akses terhadap siklus tanam ini.',
                    ], 403);
                }

                // 4. Validasi status siklus: hanya status 'aktif' yang boleh menerima pencatatan aktivitas
                if (in_array($siklus->status, [SiklusTanam::STATUS_MENUNGGU_SELESAI, SiklusTanam::STATUS_MENUNGGU_GAGAL], true)) {
                    return response()->json([
                        'success' => false,
                        'message' => 'Siklus tanam sedang dalam proses pengakhiran dan tidak dapat menerima aktivitas baru.',
                    ], 422);
                }

                if ($siklus->status !== SiklusTanam::STATUS_AKTIF) {
                    return response()->json([
                        'success' => false,
                        'message' => 'Siklus tanam sudah berakhir dan tidak dapat menerima aktivitas baru.',
                    ], 422);
                }

                $photoPath = $this->storeActivityPhoto($request);
                $photoList = $this->storeActivityPhotoList($request);
                if ($photoPath && empty($photoList)) {
                    $photoList = [$photoPath];
                } elseif (empty($photoPath) && !empty($photoList)) {
                    $photoPath = $photoList[0];
                }

                $category = strtolower($request->input('kategori'));
                $condition = match (strtolower((string) $request->input('kondisi_tanaman', 'sehat'))) {
                    'sehat' => 'sehat',
                    'perluperhatian', 'perlu_perhatian' => 'perluPerhatian',
                    'terseranghama', 'terserang_hama' => 'terserangHama',
                    default => 'sehat',
                };

                $activity = new AktivitasTanam();
                $activity->id_lahan = $land->id_lahan;
                $activity->id_siklus = $siklus->id_siklus;
                $activity->id_pengguna = $user->id_pengguna;
                $activity->id_kelompok = $land->id_kelompok;
                $activity->kategori = $category;
                $activity->judul = $request->input('judul') ?: ucfirst($category);
                $activity->kondisi_tanaman = $condition;
                $activity->tanggal = Carbon::parse($request->input('tanggal'))->toDateString();
                $activity->waktu = $request->input('waktu', now()->format('H:i') . ' WIB');
                $activity->hari_setelah_tanam = $request->input('hari_setelah_tanam', 30);
                $activity->catatan = $request->input('catatan');
                $activity->foto_aktivitas = $photoPath;
                $activity->foto_aktivitas_list = !empty($photoList) ? $photoList : null;
                $activity->biaya = (float) $request->input('biaya', 0);
                $activity->saveWithUniqueRetry();
                $activity->load(['lahan.komoditas', 'pengguna', 'siklusTanam']);

                AuditLogger::log('aktivitas.tambah', 'Anggota menambahkan aktivitas ' . $activity->judul, 'AktivitasTanam', $activity->id_aktivitas, $land->id_kelompok, $request);

                return response()->json(['success' => true, 'message' => 'Aktivitas tanam berhasil dicatat.', 'data' => new AktivitasResource($activity)], 201);
            });
        } catch (Throwable $exception) {
            Log::error('Gagal menyimpan aktivitas tanam.', ['exception' => $exception]);
            return response()->json(['success' => false, 'message' => 'Terjadi kesalahan sistem saat menyimpan aktivitas.'], 500);
        }
    }

    public function show(Request $request, string $id): JsonResponse
    {
        $user = $request->user();
        abort_unless($user && $user->role === 'Anggota' && $user->status === 'Aktif' && $user->anggota?->status_keanggotaan === 'Aktif', 403);

        $activity = AktivitasTanam::with(['lahan.komoditas', 'pengguna', 'siklusTanam'])
            ->where('id_pengguna', $user->id_pengguna)
            ->where('id_kelompok', $user->id_kelompok)
            ->findOrFail($id);

        return response()->json(['success' => true, 'message' => 'Detail aktivitas berhasil dimuat.', 'data' => new AktivitasResource($activity)]);
    }

    public function update(UpdateAktivitasRequest $request, string $id): JsonResponse
    {
        $user = $request->user();
        abort_unless($user && $user->role === 'Anggota' && $user->status === 'Aktif' && $user->anggota?->status_keanggotaan === 'Aktif', 403);

        $activity = AktivitasTanam::where('id_pengguna', $user->id_pengguna)
            ->where('id_kelompok', $user->id_kelompok)
            ->findOrFail($id);

        // Jangan izinkan pemindahan siklus tanam secara bebas antar siklus
        if ($request->filled('id_siklus') && $activity->id_siklus !== null && $request->input('id_siklus') !== $activity->id_siklus) {
            return response()->json([
                'success' => false,
                'message' => 'Aktivitas tanam tidak dapat dipindahkan ke siklus tanam lain.',
            ], 422);
        }

        // Locking jika siklus tanam sedang dalam proses pengakhiran atau telah berakhir
        if ($activity->id_siklus !== null) {
            $siklus = \App\Models\SiklusTanam::find($activity->id_siklus);
            if ($siklus && ($siklus->isMenungguVerifikasiPengakhiran() || in_array($siklus->status, \App\Models\SiklusTanam::STATUSES_TERMINAL, true))) {
                return response()->json([
                    'success' => false,
                    'message' => 'Aktivitas tanam tidak dapat diubah karena siklus tanam sedang dalam proses pengakhiran atau telah berakhir.',
                    'errors' => [
                        'id_siklus' => ['Siklus tanam sedang dalam proses pengakhiran atau telah berakhir.']
                    ],
                ], 422);
            }
        }

        try {
            if ($request->filled('id_lahan')) {
                $newLand = Lahan::where('id_lahan', $request->input('id_lahan'))
                    ->where('id_pengguna', $user->id_pengguna)
                    ->where('id_kelompok', $user->id_kelompok)
                    ->firstOrFail();
                $activity->id_lahan = $newLand->id_lahan;
                $activity->id_kelompok = $newLand->id_kelompok;
            }
            if ($request->has('kategori')) {
                $activity->kategori = strtolower($request->input('kategori'));
                if (!$request->has('judul')) $activity->judul = ucfirst($activity->kategori);
            }
            foreach (['judul', 'waktu', 'hari_setelah_tanam', 'catatan', 'biaya'] as $field) {
                if ($request->has($field)) $activity->{$field} = $request->input($field);
            }
            if ($request->has('kondisi_tanaman')) {
                $activity->kondisi_tanaman = match (strtolower((string) $request->input('kondisi_tanaman'))) {
                    'sehat' => 'sehat',
                    'perluperhatian', 'perlu_perhatian' => 'perluPerhatian',
                    'terseranghama', 'terserang_hama' => 'terserangHama',
                    default => 'sehat',
                };
            }
            if ($request->has('tanggal')) $activity->tanggal = Carbon::parse($request->input('tanggal'))->toDateString();

            // Penanganan foto lama yang dipertahankan dan foto baru
            $fotoTersimpan = [];
            if (!empty($activity->foto_aktivitas_list) && is_array($activity->foto_aktivitas_list)) {
                $fotoTersimpan = $activity->foto_aktivitas_list;
            } elseif (!empty($activity->foto_aktivitas)) {
                $fotoTersimpan = [$activity->foto_aktivitas];
            }

            $keptOld = null;
            if ($request->has('existing_photos')) {
                $rawKept = (array) $request->input('existing_photos', []);
                $keptOld = [];
                foreach ($rawKept as $p) {
                    if (is_string($p) && trim($p) !== '') {
                        $clean = trim($p);
                        if (preg_match('#(?:storage|media)[/\\\\](.*)$#i', $clean, $m)) {
                            $clean = $m[1];
                        }
                        $clean = ltrim($clean, '/');
                        $keptOld[] = $clean;
                    }
                }
                // Hapus berkas lama yang tidak lagi dipertahankan
                foreach ($fotoTersimpan as $oldPath) {
                    if (!in_array($oldPath, $keptOld, true) && !preg_match('/^https?:\\/\\//i', $oldPath) && !str_contains($oldPath, '..')) {
                        Storage::disk('public')->delete($oldPath);
                    }
                }
            }

            $newPhotos = $this->storeActivityPhotoList($request);
            if ($request->hasFile('foto_aktivitas')) {
                $singleNew = $this->storeActivityPhoto($request);
                if ($singleNew && !in_array($singleNew, $newPhotos, true)) {
                    $newPhotos[] = $singleNew;
                }
            }

            if ($keptOld !== null || !empty($newPhotos)) {
                $baseKept = $keptOld ?? $fotoTersimpan;
                $finalPhotos = array_values(array_unique(array_merge($baseKept, $newPhotos)));
                $activity->foto_aktivitas_list = !empty($finalPhotos) ? $finalPhotos : null;
                $activity->foto_aktivitas = $finalPhotos[0] ?? null;
            }

            $activity->save();
            $activity->load(['lahan.komoditas', 'pengguna', 'siklusTanam']);

            AuditLogger::log('aktivitas.ubah', 'Anggota memperbarui aktivitas ' . $activity->judul, 'AktivitasTanam', $activity->id_aktivitas, $activity->id_kelompok, $request);
            return response()->json(['success' => true, 'message' => 'Aktivitas tanam berhasil diperbarui.', 'data' => new AktivitasResource($activity)]);
        } catch (Throwable $exception) {
            Log::error('Gagal memperbarui aktivitas tanam.', ['exception' => $exception]);
            return response()->json(['success' => false, 'message' => 'Terjadi kesalahan sistem saat memperbarui aktivitas.'], 500);
        }
    }

    public function destroy(Request $request, string $id): JsonResponse
    {
        $user = $request->user();
        abort_unless($user && $user->role === 'Anggota' && $user->status === 'Aktif' && $user->anggota?->status_keanggotaan === 'Aktif', 403);
        $activity = AktivitasTanam::where('id_pengguna', $user->id_pengguna)
            ->where('id_kelompok', $user->id_kelompok)
            ->findOrFail($id);

        if ($activity->id_siklus !== null) {
            $siklus = \App\Models\SiklusTanam::find($activity->id_siklus);
            if ($siklus && ($siklus->isMenungguVerifikasiPengakhiran() || in_array($siklus->status, \App\Models\SiklusTanam::STATUSES_TERMINAL, true))) {
                return response()->json([
                    'success' => false,
                    'message' => 'Aktivitas tanam tidak dapat dihapus karena siklus tanam sedang dalam proses pengakhiran atau telah berakhir.',
                ], 422);
            }
        }

        $activity->delete();

        AuditLogger::log('aktivitas.hapus', 'Anggota menghapus aktivitas ' . $activity->judul, 'AktivitasTanam', $activity->id_aktivitas, $activity->id_kelompok, $request);

        return response()->json(['success' => true, 'message' => 'Aktivitas tanam berhasil dihapus.', 'data' => null]);
    }

    private function storeActivityPhotoList(Request $request): array
    {
        if (!$request->hasFile('foto_aktivitas_list')) {
            return [];
        }

        $files = $request->file('foto_aktivitas_list');
        if (!is_array($files)) {
            $files = [$files];
        }

        $paths = [];
        foreach ($files as $file) {
            if ($file && $file->isValid()) {
                $ext = strtolower((string) $file->getClientOriginalExtension());
                if ($ext === '') {
                    $ext = strtolower((string) $file->extension());
                }
                $ext = $ext !== '' ? $ext : 'jpg';
                $filename = 'aktivitas_' . now()->format('Ymd_His') . '_' . uniqid() . '.' . $ext;
                $path = $file->storeAs('aktivitas', $filename, 'public');
                if ($path) {
                    $paths[] = $path;
                }
            }
        }
        return $paths;
    }

    private function storeActivityPhoto(Request $request): ?string
    {
        if ($request->hasFile('foto_aktivitas')) {
            return $request->file('foto_aktivitas')->store('aktivitas', 'public');
        }

        $value = $request->input('foto_aktivitas');
        if (is_string($value) && filter_var($value, FILTER_VALIDATE_URL) && str_starts_with($value, 'https://')) {
            return $value;
        }
        if (is_string($value) && str_starts_with($value, 'aktivitas/') && !str_contains($value, '..')) {
            return $value;
        }
        return null;
    }

    private function resolveActivityPhoto(Request $request): ?string
    {
        return $this->storeActivityPhoto($request);
    }
}
