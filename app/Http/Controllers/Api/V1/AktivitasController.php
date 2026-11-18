<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Http\Requests\Api\StoreAktivitasRequest;
use App\Http\Requests\Api\UpdateAktivitasRequest;
use App\Http\Resources\AktivitasResource;
use App\Models\AktivitasTanam;
use App\Models\Lahan;
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

        $query = AktivitasTanam::with(['lahan.komoditas', 'pengguna'])
            ->where('id_pengguna', $user->id_pengguna)
            ->where('id_kelompok', $user->id_kelompok);

        if ($lahanId = ($request->query('id_lahan') ?? $request->query('lahan_id'))) {
            $query->where('id_lahan', $lahanId);
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
            $photoPath = $this->storeActivityPhoto($request);
            $category = strtolower($request->input('kategori'));
            $condition = match (strtolower((string) $request->input('kondisi_tanaman', 'sehat'))) {
                'sehat' => 'sehat',
                'perluperhatian', 'perlu_perhatian' => 'perluPerhatian',
                'terseranghama', 'terserang_hama' => 'terserangHama',
                default => 'sehat',
            };

            $activity = new AktivitasTanam();
            $activity->id_lahan = $land->id_lahan;
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
            $activity->biaya = (float) $request->input('biaya', 0);
            $activity->saveWithUniqueRetry();
            $activity->load(['lahan.komoditas', 'pengguna']);

            AuditLogger::log('aktivitas.tambah', 'Anggota menambahkan aktivitas ' . $activity->judul, 'AktivitasTanam', $activity->id_aktivitas, $land->id_kelompok, $request);

            return response()->json(['success' => true, 'message' => 'Aktivitas tanam berhasil dicatat.', 'data' => new AktivitasResource($activity)], 201);
        } catch (Throwable $exception) {
            Log::error('Gagal menyimpan aktivitas tanam.', ['exception' => $exception]);
            return response()->json(['success' => false, 'message' => 'Terjadi kesalahan sistem saat menyimpan aktivitas.'], 500);
        }
    }

    public function show(Request $request, string $id): JsonResponse
    {
        $user = $request->user();
        abort_unless($user && $user->role === 'Anggota' && $user->status === 'Aktif' && $user->anggota?->status_keanggotaan === 'Aktif', 403);

        $activity = AktivitasTanam::with(['lahan.komoditas', 'pengguna'])
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

            if ($request->hasFile('foto_aktivitas') || $request->filled('foto_aktivitas')) {
                $old = $activity->foto_aktivitas;
                $activity->foto_aktivitas = $this->resolveActivityPhoto($request);
                if ($old && !preg_match('/^https?:\\/\\//i', $old) && !str_contains($old, '..')) {
                    Storage::disk('public')->delete($old);
                }
            }
            $activity->save();
            $activity->load(['lahan.komoditas', 'pengguna']);

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
        $activity->delete();

        AuditLogger::log('aktivitas.hapus', 'Anggota menghapus aktivitas ' . $activity->judul, 'AktivitasTanam', $activity->id_aktivitas, $activity->id_kelompok, $request);

        return response()->json(['success' => true, 'message' => 'Aktivitas tanam berhasil dihapus.', 'data' => null]);
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
