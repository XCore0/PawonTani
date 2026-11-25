<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Http\Requests\Api\StoreSiklusTanamRequest;
use App\Http\Resources\SiklusTanamResource;
use App\Models\SiklusTanam;
use App\Services\AuditLogger;
use App\Services\SiklusTanamService;
use DomainException;
use Illuminate\Auth\Access\AuthorizationException;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Database\QueryException;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;
use Symfony\Component\HttpFoundation\Response;
use Throwable;

class SiklusTanamController extends Controller
{
    public function __construct(
        protected SiklusTanamService $siklusService
    ) {}

    /**
     * Menampilkan daftar siklus tanam terpaginasi.
     * Opsional menyaring berdasarkan id_lahan dan status.
     */
    public function index(Request $request, ?string $lahanId = null): JsonResponse
    {
        $user = $request->user();

        if (!$user || $user->status !== 'Aktif') {
            return response()->json([
                'success' => false,
                'message' => 'Akses ditolak. Pengguna tidak aktif.',
            ], 403);
        }

        $idLahan = $lahanId ?? $request->input('id_lahan') ?? $request->query('id_lahan');
        $status = $request->input('status') ?? $request->query('status');

        $query = $this->siklusService->getSiklusQueryForUser($user, $idLahan)
            ->with([
                'komoditas:id_komoditas,nama_komoditas,kategori',
                'kelompokTani:id_kelompok,nama_kelompok',
                'pengguna:id_pengguna,nama,role',
                'lahan:id_lahan,nama_lahan,alamat_lahan,luas,status_verifikasi,status_operasional',
            ]);

        if ($status) {
            $query->where('status', $status);
        }

        $perPage = (int) ($request->input('per_page') ?? $request->query('per_page', 10));
        if ($perPage < 1) {
            $perPage = 10;
        } elseif ($perPage > 50) {
            $perPage = 50;
        }

        $siklusList = $query->orderByDesc('tanggal_mulai')
                            ->orderByDesc('created_at')
                            ->paginate($perPage);

        return response()->json([
            'success' => true,
            'message' => 'Daftar siklus tanam berhasil diambil.',
            'data' => SiklusTanamResource::collection($siklusList),
            'meta' => [
                'current_page' => $siklusList->currentPage(),
                'last_page' => $siklusList->lastPage(),
                'per_page' => $siklusList->perPage(),
                'total' => $siklusList->total(),
            ],
            'links' => [
                'first' => $siklusList->url(1),
                'last' => $siklusList->url($siklusList->lastPage()),
                'prev' => $siklusList->previousPageUrl(),
                'next' => $siklusList->nextPageUrl(),
            ],
        ]);
    }

    /**
     * Mengambil riwayat siklus tanam untuk lahan tertentu.
     */
    public function indexByLahan(Request $request, string $idLahan): JsonResponse
    {
        return $this->index($request, $idLahan);
    }

    /**
     * Mengambil siklus berjalan pada suatu lahan.
     */
    public function berjalan(Request $request, string $idLahan): JsonResponse
    {
        $user = $request->user();

        if (!$user || $user->status !== 'Aktif') {
            return response()->json([
                'success' => false,
                'message' => 'Akses ditolak. Pengguna tidak aktif.',
            ], 403);
        }

        $siklus = $this->siklusService->getSiklusBerjalanForLahan($user, $idLahan);

        if (!$siklus) {
            return response()->json([
                'success' => true,
                'message' => 'Tidak ada siklus tanam yang sedang berjalan pada lahan ini.',
                'data' => null,
            ]);
        }

        return response()->json([
            'success' => true,
            'message' => 'Siklus tanam berjalan berhasil diambil.',
            'data' => new SiklusTanamResource($siklus),
        ]);
    }

    /**
     * Mengambil detail satu siklus tanam berdasarkan ID.
     */
    public function show(Request $request, string $id): JsonResponse
    {
        $user = $request->user();

        if (!$user || $user->status !== 'Aktif') {
            return response()->json([
                'success' => false,
                'message' => 'Akses ditolak. Pengguna tidak aktif.',
            ], 403);
        }

        try {
            $siklus = $this->siklusService->findSiklusForUser($user, $id)
                ->load([
                    'komoditas',
                    'kelompokTani:id_kelompok,nama_kelompok',
                    'pengguna:id_pengguna,nama,role',
                    'lahan:id_lahan,nama_lahan,alamat_lahan,luas,status_verifikasi,status_operasional',
                    'riwayatPengakhiran',
                ]);

            return response()->json([
                'success' => true,
                'message' => 'Detail siklus tanam berhasil diambil.',
                'data' => new SiklusTanamResource($siklus),
            ]);
        } catch (ModelNotFoundException) {
            return response()->json([
                'success' => false,
                'message' => 'Data siklus tanam tidak ditemukan atau Anda tidak memiliki akses.',
            ], 404);
        }
    }

    /**
     * Membuat Siklus Tanam baru secara manual oleh Anggota pemilik Lahan.
     */
    public function store(StoreSiklusTanamRequest $request): JsonResponse
    {
        try {
            $siklus = $this->siklusService->createSiklus(
                $request->user(),
                $request->validated()
            );

            AuditLogger::log(
                'siklus_tanam.dibuat',
                'Anggota membuat siklus tanam baru pada lahan.',
                'SiklusTanam',
                $siklus->id_siklus,
                $siklus->id_kelompok,
                $request
            );

            return response()->json([
                'success' => true,
                'message' => 'Siklus tanam berhasil dimulai.',
                'data' => new SiklusTanamResource($siklus),
            ], 201);
        } catch (ModelNotFoundException $e) {
            return response()->json([
                'success' => false,
                'message' => $e->getMessage() ?: 'Data tidak ditemukan.',
            ], 404);
        } catch (AuthorizationException $e) {
            return response()->json([
                'success' => false,
                'message' => $e->getMessage(),
            ], 403);
        } catch (DomainException $e) {
            return response()->json([
                'success' => false,
                'message' => $e->getMessage(),
            ], 422);
        } catch (QueryException $e) {
            // Tangkap pelanggaran unique constraint concurrent creation (SQLSTATE 23505)
            if (in_array((string) $e->getCode(), ['23505', '23000'], true) || str_contains($e->getMessage(), 'idx_unique_running_cycle')) {
                return response()->json([
                    'success' => false,
                    'message' => 'Lahan ini sudah memiliki siklus tanam yang sedang berjalan.',
                ], 422);
            }

            Log::error('Query error saat membuat Siklus Tanam: ' . $e->getMessage(), [
                'user_id' => $request->user()?->id_pengguna,
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Terjadi kesalahan basis data saat membuat siklus tanam.',
            ], 500);
        } catch (Throwable $e) {
            Log::error('Kesalahan sistem saat membuat Siklus Tanam: ' . $e->getMessage(), [
                'user_id' => $request->user()?->id_pengguna,
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Terjadi kesalahan sistem saat membuat siklus tanam. Silakan coba beberapa saat lagi.',
            ], 500);
        }
    }

    /**
     * Mengajukan pengakhiran berhasil (status -> 'menunggu_selesai') oleh Anggota pemilik.
     */
    public function ajukanSelesai(Request $request, string $id): JsonResponse
    {
        $user = $request->user();
        if (!$user || $user->status !== 'Aktif') {
            return response()->json(['success' => false, 'message' => 'Akses ditolak. Pengguna tidak aktif.'], 403);
        }

        $request->validate([
            'alasan' => ['required', 'string', 'max:1000'],
            'foto_bukti' => ['nullable', 'file', 'image', 'max:5120'],
        ], [
            'alasan.required' => 'Alasan pengakhiran siklus tanam wajib diisi.',
            'foto_bukti.image' => 'Berkas bukti harus berupa gambar.',
            'foto_bukti.max' => 'Ukuran berkas bukti maksimal 5MB.',
        ]);

        try {
            $siklus = $this->siklusService->ajukanPengakhiranSelesai(
                $user,
                $id,
                $request->only('alasan'),
                $request->file('foto_bukti')
            );

            AuditLogger::log(
                'siklus_tanam.pengajuan_selesai',
                "Mengajukan pengakhiran berhasil untuk siklus tanam {$siklus->id_siklus}.",
                'SiklusTanam',
                $siklus->id_siklus,
                $siklus->id_kelompok,
                $request
            );

            return response()->json([
                'success' => true,
                'message' => 'Pengajuan pengakhiran siklus tanam berhasil dikirim dan menunggu persetujuan Ketua.',
                'data' => new SiklusTanamResource($siklus),
            ]);
        } catch (ModelNotFoundException $e) {
            return response()->json(['success' => false, 'message' => $e->getMessage() ?: 'Data tidak ditemukan.'], 404);
        } catch (AuthorizationException $e) {
            return response()->json(['success' => false, 'message' => $e->getMessage()], 403);
        } catch (DomainException $e) {
            return response()->json(['success' => false, 'message' => $e->getMessage()], 422);
        } catch (Throwable $e) {
            Log::error('Error saat mengajukan pengakhiran selesai: ' . $e->getMessage(), ['id' => $id]);
            return response()->json(['success' => false, 'message' => 'Terjadi kesalahan sistem saat memproses pengajuan.'], 500);
        }
    }

    /**
     * Mengajukan pengakhiran gagal (status -> 'menunggu_gagal') oleh Anggota pemilik.
     */
    public function ajukanGagal(Request $request, string $id): JsonResponse
    {
        $user = $request->user();
        if (!$user || $user->status !== 'Aktif') {
            return response()->json(['success' => false, 'message' => 'Akses ditolak. Pengguna tidak aktif.'], 403);
        }

        $request->validate([
            'alasan' => ['required', 'string', 'max:1000'],
            'foto_bukti' => ['required', 'file', 'image', 'max:5120'],
        ], [
            'alasan.required' => 'Alasan kegagalan panen wajib diisi.',
            'foto_bukti.required' => 'Foto bukti kegagalan panen wajib dilampirkan.',
            'foto_bukti.image' => 'Berkas bukti harus berupa gambar.',
            'foto_bukti.max' => 'Ukuran berkas bukti maksimal 5MB.',
        ]);

        try {
            $siklus = $this->siklusService->ajukanPengakhiranGagal(
                $user,
                $id,
                $request->only('alasan'),
                $request->file('foto_bukti')
            );

            AuditLogger::log(
                'siklus_tanam.pengajuan_gagal',
                "Mengajukan pengakhiran gagal untuk siklus tanam {$siklus->id_siklus}.",
                'SiklusTanam',
                $siklus->id_siklus,
                $siklus->id_kelompok,
                $request
            );

            return response()->json([
                'success' => true,
                'message' => 'Pengajuan pengakhiran gagal berhasil dikirim dan menunggu persetujuan Ketua.',
                'data' => new SiklusTanamResource($siklus),
            ]);
        } catch (ModelNotFoundException $e) {
            return response()->json(['success' => false, 'message' => $e->getMessage() ?: 'Data tidak ditemukan.'], 404);
        } catch (AuthorizationException $e) {
            return response()->json(['success' => false, 'message' => $e->getMessage()], 403);
        } catch (DomainException $e) {
            return response()->json(['success' => false, 'message' => $e->getMessage()], 422);
        } catch (Throwable $e) {
            Log::error('Error saat mengajukan pengakhiran gagal: ' . $e->getMessage(), ['id' => $id]);
            return response()->json(['success' => false, 'message' => 'Terjadi kesalahan sistem saat memproses pengajuan.'], 500);
        }
    }

    /**
     * Membatalkan siklus tanam secara instan oleh Anggota pemilik (status -> 'dibatalkan').
     */
    public function batalkan(Request $request, string $id): JsonResponse
    {
        $user = $request->user();
        if (!$user || $user->status !== 'Aktif') {
            return response()->json(['success' => false, 'message' => 'Akses ditolak. Pengguna tidak aktif.'], 403);
        }

        $request->validate([
            'alasan' => ['required', 'string', 'max:1000'],
        ], [
            'alasan.required' => 'Alasan pembatalan siklus tanam wajib diisi.',
        ]);

        try {
            $siklus = $this->siklusService->batalkanSiklus(
                $user,
                $id,
                $request->only('alasan')
            );

            AuditLogger::log(
                'siklus_tanam.dibatalkan',
                "Membatalkan siklus tanam {$siklus->id_siklus}.",
                'SiklusTanam',
                $siklus->id_siklus,
                $siklus->id_kelompok,
                $request
            );

            return response()->json([
                'success' => true,
                'message' => 'Siklus tanam berhasil dibatalkan. Status lahan kini kembali istirahat.',
                'data' => new SiklusTanamResource($siklus),
            ]);
        } catch (ModelNotFoundException $e) {
            return response()->json(['success' => false, 'message' => $e->getMessage() ?: 'Data tidak ditemukan.'], 404);
        } catch (AuthorizationException $e) {
            return response()->json(['success' => false, 'message' => $e->getMessage()], 403);
        } catch (DomainException $e) {
            return response()->json(['success' => false, 'message' => $e->getMessage()], 422);
        } catch (Throwable $e) {
            Log::error('Error saat membatalkan siklus: ' . $e->getMessage(), ['id' => $id]);
            return response()->json(['success' => false, 'message' => 'Terjadi kesalahan sistem saat membatalkan siklus.'], 500);
        }
    }

    /**
     * Verifikasi keputusan pengakhiran oleh Ketua/Pengurus Kelompok.
     */
    public function verifikasiPengakhiran(Request $request, string $id): JsonResponse
    {
        $user = $request->user();
        if (!$user || $user->status !== 'Aktif') {
            return response()->json(['success' => false, 'message' => 'Akses ditolak. Pengguna tidak aktif.'], 403);
        }

        $request->validate([
            'keputusan' => ['required', 'string', 'in:disetujui,ditolak'],
            'catatan' => ['nullable', 'string', 'max:1000'],
        ], [
            'keputusan.required' => 'Keputusan verifikasi wajib ditentukan.',
            'keputusan.in' => 'Keputusan verifikasi harus berupa disetujui atau ditolak.',
        ]);

        try {
            $siklus = $this->siklusService->verifikasiPengakhiran(
                $user,
                $id,
                $request->only(['keputusan', 'catatan'])
            );

            $keputusan = $request->input('keputusan');
            AuditLogger::log(
                'siklus_tanam.diverifikasi',
                "Ketua memverifikasi pengakhiran siklus {$siklus->id_siklus} dengan keputusan '{$keputusan}'.",
                'SiklusTanam',
                $siklus->id_siklus,
                $siklus->id_kelompok,
                $request
            );

            return response()->json([
                'success' => true,
                'message' => "Pengakhiran siklus tanam ({$siklus->id_siklus}) berhasil diproses ({$keputusan}).",
                'data' => new SiklusTanamResource($siklus),
            ]);
        } catch (ModelNotFoundException $e) {
            return response()->json(['success' => false, 'message' => $e->getMessage() ?: 'Data tidak ditemukan.'], 404);
        } catch (AuthorizationException $e) {
            return response()->json(['success' => false, 'message' => $e->getMessage()], 403);
        } catch (DomainException $e) {
            return response()->json(['success' => false, 'message' => $e->getMessage()], 422);
        } catch (Throwable $e) {
            Log::error('Error saat verifikasi pengakhiran: ' . $e->getMessage(), ['id' => $id]);
            return response()->json(['success' => false, 'message' => 'Terjadi kesalahan sistem saat memproses verifikasi.'], 500);
        }
    }

    /**
     * Mengakses foto bukti kegagalan panen secara terproteksi.
     * Hanya pemilik siklus dan Pengurus (Ketua) dari kelompok yang sama yang berhak mengakses.
     */
    public function viewBuktiGagal(Request $request, string $id): Response
    {
        $user = $request->user();
        if (!$user || $user->status !== 'Aktif') {
            return response()->json(['success' => false, 'message' => 'Akses ditolak.'], 403);
        }

        $siklus = SiklusTanam::where('id_siklus', $id)->first();
        if (!$siklus) {
            return response()->json(['success' => false, 'message' => 'Siklus tanam tidak ditemukan.'], 404);
        }

        // Policy-level authorization check
        Gate::forUser($user)->authorize('viewBuktiGagal', $siklus);

        if (empty($siklus->foto_bukti_gagal)) {
            return response()->json(['success' => false, 'message' => 'Siklus ini tidak memiliki foto bukti kegagalan.'], 404);
        }

        // Resolusi file: prioritaskan private disk 'documents', fallback ke 'public' untuk file lama
        $disk = 'documents';
        $path = $siklus->foto_bukti_gagal;
        if (!Storage::disk($disk)->exists($path)) {
            if (Storage::disk('public')->exists($path)) {
                $disk = 'public';
            } else {
                return response()->json(['success' => false, 'message' => 'Berkas bukti fisik tidak ditemukan pada server.'], 404);
            }
        }

        $fullPath = Storage::disk($disk)->path($path);
        $mime = Storage::disk($disk)->mimeType($path) ?: 'image/jpeg';

        AuditLogger::log(
            'siklus_tanam.view_bukti_gagal',
            "Mengakses foto bukti kegagalan untuk siklus tanam {$siklus->id_siklus}.",
            'SiklusTanam',
            $siklus->id_siklus,
            $siklus->id_kelompok,
            $request
        );

        return response()->file($fullPath, [
            'Content-Type' => $mime,
            'Cache-Control' => 'private, no-cache, no-store, must-revalidate',
            'X-Content-Type-Options' => 'nosniff',
        ]);
    }
}
