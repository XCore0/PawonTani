<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Http\Requests\Api\StoreLahanRequest;
use App\Http\Requests\Api\UpdateLahanRequest;
use App\Http\Resources\LahanResource;
use App\Services\LahanService;
use DomainException;
use Illuminate\Auth\Access\AuthorizationException;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use InvalidArgumentException;
use Throwable;

class LahanController extends Controller
{
    public function __construct(
        protected LahanService $lahanService
    ) {}

    /**
     * Mengambil daftar lahan terpaginasi milik Anggota yang sedang login.
     */
    public function index(Request $request): JsonResponse
    {
        $user = $request->user();

        // 1. Validasi otorisasi: API Mobile Lahan hanya diperuntukkan bagi Anggota aktif
        if (!$user || $user->status !== 'Aktif' || $user->role !== 'Anggota') {
            return response()->json([
                'success' => false,
                'message' => 'Akses ditolak. Layanan ini hanya diperuntukkan bagi Anggota aktif.',
            ], 403);
        }

        // 2. Query Scoping berbasis konteks aktor (hanya lahan milik anggota aktif yang bersangkutan)
        $query = $this->lahanService->getLahanQueryForUser($user)
            ->with([
                'komoditas:id_komoditas,nama_komoditas,kategori',
                'verifikator:id_pengguna,nama,jabatan',
            ]);

        // 2. Filter opsional berdasarkan status verifikasi
        if ($status = $request->query('status_verifikasi')) {
            $query->where('status_verifikasi', $status);
        }

        // 3. Filter opsional berdasarkan komoditas
        if ($komoditasId = $request->query('id_komoditas')) {
            $query->where('id_komoditas', $komoditasId);
        }

        // 4. Pencarian teks fleksibel (case-insensitive) pada nama dan alamat lahan
        if ($search = $request->query('q')) {
            $likeOperator = DB::getDriverName() === 'pgsql' ? 'ILIKE' : 'LIKE';
            $query->where(function ($q) use ($search, $likeOperator) {
                $q->where('nama_lahan', $likeOperator, "%{$search}%")
                  ->orWhere('alamat_lahan', $likeOperator, "%{$search}%");
            });
        }

        // 5. Paginasi: default 10, batas minimum 1, batas maksimum 50
        $perPage = (int) $request->query('per_page', 10);
        if ($perPage < 1) {
            $perPage = 10;
        } elseif ($perPage > 50) {
            $perPage = 50;
        }

        $lahans = $query->orderByDesc('created_at')->paginate($perPage);

        return response()->json([
            'success' => true,
            'message' => 'Daftar lahan berhasil diambil.',
            'data' => LahanResource::collection($lahans),
            'meta' => [
                'current_page' => $lahans->currentPage(),
                'last_page' => $lahans->lastPage(),
                'per_page' => $lahans->perPage(),
                'total' => $lahans->total(),
            ],
            'links' => [
                'first' => $lahans->url(1),
                'last' => $lahans->url($lahans->lastPage()),
                'prev' => $lahans->previousPageUrl(),
                'next' => $lahans->nextPageUrl(),
            ],
        ]);
    }

    /**
     * Mengambil detail lengkap lahan beserta riwayat verifikasinya.
     */
    public function show(Request $request, string $id): JsonResponse
    {
        $user = $request->user();

        // 1. Validasi otorisasi: API Mobile Lahan hanya diperuntukkan bagi Anggota aktif
        if (!$user || $user->status !== 'Aktif' || $user->role !== 'Anggota') {
            return response()->json([
                'success' => false,
                'message' => 'Akses ditolak. Layanan ini hanya diperuntukkan bagi Anggota aktif.',
            ], 403);
        }

        try {
            $lahan = $this->lahanService->findLahanForUser($user, $id)
                ->load([
                    'komoditas',
                    'kelompokTani:id_kelompok,nama_kelompok',
                    'verifikator:id_pengguna,nama,jabatan',
                    'riwayatVerifikasi.pengguna:id_pengguna,nama,role,jabatan',
                ]);

            return response()->json([
                'success' => true,
                'message' => 'Detail lahan berhasil diambil.',
                'data' => new LahanResource($lahan),
            ]);
        } catch (ModelNotFoundException) {
            return response()->json([
                'success' => false,
                'message' => 'Data lahan tidak ditemukan atau Anda tidak memiliki akses.',
            ], 404);
        }
    }

    /**
     * Mendaftarkan lahan baru oleh Anggota aktif.
     */
    public function store(StoreLahanRequest $request): JsonResponse
    {
        try {
            $lahan = $this->lahanService->createLahan(
                $request->user(),
                $request->validated(),
                $request->file('foto_lahan')
            );

            return response()->json([
                'success' => true,
                'message' => 'Lahan berhasil didaftarkan dan menunggu proses verifikasi.',
                'data' => new LahanResource($lahan->load('komoditas')),
            ], 201);
        } catch (AuthorizationException $e) {
            return response()->json([
                'success' => false,
                'message' => $e->getMessage(),
            ], 403);
        } catch (Throwable $e) {
            Log::error('Gagal mendaftarkan lahan di LahanController@store: ' . $e->getMessage(), [
                'user_id' => $request->user()?->id_pengguna,
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Terjadi kesalahan sistem saat mendaftarkan lahan. Silakan coba beberapa saat lagi.',
            ], 500);
        }
    }

    /**
     * Memperbarui data lahan atau mengajukan ulang perbaikan oleh Anggota pemilik.
     */
    public function update(UpdateLahanRequest $request, string $id): JsonResponse
    {
        try {
            $lahan = $this->lahanService->updateLahan(
                $request->user(),
                $id,
                $request->validated(),
                $request->file('foto_lahan')
            );

            return response()->json([
                'success' => true,
                'message' => 'Data lahan berhasil diperbarui.',
                'data' => new LahanResource($lahan->load('komoditas')),
            ]);
        } catch (ModelNotFoundException) {
            return response()->json([
                'success' => false,
                'message' => 'Data lahan tidak ditemukan atau Anda tidak memiliki akses.',
            ], 404);
        } catch (AuthorizationException $e) {
            return response()->json([
                'success' => false,
                'message' => $e->getMessage(),
            ], 403);
        } catch (DomainException | InvalidArgumentException $e) {
            return response()->json([
                'success' => false,
                'message' => $e->getMessage(),
            ], 422);
        } catch (Throwable $e) {
            Log::error('Gagal memperbarui lahan di LahanController@update: ' . $e->getMessage(), [
                'id_lahan' => $id,
                'user_id' => $request->user()?->id_pengguna,
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Terjadi kesalahan sistem saat memperbarui lahan. Silakan coba beberapa saat lagi.',
            ], 500);
        }
    }

    /**
     * Menghapus lahan (Soft Delete) oleh Anggota pemilik.
     * Lahan terverifikasi dilarang keras untuk dihapus.
     */
    public function destroy(Request $request, string $id): JsonResponse
    {
        try {
            $this->lahanService->deleteLahan($request->user(), $id);

            return response()->json([
                'success' => true,
                'message' => 'Lahan berhasil dihapus.',
            ]);
        } catch (ModelNotFoundException) {
            return response()->json([
                'success' => false,
                'message' => 'Data lahan tidak ditemukan atau Anda tidak memiliki akses.',
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
        } catch (Throwable $e) {
            Log::error('Gagal menghapus lahan di LahanController@destroy: ' . $e->getMessage(), [
                'id_lahan' => $id,
                'user_id' => $request->user()?->id_pengguna,
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Terjadi kesalahan sistem saat menghapus lahan. Silakan coba beberapa saat lagi.',
            ], 500);
        }
    }
}

